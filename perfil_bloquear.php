/* admin.css — pequenos complementos visuais só do painel do admin.
   Reaproveita as cores/fontes/botões já definidos em css/style.css;
   aquele arquivo não precisou ser tocado. */

.adm-shell{max-width:1220px;margin:0 auto;padding:22px 18px 70px;display:grid;grid-template-columns:220px 1fr;gap:28px;align-items:start}
.adm-nav{position:sticky;top:76px;display:flex;flex-direction:column;gap:3px}
.adm-nav a{display:flex;align-items:center;gap:10px;padding:10px 13px;border-radius:12px;color:var(--ink-2);font-size:13.5px;font-weight:500;transition:background .18s,color .18s}
.adm-nav a:hover{background:var(--surface);color:var(--ink)}
.adm-nav a[aria-current="true"]{background:var(--ink);color:var(--paper)}
.adm-nav a svg{width:17px;height:17px;flex-shrink:0}
.adm-nav hr{border:none;border-top:1px solid var(--line);margin:10px 4px}

.badge-dot{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:100px;background:var(--hot);color:#fff;font-size:10.5px;font-weight:700;margin-left:auto}

.tbl-wrap{overflow-x:auto;background:var(--surface);border:1px solid var(--line);border-radius:var(--r-m)}
.tbl{width:100%;border-collapse:collapse;font-size:13.5px;min-width:640px}
.tbl th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--ink-3);font-weight:600;padding:12px;border-bottom:1px solid var(--line-strong);white-space:nowrap}
.tbl td{padding:11px 12px;border-bottom:1px solid var(--line);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl td.acoes{white-space:nowrap;text-align:right}
.tbl td.acoes form{display:inline-block;margin-left:6px}

.row-flex{display:flex;align-items:center;gap:10px;min-width:0}
.row-flex .nm2{font-weight:600;font-size:13.5px;display:block}

.novo-tag{font-size:10px;font-weight:700;color:var(--teal);border:1px solid var(--teal);border-radius:100px;padding:1px 7px;text-transform:uppercase;letter-spacing:.03em;margin-left:6px;white-space:nowrap}
.bloq-tag{font-size:10px;font-weight:700;color:var(--hot);border:1px solid var(--hot);border-radius:100px;padding:1px 7px;text-transform:uppercase;letter-spacing:.03em;margin-left:6px;white-space:nowrap}

@media (max-width:860px){
  .adm-shell{grid-template-columns:1fr;padding:16px 13px 70px}
  .adm-nav{position:static;flex-direction:row;flex-wrap:wrap;gap:6px}
  .adm-nav a{flex:1;justify-content:center;text-align:center;padding:9px 6px}
}
