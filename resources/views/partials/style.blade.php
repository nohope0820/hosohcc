  :root{--bg:#f5f6f8;--card:#fff;--ink:#1d2330;--mut:#667085;--line:#d9dde5;--acc:#1f5fbf}
  @media (prefers-color-scheme:dark){:root{--bg:#14171d;--card:#1c2029;--ink:#e8ebf0;--mut:#9aa3b2;--line:#323846;--acc:#5b9bff}}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--ink);font:15px/1.45 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
  .wrap{max-width:1560px;margin:0 auto;padding:16px 16px 120px}
  .layout{display:grid;grid-template-columns:minmax(0,1fr);gap:20px}
  .main{max-width:900px;width:100%;margin:0 auto}
  #pv{display:none;flex-direction:column;background:var(--card);border:1px solid var(--line);border-radius:10px;overflow:hidden}
  .pv-head{display:flex;gap:10px;align-items:center;padding:8px 12px;border-bottom:1px solid var(--line);font-size:13px;flex-wrap:wrap}
  .pv-head b{font-size:14px} #pvStatus{color:var(--mut);flex:1;min-width:80px}
  .pv-head button{padding:5px 10px;font-size:13px} .pv-head label{display:flex;gap:5px;align-items:center;margin:0;color:var(--ink)}
  #pvBody{flex:1;overflow-y:auto;overflow-x:hidden;background:#7f8590;padding:8px}
  #pvBody canvas{display:block;margin:0 auto 10px;background:#fff;box-shadow:0 1px 6px rgba(0,0,0,.35)}
  #pvBody iframe{width:100%;height:100%;border:0;background:#fff}
  .pv-msg{color:#fff;text-align:center;padding:30px 10px}
  /* wide screens: form + preview side by side */
  @media (min-width:1100px){
    .layout.pv-on{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}
    .layout.pv-on .main{margin:0}
    .layout.pv-on #pv{display:flex;position:sticky;top:12px;height:calc(100vh - 92px)}
    #pv .pv-close{display:none}
  }
  /* narrow screens: preview is a full-screen panel */
  @media (max-width:1099px){
    .layout.pv-on #pv{display:flex;position:fixed;inset:0 0 62px 0;z-index:20;border-radius:0}
  }
  nav{display:flex;gap:6px;margin-bottom:18px;flex-wrap:wrap}
  nav a{padding:6px 12px;border-radius:999px;border:1px solid var(--line);color:var(--ink);text-decoration:none;font-size:14px}
  nav a.on{background:var(--acc);border-color:var(--acc);color:#fff}
  h1{font-size:21px;margin:0 0 4px} .sub{color:var(--mut);margin:0 0 20px}
  fieldset{background:var(--card);border:1px solid var(--line);border-radius:10px;margin:0 0 16px;padding:14px 16px 16px;min-width:0}
  legend{font-weight:600;padding:0 6px}
  .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:10px 12px;margin-bottom:6px}
  .f{grid-column:span 12} .s6{grid-column:span 6} .s4{grid-column:span 4} .s3{grid-column:span 3} .s8{grid-column:span 8} .s9{grid-column:span 9}
  @media(max-width:640px){.s6,.s4,.s3,.s8,.s9{grid-column:span 12}}
  label{display:block;font-size:13px;color:var(--mut);margin-bottom:3px}
  input[type=text]{width:100%;padding:8px 10px;border:1px solid var(--line);border-radius:7px;background:transparent;color:var(--ink);font:inherit}
  textarea,select{width:100%;padding:8px 10px;border:1px solid var(--line);border-radius:7px;background:transparent;color:var(--ink);font:inherit}
  textarea{resize:vertical}
  select option{background:var(--card);color:var(--ink)}
  input[type=text]:focus,textarea:focus,select:focus{outline:2px solid var(--acc);border-color:transparent}
  .chk{display:flex;gap:8px;align-items:center;margin:4px 0} .chk label{margin:0;color:var(--ink);font-size:15px}
  .bar{position:fixed;left:0;right:0;bottom:0;background:var(--card);border-top:1px solid var(--line);padding:10px 16px}
  .bar .in{max-width:1560px;margin:0 auto;display:flex;gap:10px;flex-wrap:wrap}
  button{padding:10px 16px;border-radius:8px;border:1px solid var(--acc);background:var(--acc);color:#fff;font:inherit;cursor:pointer}
  button.sec{background:transparent;color:var(--acc)} button:hover{filter:brightness(1.08)}
  .row{display:grid;gap:8px;margin-bottom:8px;align-items:center}
  .row .rc{display:flex;gap:6px;align-items:center;font-size:13px}
  .row .rn{font-weight:600;color:var(--mut)}
  .note{font-size:12.5px;color:var(--mut);margin:10px 0 6px}
  input.shared,textarea.shared{border-left:3px solid var(--acc)}
  .hint{font-size:13px;color:var(--mut);background:var(--card);border:1px solid var(--line);border-radius:8px;padding:8px 12px;margin:0 0 16px}
  .hint i{display:inline-block;width:3px;height:12px;background:var(--acc);margin:0 6px -1px 0}
  .errs{background:#fdecea;color:#8a1c14;border-radius:8px;padding:10px 14px;margin-bottom:16px}
