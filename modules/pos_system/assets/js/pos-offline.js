/**
 * POS Offline Manager — IndexedDB + Network Sync
 *
 * Architecture:
 *   - Uses IndexedDB (via idb-keyval pattern) for local storage
 *   - Detects online/offline state via navigator.onLine + fetch test
 *   - Queues sales, payments, and stock movements when offline
 *   - Syncs on reconnect using idempotent sale_uid as dedup key
 *   - Exposes Vue composable: useOfflineManager()
 */

const DB_NAME    = 'pos_offline_db';
const DB_VERSION = 2;

// ─── IndexedDB wrapper ────────────────────────────────────────────────────────

class PosDB {
    constructor() {
        this._db = null;
    }

    async open() {
        if (this._db) return this._db;

        return new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, DB_VERSION);

            req.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Pending sales queue
                if (!db.objectStoreNames.contains('pending_sales')) {
                    const store = db.createObjectStore('pending_sales', { keyPath: 'sale_uid' });
                    store.createIndex('by_created',  'created_at', { unique: false });
                    store.createIndex('by_synced',   'synced',     { unique: false });
                    store.createIndex('by_branch',   'branch_id',  { unique: false });
                }

                // Product catalog cache
                if (!db.objectStoreNames.contains('product_cache')) {
                    const ps = db.createObjectStore('product_cache', { keyPath: 'branch_id' });
                }

                // Customer cache
                if (!db.objectStoreNames.contains('customer_cache')) {
                    db.createObjectStore('customer_cache', { keyPath: 'id' });
                }

                // Sync log
                if (!db.objectStoreNames.contains('sync_log')) {
                    const sl = db.createObjectStore('sync_log', { keyPath: 'id', autoIncrement: true });
                    sl.createIndex('by_date', 'synced_at', { unique: false });
                }
            };

            req.onsuccess = (e) => {
                this._db = e.target.result;
                resolve(this._db);
            };

            req.onerror = () => reject(req.error);
        });
    }

    async _tx(storeName, mode, fn) {
        const db    = await this.open();
        const tx    = db.transaction(storeName, mode);
        const store = tx.objectStore(storeName);
        return new Promise((resolve, reject) => {
            const req = fn(store);
            req.onsuccess = () => resolve(req.result);
            req.onerror   = () => reject(req.error);
        });
    }

    // Pending sales
    async savePendingSale(payload) {
        payload.created_at = new Date().toISOString();
        payload.synced     = 0;
        return this._tx('pending_sales', 'readwrite', s => s.put(payload));
    }

    async getPendingSales(branch_id = null) {
        const db    = await this.open();
        const store = db.transaction('pending_sales', 'readonly').objectStore('pending_sales');
        return new Promise((resolve, reject) => {
            const items = [];
            const cur   = store.openCursor();
            cur.onsuccess = (e) => {
                const cursor = e.target.result;
                if (!cursor) {
                    resolve(items);
                    return;
                }
                const v = cursor.value;
                if (!v.synced && (!branch_id || v.branch_id === branch_id)) {
                    items.push(v);
                }
                cursor.continue();
            };
            cur.onerror = () => reject(cur.error);
        });
    }

    async markSaleSynced(sale_uid) {
        return this._tx('pending_sales', 'readwrite', s => {
            const getReq = s.get(sale_uid);
            getReq.onsuccess = () => {
                if (getReq.result) {
                    getReq.result.synced = 1;
                    s.put(getReq.result);
                }
            };
            return getReq;
        });
    }

    async deleteSyncedSales() {
        const db    = await this.open();
        const store = db.transaction('pending_sales', 'readwrite').objectStore('pending_sales');
        return new Promise((resolve) => {
            const cur = store.openCursor();
            cur.onsuccess = (e) => {
                const cursor = e.target.result;
                if (!cursor) { resolve(); return; }
                if (cursor.value.synced) cursor.delete();
                cursor.continue();
            };
        });
    }

    // Product cache
    async cacheProducts(branch_id, products, categories) {
        return this._tx('product_cache', 'readwrite', s => s.put({
            branch_id,
            products,
            categories,
            cached_at: Date.now(),
        }));
    }

    async getCachedProducts(branch_id) {
        const row = await this._tx('product_cache', 'readonly', s => s.get(branch_id));
        if (!row) return null;
        // Invalidate cache older than 30 minutes
        if (Date.now() - row.cached_at > 30 * 60 * 1000) return null;
        return row;
    }

    // Customer cache
    async cacheCustomers(customers) {
        const db    = await this.open();
        const store = db.transaction('customer_cache', 'readwrite').objectStore('customer_cache');
        return new Promise((resolve) => {
            let count = 0;
            for (const c of customers) {
                const r = store.put(c);
                r.onsuccess = () => { if (++count === customers.length) resolve(); };
            }
            if (!customers.length) resolve();
        });
    }

    async searchCachedCustomers(query) {
        const db    = await this.open();
        const store = db.transaction('customer_cache', 'readonly').objectStore('customer_cache');
        const q     = query.toLowerCase();

        return new Promise((resolve) => {
            const results = [];
            const cur = store.openCursor();
            cur.onsuccess = (e) => {
                const cursor = e.target.result;
                if (!cursor) { resolve(results.slice(0, 10)); return; }
                const c = cursor.value;
                if ((c.name || '').toLowerCase().includes(q) || (c.phone || '').includes(q)) {
                    results.push(c);
                }
                cursor.continue();
            };
        });
    }

    async logSync(detail) {
        return this._tx('sync_log', 'readwrite', s => s.add({ ...detail, synced_at: new Date().toISOString() }));
    }
}

