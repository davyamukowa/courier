# CLAUDE.md — `modules/courier_goshipping/`

This file documents the internal structure of this module for Claude Code (and any future
maintainer), specifically the boundary between **general Go Shipping courier operations**
and the **Salibay/Shopify integration**, since both share the same core database tables and
it is easy to accidentally couple them.

Read the root `CLAUDE.md` first — it documents the deploy pipeline (`sync-watch.ps1` →
GitHub → cPanel cron, `modules/*` only), the MX routing gotchas, the CSRF-exclusion gotcha,
and the "self-heal in the constructor" migration pattern. Everything below assumes that
context.

## The one rule

> **General Go Shipping courier operations (manual shipment creation, status updates, waybill
> printing, branch assignment) must keep working exactly as before, completely independently
> of whether a shipment happens to be Salibay-linked. Salibay-only behavior (route-tag branch
> resolution, sourcing-tag capture, customer tracking emails, the international leg) must
> never fire for, or block, a plain shipment that has no linked `shopify_orders` row.**

This was an explicit, repeated instruction from the project owner after Salibay-related work
in this module caused (or was suspected of causing) friction with plain courier operations.
Any change here should be checked against this rule before shipping.

## Change-safety protocol — never regress a feature that's already been closed out as working

The project owner has repeatedly flagged regressions where a change made for one feature
(e.g. the "Assign Agent / Staff" button) silently broke or altered unrelated, already-working
Salibay or general courier functionality (e.g. the rider assignment flow) because they share
the same view/controller/table. **Treat every already-shipped, confirmed-working flow in this
module as load-bearing** — do not refactor, rename, or "clean up" code near it as a side effect
of an unrelated task.

Before touching anything in `Shipments.php`, `Fulfilment.php`, `Shopify_connector*`,
`Salibay_delivery.php`, `Rider_api.php`/`Rider_app.php`, or their views:

1. **Scope the change narrowly.** Only touch the exact button/field/query the request is
   about. If a helper or query is shared by multiple features (e.g. `staff_members`,
   `courier_riders`, `push_shopify_fulfillment_status()`), changing its shape/behavior affects
   every caller — enumerate those callers first (`grep`) and check each one still gets what it
   expects.
2. **Don't merge or "simplify" two features into one code path** unless explicitly asked. The
   earlier bug in this exact area (one modal silently swapping between showing riders and
   showing agents/staff depending on `salibay_delivery_link`) was caused by exactly this kind
   of well-intentioned merge. Prefer two explicit, separate code paths/buttons/modals over one
   clever conditional one, even if it looks like duplication.
3. **After the change, re-check the general (non-Salibay) path still behaves exactly as
   before**, not just the Salibay path (or vice versa) — per "The one rule" above. A change
   that only gets tested against the flow it was written for is exactly how these regressions
   have slipped through previously.
4. **When in doubt about blast radius, ask or use a reviewing subagent** rather than guessing.
   For a change that touches a shared view/controller, it's worth spawning a second pass (a
   code-review agent, or `/code-review`) specifically checking "does this change alter the
   behavior of any feature other than the one requested?" before considering the task done.

## How to tell "Salibay shipment" from "general shipment" in code

A shipment is Salibay-sourced **if and only if** a row exists in `tblshopify_orders` with
`gs_shipment_id = <the shipment's id>`. There is no flag on `tbl_shipments` itself for this —
always check via that join/lookup, e.g.:

```php
$is_salibay = (bool) $this->db->where('gs_shipment_id', $shipment_id)
    ->get(db_prefix() . 'shopify_orders')->row();
```

Do this check **before** running any Salibay-only side effect (email, route-tag resolution,
international-leg logic). Never assume a code path is Salibay-only just because it lives in
this module or near Salibay code — the Rider app and the general `Shipments` controller are
both shared by both flows.

## Salibay Fulfilment staff permissions

