<?php
/**
 * Estilo das telas. Fica num arquivo PHP para o projeto ter só PHP;
 * o layout imprime este conteúdo dentro de <style>.
 * Cores do gráfico (--series-in / --series-out) validadas para daltonismo nos dois temas.
 */
?>
:root {
  --bg: #f4f5f7;
  --surface: #fff;
  --surface-2: #f8f9fb;
  --column: #eef0f3;
  --border: #e3e6eb;
  --border-strong: #cdd2da;
  --text: #171a1f;
  --muted: #626975;
  --faint: #959ba5;

  --brand: #c2185b;
  --brand-hover: #a3124b;
  --brand-soft: #fbe5ee;
  --brand-text: #ad1450;

  --blue: #2463c4;   --blue-soft: #e4edfb;
  --amber: #9b5a00;  --amber-soft: #fcefd6;
  --green: #1b7f48;  --green-soft: #def2e6;
  --red: #c42b2b;    --red-soft: #fde4e4;
  --gray-soft: #eceef1;

  /* gráfico: validado com o validate_palette (light, superfície #fff) */
  --series-in: #2a78d6;
  --series-out: #eb6834;

  --shadow-sm: 0 1px 2px rgba(23, 26, 31, .06);
  --shadow-md: 0 8px 28px rgba(23, 26, 31, .12);
  --radius: 12px;
  color-scheme: light;
}

@media (prefers-color-scheme: dark) {
  :root {
    --bg: #0f1114; --surface: #171a1f; --surface-2: #1c2026; --column: #13161a;
    --border: #262b33; --border-strong: #363d47; --text: #e8ebef; --muted: #9aa2ad; --faint: #6b737e;
    --brand: #dd3f7f; --brand-hover: #e85b93; --brand-soft: rgba(221, 63, 127, .16); --brand-text: #f07aa8;
    --blue: #74a8f0; --blue-soft: rgba(116, 168, 240, .14);
    --amber: #e6aa48; --amber-soft: rgba(230, 170, 72, .14);
    --green: #4fc48a; --green-soft: rgba(79, 196, 138, .14);
    --red: #f07272; --red-soft: rgba(240, 114, 114, .14);
    --gray-soft: #23282f;
    --series-in: #3987e5; --series-out: #d95926;
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, .35); --shadow-md: 0 10px 34px rgba(0, 0, 0, .5);
    color-scheme: dark;
  }
}

