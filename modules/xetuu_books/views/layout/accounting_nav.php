<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<nav class="acc-nav" id="acc-nav" role="navigation">

  <!-- Brand ────────────────────────────────────────────── -->
  <a href="<?php echo admin_url('xetuu_books'); ?>" class="acc-nav-brand">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
      <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15zM6.5 19H20v2H6.5A2.5 2.5 0 0 1 4 18.5A2.5 2.5 0 0 1 6.5 16"/>
    </svg>
    Xetuu Books
  </a>
  <div class="acc-nav-separator"></div>

  <div class="acc-nav-items">

    <!-- Dashboard -->
    <div class="acc-nav-item">
      <a href="<?php echo admin_url('xetuu_books'); ?>" class="acc-nav-plain">Dashboard</a>
    </div>

    <!-- ═══ CUSTOMERS ═══════════════════════════════════════ -->
    <div class="acc-nav-item" data-has-dropdown>
      <span class="acc-nav-label">Customers
        <svg class="acc-nav-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
      </span>
      <div class="acc-dropdown acc-dropdown-split">
        <!-- left: main actions with coloured tiles -->
        <div class="acc-split-left">
          <a href="<?php echo admin_url('xetuu_books/invoices'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-green">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </span>
            <span>Invoices</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/credit_notes'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-blue">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
            </span>
            <span>Credit Notes</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/receipts'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-purple">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19.5 3.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2v14H3v3c0 1.66 1.34 3 3 3h12c1.66 0 3-1.34 3-3V2l-1.5 1.5zM19 19c0 .55-.45 1-1 1s-1-.45-1-1v-3H8V5h11v14z"/></svg>
            </span>
            <span>Receipts</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/payments'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-orange">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
            </span>
            <span>Payments</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/batch_payments'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-red">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 4v4h16V4H4zm0 14h16v-2H4v2zm0-6h16v-2H4v2z"/></svg>
            </span>
            <span>Batch Payments</span>
          </a>
        </div>
        <!-- right: grouped links -->
        <div class="acc-split-right">
          <div class="acc-split-section">
            <div class="acc-split-section-header">Collections</div>
            <a href="<?php echo admin_url('xetuu_books/reports/aged_receivable'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
              Follow-up Reports
            </a>
          </div>
          <div class="acc-split-section">
            <div class="acc-split-section-header">Master Data</div>
            <a href="<?php echo admin_url('xetuu_books/customers'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
              Customers
            </a>
            <a href="<?php echo admin_url('xetuu_books/products'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 16.5c0 .38-.21.71-.53.88l-7.9 4.44c-.16.12-.36.18-.57.18-.21 0-.41-.06-.57-.18l-7.9-4.44A.991.991 0 013 16.5v-9c0-.38.21-.71.53-.88l7.9-4.44c.16-.12.36-.18.57-.18.21 0 .41.06.57.18l7.9 4.44c.32.17.53.5.53.88v9zM12 4.15L6.04 7.5 12 10.85l5.96-3.35L12 4.15zM5 15.91l6 3.38v-6.71L5 9.21v6.7zM19 15.91v-6.7l-6 3.38v6.71l6-3.39z"/></svg>
              Products
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ VENDORS ══════════════════════════════════════════ -->
    <div class="acc-nav-item" data-has-dropdown>
      <span class="acc-nav-label">Vendors
        <svg class="acc-nav-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
      </span>
      <div class="acc-dropdown acc-4col-mega">

        <!-- Col 1: Accounting AP (existing operations — untouched) -->
        <div class="acc-mega-col acc-mega-col--accounting">
          <div class="acc-mega-col-label">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" style="width:13px;height:13px;vertical-align:-2px;margin-right:5px;fill:currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15zM6.5 19H20v2H6.5A2.5 2.5 0 0 1 4 18.5A2.5 2.5 0 0 1 6.5 16"/></svg>
            Accounting
          </div>
          <a href="<?php echo admin_url('xetuu_books/bills'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-indigo">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg>
            </span>
            <span>Bills (GL)</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/purchase_debit_notes'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-orange">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
            </span>
            <span>Debit Notes</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/vendor_payments'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-teal">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
            </span>
            <span>Payments</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/refunds'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-purple">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/></svg>
            </span>
            <span>Refunds</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/expenses'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-red">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </span>
            <span>Expenses</span>
          </a>
        </div>

        <!-- Col 2: Purchase Workflow (NEW — purchase module integration) -->
        <div class="acc-mega-col acc-mega-col--purchase">
          <div class="acc-mega-col-label">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" style="width:13px;height:13px;vertical-align:-2px;margin-right:5px;fill:currentColor"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
            Purchase Workflow
          </div>
          <a href="<?php echo admin_url('xetuu_books/purchase_requests'); ?>" class="acc-split-item">
            <span class="acc-tile" style="background:#0ea5e9;">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            </span>
            <span>Purchase Requests</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/purchase_quotations'); ?>" class="acc-split-item">
            <span class="acc-tile" style="background:#7c3aed;">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm1 7h-4V7.5L14.5 9H15zm-2 9H7v-2h6v2zm2-4H7v-2h8v2z"/></svg>
            </span>
            <span>Quotations</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/purchase_orders'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-green">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            </span>
            <span>Purchase Orders</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/purchase_invoices'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-blue">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </span>
            <span>Purchase Invoices</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/purchase_contracts'); ?>" class="acc-split-item">
            <span class="acc-tile" style="background:#d97706;">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
            </span>
            <span>Contracts <span style="font-size:9px;background:#fef3c7;color:#92400e;padding:1px 5px;border-radius:10px;margin-left:4px;font-weight:700;">SIGN</span></span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/purchase_order_returns'); ?>" class="acc-split-item">
            <span class="acc-tile" style="background:#ef4444;">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/></svg>
            </span>
            <span>Order Returns</span>
          </a>
        </div>

        <!-- Col 3: Master Data -->
        <div class="acc-mega-col acc-mega-col--master">
          <div class="acc-mega-col-label">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" style="width:13px;height:13px;vertical-align:-2px;margin-right:5px;fill:currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Master Data
          </div>
          <a href="<?php echo admin_url('xetuu_books/vendors'); ?>" class="acc-split-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Vendors
          </a>
          <a href="<?php echo admin_url('xetuu_books/products_vendors'); ?>" class="acc-split-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 16.5c0 .38-.21.71-.53.88l-7.9 4.44c-.16.12-.36.18-.57.18-.21 0-.41-.06-.57-.18l-7.9-4.44A.991.991 0 013 16.5v-9c0-.38.21-.71.53-.88l7.9-4.44c.16-.12.36-.18.57-.18.21 0 .41.06.57.18l7.9 4.44c.32.17.53.5.53.88v9z"/></svg>
            Vendor Items
          </a>
          <a href="<?php echo admin_url('xetuu_books/vendor_receipts'); ?>" class="acc-split-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19.5 3.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2v14H3v3c0 1.66 1.34 3 3 3h12c1.66 0 3-1.34 3-3V2l-1.5 1.5z"/></svg>
            Receipts
          </a>
          <div class="acc-mega-col-label" style="margin-top:10px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" style="width:13px;height:13px;vertical-align:-2px;margin-right:5px;fill:currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
            Reports
          </div>
          <a href="<?php echo admin_url('xetuu_books/reports/aged_payable'); ?>" class="acc-split-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>
            Aged Payable
          </a>
          <a href="<?php echo admin_url('purchase/reports'); ?>" class="acc-split-link" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
            Purchase Reports <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" style="width:10px;height:10px;fill:#999;margin-left:3px;"><path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>
          </a>
        </div>

      </div>
    </div>

    <!-- ═══ ACCOUNTING ════════════════════════════════════════ -->
    <div class="acc-nav-item" data-has-dropdown>
      <span class="acc-nav-label">Accounting
        <svg class="acc-nav-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
      </span>
      <div class="acc-dropdown acc-dropdown-split">
        <div class="acc-split-left">
          <a href="<?php echo admin_url('xetuu_books/journal_entries'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-indigo">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15zM6.5 19H20v2H6.5A2.5 2.5 0 0 1 4 18.5A2.5 2.5 0 0 1 6.5 16"/></svg>
            </span>
            <span>Journal Entries</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/journal_items'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-blue">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>
            </span>
            <span>Journal Items</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/analytic_items'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-amber">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
            </span>
            <span>Analytic Items</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/auto_transfers'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-cyan">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 4l-1.41 1.41L15.17 10H4v2h11.17l-4.58 4.59L12 20l8-8z"/></svg>
            </span>
            <span>Auto Transfers</span>
          </a>
        </div>
        <div class="acc-split-right">
          <div class="acc-split-section">
            <div class="acc-split-section-header">Management</div>
            <a href="<?php echo admin_url('xetuu_books/reconciliation'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 11V3H8v6H2v12h20V11h-6zm-6-6h4v14h-4V5zm-6 6h4v8H4v-8zm16 8h-4v-6h4v6z"/></svg>
              Reconciliation
            </a>
            <a href="<?php echo admin_url('xetuu_books/budgets_assets'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
              Budgets / Assets
            </a>
            <a href="<?php echo admin_url('xetuu_books/deferred_rev_exp'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
              Deferred Rev/Exp
            </a>
            <a href="<?php echo admin_url('xetuu_books/cost_centre_report'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 3v18h18V3H3zm16 16H5V5h14v14zM7 7h10v2H7zm0 4h10v2H7zm0 4h7v2H7z"/></svg>
              Cost Centre Report
            </a>
            <a href="<?php echo admin_url('xetuu_books/config/lock_dates'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
              Lock Dates
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ REPORTING ════════════════════════════════════════ -->
    <div class="acc-nav-item" data-has-dropdown>
      <span class="acc-nav-label">Reporting
        <svg class="acc-nav-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
      </span>
      <div class="acc-dropdown acc-dropdown-split">
        <div class="acc-split-left">
          <a href="<?php echo admin_url('xetuu_books/reports/profit_and_loss'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-green">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
            </span>
            <span>Profit &amp; Loss</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/reports/balance_sheet'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-blue">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 3H4v10c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4v-3h2c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 5h-2V5h2v3zM4 19h16v2H4z"/></svg>
            </span>
            <span>Balance Sheet</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/reports/cash_flow'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-purple">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
            </span>
            <span>Cash Flow</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/reports/executive_summary'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-orange">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
            </span>
            <span>Executive Summary</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/reports/tax_report'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-red">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
            </span>
            <span>Tax Report</span>
          </a>
        </div>
        <div class="acc-split-right">
          <div class="acc-split-section">
            <div class="acc-split-section-header">Audit Reports</div>
            <a href="<?php echo admin_url('xetuu_books/reports/general_ledger'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15zM6.5 19H20v2H6.5A2.5 2.5 0 0 1 4 18.5A2.5 2.5 0 0 1 6.5 16"/></svg>
              General Ledger
            </a>
            <a href="<?php echo admin_url('xetuu_books/reports/trial_balance'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 3H4v10c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4v-3h2c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 5h-2V5h2v3zM4 19h16v2H4z"/></svg>
              Trial Balance
            </a>
            <a href="<?php echo admin_url('xetuu_books/reports/journal_report'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>
              Journal Report
            </a>
          </div>
          <div class="acc-split-section">
            <div class="acc-split-section-header">Partner Reports</div>
            <a href="<?php echo admin_url('xetuu_books/reports/partner_ledger'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
              Partner Ledger
            </a>
            <a href="<?php echo admin_url('xetuu_books/reports/aged_receivable'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>
              Aged Receivable
            </a>
            <a href="<?php echo admin_url('xetuu_books/reports/aged_payable'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
              Aged Payable
            </a>
          </div>
          <div class="acc-split-section">
            <div class="acc-split-section-header">Management</div>
            <a href="<?php echo admin_url('xetuu_books/reports/invoice_analysis'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
              Invoice Analysis
            </a>
            <a href="<?php echo admin_url('xetuu_books/reports/depreciation_schedule'); ?>" class="acc-split-link">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
              Depreciation Schedule
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ CONFIGURATION (3-column, right-aligned) ════════════ -->
    <div class="acc-nav-item" data-has-dropdown>
      <span class="acc-nav-label">Configuration
        <svg class="acc-nav-caret" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>
      </span>
      <div class="acc-dropdown acc-dropdown-3col">

        <!-- ── Column 1: Accounting ──────────────────────────── -->
        <div class="acc-3col-col">
          <div class="acc-3col-header">Accounting</div>
          <a href="<?php echo admin_url('xetuu_books/config/chart_of_accounts'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-green">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22 9V7h-2V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-2h2v-2h-2v-2h2v-2h-2V9h2zm-4 10H4V5h14v14z"/><path d="M6 13h5v4H6zm6 0h2v4h-2zm-6-6h2v4H6zm3 0h5v4H9z"/></svg>
            </span>
            <span>Chart of Accounts</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/taxes'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-blue">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
            </span>
            <span>Taxes</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/journals'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-purple">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15zM6.5 19H20v2H6.5A2.5 2.5 0 0 1 4 18.5A2.5 2.5 0 0 1 6.5 16"/></svg>
            </span>
            <span>Journals</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/currencies'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-orange">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/></svg>
            </span>
            <span>Currencies</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/bank_accounts'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-teal">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 10v7h3v-7H4zm6 0v7h3v-7h-3zM2 22h19v-3H2v3zm14-12v7h3v-7h-3zM11.5 1L2 6v2h19V6l-9.5-5z"/></svg>
            </span>
            <span>Bank Accounts</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/settings'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-indigo">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
            </span>
            <span>Settings</span>
          </a>
        </div>

        <!-- ── Column 2: Invoicing ────────────────────────────── -->
        <div class="acc-3col-col">
          <div class="acc-3col-header">Invoicing</div>
          <a href="<?php echo admin_url('xetuu_books/config/payment_terms'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-cyan">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>
            </span>
            <span>Payment Terms</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/follow_up_levels'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-amber">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
            </span>
            <span>Follow-up Levels</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/incoterms'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-red">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
            </span>
            <span>Incoterms</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/stamp'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-purple">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            </span>
            <span>Stamp Configuration</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/reconcil_models'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-green">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 11V3H8v6H2v12h20V11h-6zm-6-6h4v14h-4V5zm-6 6h4v8H4v-8zm16 8h-4v-6h4v6z"/></svg>
            </span>
            <span>Reconcil. Models</span>
          </a>
        </div>

        <!-- ── Column 3: Management ───────────────────────────── -->
        <div class="acc-3col-col">
          <div class="acc-3col-header">Management</div>
          <a href="<?php echo admin_url('xetuu_books/config/fiscal_positions'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-purple">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </span>
            <span>Fiscal Positions</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/fiscal_years'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-blue">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
            </span>
            <span>Fiscal Years</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/asset_models'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-orange">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 16.5c0 .38-.21.71-.53.88l-7.9 4.44c-.16.12-.36.18-.57.18-.21 0-.41-.06-.57-.18l-7.9-4.44A.991.991 0 013 16.5v-9c0-.38.21-.71.53-.88l7.9-4.44c.16-.12.36-.18.57-.18.21 0 .41.06.57.18l7.9 4.44c.32.17.53.5.53.88v9z"/></svg>
            </span>
            <span>Asset Models</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/analytic_accounts'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-teal">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
            </span>
            <span>Analytic Accounts</span>
          </a>
          <a href="<?php echo admin_url('xetuu_books/config/payment_providers'); ?>" class="acc-split-item">
            <span class="acc-tile acc-tile-red">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
            </span>
            <span>Payment Providers</span>
          </a>
        </div>

      </div>
    </div>

  </div><!-- /.acc-nav-items -->

</nav>