export const posDB = new PosDB();

// ─── Network detection ────────────────────────────────────────────────────────

async function checkOnline(api_url) {
    if (!navigator.onLine) return false;
    try {
        const resp = await fetch(`${api_url}/auth/me`, {
            method:  'HEAD',
            cache:   'no-store',
            signal:  AbortSignal.timeout(3000),
        });
        return resp.ok || resp.status === 401; // 401 means server is reachable
    } catch {
        return false;
    }
}

// ─── Vue composable ───────────────────────────────────────────────────────────

export function useOfflineManager(api, branch_id) {
    const { ref, onMounted, onUnmounted } = Vue;

    const is_online        = ref(navigator.onLine);
    const pending_count    = ref(0);
    const is_syncing       = ref(false);
    const last_sync        = ref(null);
    const sync_error       = ref(null);

    let interval_id = null;

    async function refreshPendingCount() {
        const items   = await posDB.getPendingSales(branch_id);
        pending_count.value = items.length;
    }

    /**
     * Queue a sale for offline storage. Called instead of API when offline.
     */
    async function queueSale(sale, items, payments) {
        const payload = {
            sale_uid:   sale.sale_uid,
            branch_id,
            sale,
            items,
            payments,
            created_at: new Date().toISOString(),
            synced:     0,
        };
        await posDB.savePendingSale(payload);
        await refreshPendingCount();

        return {
            success:        true,
            offline:        true,
            receipt_number: sale.sale_uid.substring(0, 8).toUpperCase(),
            sale_uid:       sale.sale_uid,
        };
    }

    /**
     * Sync all pending sales to the server.
     */
    async function syncPending() {
        if (is_syncing.value || !is_online.value) return;

        const pending = await posDB.getPendingSales(branch_id);
        if (!pending.length) return;

        is_syncing.value = true;
        sync_error.value = null;

        try {
            const resp = await api.post('/sales/sync', {
                sales: pending.map(p => ({
                    sale:     p.sale,
                    items:    p.items,
                    payments: p.payments,
                })),
            });

            const result = resp.data.data;

            for (const uid of (result.success || [])) {
                await posDB.markSaleSynced(uid);
            }

            if (result.failed?.length) {
                sync_error.value = `${result.failed.length} sale(s) failed to sync`;
            }

            await posDB.deleteSyncedSales();
            await posDB.logSync({ success: result.success?.length, failed: result.failed?.length });
            await refreshPendingCount();

            last_sync.value = new Date().toLocaleTimeString();

        } catch (e) {
            sync_error.value = 'Sync failed: ' + e.message;
        } finally {
            is_syncing.value = false;
        }
    }

    /**
     * Prefetch and cache product catalog for offline use.
     */
    async function prefetchProducts() {
        if (!is_online.value) return;
        try {
            const [p, c] = await Promise.all([
                api.get('/products/pos'),
                api.get('/products/categories'),
            ]);
            await posDB.cacheProducts(branch_id, p.data.data, c.data.data);
        } catch (e) {
            // Non-fatal
        }
    }

    /**
     * Load products: from API if online, from IndexedDB cache if offline.
     */
    async function loadProducts() {
        if (is_online.value) {
            const cached = await posDB.getCachedProducts(branch_id);
            if (cached) {
                prefetchProducts(); // Background refresh
                return cached;
            }
            const [p, c] = await Promise.all([
                api.get('/products/pos'),
                api.get('/products/categories'),
            ]);
            const data = { products: p.data.data, categories: c.data.data };
            await posDB.cacheProducts(branch_id, data.products, data.categories);
            return data;
        } else {
            return await posDB.getCachedProducts(branch_id) ?? { products: [], categories: [] };
        }
    }

    function _handle_online()  { is_online.value = true;  syncPending(); }
    function _handle_offline() { is_online.value = false; }

    onMounted(async () => {
        window.addEventListener('online',  _handle_online);
        window.addEventListener('offline', _handle_offline);

        is_online.value = await checkOnline(api.defaults.baseURL);
        await refreshPendingCount();

        // Auto-sync every 2 minutes when online
        interval_id = setInterval(async () => {
            is_online.value = await checkOnline(api.defaults.baseURL);
            if (is_online.value && pending_count.value > 0) {
                await syncPending();
            }
        }, 120_000);
    });

    onUnmounted(() => {
        window.removeEventListener('online',  _handle_online);
        window.removeEventListener('offline', _handle_offline);
        clearInterval(interval_id);
    });

    return {
        is_online,
        pending_count,
        is_syncing,
        last_sync,
        sync_error,
        queueSale,
        syncPending,
        prefetchProducts,
        loadProducts,
    };
}