* { box-sizing: border-box; }
html, body { margin: 0; height: 100%; }
body {
  background: var(--bg);
  color: var(--text);
  font: 14px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  font-variant-numeric: tabular-nums;
  -webkit-font-smoothing: antialiased;
}
button, input, select, textarea { font: inherit; color: inherit; }
button { cursor: pointer; }
a { color: var(--brand-text); text-decoration: none; }
a:hover { text-decoration: underline; }
h1 { font-size: 24px; font-weight: 700; letter-spacing: -.02em; margin: 0; }
h2 { font-size: 15px; font-weight: 600; margin: 0; }
.muted { color: var(--muted); }
.small { font-size: 12.5px; }
.right { text-align: right; }
.nowrap { white-space: nowrap; }
.text-red { color: var(--red); }
.text-green { color: var(--green); }
svg.i { width: 17px; height: 17px; flex: none; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

/* estrutura */
.shell { display: grid; grid-template-columns: 224px minmax(0, 1fr); min-height: 100%; }
.side { position: sticky; top: 0; height: 100vh; display: flex; flex-direction: column; gap: 24px; padding: 20px 14px; background: var(--surface); border-right: 1px solid var(--border); }
.logo { display: flex; align-items: center; gap: 10px; padding: 0 6px; color: var(--text); font-weight: 700; font-size: 16px; line-height: 1.15; white-space: nowrap; }
.logo:hover { text-decoration: none; }
.logo small { display: block; font-weight: 500; font-size: 12px; color: var(--muted); }
.side nav { display: flex; flex-direction: column; gap: 2px; }
.side nav a { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 8px; color: var(--muted); font-weight: 500; }
.side nav a:hover { background: var(--surface-2); color: var(--text); text-decoration: none; }
.side nav a.active { background: var(--brand-soft); color: var(--brand-text); font-weight: 600; }
.side nav .badge { margin-left: auto; min-width: 20px; padding: 0 6px; border-radius: 99px; background: var(--brand); color: #fff; font-size: 11px; font-weight: 600; text-align: center; }

.main { min-width: 0; display: flex; flex-direction: column; }
.bar { position: sticky; top: 0; z-index: 5; display: flex; align-items: center; gap: 12px; padding: 14px 28px; background: var(--surface); border-bottom: 1px solid var(--border); }
.bar .btn.primary { margin-left: auto; }
main { width: 100%; max-width: 1440px; padding: 28px; }
.page-head { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-bottom: 22px; }
.page-head p { margin: 2px 0 0; color: var(--muted); }
.page-head .tools { margin-left: auto; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

/* controles */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 14px; border: 1px solid var(--border); border-radius: 9px; background: var(--surface); color: var(--text); font-weight: 600; white-space: nowrap; box-shadow: var(--shadow-sm); }
.btn:hover { border-color: var(--border-strong); text-decoration: none; }
.btn.primary { background: var(--brand); border-color: var(--brand); color: #fff; }
.btn.primary:hover { background: var(--brand-hover); border-color: var(--brand-hover); }
.btn.green { background: var(--green); border-color: var(--green); color: #fff; }
.btn.small { padding: 5px 10px; font-size: 12.5px; }
.btn.ghost { border-color: transparent; background: none; box-shadow: none; color: var(--muted); }
.btn.ghost:hover { background: var(--surface-2); color: var(--text); }
.btn.danger { color: var(--red); }
.btn:disabled { opacity: .5; cursor: default; }
.icon-btn { width: 30px; height: 30px; display: inline-grid; place-items: center; border: 0; border-radius: 7px; background: none; color: var(--faint); }
.icon-btn:hover { background: var(--red-soft); color: var(--red); }
.month-nav { display: inline-flex; align-items: center; gap: 4px; padding: 3px; border: 1px solid var(--border); border-radius: 10px; background: var(--surface); }
.month-nav b { min-width: 130px; text-align: center; font-weight: 600; }
.month-nav a, .month-nav .disabled { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 7px; color: var(--muted); }
.month-nav a:hover { background: var(--surface-2); color: var(--text); }
.month-nav .disabled { opacity: .3; }
input, select, textarea { width: 100%; min-height: 38px; padding: 8px 10px; border: 1px solid var(--border); border-radius: 9px; background: var(--surface); }
input:focus, select:focus, textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-soft); }
textarea { resize: vertical; min-height: 64px; }
label.field { display: block; font-size: 12.5px; font-weight: 600; color: var(--muted); }
label.field > input, label.field > select, label.field > textarea { margin-top: 5px; color: var(--text); font-weight: 400; }

/* blocos */
.panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); }
.panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px 0; }
.panel-body { padding: 14px 18px 18px; }
.grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 20px; }
.cols-2 { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr); gap: 20px; align-items: start; }
.cols-even { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; align-items: start; }

.kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
.kpi { padding: 16px 18px; }
.kpi .label { display: flex; align-items: center; gap: 8px; color: var(--muted); font-weight: 500; }
.kpi .ico { width: 30px; height: 30px; border-radius: 8px; display: grid; place-items: center; }
.kpi .num { font-size: 26px; font-weight: 700; letter-spacing: -.02em; margin: 10px 0 2px; }
.kpi .hint { font-size: 12.5px; color: var(--muted); }
.tone-blue { background: var(--blue-soft); color: var(--blue); }
.tone-amber { background: var(--amber-soft); color: var(--amber); }
.tone-green { background: var(--green-soft); color: var(--green); }
.tone-red { background: var(--red-soft); color: var(--red); }
.tone-brand { background: var(--brand-soft); color: var(--brand-text); }
.tone-gray { background: var(--gray-soft); color: var(--muted); }

