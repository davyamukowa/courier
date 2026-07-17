<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo _l('pos_terminal'); ?> — <?php echo get_option('companyname'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300..800;1,9..40,300..800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%;overflow:hidden}
    :root{
      --g50:#f0fdf4;--g100:#dcfce7;--g200:#bbf7d0;--g300:#86efac;--g400:#4ade80;
      --g500:#22c55e;--g600:#16a34a;--g700:#15803d;--g800:#166534;--g900:#14532d;
      --slate50:#f8fafc;--slate100:#f1f5f9;--slate200:#e2e8f0;--slate300:#cbd5e1;
      --slate400:#94a3b8;--slate500:#64748b;--slate600:#475569;--slate700:#334155;
      --slate800:#1e293b;--white:#ffffff;
      --red:#ef4444;--amber:#f59e0b;--blue:#3b82f6;
      --font:'DM Sans',system-ui,sans-serif;--mono:'JetBrains Mono',monospace;
    }
    body{font-family:var(--font);background:var(--slate100)}
    #pos-app{height:100vh;display:flex;flex-direction:column}

    /* ── Shell ─────────────────────────────────────────────────── */
    .pos-shell{background:var(--slate100);height:100vh;display:flex;flex-direction:column;overflow:hidden}

    /* ── Topbar ────────────────────────────────────────────────── */
    .pos-topbar{background:var(--slate800);height:52px;display:flex;align-items:center;padding:0 14px;gap:12px;flex-shrink:0;border-bottom:2px solid var(--g700)}
    .logo-wrap{display:flex;align-items:center;gap:8px}
    .logo-circle{width:30px;height:30px;background:var(--g600);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:13px;color:#fff;flex-shrink:0}
    .logo-img{width:30px;height:30px;border-radius:8px;object-fit:cover;border:2px solid var(--g600)}
    .logo-text{font-weight:800;font-size:14px;color:#fff;letter-spacing:-.3px;white-space:nowrap}
    .logo-text span{color:var(--g400);font-size:10px;font-weight:500;margin-left:3px;text-transform:uppercase;letter-spacing:.05em}
    .topbar-mid{flex:1;display:flex;align-items:center;gap:14px;padding:0 12px;min-width:0;overflow:hidden}
    .tb-chip{background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.4);border-radius:20px;padding:3px 10px;font-size:11px;color:var(--g300);display:flex;align-items:center;gap:5px;white-space:nowrap}
    .tb-chip .dot{width:7px;height:7px;border-radius:50%;background:var(--g400);flex-shrink:0;animation:pulse 2s infinite}
    .tb-chip .dot.amber{background:var(--amber);animation:none}
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
    .tb-stat{display:flex;flex-direction:column;gap:0}
    .tb-stat .lbl{font-size:9px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.07em}
    .tb-stat .val{font-size:12px;color:#fff;font-weight:600;white-space:nowrap}
    .topbar-right{display:flex;align-items:center;gap:5px;flex-shrink:0}
    .tb-btn{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:7px;padding:5px 10px;color:rgba(255,255,255,.8);font-size:11px;cursor:pointer;display:flex;align-items:center;gap:5px;font-family:var(--font);font-weight:500;transition:all .15s}
    .tb-btn:hover{background:rgba(22,163,74,.25);border-color:var(--g600);color:#fff}
    .tb-btn.danger:hover{background:rgba(220,38,38,.25);border-color:#dc2626;color:#fca5a5}
    .timer-badge{background:var(--slate700);color:var(--g300);font-size:11px;padding:4px 9px;border-radius:6px;font-family:var(--mono);font-weight:500;border:1px solid var(--slate600)}

    /* ── Body layout ───────────────────────────────────────────── */
    .pos-body{display:flex;flex:1;overflow:hidden}

    /* ── Category sidebar ──────────────────────────────────────── */
    .cat-sidebar{width:170px;display:flex;flex-direction:column;background:var(--slate800);border-right:1px solid var(--slate700);flex-shrink:0;overflow:hidden}
    .cat-sidebar-hdr{padding:9px 12px 7px;font-size:9px;font-weight:700;color:var(--g400);text-transform:uppercase;letter-spacing:.12em;border-bottom:1px solid var(--slate700);flex-shrink:0;display:flex;align-items:center;gap:5px}
    .cat-sidebar-list{flex:1;overflow-y:auto;padding:5px 6px 8px}
    .cat-sidebar-list::-webkit-scrollbar{width:3px}
    .cat-sidebar-list::-webkit-scrollbar-thumb{background:var(--slate600);border-radius:4px}
    .cat-sb-btn{width:100%;text-align:left;padding:7px 10px;border-radius:7px;font-size:11.5px;font-weight:600;cursor:pointer;border:none;background:transparent;color:rgba(255,255,255,.55);font-family:var(--font);transition:all .15s;display:flex;align-items:center;justify-content:space-between;margin-bottom:2px;line-height:1.3}
    .cat-sb-btn:hover{background:rgba(22,163,74,.22);color:#fff}
    .cat-sb-btn.active{background:var(--g700);color:#fff}
    .cat-sb-count{font-size:9px;font-weight:700;background:rgba(255,255,255,.1);padding:1px 6px;border-radius:10px;font-family:var(--mono);flex-shrink:0}
    .cat-sb-btn.active .cat-sb-count{background:rgba(74,222,128,.25);color:var(--g300)}
    .cat-sb-divider{height:1px;background:rgba(255,255,255,.07);margin:4px 2px 6px}

    /* ── Left panel (products center) ──────────────────────────── */
    .pos-left{flex:1;display:flex;flex-direction:column;background:var(--slate100);overflow:hidden;min-width:0}

    /* Search + barcode zone */
    .search-zone{padding:8px 12px 7px;background:var(--white);border-bottom:1px solid var(--slate200);display:flex;gap:7px;align-items:center}
    .search-wrap{position:relative;flex:1}
    .search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--slate400);font-size:15px;pointer-events:none}
    .search-input{width:100%;background:var(--slate50);border:1.5px solid var(--slate200);border-radius:8px;padding:8px 11px 8px 33px;color:var(--slate800);font-size:12.5px;font-family:var(--font);outline:none;transition:border-color .15s}
    .search-input:focus{border-color:var(--g500);background:#fff}
    .search-input::placeholder{color:var(--slate400)}
    .shortcut-hint{font-size:10px;color:var(--slate400);background:var(--slate100);border:1px solid var(--slate200);border-radius:5px;padding:2px 6px;font-family:var(--mono);white-space:nowrap;flex-shrink:0}

    /* Category strip — hidden (replaced by sidebar) */
    .cat-strip{display:none!important}

    /* Product grid — auto-fill, slightly larger cards */
    .pgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;padding:10px 12px;overflow-y:auto;flex:1;align-content:start}
    .pgrid::-webkit-scrollbar{width:4px}
    .pgrid::-webkit-scrollbar-thumb{background:var(--slate300);border-radius:4px}

    /* Compact product card */
    .pcard{background:var(--white);border:1.5px solid var(--slate200);border-radius:10px;padding:7px 7px 6px;cursor:pointer;transition:all .15s;position:relative;overflow:hidden;display:flex;flex-direction:column}
    .pcard:hover{border-color:var(--g500);box-shadow:0 3px 10px rgba(22,163,74,.14);transform:translateY(-1px)}
    .pcard:active{transform:scale(.96)}
    .pcard.out{opacity:.38;pointer-events:none}
    .pcard-top{background:var(--g50);border-radius:6px;height:62px;display:flex;align-items:center;justify-content:center;margin-bottom:6px;border:1px solid var(--g100);overflow:hidden;flex-shrink:0}
    .pcard-top img{max-height:56px;max-width:100%;object-fit:contain}

    /* Stock badge — top-left of card */
    .sbadge{position:absolute;top:6px;left:6px;font-size:8px;font-weight:700;padding:2px 5px;border-radius:6px;font-family:var(--mono);line-height:1;white-space:nowrap}
    .sok{background:var(--g100);color:var(--g800);border:1px solid var(--g200)}
    .slow{background:#fef3c7;color:#92400e;border:1px solid #fcd34d}
    .sout{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
    .sorder{background:#fef3c7;color:#b45309;border:1px dashed #fcd34d}

    /* Category color dot on card */
    .cat-dot{position:absolute;top:6px;right:6px;width:8px;height:8px;border-radius:50%}

    .pname{font-size:11px;font-weight:600;color:var(--slate700);line-height:1.35;margin-bottom:3px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .psku{font-size:9px;color:var(--slate400);font-family:var(--mono);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .pprice{font-size:12.5px;font-weight:800;color:var(--g700);font-family:var(--mono);margin-top:4px}
    .padd-hint{position:absolute;bottom:6px;right:6px;width:18px;height:18px;background:var(--g600);border-radius:50%;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .15s}
    .pcard:hover .padd-hint{opacity:1}
    .padd-hint i{font-size:11px;color:#fff}

    .pgrid-empty{grid-column:1/-1;text-align:center;color:var(--slate400);padding:40px 20px;font-size:13px}
    .pgrid-empty i{font-size:32px;display:block;margin-bottom:10px;color:var(--slate300)}

    /* Product count bar */
    .pcount-bar{padding:4px 14px;font-size:10px;color:var(--slate400);background:var(--slate100);border-bottom:1px solid var(--slate200);flex-shrink:0;display:flex;align-items:center;gap:6px}

    /* ── Right panel (cart) ────────────────────────────────────── */
    .pos-right{width:390px;display:flex;flex-direction:column;background:var(--white);border-left:1px solid var(--slate200);flex-shrink:0}

    .cart-head{padding:9px 12px 8px;border-bottom:1px solid var(--slate200);background:var(--slate800)}
    .cart-title{font-size:13px;font-weight:700;color:#fff;margin-bottom:7px;display:flex;align-items:center;gap:6px}
    .cart-badge{background:var(--g600);color:#fff;border-radius:20px;padding:1px 7px;font-size:10px;font-weight:700}
    .cust-sel{width:100%;background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.15);border-radius:8px;padding:6px 10px;color:rgba(255,255,255,.75);font-size:11px;font-family:var(--font);cursor:pointer;outline:none;transition:border-color .15s}
    .cust-sel:focus{border-color:var(--g500);background:rgba(255,255,255,.12)}
    .cust-sel option{color:var(--slate800);background:#fff}

    .cart-list{flex:1;overflow-y:auto;padding:7px 10px}
    .cart-list::-webkit-scrollbar{width:3px}
    .cart-list::-webkit-scrollbar-thumb{background:var(--slate200)}
    .empty-cart{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--slate400);gap:8px;padding:20px;text-align:center}
    .empty-cart i{font-size:36px;color:var(--g200)}
    .empty-cart p{font-size:11.5px;color:var(--slate400);line-height:1.6}

    .ci{display:flex;align-items:center;flex-wrap:wrap;gap:6px;padding:6px 5px;border-radius:8px;border:1px solid transparent;transition:all .15s;margin-bottom:3px}
    .ci:hover{border-color:var(--slate200);background:var(--slate50)}
    .ci-img{width:28px;height:28px;background:var(--g50);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;border:1px solid var(--g100);color:var(--g600)}
    .ci-info{flex:1;min-width:0}
    .ci-name{font-size:10.5px;font-weight:600;color:var(--slate700);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .ci-sub{font-size:9px;color:var(--slate400);font-family:var(--mono)}
    .qty-ctrl{display:flex;align-items:center;gap:3px;flex-shrink:0}
    .qb{width:20px;height:20px;background:var(--g50);border:1.5px solid var(--g200);border-radius:5px;color:var(--g800);font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:var(--font);transition:all .15s;line-height:1}
    .qb:hover{background:var(--g600);border-color:var(--g600);color:#fff}
    .qn{font-size:11px;font-weight:700;color:var(--slate700);min-width:16px;text-align:center;font-family:var(--mono)}
    .cdel{width:18px;height:18px;background:none;border:none;color:var(--slate300);cursor:pointer;display:flex;align-items:center;justify-content:center;border-radius:4px;font-size:13px;flex-shrink:0}
    .cdel:hover{color:var(--red);background:#fee2e2}

    /* Per-item discount */
    .ci-disc-row{width:100%;padding:2px 3px 3px 34px;display:flex;align-items:center;gap:5px}
    .disc-badge{font-size:9px;font-weight:700;color:#92400e;background:#fef3c7;border:1px solid #fcd34d;padding:1px 5px;border-radius:7px;font-family:var(--mono);white-space:nowrap}
    .disc-btn{font-size:9px!important;font-weight:800;color:var(--amber);border-color:var(--amber)!important;padding:0 4px!important;flex-shrink:0;width:18px!important;height:19px!important}
    .disc-btn:hover{background:var(--amber)!important;border-color:var(--amber)!important;color:#fff!important}
    .disc-input{width:52px;background:#fff;border:1.5px solid var(--g400);border-radius:5px;padding:3px 5px;font-size:11px;font-family:var(--mono);color:var(--slate700);outline:none;text-align:center}
    .disc-input:focus{border-color:var(--g600)}
    .disc-apply{background:var(--g600);border:none;color:#fff;font-size:10px;font-weight:700;padding:3px 8px;border-radius:5px;cursor:pointer;font-family:var(--font);white-space:nowrap}
    .disc-apply:hover{background:var(--g700)}
    .disc-cancel{background:none;border:none;color:var(--slate400);font-size:15px;cursor:pointer;line-height:1;padding:0 2px;display:flex;align-items:center}
    .disc-cancel:hover{color:var(--red)}

    /* Note field */
    .note-wrap{padding:6px 10px 4px;border-top:1px solid var(--slate100)}
    .note-field{width:100%;background:var(--slate50);border:1.5px solid var(--slate200);border-radius:7px;padding:5px 9px;color:var(--slate700);font-size:11px;font-family:var(--font);outline:none;resize:none;height:30px;transition:border-color .15s;line-height:1.4}
    .note-field:focus{border-color:var(--g500);background:#fff;height:44px}
    .note-field::placeholder{color:var(--slate400)}

    .totals-panel{padding:9px 12px;border-top:1.5px solid var(--slate200)}
    .trow{display:flex;justify-content:space-between;margin-bottom:3px}
    .tlbl{font-size:11px;color:var(--slate500)}
    .tval{font-size:11px;color:var(--slate700);font-family:var(--mono)}
    .tdivider{border:none;border-top:1.5px dashed var(--slate200);margin:6px 0}
    .grand-row{display:flex;justify-content:space-between;align-items:center}
    .grand-lbl{font-size:13px;font-weight:800;color:var(--slate800)}
    .grand-val{font-size:20px;font-weight:800;color:var(--g700);font-family:var(--mono)}

    .actions{padding:8px 12px 10px;display:flex;flex-direction:column;gap:6px;border-top:1px solid var(--slate200)}
    .action-row{display:flex;gap:5px}
    .abtn{flex:1;padding:7px 4px;border-radius:8px;font-size:10px;font-weight:600;cursor:pointer;border:1.5px solid var(--slate200);background:var(--white);color:var(--slate500);font-family:var(--font);transition:all .15s;display:flex;align-items:center;justify-content:center;gap:3px;position:relative}
    .abtn:hover{border-color:var(--g500);color:var(--g700);background:var(--g50)}
    .abtn.danger:hover{border-color:var(--red);color:var(--red);background:#fff5f5}
    .abtn.amber-btn:hover{border-color:var(--amber);color:#92400e;background:#fffbeb}
    .held-count{position:absolute;top:-5px;right:-4px;background:var(--amber);color:#fff;border-radius:20px;padding:0 4px;font-size:8px;font-weight:700;min-width:14px;text-align:center;line-height:14px;height:14px}
    .checkout{width:100%;padding:12px;background:var(--g600);border:none;border-radius:10px;color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:var(--font);transition:all .15s;display:flex;align-items:center;justify-content:center;gap:7px;letter-spacing:.2px}
    .checkout:hover:not(:disabled){background:var(--g700)}
    .checkout:disabled{background:var(--slate200);color:var(--slate400);cursor:not-allowed}

    /* ── Modals ─────────────────────────────────────────────────── */
    .modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.6);display:flex;align-items:center;justify-content:center;z-index:1000}
    .modal{background:var(--white);border-radius:14px;width:340px;overflow:hidden;border:1px solid var(--slate200);max-height:95vh;display:flex;flex-direction:column}
    .modal-wide{width:380px}
    .mhdr{background:var(--slate800);padding:13px 16px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;border-bottom:2px solid var(--g700)}
    .mtitle{font-size:13px;font-weight:700;color:#fff;display:flex;align-items:center;gap:6px}
    .mclose{background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);font-size:15px;cursor:pointer;width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s}
    .mclose:hover{background:rgba(220,38,38,.3);color:#fca5a5}
    .mbody{padding:15px;overflow-y:auto;flex:1 1 auto}
    .mfooter{padding:0 15px 15px;flex-shrink:0}

    .amount-due{background:var(--g50);border:2px solid var(--g300);border-radius:10px;padding:11px;text-align:center;margin-bottom:12px}
    .ad-lbl{font-size:10px;color:var(--g700);font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:2px}
    .ad-val{font-size:26px;font-weight:800;color:var(--g800);font-family:var(--mono)}

    /* Quick amount buttons */
    .qamt-row{display:flex;gap:5px;margin-bottom:11px;flex-wrap:wrap}
    .qamt{padding:5px 10px;background:var(--slate50);border:1.5px solid var(--slate200);border-radius:7px;font-size:10.5px;font-weight:600;cursor:pointer;font-family:var(--mono);color:var(--slate600);transition:all .15s;white-space:nowrap}
    .qamt:hover{background:var(--g100);border-color:var(--g500);color:var(--g800)}
    .qamt.exact{background:var(--g50);border-color:var(--g400);color:var(--g700)}
    .qamt.exact:hover{background:var(--g100)}

    .pay-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:12px}
    .pm{padding:9px 5px;border:1.5px solid var(--slate200);border-radius:9px;background:var(--white);cursor:pointer;text-align:center;transition:all .15s}
    .pm:hover{border-color:var(--g400);background:var(--g50)}
    .pm.sel{border-color:var(--g600);background:var(--g50)}
    .pm-ico{font-size:18px;margin-bottom:3px}
    .pm-lbl{font-size:9px;color:var(--slate500);font-weight:600}
    .pm.sel .pm-lbl{color:var(--g700)}

    .input-field{width:100%;background:var(--slate50);border:1.5px solid var(--slate200);border-radius:9px;padding:8px 11px;color:var(--slate800);font-size:13px;font-family:var(--font);outline:none;transition:border-color .15s}
    .input-field:focus{border-color:var(--g500);background:#fff}
    .input-field::placeholder{color:var(--slate400)}

    .tendered-box{background:var(--slate50);border:1.5px solid var(--slate200);border-radius:9px;padding:9px 12px;margin-bottom:7px;min-height:54px}
    .tb-lbl{font-size:10px;color:var(--slate400);margin-bottom:3px;font-weight:500}
    .tb-val{font-size:20px;font-weight:700;color:var(--slate800);font-family:var(--mono)}
    .tb-input{width:100%;background:transparent;border:none;outline:none;font-size:20px;font-weight:700;color:var(--slate800);font-family:var(--mono);padding:0;cursor:text}
    .tb-input::placeholder{color:var(--slate300);font-weight:400;font-size:16px}
    .change-box{display:flex;justify-content:space-between;align-items:center;padding:8px 11px;background:var(--g50);border:1.5px solid var(--g300);border-radius:9px;margin-bottom:11px}
    .ch-lbl{font-size:11px;color:var(--g800);font-weight:600}
    .ch-val{font-size:16px;font-weight:800;color:var(--g700);font-family:var(--mono)}

    .numpad{display:grid;grid-template-columns:repeat(3,1fr);gap:5px}
    .np{padding:10px;background:var(--slate50);border:1.5px solid var(--slate200);border-radius:8px;color:var(--slate700);font-size:15px;font-weight:700;cursor:pointer;font-family:var(--mono);transition:all .15s;text-align:center}
    .np:hover{background:var(--g100);border-color:var(--g400);color:var(--g800)}
    .np:active{transform:scale(.93)}
    .np.w2{grid-column:span 2}
    .np.clr{background:#fff5f5;border-color:#fca5a5;color:var(--red)}
    .np.clr:hover{background:#fee2e2}
    .complete-sale{width:100%;padding:12px;background:var(--g600);border:none;border-radius:9px;color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:var(--font);transition:background .15s;display:flex;align-items:center;justify-content:center;gap:7px}
    .complete-sale:hover:not(:disabled){background:var(--g700)}
    .complete-sale:disabled{background:var(--slate200);color:var(--slate400);cursor:not-allowed}

    /* ── Receipt ────────────────────────────────────────────────── */
    .receipt-paper{background:#fff;color:#1e293b;padding:15px;font-size:10px;font-family:var(--mono);overflow-y:auto;max-height:52vh}
    .rlogo{text-align:center;margin-bottom:9px}
    .rlogo-name{font-size:14px;font-weight:800;color:var(--g700);font-family:var(--font)}
    .rlogo-sub{font-size:9px;color:var(--slate400);margin-top:2px}
    .rdiv{border:none;border-top:1px dashed var(--slate300);margin:6px 0}
    .rrow{display:flex;justify-content:space-between;margin-bottom:3px;font-size:10px;color:var(--slate700)}
    .rtotal{display:flex;justify-content:space-between;font-weight:800;font-size:13px;border-top:1.5px solid var(--g600);padding-top:5px;margin-top:5px;color:var(--g800)}
    .rfooter{text-align:center;font-size:9px;color:var(--slate400);margin-top:9px;line-height:1.8}
    .ract{padding:11px;display:flex;gap:6px;border-top:1px solid var(--slate200);flex-shrink:0}
    .rb{flex:1;padding:9px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;border:1.5px solid var(--slate200);background:var(--white);color:var(--slate600);font-family:var(--font);transition:all .15s;display:flex;align-items:center;justify-content:center;gap:4px}
    .rb:hover{border-color:var(--g500);color:var(--g700);background:var(--g50)}
    .rb.primary{background:var(--g600);border-color:var(--g600);color:#fff}
    .rb.primary:hover{background:var(--g700)}

    /* ── Shift Modal ────────────────────────────────────────────── */
    .shift-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
    .sg-card{background:var(--slate50);border:1px solid var(--slate200);border-radius:9px;padding:10px}
    .sg-lbl{font-size:9px;color:var(--slate500);text-transform:uppercase;letter-spacing:.07em;margin-bottom:3px;font-weight:600}
    .sg-val{font-size:14px;font-weight:800;color:var(--slate800);font-family:var(--mono)}
    .close-shift{width:100%;padding:11px;background:#dc2626;border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);transition:background .15s;display:flex;align-items:center;justify-content:center;gap:6px}
    .close-shift:hover{background:#b91c1c}

    /* ── Held Orders Modal ──────────────────────────────────────── */
    .held-item{display:flex;align-items:center;gap:9px;padding:9px 10px;border:1.5px solid var(--slate200);border-radius:9px;margin-bottom:6px;cursor:default}
    .held-item:hover{border-color:var(--g400);background:var(--g50)}
    .held-info{flex:1;min-width:0}
    .held-name{font-size:12px;font-weight:600;color:var(--slate700)}
    .held-meta{font-size:10px;color:var(--slate400);margin-top:1px;font-family:var(--mono)}
    .held-recall{background:var(--g600);border:none;color:#fff;font-size:10px;font-weight:700;padding:5px 10px;border-radius:7px;cursor:pointer;font-family:var(--font);white-space:nowrap}
    .held-recall:hover{background:var(--g700)}
    .held-del{background:none;border:none;color:var(--slate300);cursor:pointer;font-size:16px;padding:3px;display:flex;align-items:center;border-radius:5px}
    .held-del:hover{color:var(--red);background:#fee2e2}

    /* ── Toast ──────────────────────────────────────────────────── */
    .pos-toast{position:fixed;top:60px;right:14px;background:var(--slate800);color:#fff;padding:8px 14px;border-radius:9px;font-size:12px;font-weight:600;z-index:2000;opacity:0;transition:opacity .2s;pointer-events:none;display:flex;align-items:center;gap:7px;border:1px solid var(--g600);box-shadow:0 4px 16px rgba(0,0,0,.25)}
    .pos-toast.on{opacity:1}
    .pos-toast.err{border-color:#dc2626;background:#1e293b}

    /* ── Offline / sync badges ─────────────────────────────────────── */
    .conn-badge{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;display:flex;align-items:center;gap:4px;font-family:var(--font);flex-shrink:0;border:1px solid transparent}
    .conn-badge.online{background:rgba(22,163,74,.18);color:var(--g300);border-color:rgba(22,163,74,.3)}
    .conn-badge.offline{background:rgba(220,38,38,.2);color:#fca5a5;border-color:rgba(220,38,38,.35)}
    .sync-badge{background:rgba(245,158,11,.2);color:#fcd34d;border:1px solid rgba(245,158,11,.3);padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;display:flex;align-items:center;gap:4px;cursor:pointer;flex-shrink:0}
    .sync-badge:hover{background:rgba(245,158,11,.35)}
    .offline-sale-banner{background:#fef3c7;border:1.5px solid #fcd34d;border-radius:8px;padding:8px 10px;text-align:center;margin-bottom:10px;font-size:10px;color:#92400e;font-weight:700;display:flex;align-items:center;justify-content:center;gap:6px}

    /* ── Animations ─────────────────────────────────────────────── */
    @keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
    .spin{animation:spin .9s linear infinite;display:inline-block}

    /* ── Restaurant panel ─────────────────────────────────────── */
    .rst-panel{margin-top:7px;display:flex;flex-direction:column;gap:5px}
    .rst-row{display:flex;align-items:center;gap:5px}
    .rst-type-btn{flex:1;padding:4px 0;border-radius:7px;font-size:10px;font-weight:700;cursor:pointer;border:1.5px solid rgba(255,255,255,.15);background:rgba(255,255,255,.06);color:rgba(255,255,255,.55);font-family:var(--font);transition:all .15s;text-align:center}
    .rst-type-btn:hover{background:rgba(255,255,255,.12);color:#fff}
    .rst-type-btn.active{background:rgba(74,222,128,.2);border-color:rgba(74,222,128,.5);color:var(--g300)}
    .rst-sel{flex:1;background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.15);border-radius:8px;padding:5px 9px;color:rgba(255,255,255,.75);font-size:11px;font-family:var(--font);cursor:pointer;outline:none;transition:border-color .15s}
    .rst-sel:focus{border-color:var(--g500);background:rgba(255,255,255,.12)}
    .rst-sel option{color:var(--slate800);background:#fff}
    .rst-sel.has-val{border-color:rgba(74,222,128,.5);color:var(--g300)}
    .rst-num{width:58px;background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.15);border-radius:8px;padding:5px 8px;color:rgba(255,255,255,.75);font-size:11px;font-family:var(--font);outline:none;text-align:center;-moz-appearance:textfield}
    .rst-num::-webkit-outer-spin-button,.rst-num::-webkit-inner-spin-button{-webkit-appearance:none}
    .rst-num:focus{border-color:var(--g500)}
    .rst-cust-wrap{position:relative;flex:1}
    .rst-cust-input{width:100%;background:rgba(255,255,255,.08);border:1.5px solid rgba(255,255,255,.15);border-radius:8px;padding:5px 9px;color:rgba(255,255,255,.75);font-size:11px;font-family:var(--font);outline:none;transition:border-color .15s}
    .rst-cust-input:focus{border-color:var(--g500);background:rgba(255,255,255,.12)}
    .rst-cust-input::placeholder{color:rgba(255,255,255,.35)}
    .rst-cust-input.has-cust{border-color:rgba(74,222,128,.5);color:var(--g300)}
    .cust-dropdown{position:absolute;top:calc(100% + 3px);left:0;right:0;background:var(--slate700);border:1.5px solid var(--slate600);border-radius:8px;z-index:500;max-height:130px;overflow-y:auto;box-shadow:0 6px 16px rgba(0,0,0,.35)}
    .cust-opt{padding:6px 10px;font-size:11px;color:rgba(255,255,255,.8);cursor:pointer;transition:background .12s;border-bottom:1px solid rgba(255,255,255,.06)}
    .cust-opt:last-child{border-bottom:none}
    .cust-opt:hover{background:rgba(22,163,74,.25);color:#fff}
    .cust-opt-name{font-weight:600}
    .cust-opt-phone{font-size:9px;color:rgba(255,255,255,.45);font-family:var(--mono)}

    /* ── Print ──────────────────────────────────────────────────── */
    @media print{
      .pos-topbar,.cat-sidebar,.pos-left,.pos-right .cart-head,.pos-right .cart-list,
      .pos-right .totals-panel,.pos-right .actions,.ract,.pos-toast,
      .mhdr .mclose,.ci-disc-row,.note-wrap{display:none!important}
      .modal-bg{position:static;background:none;display:block}
      .modal{border:none;max-height:none;box-shadow:none;width:100%}
      .mhdr{background:none;padding:8px 0}
      .mtitle{color:var(--slate800)}
      body.print-thermal .modal{width:76mm!important;font-size:9px}
      body.print-thermal .receipt-paper{font-size:9px;padding:8px}
      body.print-thermal .rlogo-name{font-size:13px}
      body.print-pos .modal{width:58mm!important;font-size:8px}
      body.print-pos .receipt-paper{font-size:8px;padding:6px}
      body.print-a4 .modal{width:190mm!important;max-width:210mm;margin:0 auto;font-size:11px}
      body.print-a4 .receipt-paper{font-size:11px;padding:20px 24px;max-height:none}
      body.print-a4 .rlogo-name{font-size:18px}
    }
  </style>
</head>
<body>

<div id="pos-app">
  <div style="height:100vh;display:flex;align-items:center;justify-content:center;background:var(--slate800);font-family:'DM Sans',sans-serif;">
    <div style="text-align:center">
      <div style="width:44px;height:44px;background:var(--g600);border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-weight:900;font-size:18px;color:#fff">
        <?php echo strtoupper(substr(get_option('companyname'), 0, 1)); ?>
      </div>
      <p style="font-size:13px;font-weight:600;color:rgba(255,255,255,.7)">Loading POS Terminal…</p>
    </div>
  </div>
</div>

<script>
  window.POS_CONFIG = {
    api_url:      '<?php echo rtrim($api_url, '/'); ?>',
    api_token:    '<?php echo $api_token; ?>',
    branch_id:    <?php echo (int) $branch_id; ?>,
    currency:     '<?php echo pos_get_setting('default_currency', $branch_id) ?? 'KES'; ?>',
    locale:       '<?php echo get_option('default_language') ?: 'en'; ?>',
    admin_url:    '<?php echo admin_url(); ?>',
    logo:         '<?php echo base_url('uploads/' . get_option('company_logo')); ?>',
    company_name: '<?php echo addslashes(get_option('companyname')); ?>',
    staff_name:    '<?php echo addslashes(get_staff_full_name(get_staff_user_id())); ?>',
    receipt_format:    '<?php echo pos_get_setting('pos_receipt_format', $branch_id) ?? 'thermal'; ?>',
    restaurant_mode:   <?php echo pos_get_setting('pos_restaurant_mode') == '1' ? 'true' : 'false'; ?>,
  };
</script>

<!-- Register Service Worker for offline support -->
<script>
if ('serviceWorker' in navigator) {
  const swUrl   = '<?php echo base_url("pos-sw.js"); ?>';
  const swScope = '<?php echo parse_url(base_url(), PHP_URL_PATH) ?: "/"; ?>';
  navigator.serviceWorker.register(swUrl, { scope: swScope })
    .then(r => console.log('[POS-SW] registered scope:', r.scope))
    .catch(e => console.warn('[POS-SW] registration failed:', e));
}
</script>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="https://unpkg.com/vue-demi"></script>
<script src="https://unpkg.com/pinia@2/dist/pinia.iife.prod.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<script>
const { createApp, ref, computed, onMounted, onUnmounted, watch } = Vue;
const { createPinia, defineStore } = Pinia;

const api = axios.create({
  baseURL: window.POS_CONFIG.api_url,
  headers: { Authorization: `Bearer ${window.POS_CONFIG.api_token}` },
});

// ─── Store: Cart ──────────────────────────────────────────────────────────
const useCartStore = defineStore('cart', {
  state: () => ({
    items:    [],
    customer: null,
    discount: { type: null, value: 0 },
    note:     '',
    session:  null,
  }),
  getters: {
    subtotal:        (s) => s.items.reduce((sum, i) => sum + i.line_total, 0),
    tax_total:       (s) => s.items.reduce((sum, i) => sum + i.tax_amount, 0),
    discount_amount: (s) => {
      if (!s.discount.type) return 0;
      const sub = s.items.reduce((sum, i) => sum + i.line_total, 0);
      return s.discount.type === 'percent'
        ? sub * (s.discount.value / 100)
        : parseFloat(s.discount.value);
    },
    total: (s) => {
      const sub  = s.items.reduce((sum, i) => sum + i.line_total, 0);
      const tax  = s.items.reduce((sum, i) => sum + i.tax_amount, 0);
      const disc = (() => {
        if (!s.discount.type) return 0;
        return s.discount.type === 'percent'
          ? sub * (s.discount.value / 100)
          : parseFloat(s.discount.value);
      })();
      return Math.max(0, sub + tax - disc);
    },
    item_count: (s) => s.items.reduce((sum, i) => sum + i.quantity, 0),
  },
  actions: {
    add_item(product, variation = null) {
      const key      = variation ? `${product.id}-${variation.id}` : `${product.id}`;
      const idx      = this.items.findIndex(i => i._key === key);
      const price    = variation ? parseFloat(variation.selling_price) : parseFloat(product.selling_price);
      const tax_rate = parseFloat(product.tax_rate_value || 0) / 100;
      if (idx >= 0) {
        this.items[idx].quantity++;
      } else {
        this.items.push({
          _key:         key,
          product_id:   product.pos_product_id || product.id,
          variation_id: variation ? variation.id : null,
          product_name: variation
            ? `${product.name} (${variation.options.map(o => o.attribute_value).join(', ')})`
            : product.name,
          sku:          variation ? variation.sku : product.sku,
          quantity:     1,
          unit_price:   price,
          cost_price:   parseFloat(variation ? variation.cost_price : product.cost_price || 0),
          discount_pct: 0,
          discount_amt: 0,
          tax_rate,
          tax_amount:   price * tax_rate,
          line_total:   price,
        });
      }
      this._recalc_item(this.items.findIndex(i => i._key === key));
    },
    update_qty(key, qty) {
      const idx = this.items.findIndex(i => i._key === key);
      if (idx < 0) return;
      if (qty <= 0) { this.items.splice(idx, 1); return; }
      this.items[idx].quantity = qty;
      this._recalc_item(idx);
    },
    remove_item(key) { this.items = this.items.filter(i => i._key !== key); },
    clear() {
      this.items    = [];
      this.customer = null;
      this.discount = { type: null, value: 0 };
      this.note     = '';
    },
    _recalc_item(idx) {
      const item       = this.items[idx];
      const base       = item.quantity * item.unit_price;
      const disc       = item.discount_pct > 0 ? base * (item.discount_pct / 100) : item.discount_amt;
      item.discount_amt = disc;
      item.line_total  = base - disc;
      item.tax_amount  = item.line_total * item.tax_rate;
    },
  },
  persist: { key: `pos_cart_${window.POS_CONFIG.branch_id}` },
});

// ─── IndexedDB offline cache ──────────────────────────────────────────────
const OfflineDB = (() => {
  const DB_NAME = 'pos_offline_db', DB_VER = 2;
  let _db = null;

  function open() {
    if (_db) return Promise.resolve(_db);
    return new Promise((res, rej) => {
      const r = indexedDB.open(DB_NAME, DB_VER);
      r.onupgradeneeded = e => {
        const db = e.target.result;
        ['products','categories','payment_methods','offline_sales','meta'].forEach(s => {
          if (!db.objectStoreNames.contains(s)) {
            const kp = s === 'offline_sales' ? 'sale_uid' : s === 'meta' ? 'k' : 'id';
            db.createObjectStore(s, { keyPath: kp });
          }
        });
      };
      r.onsuccess = e => { _db = e.target.result; res(_db); };
      r.onerror   = e => rej(e.target.error);
    });
  }

  function store(name, mode = 'readonly') {
    return open().then(db => db.transaction(name, mode).objectStore(name));
  }

  function getAll(name) {
    return new Promise((res, rej) =>
      store(name).then(s => { const r = s.getAll(); r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error); })
    );
  }

  function put(name, val) {
    return new Promise((res, rej) =>
      store(name, 'readwrite').then(s => { const r = s.put(val); r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error); })
    );
  }

  function putMany(name, items) {
    return open().then(db => new Promise((res, rej) => {
      const t = db.transaction(name, 'readwrite'), s = t.objectStore(name);
      items.forEach(i => s.put(i));
      t.oncomplete = res; t.onerror = rej;
    }));
  }

  function del(name, key) {
    return new Promise((res, rej) =>
      store(name, 'readwrite').then(s => { const r = s.delete(key); r.onsuccess = res; r.onerror = rej; })
    );
  }

  return { getAll, put, putMany, del };
})();

// ─── Store: Products ──────────────────────────────────────────────────────
const useProductStore = defineStore('products', {
  state: () => ({ products: [], categories: [], loading: false, last_sync: null }),
  actions: {
    async load(force = false) {
      if (!force && this.products.length && this.last_sync && (Date.now() - this.last_sync) < 300_000) return;
      this.loading = true;
      try {
        if (!navigator.onLine) throw new Error('offline');
        const [p, c] = await Promise.all([
          api.get('/products/pos'),
          api.get('/products/categories'),
        ]);
        this.products   = p.data.data;
        this.categories = c.data.data;
        this.last_sync  = Date.now();
        // Cache for offline use
        await Promise.all([
          OfflineDB.putMany('products',   this.products),
          OfflineDB.putMany('categories', this.categories),
        ]);
      } catch {
        // Offline or network error — serve from IndexedDB cache
        const [prods, cats] = await Promise.all([
          OfflineDB.getAll('products'),
          OfflineDB.getAll('categories'),
        ]);
        if (prods.length) { this.products = prods; this.categories = cats; }
      } finally { this.loading = false; }
    },
  },
});

// ─── Category color palette (hashed from id) ──────────────────────────────
const CAT_COLORS = ['#22c55e','#3b82f6','#f59e0b','#ec4899','#8b5cf6','#06b6d4','#f97316','#14b8a6'];
function cat_color(id) { return CAT_COLORS[(id || 0) % CAT_COLORS.length]; }

// ─── Main App ─────────────────────────────────────────────────────────────
const App = {
  template: `
    <div class="pos-shell">

      <!-- ══ Topbar ══════════════════════════════════════════════════ -->
      <div class="pos-topbar">
        <div class="logo-wrap">
          <img v-if="cfg.logo && cfg.logo.indexOf('uploads/') > -1 && cfg.logo.slice(-1) !== '/'"
               :src="cfg.logo" class="logo-img"
               @error="e => e.target.style.display='none'">
          <div class="logo-circle">{{ company_initial }}</div>
          <div class="logo-text">{{ cfg.company_name || 'XetuuPOS' }} <span>POS Terminal</span></div>
        </div>
        <div class="topbar-mid">
          <div class="tb-chip">
            <span :class="['dot', session ? '' : 'amber']"></span>
            {{ session ? 'Session Active' : 'No Session' }}
          </div>
          <div class="tb-stat">
            <span class="lbl">Cashier</span>
            <span class="val">{{ short_name }}</span>
          </div>
          <div class="tb-stat">
            <span class="lbl">Branch</span>
            <span class="val">#{{ cfg.branch_id }}</span>
          </div>
          <div v-if="session" class="tb-stat">
            <span class="lbl">Float</span>
            <span class="val">{{ format_price(session.opening_float || 0) }}</span>
          </div>
        </div>
        <div class="topbar-right">
          <span class="timer-badge">{{ shift_timer }}</span>
          <!-- Connection status -->
          <span :class="['conn-badge', is_online ? 'online' : 'offline']">
            <i :class="is_online ? 'ti ti-wifi' : 'ti ti-wifi-off'"></i>
            {{ is_online ? 'Online' : 'Offline' }}
          </span>
          <!-- Pending sync count (offline sales queued) -->
          <span v-if="pending_sync_count > 0" class="sync-badge"
                @click="is_online && sync_offline_queue()"
                :title="is_online ? 'Click to sync now' : 'Will sync when online'">
            <i :class="['ti ti-cloud-upload', syncing_queue && 'spin']"></i>
            {{ pending_sync_count }} pending
          </span>
          <button class="tb-btn" @click="show_shift = true" title="Shift overview">
            <i class="ti ti-clock"></i> Shift
          </button>
          <button class="tb-btn" @click="productStore.load(true)" :title="'Sync products'">
            <i :class="productStore.loading ? 'ti ti-refresh spin' : 'ti ti-refresh'"></i>
          </button>
          <button class="tb-btn" @click="go_admin" title="Back to Admin">
            <i class="ti ti-layout-dashboard"></i>
          </button>
        </div>
      </div>

      <!-- ══ Body ═════════════════════════════════════════════════════ -->
      <div class="pos-body">

        <!-- ── Category Sidebar ─────────────────────────────────── -->
        <div class="cat-sidebar">
          <div class="cat-sidebar-hdr">
            <i class="ti ti-category" style="font-size:11px"></i> Categories
          </div>
          <div class="cat-sidebar-list">
            <button :class="['cat-sb-btn', !active_category && 'active']"
                    @click="active_category = null">
              <span>All Items</span>
              <span class="cat-sb-count">{{ productStore.products.length }}</span>
            </button>
            <div class="cat-sb-divider" v-if="productStore.categories.length"></div>
            <button v-for="cat in productStore.categories" :key="cat.id"
                    :class="['cat-sb-btn', active_category === cat.id && 'active']"
                    @click="active_category = cat.id">
              <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;flex:1">{{ cat.name }}</span>
              <span class="cat-sb-count">{{ productStore.products.filter(p => p.category_id == cat.id).length }}</span>
            </button>
          </div>
        </div>

        <!-- ── Center: Products ──────────────────────────────────── -->
        <div class="pos-left">

          <!-- Search / Barcode -->
          <div class="search-zone">
            <div class="search-wrap">
              <i class="ti ti-barcode search-icon"></i>
              <input ref="search_input" v-model="search" class="search-input"
                     placeholder="Search products or scan barcode…"
                     @keyup.enter="barcode_lookup" autocomplete="off">
            </div>
            <span class="shortcut-hint" title="Press / to focus search">/</span>
          </div>

          <!-- Product count -->
          <div class="pcount-bar">
            <i class="ti ti-package" style="font-size:12px"></i>
            {{ filtered_products.length }} product{{ filtered_products.length !== 1 ? 's' : '' }}
            <span v-if="active_category" style="color:var(--g600);font-weight:600"> · {{ (productStore.categories.find(c => c.id === active_category) || {}).name || '' }}</span>
            <span v-if="search" style="color:var(--g600);font-weight:600"> matching "{{ search }}"</span>
          </div>

          <!-- Product Grid -->
          <div class="pgrid" v-if="!productStore.loading">
            <div v-if="filtered_products.length === 0" class="pgrid-empty">
              <i class="ti ti-search-off"></i>No products found
            </div>
            <div v-for="p in filtered_products" :key="p.id"
                 :class="['pcard', (p.stock_qty <= 0 && !p.allow_negative) && 'out']"
                 @click="select_product(p)"
                 :title="p.name + (p.sku ? ' · ' + p.sku : '') + ' — ' + stock_label(p)">
              <!-- Stock badge: always shows qty number; amber dashed when out but orderable -->
              <span :class="['sbadge',
                p.stock_qty <= 0 && !p.allow_negative ? 'sout' :
                p.stock_qty <= 0 &&  p.allow_negative ? 'sorder' :
                p.stock_qty <= 5  ? 'slow' : 'sok']">
                {{ p.stock_qty <= 0 ? 'Out' : p.stock_qty > 999 ? '999+' : Math.floor(p.stock_qty) }}
              </span>
              <!-- Category color dot -->
              <span v-if="p.category_id" class="cat-dot"
                    :style="{ background: cat_color(p.category_id) }"></span>
              <!-- Image / icon -->
              <div class="pcard-top">
                <img v-if="p.image" :src="p.image" :alt="p.name">
                <i v-else class="ti ti-package" style="font-size:22px;color:var(--g400)"></i>
              </div>
              <div class="pname">{{ p.name }}</div>
              <div class="pprice">{{ format_price(p.selling_price) }}</div>
              <div class="padd-hint"><i class="ti ti-plus"></i></div>
            </div>
          </div>
          <div class="pgrid" v-else style="align-content:center">
            <div class="pgrid-empty">
              <i class="ti ti-loader spin" style="color:var(--g400)"></i>
              Loading products…
            </div>
          </div>

        </div><!-- /pos-left -->

        <!-- ── Right: Cart ─────────────────────────────────────────── -->
        <div class="pos-right">

          <div class="cart-head">
            <div class="cart-title">
              <i class="ti ti-shopping-cart" style="color:var(--g400);font-size:15px"></i>
              Cart <span class="cart-badge">{{ cartStore.item_count }}</span>
            </div>

            <!-- Restaurant order panel -->
            <div v-if="cfg.restaurant_mode" class="rst-panel">
              <!-- Order type: Dine-in / Takeaway / Bar -->
              <div class="rst-row">
                <button v-for="t in ['dine-in','takeaway','bar']" :key="t"
                        :class="['rst-type-btn', order_type === t && 'active']"
                        @click="order_type = t">
                  {{ t === 'dine-in' ? 'Restaurant' : t === 'takeaway' ? 'Takeaway' : 'Bar' }}
                </button>
              </div>
              <!-- Table + Covers -->
              <div class="rst-row">
                <i class="ti ti-armchair-2" style="color:var(--g400);font-size:13px;flex-shrink:0"></i>
                <select :class="['rst-sel', selected_table && 'has-val']" v-model="selected_table"
                        style="flex:1">
                  <option value="">— Table —</option>
                  <option v-for="tb in tables" :key="tb.id" :value="tb.id"
                          :disabled="tb.status === 'occupied'">
                    {{ tb.table_number }}<template v-if="tb.name"> · {{ tb.name }}</template>
                    ({{ tb.seats }})<template v-if="tb.status === 'occupied'"> ×</template>
                  </option>
                </select>
                <i class="ti ti-users" style="color:var(--g400);font-size:13px;flex-shrink:0"></i>
                <input type="number" v-model.number="covers" min="1" max="99"
                       class="rst-num" title="Number of covers / guests">
              </div>
              <!-- Customer search -->
              <div class="rst-row">
                <i class="ti ti-user" style="color:var(--g400);font-size:13px;flex-shrink:0"></i>
                <div class="rst-cust-wrap">
                  <input :class="['rst-cust-input', selected_customer && 'has-cust']"
                         v-model="cust_search"
                         @input="on_cust_input"
                         @keydown.escape="cust_results = []; selected_customer = null; cust_search = ''"
                         :placeholder="selected_customer ? selected_customer.name : 'Customer name…'"
                         autocomplete="off">
                  <div v-if="cust_results.length" class="cust-dropdown">
                    <div v-for="c in cust_results" :key="c.id" class="cust-opt"
                         @mousedown.prevent="pick_customer(c)">
                      <div class="cust-opt-name">{{ c.name }}</div>
                      <div class="cust-opt-phone">{{ c.phone }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Walk-in customer (non-restaurant mode) -->
            <select v-else class="cust-sel">
              <option value="">Walk-in Customer</option>
            </select>
          </div>

          <div class="cart-list">
            <div v-if="initializing" class="empty-cart">
              <i class="ti ti-loader spin" style="color:var(--g300)"></i>
              <p>Loading…</p>
            </div>
            <div v-else-if="!session" class="empty-cart">
              <i class="ti ti-lock" style="color:var(--amber)"></i>
              <p>No active session<br>
                <button class="abtn" style="margin-top:10px;padding:8px 16px;flex:none"
                        @click="open_session_modal = true">
                  <i class="ti ti-lock-open"></i> Open Session
                </button>
              </p>
            </div>
            <div v-else-if="cartStore.items.length === 0" class="empty-cart">
              <i class="ti ti-shopping-bag"></i>
              <p>Cart is empty<br><small>Tap a product or scan a barcode</small></p>
            </div>
            <div v-for="item in cartStore.items" :key="item._key" class="ci">
              <div class="ci-img"><i class="ti ti-package"></i></div>
              <div class="ci-info">
                <div class="ci-name" :title="item.product_name">{{ item.product_name }}</div>
                <div class="ci-sub" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap">
                  {{ format_price(item.unit_price) }} × {{ item.quantity }}
                  <span v-if="item.discount_pct > 0" class="disc-badge">−{{ item.discount_pct }}%</span>
                  <span v-if="item.tax_rate > 0" style="font-size:8px;color:var(--slate400)">(VAT {{ (item.tax_rate*100).toFixed(0) }}%)</span>
                </div>
              </div>
              <button class="qb disc-btn" title="Item discount" @click="open_item_discount(item._key)">%</button>
              <div class="qty-ctrl">
                <button class="qb" @click="cartStore.update_qty(item._key, item.quantity - 1)">−</button>
                <span class="qn">{{ item.quantity }}</span>
                <button class="qb" @click="cartStore.update_qty(item._key, item.quantity + 1)">+</button>
              </div>
              <button class="cdel" @click="cartStore.remove_item(item._key)">
                <i class="ti ti-x"></i>
              </button>
              <div v-if="editing_discount === item._key" class="ci-disc-row">
                <span style="font-size:9px;color:var(--slate500);font-weight:600;flex-shrink:0">% off</span>
                <input v-model="disc_pct_input" type="number" min="0" max="100" step="0.5"
                       class="disc-input" placeholder="0"
                       @keyup.enter="apply_item_discount(item._key)"
                       @keyup.escape="editing_discount = null">
                <button class="disc-apply" @click="apply_item_discount(item._key)">Apply</button>
                <button class="disc-cancel" @click="editing_discount = null">×</button>
              </div>
            </div>
          </div>

          <!-- Sale note field -->
          <div class="note-wrap" v-if="session">
            <textarea v-model="cartStore.note" class="note-field"
                      placeholder="Add note to this sale…" maxlength="200"></textarea>
          </div>

          <!-- Totals -->
          <div class="totals-panel" v-if="cartStore.items.length > 0">
            <div class="trow">
              <span class="tlbl">Subtotal</span>
              <span class="tval">{{ format_price(cartStore.subtotal) }}</span>
            </div>
            <div v-if="cartStore.discount_amount > 0" class="trow">
              <span class="tlbl" style="color:#b45309">Discount</span>
              <span class="tval" style="color:#b45309">−{{ format_price(cartStore.discount_amount) }}</span>
            </div>
            <div v-if="cartStore.tax_total > 0" class="trow">
              <span class="tlbl">Tax</span>
              <span class="tval">{{ format_price(cartStore.tax_total) }}</span>
            </div>
            <hr class="tdivider">
            <div class="grand-row">
              <span class="grand-lbl">TOTAL</span>
              <span class="grand-val">{{ format_price(cartStore.total) }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="actions">
            <div class="action-row">
              <button class="abtn amber-btn" @click="do_discount">
                <i class="ti ti-percent"></i> Discount
              </button>
              <button class="abtn" @click="hold_order" style="position:relative">
                <i class="ti ti-bookmark"></i> Hold
                <span v-if="held_orders.length" class="held-count">{{ held_orders.length }}</span>
              </button>
              <button class="abtn" @click="held_orders.length && (show_held = true)"
                      :style="{ opacity: held_orders.length ? 1 : 0.4 }"
                      title="Recall held order">
                <i class="ti ti-history"></i> Recall
              </button>
              <button class="abtn danger" @click="void_cart">
                <i class="ti ti-trash"></i> Void
              </button>
            </div>
            <!-- Restaurant status indicator -->
            <div v-if="cfg.restaurant_mode && selected_table" style="display:flex;align-items:center;gap:6px;padding:4px 2px;font-size:10px;color:var(--g600)">
              <i class="ti ti-chef-hat" style="font-size:13px"></i>
              KOT will fire to kitchen on payment
            </div>
            <button class="checkout"
                    :disabled="cartStore.items.length === 0 || !session"
                    @click="open_payment_modal">
              <i class="ti ti-credit-card" style="font-size:16px"></i>
              Pay Now — {{ format_price(cartStore.total) }}
            </button>
          </div>

        </div><!-- /pos-right -->
      </div><!-- /pos-body -->

      <!-- ══ Toast ═════════════════════════════════════════════════════ -->
      <div :class="['pos-toast', toast_on && 'on', toast_err && 'err']">
        <i :class="toast_err ? 'ti ti-alert-circle' : 'ti ti-check'"></i>
        <span>{{ toast_msg }}</span>
      </div>

      <!-- ══ Payment Modal ══════════════════════════════════════════════ -->
      <div v-if="show_payment" class="modal-bg" @click.self="show_payment = false">
        <div class="modal">
          <div class="mhdr">
            <span class="mtitle"><i class="ti ti-credit-card"></i> Payment</span>
            <button class="mclose" @click="show_payment = false"><i class="ti ti-x"></i></button>
          </div>
          <div class="mbody">
            <div class="amount-due">
              <div class="ad-lbl">Amount Due</div>
              <div class="ad-val">{{ format_price(cartStore.total) }}</div>
            </div>
            <!-- Quick amount presets -->
            <div class="qamt-row">
              <button v-for="qa in quick_amounts" :key="qa.value"
                      :class="['qamt', qa.is_exact && 'exact']"
                      @click="set_quick_amount(qa.value)">
                {{ qa.label }}
              </button>
            </div>
            <!-- Payment methods -->
            <div class="pay-grid">
              <div v-for="method in payment_methods" :key="method.id"
                   :class="['pm', selected_payment === method.id && 'sel']"
                   @click="selected_payment = method.id">
                <div class="pm-ico">{{ payment_icon(method.type) }}</div>
                <div class="pm-lbl">{{ method.name }}</div>
              </div>
            </div>
            <div v-if="selected_payment_obj && selected_payment_obj.type === 'mobile_money'"
                 style="margin-bottom:10px">
              <input v-model="mm_phone" type="tel" placeholder="07XX XXX XXX"
                     class="input-field" style="margin-bottom:0">
            </div>
            <div class="tendered-box" style="cursor:text" @click="$refs.tendered_input && $refs.tendered_input.focus()">
              <div class="tb-lbl">Amount Received</div>
              <input ref="tendered_input" type="text" inputmode="decimal" class="tb-input"
                     v-model="tendered_str" placeholder="0"
                     @input="sanitize_tendered"
                     @keydown.enter.prevent="numpad_can_complete && !processing && complete_sale()">
            </div>
            <div class="change-box">
              <span class="ch-lbl"><i class="ti ti-cash" style="margin-right:4px;font-size:13px"></i>Change</span>
              <span class="ch-val">{{ format_price(numpad_change) }}</span>
            </div>
            <div class="numpad">
              <button v-for="n in [7,8,9,4,5,6,1,2,3]" :key="n" class="np" @click="numpad_press(n)">{{ n }}</button>
              <button class="np w2" @click="numpad_press(0)">0</button>
              <button class="np clr" @click="numpad_backspace"><i class="ti ti-backspace"></i></button>
            </div>
          </div>
          <div class="mfooter">
            <button class="complete-sale" :disabled="!numpad_can_complete || processing" @click="complete_sale">
              <i :class="processing ? 'ti ti-loader spin' : 'ti ti-check'"></i>
              {{ processing ? 'Processing…' : 'Complete Sale' }}
            </button>
          </div>
        </div>
      </div>

      <!-- ══ Receipt Modal ══════════════════════════════════════════════ -->
      <div v-if="last_sale" class="modal-bg">
        <div class="modal" style="width:300px">
          <div class="mhdr">
            <span class="mtitle"><i class="ti ti-receipt"></i> Receipt</span>
            <button class="mclose" @click="new_sale"><i class="ti ti-x"></i></button>
          </div>
          <div class="receipt-paper">
            <div v-if="last_sale._offline" class="offline-sale-banner">
              <i class="ti ti-cloud-off"></i> OFFLINE SALE — syncs automatically when connected
            </div>
            <div class="rlogo">
              <div class="rlogo-name">{{ cfg.company_name || 'XetuuPOS' }}</div>
              <div class="rlogo-sub">{{ last_sale.receipt_number }}</div>
            </div>
            <hr class="rdiv">
            <div class="rrow"><span>Date</span><span>{{ last_sale.date_created }}</span></div>
            <div class="rrow"><span>Cashier</span><span>{{ cfg.staff_name }}</span></div>
            <div class="rrow"><span>Payment</span><span>{{ selected_payment_name }}</span></div>
            <hr class="rdiv">
            <div v-for="item in last_sale.items" :key="item.id" class="rrow">
              <span>{{ item.quantity }}× {{ item.product_name }}</span>
              <span>{{ format_price(item.line_total) }}</span>
            </div>
            <div class="rrow" style="margin-top:5px">
              <span>Subtotal</span><span>{{ format_price(last_sale.subtotal || 0) }}</span>
            </div>
            <div v-if="last_sale.discount_amount > 0" class="rrow">
              <span>Discount</span><span>−{{ format_price(last_sale.discount_amount) }}</span>
            </div>
            <div v-if="last_sale.tax_total > 0" class="rrow">
              <span>Tax</span><span>{{ format_price(last_sale.tax_total) }}</span>
            </div>
            <div class="rtotal">
              <span>TOTAL</span><span>{{ format_price(last_sale.total) }}</span>
            </div>
            <div v-if="last_sale.amount_paid" class="rrow" style="margin-top:4px">
              <span>Paid</span><span>{{ format_price(last_sale.amount_paid) }}</span>
            </div>
            <div v-if="last_sale.change_given > 0" class="rrow">
              <span>Change</span><span>{{ format_price(last_sale.change_given) }}</span>
            </div>
            <div v-if="last_sale.note" class="rrow" style="margin-top:4px">
              <span>Note</span><span style="text-align:right;max-width:60%">{{ last_sale.note }}</span>
            </div>
            <hr class="rdiv">
            <div class="rfooter">Thank you for your business!</div>
          </div>
          <div class="ract">
            <button class="rb" @click="new_sale"><i class="ti ti-x"></i> Close</button>
            <button class="rb" @click="print_receipt"><i class="ti ti-printer"></i> Print</button>
            <button class="rb primary" @click="new_sale"><i class="ti ti-plus"></i> New</button>
          </div>
        </div>
      </div>

      <!-- ══ Variation Picker ═══════════════════════════════════════════ -->
      <div v-if="variation_product" class="modal-bg" @click.self="variation_product = null">
        <div class="modal">
          <div class="mhdr">
            <span class="mtitle"><i class="ti ti-list"></i> Select Variation</span>
            <button class="mclose" @click="variation_product = null"><i class="ti ti-x"></i></button>
          </div>
          <div class="mbody">
            <p style="font-size:12px;color:var(--slate500);margin-bottom:12px;font-weight:600">
              {{ variation_product.name }}
            </p>
            <div class="pay-grid" style="grid-template-columns:repeat(2,1fr)">
              <div v-for="v in variation_product.variations" :key="v.id"
                   class="pm"
                   @click="add_variation(variation_product, v)">
                <div class="pm-ico" style="font-size:14px;margin-bottom:4px">📦</div>
                <div class="pm-lbl" style="font-size:10px;line-height:1.4">
                  {{ v.options.map(o => o.attribute_value).join(' / ') }}
                </div>
                <div style="font-size:11px;font-weight:800;color:var(--g700);font-family:var(--mono);margin-top:5px">
                  {{ format_price(v.selling_price) }}
                </div>
              </div>
            </div>
            <button class="complete-sale" style="background:var(--slate100);color:var(--slate600);margin-top:4px"
                    @click="variation_product = null">Cancel</button>
          </div>
        </div>
      </div>

      <!-- ══ Open Session Modal ══════════════════════════════════════════ -->
      <div v-if="open_session_modal" class="modal-bg">
        <div class="modal">
          <div class="mhdr">
            <span class="mtitle"><i class="ti ti-lock-open"></i> Open POS Session</span>
            <button class="mclose" @click="open_session_modal = false"><i class="ti ti-x"></i></button>
          </div>
          <div class="mbody">
            <div style="margin-bottom:16px">
              <div class="tb-lbl" style="margin-bottom:6px;font-size:11px;color:var(--slate600)">
                Opening Float (Cash in Drawer)
              </div>
              <input v-model.number="session_float" type="number" step="0.01" min="0"
                     class="input-field" placeholder="0.00">
            </div>
            <button @click="do_open_session" class="complete-sale" :disabled="processing">
              <i :class="processing ? 'ti ti-loader spin' : 'ti ti-lock-open'"></i>
              {{ processing ? 'Opening…' : 'Open Session' }}
            </button>
            <button @click="open_session_modal = false"
                    class="complete-sale" style="background:var(--slate100);color:var(--slate600);margin-top:7px">
              Cancel
            </button>
          </div>
        </div>
      </div>

      <!-- ══ Shift Overview Modal ════════════════════════════════════════ -->
      <div v-if="show_shift" class="modal-bg" @click.self="show_shift = false">
        <div class="modal">
          <div class="mhdr">
            <span class="mtitle"><i class="ti ti-clock"></i> My Shift</span>
            <button class="mclose" @click="show_shift = false"><i class="ti ti-x"></i></button>
          </div>
          <div class="mbody">
            <div class="shift-grid">
              <div class="sg-card">
                <div class="sg-lbl">Duration</div>
                <div class="sg-val">{{ shift_timer }}</div>
              </div>
              <div class="sg-card">
                <div class="sg-lbl">Cash Float</div>
                <div class="sg-val">{{ format_price(session ? session.opening_float || 0 : 0) }}</div>
              </div>
              <div class="sg-card">
                <div class="sg-lbl">Cashier</div>
                <div class="sg-val" style="font-size:11px;font-family:var(--font)">{{ cfg.staff_name }}</div>
              </div>
              <div class="sg-card">
                <div class="sg-lbl">Branch</div>
                <div class="sg-val" style="font-size:11px">#{{ cfg.branch_id }}</div>
              </div>
            </div>
            <button class="close-shift" @click="confirm_close_shift">
              <i class="ti ti-lock"></i> Close Shift
            </button>
          </div>
        </div>
      </div>

      <!-- ══ Held Orders Modal ══════════════════════════════════════════ -->
      <div v-if="show_held" class="modal-bg" @click.self="show_held = false">
        <div class="modal">
          <div class="mhdr">
            <span class="mtitle"><i class="ti ti-history"></i> Held Orders ({{ held_orders.length }})</span>
            <button class="mclose" @click="show_held = false"><i class="ti ti-x"></i></button>
          </div>
          <div class="mbody">
            <div v-if="held_orders.length === 0" style="text-align:center;padding:24px;color:var(--slate400);font-size:12px">
              No held orders
            </div>
            <div v-for="(order, idx) in held_orders" :key="order.id" class="held-item">
              <div class="held-info">
                <div class="held-name">{{ order.items.length }} item{{ order.items.length !== 1 ? 's' : '' }} — {{ format_price(order.total) }}</div>
                <div class="held-meta">Held at {{ order.held_at }} · {{ order.items.map(i => i.product_name).slice(0,2).join(', ') }}{{ order.items.length > 2 ? '…' : '' }}</div>
              </div>
              <button class="held-recall" @click="recall_hold(idx)">Recall</button>
              <button class="held-del" @click="delete_hold(idx)" title="Discard"><i class="ti ti-trash"></i></button>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /pos-shell -->
  `,

  setup() {
    const cartStore    = useCartStore();
    const productStore = useProductStore();
    const cfg          = window.POS_CONFIG;

    const search             = ref('');
    const active_category    = ref(null);
    const selected_payment   = ref(null);
    const mm_phone           = ref('');
    const processing         = ref(false);
    const payment_methods    = ref([]);
    const session            = ref(null);
    const variation_product  = ref(null);
    const open_session_modal = ref(false);
    const session_float      = ref(0);
    const last_sale          = ref(null);

    // ── Restaurant ─────────────────────────────────────────────────
    const tables             = ref([]);
    const selected_table     = ref('');
    const order_type         = ref('dine-in');
    const covers             = ref(1);
    const cust_search        = ref('');
    const cust_results       = ref([]);
    const selected_customer  = ref(null);
    let   cust_search_timer  = null;

    const toast_msg       = ref('');
    const toast_on        = ref(false);
    const toast_err       = ref(false);
    const show_payment    = ref(false);
    const show_shift      = ref(false);
    const show_held       = ref(false);
    const tendered_str    = ref('');
    const tendered_input  = ref(null);
    const search_input    = ref(null);
    const shift_start     = ref(Date.now());
    const shift_timer     = ref('00:00:00');
    const initializing    = ref(true);
    const editing_discount = ref(null);
    const disc_pct_input  = ref('');
    let   timer_id        = null;

    // ── Offline state ──────────────────────────────────────────────
    const is_online          = ref(navigator.onLine);
    const pending_sync_count = ref(0);
    const syncing_queue      = ref(false);

    // Held orders — persisted to localStorage
    const held_orders = ref(
      JSON.parse(localStorage.getItem(`pos_held_${cfg.branch_id}`) || '[]')
    );

    function save_held() {
      localStorage.setItem(`pos_held_${cfg.branch_id}`, JSON.stringify(held_orders.value));
    }

    // ── Computed ───────────────────────────────────────────────────
    const filtered_products = computed(() => {
      let list = productStore.products;
      if (active_category.value) list = list.filter(p => p.category_id == active_category.value);
      if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(p => p.name.toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q));
      }
      return list;
    });

    const selected_payment_obj = computed(() =>
      payment_methods.value.find(m => m.id === selected_payment.value) ?? null
    );

    const selected_payment_name = computed(() => {
      const pm = selected_payment_obj.value;
      return pm ? pm.name : 'Cash';
    });

    const company_initial = computed(() =>
      (cfg.company_name || 'P').charAt(0).toUpperCase()
    );

    const short_name = computed(() => {
      const parts = (cfg.staff_name || '').trim().split(' ');
      return parts[0] + (parts[1] ? ' ' + parts[1].charAt(0) + '.' : '');
    });

    const numpad_change = computed(() =>
      Math.max(0, (parseFloat(tendered_str.value) || 0) - cartStore.total)
    );

    const numpad_can_complete = computed(() => {
      if (!cartStore.items.length || !selected_payment.value) return false;
      const pm = selected_payment_obj.value;
      if (!pm) return false;
      if (pm.type === 'cash') return (parseFloat(tendered_str.value) || 0) >= cartStore.total;
      if (pm.type === 'mobile_money') return mm_phone.value.replace(/\s/g,'').length >= 9;
      return true;
    });

    // Quick payment amounts — exact + nearest round amounts
    const quick_amounts = computed(() => {
      const total = cartStore.total;
      if (total <= 0) return [];
      const sym = { KES:'KSh', UGX:'USh', TZS:'TSh', RWF:'RWF', ETB:'ETB', USD:'$' }[cfg.currency] || cfg.currency;
      const fmt = v => `${sym} ${v >= 1000 ? (v/1000 % 1 === 0 ? (v/1000)+'K' : (v/1000).toFixed(1)+'K') : v}`;
      const results = [{ label: 'Exact', value: total, is_exact: true }];
      for (const step of [50,100,200,500,1000,2000,5000,10000,20000,50000]) {
        const rounded = Math.ceil(total / step) * step;
        if (rounded > total && results.length < 5) {
          results.push({ label: fmt(rounded), value: rounded, is_exact: false });
        }
      }
      return results;
    });

    function set_quick_amount(val) {
      tendered_str.value = String(val % 1 === 0 ? val : val.toFixed(2));
    }

    // ── Toast ──────────────────────────────────────────────────────
    function show_toast(msg, is_err = false) {
      toast_msg.value = msg;
      toast_err.value = is_err;
      toast_on.value  = true;
      setTimeout(() => { toast_on.value = false; }, 2800);
    }

    // ── Shift timer ────────────────────────────────────────────────
    function start_shift_timer() {
      shift_start.value = Date.now();
      if (timer_id) clearInterval(timer_id);
      timer_id = setInterval(() => {
        const e = Math.floor((Date.now() - shift_start.value) / 1000);
        shift_timer.value = [
          String(Math.floor(e / 3600)).padStart(2,'0'),
          String(Math.floor((e % 3600) / 60)).padStart(2,'0'),
          String(e % 60).padStart(2,'0'),
        ].join(':');
      }, 1000);
    }

    // ── Numpad ─────────────────────────────────────────────────────
    function open_payment_modal() {
      if (!cartStore.items.length || !session.value) return;
      tendered_str.value = '';
      show_payment.value = true;
    }

    function numpad_press(n) {
      if (tendered_str.value.length >= 10) return;
      tendered_str.value += String(n);
    }

    function numpad_backspace() {
      tendered_str.value = tendered_str.value.slice(0, -1);
    }

    function sanitize_tendered() {
      let v = tendered_str.value.replace(/[^0-9.]/g, '');
      const parts = v.split('.');
      if (parts.length > 2) v = parts[0] + '.' + parts.slice(1).join('');
      tendered_str.value = v;
    }

    watch(show_payment, (v) => {
      if (v) setTimeout(() => tendered_input.value && tendered_input.value.focus(), 80);
    });

    // ── Payment icon ───────────────────────────────────────────────
    function payment_icon(type) {
      const map = { cash:'💵', mobile_money:'📱', card:'💳', bank:'🏦', cheque:'📝', credit:'🏛', other:'💰' };
      return map[type] || '💳';
    }

    // ── Cart actions ───────────────────────────────────────────────
    function do_discount() {
      const v = parseFloat(prompt('Enter cart-wide discount percentage (0-100):'));
      if (!isNaN(v) && v >= 0 && v <= 100) {
        cartStore.discount = { type: 'percent', value: v };
        show_toast(v + '% discount applied');
      }
    }

    function open_item_discount(key) {
      const item = cartStore.items.find(i => i._key === key);
      disc_pct_input.value   = item ? (item.discount_pct > 0 ? String(item.discount_pct) : '') : '';
      editing_discount.value = editing_discount.value === key ? null : key;
    }

    function apply_item_discount(key) {
      const pct = parseFloat(disc_pct_input.value);
      const idx = cartStore.items.findIndex(i => i._key === key);
      if (idx < 0) { editing_discount.value = null; return; }
      const clamped = isNaN(pct) ? 0 : Math.min(100, Math.max(0, pct));
      cartStore.items[idx].discount_pct = clamped;
      cartStore.items[idx].discount_amt = 0;
      cartStore._recalc_item(idx);
      editing_discount.value = null;
      show_toast(clamped > 0 ? `${clamped}% discount applied` : 'Item discount removed');
    }

    // Hold current cart
    function hold_order() {
      if (!cartStore.items.length) return;
      const total = cartStore.total;
      held_orders.value.push({
        id:       Date.now(),
        items:    JSON.parse(JSON.stringify(cartStore.items)),
        customer: cartStore.customer,
        discount: { ...cartStore.discount },
        note:     cartStore.note,
        total,
        held_at:  new Date().toLocaleTimeString(),
      });
      save_held();
      cartStore.clear();
      show_toast(`Order held — ${held_orders.value.length} on hold`);
    }

    // Recall a held order into the cart
    function recall_hold(idx) {
      const order = held_orders.value[idx];
      if (!order) return;
      if (cartStore.items.length && !confirm('Replace current cart with held order?')) return;
      cartStore.items    = order.items;
      cartStore.customer = order.customer;
      cartStore.discount = order.discount;
      cartStore.note     = order.note;
      held_orders.value.splice(idx, 1);
      save_held();
      show_held.value = false;
      show_toast('Order recalled');
    }

    function delete_hold(idx) {
      held_orders.value.splice(idx, 1);
      save_held();
    }

    function void_cart() {
      if (!cartStore.items.length) return;
      cartStore.clear();
      show_toast('Cart cleared');
    }

    function new_sale() {
      last_sale.value        = null;
      show_payment.value     = false;
      selected_table.value   = '';
      covers.value           = 1;
      order_type.value       = 'dine-in';
      selected_customer.value = null;
      cust_search.value      = '';
      cartStore.clear();
      load_tables();
      show_toast('Ready for next customer');
    }

    function print_receipt() {
      const fmt = cfg.receipt_format || 'thermal';
      document.body.classList.add('print-' + fmt);
      window.print();
      setTimeout(() => document.body.classList.remove('print-' + fmt), 800);
    }

    function go_admin() { window.location.href = cfg.admin_url; }

    function confirm_close_shift() {
      if (confirm('Close this shift? This will end your session.')) {
        show_toast('Shift closed');
        show_shift.value = false;
      }
    }

    // ── Product selection ──────────────────────────────────────────
    function select_product(product) {
      if (product.stock_qty <= 0 && !product.allow_negative) return;
      if (product.has_variations && product.variations?.length) {
        variation_product.value = product;
      } else {
        cartStore.add_item(product);
        show_toast('Added — ' + product.name);
      }
    }

    function add_variation(product, variation) {
      cartStore.add_item(product, variation);
      variation_product.value = null;
      show_toast('Added — ' + product.name);
    }

    // ── Barcode lookup (offline: searches local cache) ─────────────
    function local_barcode_search(q) {
      const ql = q.toLowerCase();
      return productStore.products.find(p =>
        (p.barcode && p.barcode.toLowerCase() === ql) ||
        (p.sku     && p.sku.toLowerCase()     === ql)
      ) || null;
    }

    async function barcode_lookup() {
      if (!search.value) return;
      if (!navigator.onLine) {
        const found = local_barcode_search(search.value);
        if (found) { select_product(found); search.value = ''; }
        else show_toast('Barcode not found (offline)', true);
        return;
      }
      try {
        const resp   = await api.get(`/products/barcode/${encodeURIComponent(search.value)}`);
        const result = resp.data;
        if (result.type === 'product') {
          cartStore.add_item(result.data);
          show_toast('Added — ' + result.data.name);
        } else if (result.type === 'variation') {
          const product   = productStore.products.find(p => p.id == result.data.product_id);
          const variation = product?.variations?.find(v => v.id == result.data.id);
          if (product) { cartStore.add_item(product, variation); show_toast('Added — ' + product.name); }
        }
        search.value = '';
      } catch (e) {
        // Network error — try local
        const found = local_barcode_search(search.value);
        if (found) { select_product(found); search.value = ''; }
        else show_toast('Barcode not found', true);
      }
    }

    // ── API loaders (with offline fallback) ───────────────────────
    async function load_payment_methods() {
      try {
        if (!navigator.onLine) throw new Error('offline');
        const resp = await api.get('/payments/methods');
        payment_methods.value = resp.data.data;
        if (payment_methods.value.length) selected_payment.value = payment_methods.value[0].id;
        await OfflineDB.putMany('payment_methods', payment_methods.value);
      } catch {
        const cached = await OfflineDB.getAll('payment_methods');
        if (cached.length) { payment_methods.value = cached; selected_payment.value = cached[0].id; }
      }
    }

    async function load_session() {
      try {
        if (!navigator.onLine) throw new Error('offline');
        const resp    = await api.get('/sessions/current');
        session.value = resp.data.session;
        if (session.value) localStorage.setItem(`pos_session_${cfg.branch_id}`, JSON.stringify(session.value));
      } catch {
        const s = localStorage.getItem(`pos_session_${cfg.branch_id}`);
        if (s) { try { session.value = JSON.parse(s); } catch {} }
      }
    }

    async function load_tables() {
      if (!cfg.restaurant_mode) return;
      try {
        const resp    = await api.get('/restaurant/tables');
        tables.value  = resp.data.data || [];
      } catch { /* non-fatal — table list just stays empty */ }
    }

    // ── Customer search (debounced) ────────────────────────────────
    function on_cust_input() {
      clearTimeout(cust_search_timer);
      selected_customer.value = null;
      if (cust_search.value.length < 2) { cust_results.value = []; return; }
      cust_search_timer = setTimeout(async () => {
        try {
          const resp = await api.get('/customers/search', { params: { q: cust_search.value, limit: 6 } });
          cust_results.value = resp.data.data || resp.data || [];
        } catch { cust_results.value = []; }
      }, 280);
    }

    function pick_customer(c) {
      selected_customer.value = c;
      cust_search.value       = c.name;
      cust_results.value      = [];
    }

    async function do_open_session() {
      processing.value = true;
      try {
        await api.post('/sessions/open', { opening_float: session_float.value });
        await load_session();
        open_session_modal.value = false;
        start_shift_timer();
        show_toast('Session opened — welcome!');
      } catch (e) {
        show_toast('Could not open session: ' + (e.response?.data?.error || e.message), true);
      } finally { processing.value = false; }
    }

    // ── Queue a sale for offline sync ──────────────────────────────
    async function queue_offline_sale(payload, amount_tendered, change_given) {
      await OfflineDB.put('offline_sales', {
        ...payload,
        _queued_at:       new Date().toISOString(),
        _amount_tendered: amount_tendered,
        _change_given:    change_given,
      });
      pending_sync_count.value = (await OfflineDB.getAll('offline_sales')).length;
      last_sale.value = {
        receipt_number: 'OFFLINE-' + Date.now(),
        items:          [...cartStore.items],
        subtotal:       cartStore.subtotal,
        tax_total:      cartStore.tax_total,
        discount_amount:cartStore.discount_amount,
        total:          cartStore.total,
        amount_paid:    amount_tendered,
        change_given,
        date_created:   new Date().toLocaleString(),
        note:           cartStore.note,
        _offline:       true,
      };
      show_payment.value = false;
      processing.value   = false;
      show_toast('Saved offline — syncs when connected');
    }

    // ── Drain offline queue when connection returns ─────────────────
    async function sync_offline_queue() {
      if (syncing_queue.value) return;
      const pending = await OfflineDB.getAll('offline_sales');
      if (!pending.length) return;
      syncing_queue.value = true;
      let synced = 0;
      for (const sale of pending) {
        // Strip internal _* keys before sending
        const payload = Object.fromEntries(Object.entries(sale).filter(([k]) => !k.startsWith('_')));
        try {
          await api.post('/sales/create', payload);
          await OfflineDB.del('offline_sales', sale.sale_uid);
          synced++;
        } catch (e) {
          if (!e.response) break; // Still offline — stop trying
        }
      }
      pending_sync_count.value = (await OfflineDB.getAll('offline_sales')).length;
      syncing_queue.value = false;
      if (synced > 0) show_toast(`Synced ${synced} offline sale${synced > 1 ? 's' : ''} ✓`);
    }

    // ── Complete sale (offline-aware) ──────────────────────────────
    async function complete_sale() {
      if (!numpad_can_complete.value || processing.value) return;
      processing.value = true;

      const amount_tendered = parseFloat(tendered_str.value) || cartStore.total;
      const change_given    = Math.max(0, amount_tendered - cartStore.total);
      const sale_uid        = crypto.randomUUID();

      const pm = selected_payment_obj.value;
      const payment = {
        payment_method_id: selected_payment.value,
        amount:            cartStore.total,
        status:            'completed',
        currency:          cfg.currency,
      };

      const build_items = () => cartStore.items.map(i => ({
        product_id:   i.product_id,
        variation_id: i.variation_id,
        product_name: i.product_name,
        sku:          i.sku,
        quantity:     i.quantity,
        unit_price:   i.unit_price,
        cost_price:   i.cost_price,
        discount_pct: i.discount_pct,
        discount_amt: i.discount_amt,
        tax_rate:     i.tax_rate,
        tax_amount:   i.tax_amount,
        line_total:   i.line_total,
      }));

      const base_payload = {
        session_id:      session.value?.id,
        customer_id:     selected_customer.value?.id ?? null,
        order_type:      order_type.value,
        table_id:        selected_table.value ? parseInt(selected_table.value) : null,
        covers:          covers.value || 1,
        currency:        cfg.currency,
        discount_type:   cartStore.discount.type,
        discount_value:  cartStore.discount.value,
        discount_amount: cartStore.discount_amount,
        notes:           cartStore.note,
        sale_uid,
      };

      // ─ Offline path ───────────────────────────────────────────────
      if (!navigator.onLine) {
        if (pm?.type === 'mobile_money') { payment.status = 'pending_offline'; payment.phone = mm_phone.value; }
        await queue_offline_sale({ ...base_payload, items: build_items(), payments: [payment] }, amount_tendered, change_given);
        return;
      }

      try {
        // Mobile money push (online only)
        if (pm?.type === 'mobile_money') {
          const mm_resp = await api.post('/payments/mobile-money', {
            provider:          pm.provider,
            phone:             mm_phone.value,
            amount:            cartStore.total,
            reference:         `POS-${Date.now()}`,
            payment_method_id: selected_payment.value,
          });
          payment.reference = mm_resp.data.checkout_request_id;
          payment.status    = 'pending';
        }

        const payload = {
          ...base_payload,
          items: build_items(),
          payments: [payment],
        };

        const resp = await api.post('/sales/create', payload);
        const sale = resp.data;
        sale.amount_paid  = amount_tendered;
        sale.change_given = change_given;
        sale.note         = cartStore.note;

        // Update local stock immediately — no need to wait for next product sync
        deduct_local_stock(cartStore.items);

        last_sale.value   = sale;
        show_payment.value = false;

        if (session.value) {
          session.value.total_sales_count  = (session.value.total_sales_count  || 0) + 1;
          session.value.total_sales_amount = (
            parseFloat(session.value.total_sales_amount || 0) + parseFloat(sale.total)
          ).toFixed(2);
        }

        show_toast('Sale complete — ' + sale.receipt_number);

      } catch (err) {
        if (!err.response) {
          // Network dropped mid-sale — queue it
          await queue_offline_sale({ ...base_payload, items: build_items(), payments: [payment] }, amount_tendered, change_given);
          return;
        }
        show_toast('Sale failed: ' + (err.response?.data?.error || err.message), true);
      } finally {
        processing.value = false;
      }
    }

    // ── Deduct stock locally after a sale for immediate UI feedback ─
    function deduct_local_stock(items) {
      items.forEach(item => {
        const idx = productStore.products.findIndex(
          p => String(p.pos_product_id || p.id) === String(item.product_id)
        );
        if (idx < 0) return;
        const p = productStore.products[idx];
        const new_qty = p.stock_qty - item.quantity;
        p.stock_qty = p.allow_negative ? new_qty : Math.max(0, new_qty);
      });
      // Persist updated quantities to offline cache
      OfflineDB.putMany('products', productStore.products).catch(() => {});
    }

    // ── Stock label (used in card title/tooltip) ───────────────────
    function stock_label(p) {
      if (p.stock_qty <= 0 && !p.allow_negative) return 'Out of stock';
      if (p.stock_qty <= 0 &&  p.allow_negative) return 'Out of stock (orderable)';
      return p.stock_qty + ' ' + (p.unit || 'pcs') + ' in stock';
    }

    // ── Price formatter ────────────────────────────────────────────
    function format_price(amount) {
      const curr     = cfg.currency;
      const symbols  = { KES:'KSh', UGX:'USh', TZS:'TSh', RWF:'RWF', ETB:'ETB', USD:'$' };
      const decimals = ['UGX','RWF'].includes(curr) ? 0 : 2;
      return `${symbols[curr] || curr} ${parseFloat(amount || 0).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g,',')}`;
    }

    // ── Keyboard shortcuts ─────────────────────────────────────────
    function on_keydown(e) {
      // '/' focuses search unless already in an input
      if (e.key === '/' && !['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName)) {
        e.preventDefault();
        search_input.value && search_input.value.focus();
      }
      // Escape closes modals
      if (e.key === 'Escape') {
        if (show_payment.value) { show_payment.value = false; return; }
        if (show_held.value)    { show_held.value    = false; return; }
        if (show_shift.value)   { show_shift.value   = false; return; }
        if (variation_product.value) { variation_product.value = null; return; }
      }
    }

    // ── Lifecycle ──────────────────────────────────────────────────
    function on_online()  { is_online.value = true;  sync_offline_queue(); productStore.load(true); }
    function on_offline() { is_online.value = false; show_toast('Offline — sales will be queued'); }

    onMounted(async () => {
      document.addEventListener('keydown', on_keydown);
      window.addEventListener('online',  on_online);
      window.addEventListener('offline', on_offline);

      // Load pending offline count immediately
      pending_sync_count.value = (await OfflineDB.getAll('offline_sales')).length;

      await Promise.all([
        productStore.load(),
        load_payment_methods(),
        load_session(),
        load_tables(),
      ]);
      initializing.value = false;
      if (session.value) start_shift_timer();

      // Drain any queued sales if we're online
      if (navigator.onLine && pending_sync_count.value > 0) sync_offline_queue();
    });

    onUnmounted(() => {
      if (timer_id) clearInterval(timer_id);
      document.removeEventListener('keydown', on_keydown);
      window.removeEventListener('online',  on_online);
      window.removeEventListener('offline', on_offline);
    });

    return {
      cartStore, productStore, cfg,
      search, active_category, selected_payment, selected_payment_obj, selected_payment_name,
      mm_phone, processing, payment_methods, session,
      variation_product, open_session_modal, session_float, last_sale,
      toast_msg, toast_on, toast_err, show_payment, show_shift, show_held,
      tendered_str, tendered_input, search_input, shift_timer, initializing,
      editing_discount, disc_pct_input,
      held_orders,
      is_online, pending_sync_count, syncing_queue,
      filtered_products, numpad_change, numpad_can_complete, quick_amounts,
      company_initial, short_name,
      cat_color,
      select_product, add_variation, barcode_lookup, complete_sale,
      do_open_session, do_discount, hold_order, void_cart, new_sale,
      open_payment_modal, numpad_press, numpad_backspace, sanitize_tendered,
      open_item_discount, apply_item_discount,
      payment_icon, format_price, stock_label, deduct_local_stock, show_toast, print_receipt, go_admin,
      set_quick_amount, recall_hold, delete_hold, confirm_close_shift,
      sync_offline_queue,
      // restaurant
      tables, selected_table, order_type, covers,
      cust_search, cust_results, selected_customer, on_cust_input, pick_customer,
    };
  },
};

const pinia = createPinia();
const app   = createApp(App);
app.use(pinia);
app.mount('#pos-app');
</script>

</body>
</html>