`Fulfilment.php`'s constructor gates on `staff_can('view_salibay', 'courier-salibay')`
(registered in `courier_goshipping.php::create_permissions()`, shows as "Courier - Salibay
Fulfilment" in the staff permissions UI) — it used to gate on
`has_permission('courier_goshipping', '', 'view')`, a capability that was **never registered
anywhere**, meaning no non-admin staff could ever be granted access no matter what was checked
for them.

**Use the specific `can_manage_salibay_*()` methods, never the broad `can_manage_fulfilment()`,
to gate an individual action.** `can_manage_fulfilment()` is a flat OR across every manage-level
`courier-salibay` capability (settings, riders, logs, create-shipments) — it was originally used
to gate all ~23 write-actions, which meant a staffer granted ONLY "Manage Riders" could also
reach Settings, Webhooks, and Integration Health, since any one of those capabilities satisfied
the same umbrella check. That's a real bug a user hit and reported. The actions are now split:
- `can_manage_salibay_riders()` — riders(), link_rider(), toggle_rider_status(), reset_rider_password()
- `can_manage_salibay_settings()` — settings(), save_settings(), webhooks, route/product/location mapping
- `can_manage_salibay_logs()` — health(), webhook events/logs datatables, requeue/retry/clear/export
- `can_create_salibay_shipments()` — create_shipment()
- `can_manage_salibay_settings_or_logs()` — test_connection() and run_inventory_sync() specifically,
  since both are legitimately triggered from both the Settings page and the Integration Health page

`can_manage_fulfilment()` itself is kept only for `can_view_fulfilment()`'s "does this staffer
have ANY Salibay manage capability" check — appropriate there since having any manage permission
should imply base view access. `build_base_data()` passes ALL of these as separate flags to every
Fulfilment view (`can_manage_salibay_riders`, `_settings`, `_logs`, `can_create_salibay_shipments`,
`_settings_or_logs`) — **a view must condition a button/menu-link on the specific flag matching
what that action's controller method actually checks**, not a shared/broad one, or you get the
same bug again (a visible link/button that dead-ends at `access_denied()`/`ajax_access_denied()`
for a permission the user was clearly not granted — confusing "is the system broken?" UX, not just
a security nicety). This applies to `views/layout/_topnav.php`'s mega-menu columns too — each
column is gated by the flag matching its links' actual controller-side requirement, computed
inline near the menu (mirroring, not calling, the controller methods, since views don't have
access to `$this->can_manage_salibay_*()`).

## Instant "waybill created" customer emails — universal, fires on ALL three creation paths

The moment a shipment is created — general Go Shipping (`Shipments::store()`), Salibay manual
(`Fulfilment::create_courier_shipment()`), or Salibay webhook
(`Shopify_connector::create_courier_shipment()`) — two emails go out automatically, both
non-blocking (wrapped in try/catch, a send failure never stops shipment creation):

- `courier_send_shipment_waybill_email($shipment_id)` (`helpers/courier_helper.php`) → the
  **recipient**, the actual formatted waybill (waybill number as a clickable tracking link,
  sender/recipient/mode/status table, big red "Track Your Shipment" CTA). This is the exact
  same email as the manual "Send Waybill by Email" button
  (`Shipments::send_waybill_email()` — kept as a separate, independent implementation rather
  than refactored to share code, so a future change to the manual button can't accidentally
  also change the auto-send path or vice versa).
- `courier_send_sender_tracking_email($shipment_id)` (`helpers/courier_helper.php`) → the
  **sender** (the person who handed over the parcel, resolved via
  `courier_resolve_shipment_sender_email()` — `tbl_shipment_senders` or, if entered as a
  company, `tbl_shipment_companies`), a simpler "here's how to track your shipment" notice
  with the same big CTA button, using the `courier_sender_tracking_info` template/mailable.

Both are intentionally **universal**, not Salibay-gated — unlike
`courier_send_shipment_in_transit_email()` (Salibay-only, see "Known past bug" below), these
two are meant to fire for every shipment regardless of source. Don't add a
`shopify_orders.gs_shipment_id` guard to either of these two.

`Fulfilment.php`/`Shopify_connector.php` used to call the older, simpler
`courier_send_shipment_created_email()` (still defined in `courier_helper.php`, now unused —
left in place rather than deleted in case something else still references it) — that call was
replaced with `courier_send_shipment_waybill_email()` so Salibay orders get the identical
detailed waybill email a manually created shipment gets, per explicit instruction.

## Pushing status back to Shopify — `push_shopify_fulfillment_status()`

`Shopify_connector_model::push_shopify_fulfillment_status($shipment_id, $status_id)` is the
**only** place that writes anything back to Shopify (creates/updates the order's Shopify
fulfillment — this is what flips "Unfulfilled" → "Fulfilled" and shows the customer a tracking
link). Everything else described in "Instant waybill emails" above and the Shopify order Tags
box is one-directional (Go Shipping *reads* Shopify, never writes back because of a tag edit).

Called from 4 sites tied to the **domestic** `status_id` track (`Shipments::update_status()`,
`Rider_api::delivery_start()`, `Salibay_delivery::start()`, `Pickups.php`) — "Fulfilled" means
status 5/6 (in_transit), matching Salibay's own ops semantics ("goods left the warehouse").
Status 9 (cancelled) triggers `cancel_fulfillment()` instead. Anything earlier (Created/Picked
up/Received) is deliberately skipped — nothing real to tell the customer yet.

The **international** leg (`international_status_id` 10–13) also pushes now — status 10 (At
Origin Airport, set by `activate_international_air_freight_leg()` in
`shopify_connector/controllers/Shopify_connector.php`, triggered by the "Ready for
International Fulfillment" tag) is the international equivalent of domestic's first in_transit:
the moment Go Shipping actually takes physical custody and the parcel starts moving. **Not**
"Salibay Warehouse Received" — that tag means the *sourcing* team's own warehouse abroad
received it, before Go Shipping has custody, so there's nothing to fulfil yet (same reasoning
domestic uses to skip Created/Picked up). Once the international leg reaches 13 and hands off
to the domestic track, no separate wiring is needed — the existing domestic call sites push
out_for_delivery/delivered normally from there. Two call sites needed the international push
(both call `activate_international_status()`): the normal case in
`activate_international_air_freight_leg()`, and the race-condition case in
`create_courier_shipment()` where the tag arrives before the shipment even exists yet (shipment
is born already in AIR (AIR FREIGHT) mode with international tracking pre-activated).

## Shared core tables (used by BOTH flows — universal)

- `tbl_shipments` — one row per shipment, whichever flow created it.
  - `status_id` (1–9): the universal domestic/general status track. Always valid, always
    updated via `Shipments::update_status()`.
  - `international_status_id` (10–13, nullable): Salibay/international-leg-only. `NULL` for
    every plain Go Shipping shipment. See "Two-leg tracking" below.
  - `parent_shipment_id`: legacy column from an abandoned two-row international-leg design
    (see History). Left in place harmlessly; not used by the current schema. Don't build new
    logic on it.
  - `waybill_number` / `tracking_id`, `branch_id`, `shipping_mode`, `shipping_category` —
    universal.
- `tbl_shipment_statuses` — statuses 1–9 are universal (general courier + Salibay domestic
  leg both use them); statuses 10–13 are international-leg-only, only ever set on shipments
  that also have `international_status_id` populated.
- `tbl_courier_branches`, `tblcourier_route_branch_map` — universal. Route-tag → branch
  resolution (`courier_resolve_branch_from_route_tag()` in `helpers/courier_helper.php`) is
  *used* by the Salibay/Shopify webhook flow to auto-assign a branch, but the table itself and
  the manual "pick a branch" dropdown are universal — a staff member creating a plain shipment
  by hand just picks a branch normally; route-tag resolution never runs unless a Shopify route
  tag string was actually supplied.
- `tbl_courier_riders`, `tbl_courier_rider_tokens` — universal. Any rider can be assigned any
  shipment, Salibay or not, via `driver_id`/`staff_id`.
- `tbl_courier_metrics_cache` — universal, dashboard-wide.

## Salibay/Shopify-only tables and columns

- `tblshopify_orders` — the join table. A `gs_shipment_id` on a row here is the **only**
  correct signal that a shipment is Salibay-sourced.
- `shopify_integration_logs`, `shopify_webhook_events` — Salibay-only, pruned on a schedule
  (`run_scheduled_log_pruning()` in `courier_goshipping.php`); this pruning explicitly never
  touches `shipment_status_history` or `courier_sourcing_events`, which both flows may write
  to.
- `courier_sourcing_events` — records external sourcing-system tags (`GSC-{cc}-{port}` route
  tags, milestone tags) captured off a Shopify order, surfaced to customers as the "Full
  Journey" timeline. Empty/unused for plain shipments.
- Customer-facing emails — `courier_send_shipment_created_email()` and
  `courier_send_shipment_in_transit_email()` (`helpers/courier_helper.php`) are **Salibay-only
  by design** (they're branded "Salibay" in the mail templates) and must always be guarded by
  the `shopify_orders.gs_shipment_id` check above before sending. See "Known past bug" below —
  this was NOT guarded correctly until this session.

## Two-leg (international) tracking — current design

A Salibay order that requires an international freight leg before its domestic delivery leg
gets **one shipment row with one waybill number** — not two separate shipment rows. This was
a deliberate redesign after an earlier two-row design was rejected by the project owner
("lets use 1 separate waybill number... can not update the domestic without updating the
international one").

- `tbl_shipments.international_status_id` (10–13) tracks the international leg on the *same*
  row as the domestic `status_id` (1–9).
- While `international_status_id` is set and `< 13` (not yet arrived/cleared), the domestic
  `Shipments::update_status()` action is blocked — the international leg must complete first.
  This is intentional coupling **within a single Salibay shipment**, not a general-vs-Salibay
  interference — it only ever applies to a shipment that already has an international leg.
- `Shipments::update_international_status($id)` progresses the international track. On
  reaching status 13, it flips `shipping_mode`/`shipping_category` back to domestic and nudges
  `status_id` to 3 if still ≤2, handing the shipment back to the normal domestic flow.
- `waybill.php` renders two steppers (an "International" card shown only if
  `international_active`, and the normal "Domestic" card) — a plain shipment with
  `international_status_id = NULL` only ever sees the domestic stepper, unchanged from before
  this feature existed.
- Migration `v43` (in `courier_goshipping.php`) is the authoritative schema: it added
  `international_status_id`, and cleaned up/migrated any leftover rows from the abandoned
  two-row design (`v36`/`v42`, now superseded — those columns/backfills are harmless leftovers,
  not part of the live design).

## Rider app — shared, not Salibay-only

`modules/courier_goshipping/controllers/Rider_app.php`, `Rider_api.php`,
`views/rider_app/shell.php`, `models/Rider_model.php` are the installable PWA
(`/rider` or `admin/courier_goshipping/rider`) used by riders for **both** flows — a rider
sees and acts on any shipment assigned to them, general or Salibay. Riders are **not**
Perfex staff: they live in `tbl_courier_riders` (bearer-token auth, separate from
`tbl_staff`), auto-linked to a hidden, login-blocked (`active = 0`) synthetic `Fleet: Driver`
staff row purely so existing `staff_id`/`driver_id` assignment fields keep working. Password
reset on that synthetic staff row does **nothing** for a rider's real login — always use the
rider-specific reset path (`Rider_model::change_password()` /
`request_password_reset()` + `complete_password_reset()`, `Rider_api::forgot_password()` /
`reset_password()`).

`Salibay_delivery.php` is a **separate**, Salibay-only public/token-gated controller for a
shipment's per-shipment delivery link, used instead of the Rider app for some Salibay flows —
it is a distinct entry point from `Rider_api::delivery_start()`, and both call into the same
`courier_send_shipment_in_transit_email()` helper, which is why that helper needs its own
Salibay-only guard rather than relying on the caller to already be Salibay-scoped.

## Known past bug (fixed this session)

`courier_send_shipment_in_transit_email()` was wired into `Rider_api::delivery_start()` (the
Rider app's generic "Start Delivery" action, usable on any assigned shipment) with **no check**
that the shipment was actually Salibay-linked. A rider starting delivery on a plain general
Go Shipping shipment would trigger a Salibay-branded "your shipment is in transit" customer
email, as long as the recipient had an email on file — a direct violation of the
general/Salibay non-interference rule. Fixed by adding a `shopify_orders.gs_shipment_id`
existence check at the top of the function (see `helpers/courier_helper.php`) so it now
silently no-ops for non-Salibay shipments instead of sending.

**When adding any new cross-cutting helper** (new notification, new status side-effect, new
webhook), ask: "does this only make sense for a Salibay order?" If yes, guard it with the
`gs_shipment_id` existence check at the top, the same way. Don't rely on the call site being
Salibay-only — as this bug shows, a call site can be shared even when the helper's *intent*
was Salibay-specific.

## Performance work (this session, universal — affects both flows equally)

- `Fulfilment::get_fulfilment_metrics()` now reads/writes `tbl_courier_metrics_cache`
  (15s TTL, keyed by branch scope) instead of recomputing dashboard aggregates on every load.
- FULLTEXT + BOOLEAN MODE search replaced `LIKE '%...%'` scans in
  `get_salibay_order_list_datatable()` (via `build_boolean_search_term()`).
- Added indexes across courier tables (`v37`) and several core Perfex/warehouse/purchase
  tables (`v38`) that were doing full scans on high-traffic pages — bundled into this module's
  migration runner (not their "natural" module) because of the `modules/*`-only cPanel deploy
  constraint documented in the root `CLAUDE.md`.
- Rider/fulfilment dashboard JS poll interval reduced from 3s to 20s.
- All migrations (`v33`–`v43`) are self-healing, gated by `get_option('courier_schema_vNN_done')`,
  hooked to `admin_init` — see root `CLAUDE.md`'s "DB migrations don't auto-run" section for why.

## Branch consolidation (this session)

Branches with zero references anywhere (no shipments, no staff assignment, no route-tag
mapping) were deleted (`v34`) to reduce a confusing list down to 4 curated branches. Manual
shipment creation previously never consulted the route-tag map at all (only the Shopify
webhook path did) — this was the actual root cause of an earlier "wrong branch" bug, now fixed
via a shared helper (`courier_resolve_branch_from_route_tag()`) used by both the manual
creation path (`Fulfilment::create_courier_shipment()`) and the webhook path
(`Shopify_connector::create_courier_shipment()`). This helper also does prefix-based sibling
fallback matching (`GSC-US-EWR` falls back to any `GSC-US-*` mapping) since exact-tag-only
matching was too strict for how the external sourcing system actually tags orders.

## If something seems to be silently failing

- CI3's default DB driver does **not** throw on query failure — a failed `INSERT`/`UPDATE`
  can silently no-op with zero visible error. Check `application/logs/log-<date>.php` for the
  actual PHP error, not just the browser or the web-server access log (the access log only
  shows HTTP status codes, never application-level DB/PHP errors).
- If a schema-dependent feature breaks with no error, don't assume a migration ran just
  because it's old — add a defensive, idempotent self-heal in the constructor of whatever
  controller needs the column/table, same pattern as the rest of this module.