.flow { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
.flow a { display: flex; align-items: center; gap: 14px; padding: 16px 18px; color: var(--text); border-top: 3px solid var(--c); }
.flow a:hover { text-decoration: none; border-color: var(--border-strong); border-top-color: var(--c); box-shadow: var(--shadow-md); }
.flow .big { font-size: 30px; font-weight: 700; line-height: 1; color: var(--c); }
.flow .what b { display: block; font-weight: 600; }
.flow .arrow { margin-left: auto; color: var(--faint); }

/* listas */
.list { list-style: none; margin: 0; padding: 0; }
.row-item { display: flex; align-items: center; gap: 12px; padding: 11px 0; border-top: 1px solid var(--border); }
.row-item:first-child { border-top: 0; padding-top: 2px; }
.row-item .main-col { flex: 1; min-width: 0; cursor: pointer; }
.row-item .main-col:hover b { color: var(--brand-text); }
.row-item b { font-weight: 600; }
.ellipsis { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.empty { color: var(--muted); padding: 10px 0; margin: 0; }

/* etiquetas */
.pill { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 99px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.pill.dot::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.st-new { --c: var(--blue); background: var(--blue-soft); color: var(--blue); }
.st-production { --c: var(--amber); background: var(--amber-soft); color: var(--amber); }
.st-ready { --c: var(--green); background: var(--green-soft); color: var(--green); }
.st-delivered { --c: var(--muted); background: var(--gray-soft); color: var(--muted); }
.st-cancelled { --c: var(--red); background: var(--red-soft); color: var(--red); }
.flow a.st-new, .flow a.st-production, .flow a.st-ready { background: var(--surface); }

/* quadro de pedidos */
.board { display: grid; grid-template-columns: repeat(4, minmax(220px, 1fr)); gap: 14px; overflow-x: auto; padding-bottom: 10px; align-items: start; }
.col { background: var(--column); border: 1px solid var(--border); border-radius: var(--radius); padding: 10px; min-height: 300px; scroll-margin-top: 80px; }
.col-head { display: flex; align-items: center; gap: 8px; padding: 4px 4px 10px; }
.col-head .bullet { width: 8px; height: 8px; border-radius: 50%; background: var(--c); }
.col-head b { font-weight: 600; }
.col-head .count { margin-left: auto; font-size: 12px; font-weight: 600; color: var(--muted); background: var(--surface); border: 1px solid var(--border); border-radius: 99px; padding: 0 8px; }
.card { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 12px; margin-bottom: 8px; box-shadow: var(--shadow-sm); transition: border-color .15s, box-shadow .15s; }
.card-link { display: block; color: var(--text); }
.card-link:hover { text-decoration: none; }
.card-link:hover .customer { color: var(--brand-text); }
.card-link > span { display: flex; }
.card .customer, .card .desc { display: block !important; }
.card:hover { border-color: var(--border-strong); box-shadow: var(--shadow-md); }
.card .top { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 12.5px; color: var(--muted); }
.card .num { font-weight: 700; color: var(--text); }
.card .customer { font-weight: 600; margin-top: 4px; line-height: 1.3; }
.card .desc { font-size: 12.5px; color: var(--muted); margin-top: 2px; }
.card .money { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-top: 10px; }
.card .total { font-weight: 700; font-size: 15px; }
.card .next { width: 100%; margin-top: 10px; }
.col-more { display: block; text-align: center; font-size: 12.5px; color: var(--muted); padding: 6px; }

/* tabelas */
.table-wrap { overflow: auto; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; font-weight: 600; font-size: 12px; color: var(--muted); padding: 10px 14px; border-bottom: 1px solid var(--border); background: var(--surface-2); white-space: nowrap; }
td { padding: 11px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
tbody tr:last-child td { border-bottom: 0; }
tr.click { cursor: pointer; }
tr.click:hover td { background: var(--surface-2); }
.panel > .table-wrap:first-child th:first-child { border-top-left-radius: var(--radius); }
.panel > .table-wrap:first-child th:last-child { border-top-right-radius: var(--radius); }
tfoot td { font-weight: 700; border-top: 1px solid var(--border); border-bottom: 0; }
.cat-row { margin-bottom: 12px; }
.cat-row .line { display: flex; justify-content: space-between; gap: 8px; }
.track { height: 6px; border-radius: 99px; background: var(--gray-soft); margin-top: 6px; overflow: hidden; }
.track i { display: block; height: 100%; border-radius: 99px; background: var(--series-out); }

/* gráfico */
.legend { display: flex; gap: 16px; font-size: 12.5px; color: var(--muted); }
.legend span { display: inline-flex; align-items: center; gap: 6px; }
.legend i { width: 10px; height: 10px; border-radius: 3px; }
.chart { position: relative; width: 100%; }
.chart svg { display: block; width: 100%; height: auto; overflow: visible; }
.chart .grid-line { stroke: var(--border); stroke-width: 1; }
.chart .axis-label { fill: var(--muted); font-size: 11.5px; }
.chart .bar-in { fill: var(--series-in); }
.chart .bar-out { fill: var(--series-out); }
.chart .hit { fill: transparent; }
.chart .group:hover .hit { fill: var(--text); fill-opacity: .05; }
/* gastos */
.expense-form { display: grid; grid-template-columns: 150px 190px minmax(0, 1fr) 140px auto; gap: 10px; align-items: end; }

.items td, .items th { padding-left: 0; background: none; }
.items td:last-child, .items th:last-child { padding-right: 0; }
.pay-box { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.pay-box div { padding: 10px 12px; border-radius: 9px; background: var(--surface-2); border: 1px solid var(--border); }
.pay-box b { display: block; font-size: 16px; }
.pay-form { display: grid; grid-template-columns: 1fr 1fr auto; gap: 8px; margin-top: 12px; align-items: end; }
.payments { margin-top: 10px; font-size: 13px; }
.payments li { display: flex; justify-content: space-between; padding: 5px 0; border-top: 1px dashed var(--border); }

.form-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 18px; }
.form-grid .span-2 { grid-column: span 2; }
.form-grid .full { grid-column: 1 / -1; }
.item-lines { margin-top: 18px; }
.item-line { display: grid; grid-template-columns: minmax(0, 1fr) 90px 140px; gap: 8px; align-items: center; margin-bottom: 8px; }
.item-line.head { font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
.order-total { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding: 12px 14px; border-radius: 10px; background: var(--surface-2); border: 1px solid var(--border); }
.order-total b { font-size: 20px; }
.buttons { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }


@media (max-width: 1180px) {
  .kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .cols-2, .cols-even { grid-template-columns: minmax(0, 1fr); }
  .expense-form { grid-template-columns: 1fr 1fr; }
  .expense-form .grow { grid-column: 1 / -1; }
}
@media (max-width: 760px) {
  .shell { grid-template-columns: minmax(0, 1fr); }
  .side { position: static; height: auto; flex-direction: row; align-items: center; gap: 10px; padding: 10px 16px; border-right: 0; border-bottom: 1px solid var(--border); }
  .logo small, .side nav a .label { display: none; }
  .side nav { flex-direction: row; }
  .side nav .badge { margin-left: -6px; }
  .bar { position: static; padding: 10px 16px; }
  main { padding: 18px 16px 40px; }
  .kpis, .flow { grid-template-columns: minmax(0, 1fr); }
  .kpi .num { font-size: 22px; }
  .form-grid { grid-template-columns: 1fr; }
  .form-grid .span-2 { grid-column: auto; }
  .item-line { grid-template-columns: minmax(0, 1fr) 60px 90px; }
  .confirm, .deliver-form { grid-template-columns: 1fr; }
  .pay-box { grid-template-columns: 1fr; }
  .pay-form { grid-template-columns: 1fr; }
  .expense-form { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) {
  .card { transition: none; }
}

/* páginas sem JS: avisos, confirmações e links */
.alert { display: flex; align-items: center; gap: 8px; padding: 11px 14px; margin-bottom: 18px; border-radius: 10px; font-weight: 600; }
.alert.ok { background: var(--green-soft); color: var(--green); }
.alert.error { background: var(--red-soft); color: var(--red); }
.confirm { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 16px; align-items: center; padding: 18px; margin-bottom: 20px; border-color: var(--border-strong); box-shadow: var(--shadow-md); }
.confirm h2 { margin-bottom: 2px; }
.confirm p { margin: 0; }
.deliver-form { display: grid; grid-template-columns: 160px 140px auto; gap: 10px; align-items: end; }
.back-link { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px; color: var(--muted); font-size: 13px; font-weight: 500; }
.back-link svg.i { width: 15px; height: 15px; }
.row-link { font-weight: 700; color: var(--text); }
a.main-col { color: var(--text); display: flex; flex-direction: column; }
a.main-col:hover { text-decoration: none; }
.btn.danger-solid { background: var(--red); border-color: var(--red); color: #fff; }
tr.selected td { background: var(--brand-soft); }
.steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
.steps div { font-size: 11.5px; font-weight: 600; color: var(--faint); text-align: center; }
.steps div::before { content: ""; display: block; height: 4px; border-radius: 99px; background: var(--gray-soft); margin-bottom: 6px; }
.steps div.done { color: var(--text); }
.steps div.done::before { background: var(--brand); }
.actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; align-items: center; }
.pay-box { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.pay-box div { padding: 10px 12px; border-radius: 9px; background: var(--surface-2); border: 1px solid var(--border); }
.pay-box b { display: block; font-size: 16px; }
.pay-form { display: grid; grid-template-columns: 1fr 1fr auto; gap: 8px; margin-top: 14px; align-items: end; }
.payments { margin-top: 10px; font-size: 13px; }
.payments li { display: flex; justify-content: space-between; padding: 6px 0; border-top: 1px dashed var(--border); }
.price-table { margin-top: 22px; padding: 14px 16px; border-radius: 10px; background: var(--surface-2); border: 1px solid var(--border); }
.price-table h2 { margin-bottom: 8px; }
.price-table ul { list-style: none; margin: 0; padding: 0; columns: 2; column-gap: 28px; font-size: 13px; }
.price-table li { display: flex; justify-content: space-between; gap: 10px; padding: 3px 0; break-inside: avoid; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0 0 0 0); border: 0; }
@media (max-width: 760px) {
  .pay-box, .pay-form { grid-template-columns: 1fr; }
  .price-table ul { columns: 1; }
}
