<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .cm { --cm-ink:#0f172a; --cm-muted:#64748b; --cm-line:#e2e8f0; --cm-soft:#f8fafc; --cm-brand:#0d47a1; --cm-accent:#c62828; color:var(--cm-ink); }
    .cm * { box-sizing: border-box; }
    .cm-hero { display:flex; justify-content:space-between; align-items:flex-end; gap:20px; flex-wrap:wrap;
        background:linear-gradient(135deg,#0d47a1 0%,#1565c0 60%,#1e88e5 100%); color:#fff; border-radius:14px; padding:22px 28px; margin-bottom:18px; }
    .cm-hero h1 { margin:0 0 4px; font-size:24px; font-weight:800; letter-spacing:-.3px; color:#fff; }
    .cm-hero p { margin:0; opacity:.85; font-size:13px; }
    .cm-number { text-align:right; }
    .cm-number small { display:block; font-size:11px; letter-spacing:1.4px; text-transform:uppercase; opacity:.8; margin-bottom:2px; }
    .cm-number strong { display:block; font-size:30px; font-weight:800; font-family:Consolas,Menlo,monospace; letter-spacing:1px; line-height:1.1; }
    .cm-number span { display:inline-block; margin-top:6px; background:rgba(255,255,255,.18); border-radius:20px; padding:2px 10px; font-size:11px; }

    .cm-card { background:#fff; border:1px solid var(--cm-line); border-radius:14px; box-shadow:0 1px 3px rgba(15,23,42,.05); margin-bottom:18px; }
    .cm-card__head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:14px 20px; border-bottom:1px solid var(--cm-line); }
    .cm-card__head h3 { margin:0; font-size:15px; font-weight:700; }
    .cm-card__body { padding:18px 20px; }

    .cm-filters { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px 16px; }
    .cm-field label { display:block; font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--cm-muted); margin-bottom:5px; }
    .cm-field select, .cm-field input[type=date] { width:100%; height:40px; border:1px solid #cbd5e1; border-radius:8px; padding:0 10px; background:#fff; color:var(--cm-ink); font-size:13px; }
    .cm-field select:focus, .cm-field input:focus { outline:0; border-color:var(--cm-brand); box-shadow:0 0 0 3px rgba(13,71,161,.12); }
    .cm-actions { display:flex; gap:10px; align-items:flex-end; }
    .cm-btn { display:inline-flex; align-items:center; gap:7px; height:40px; padding:0 18px; border-radius:8px; border:1px solid transparent; font-weight:700; font-size:13px; cursor:pointer; text-decoration:none !important; white-space:nowrap; }
    .cm-btn--primary { background:var(--cm-brand); color:#fff !important; }
    .cm-btn--primary:hover { background:#0a3a85; }
    .cm-btn--accent { background:var(--cm-accent); color:#fff !important; }
    .cm-btn--accent:hover { background:#a31f1f; }
    .cm-btn--ghost { background:#fff; color:var(--cm-ink) !important; border-color:#cbd5e1; }
    .cm-btn--ghost:hover { background:var(--cm-soft); }
    .cm-scope { margin-top:14px; padding:9px 12px; background:var(--cm-soft); border:1px dashed #cbd5e1; border-radius:8px; font-size:12px; color:var(--cm-muted); }
    .cm-scope b { color:var(--cm-ink); }

    .cm-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:18px; }
    .cm-kpi { background:#fff; border:1px solid var(--cm-line); border-radius:12px; padding:12px 16px; }
    .cm-kpi small { display:block; font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--cm-muted); }
    .cm-kpi strong { display:block; font-size:20px; font-weight:800; margin-top:2px; }

    .cm-table-wrap { overflow-x:auto; }
    table.cm-table { width:100%; border-collapse:separate; border-spacing:0; font-size:13px; }
    .cm-table thead th { position:sticky; top:0; background:#0f172a; color:#fff; font-size:11px; font-weight:700; letter-spacing:.7px; text-transform:uppercase; padding:11px 12px; text-align:left; white-space:nowrap; }
    .cm-table thead th:first-child { border-top-left-radius:10px; }
    .cm-table thead th:last-child { border-top-right-radius:10px; }
    .cm-table tbody td { padding:11px 12px; border-bottom:1px solid var(--cm-line); vertical-align:middle; }
    .cm-table tbody tr:nth-child(even) td { background:#fafcff; }
    .cm-table tbody tr:hover td { background:#eef4ff; }
    .cm-table tfoot td { padding:12px; font-weight:800; background:var(--cm-soft); border-top:2px solid #0f172a; }
    .cm-num { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .cm-wb { font-family:Consolas,Menlo,monospace; font-weight:700; color:var(--cm-brand); white-space:nowrap; }
    .cm-sub { display:block; font-size:11px; color:var(--cm-muted); }
    .cm-pill { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; background:#e2e8f0; color:#334155; }
    .cm-pill--ok { background:#dcfce7; color:#166534; }
    .cm-pill--move { background:#dbeafe; color:#1e40af; }
    .cm-pill--warn { background:#fef3c7; color:#92400e; }
    .cm-pill--bad { background:#fee2e2; color:#991b1b; }
    .cm-type { display:inline-block; padding:3px 9px; border-radius:6px; font-size:11px; font-weight:700; background:#eef2ff; color:#3730a3; white-space:nowrap; }
    .cm-type--local { background:#ecfdf5; color:#047857; }
    .cm-empty { text-align:center; padding:48px 20px; color:var(--cm-muted); }
    .cm-empty i { font-size:34px; display:block; margin-bottom:10px; opacity:.5; }

    /* generated document */
    .cm-doc { background:#fff; border:2px solid #0f172a; border-radius:12px; padding:26px 30px; }
    .cm-doc__top { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; padding-bottom:16px; border-bottom:3px solid #0f172a; margin-bottom:16px; }
    .cm-doc__brand img { max-height:54px; display:block; margin-bottom:6px; }
    .cm-doc__brand b { font-size:15px; }
    .cm-doc__no { text-align:right; }
    .cm-doc__no small { font-size:11px; letter-spacing:1.5px; text-transform:uppercase; color:var(--cm-muted); }
    .cm-doc__no strong { display:block; font-size:30px; font-family:Consolas,Menlo,monospace; letter-spacing:1px; line-height:1.15; }
    .cm-meta { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:8px 20px; margin-bottom:16px; font-size:12px; }
    .cm-meta span { color:var(--cm-muted); display:block; font-size:10px; letter-spacing:.6px; text-transform:uppercase; font-weight:700; }
    .cm-sign { display:flex; gap:40px; margin-top:42px; }
    .cm-sign div { flex:1; border-top:1px solid #0f172a; padding-top:6px; font-size:11px; color:var(--cm-muted); }

    @media print {
        body * { visibility:hidden; }
        .cm-print, .cm-print * { visibility:visible; }
        .cm-print { position:absolute; left:0; top:0; width:100%; }
        .cm-noprint { display:none !important; }
        .cm-doc { border-radius:0; box-shadow:none; padding:10px 14px; }
        .cm-table thead th { position:static; background:#0f172a !important; color:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        .cm-table tbody tr:nth-child(even) td, .cm-table tfoot td { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        .cm-table { font-size:10px; }
        @page { size: A4 landscape; margin: 10mm; }
    }
</style>
