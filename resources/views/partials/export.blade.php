@php
  $__forms = collect(\App\Http\Controllers\FormController::forms())->map(fn ($f) => $f[1]);
@endphp
<style>
  .ov{position:fixed;inset:0;background:rgba(10,14,22,.55);display:none;align-items:center;justify-content:center;z-index:50;padding:16px}
  .ov.on{display:flex}
  .md{background:var(--card);color:var(--ink);border-radius:14px;max-width:1040px;width:100%;max-height:90vh;overflow:auto;padding:22px;box-shadow:0 20px 60px rgba(0,0,0,.35)}
  .md h2{margin:0 0 6px;font-size:20px} .md p{color:var(--mut);margin:0 0 14px;font-size:14px}
  .md .okc{color:#1a7f45;font-weight:600}
  .miss{display:grid;gap:10px;margin-bottom:16px}
  .miss .m{border:1px solid var(--line);border-radius:10px;padding:10px 14px}
  .miss .m.off{opacity:.55}
  .miss .m .hd{display:flex;gap:10px;align-items:center}
  .miss .m .hd input{width:18px;height:18px;flex:none}
  .miss .m .hd b{flex:1} .miss .m .hd a{font-size:13px;color:var(--acc);text-decoration:none}
  .miss .m .st{font-size:13px;margin:2px 0 0 28px;color:#1a7f45} .miss .m .st.warn{color:#b26a00}
  .miss ul{margin:6px 0 0 28px;padding-left:20px;font-size:14px;color:var(--mut);columns:2}
  .md .foot{position:sticky;bottom:-22px;margin:0 -22px -22px;padding:12px 22px;background:var(--card);border-top:1px solid var(--line);border-radius:0 0 14px 14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center}
  .md .foot .nmr{display:flex;align-items:center;gap:6px;flex:1;min-width:220px}
  .md .foot .nmr input{flex:1;min-width:0;padding:10px;border:1px solid var(--line);border-radius:8px;background:var(--bg);color:var(--ink);font:inherit}
  .md .foot .nmr span{color:var(--mut);font-size:13px;white-space:nowrap}
  #expMsg{font-size:14px;color:var(--mut);min-height:20px;margin-top:10px}
  @media (max-width:700px){.miss ul{columns:1}}
</style>
<div class="ov" id="ovExp"><div class="md" role="dialog" aria-modal="true" aria-labelledby="expT">
  <h2 id="expT">Kiểm tra thông tin trước khi xuất</h2>
  <p id="expS"></p>
  <div class="miss" id="missList"></div>
  <div id="expMsg"></div>
  <div class="foot">
    <div class="nmr"><input type="text" id="fname" value="bo-ho-so-nha-dat" maxlength="80" aria-label="Tên file"><span>.docx / .pdf</span></div>
    <button type="button" id="bDocx">Xuất Word (.docx)</button>
    <button type="button" id="bPdf">Xuất PDF</button>
    <button type="button" id="bWipe" class="sec" style="color:#c0392b;border-color:#c0392b">Xóa toàn bộ dữ liệu</button>
    <button type="button" id="bClose" class="sec">Đóng</button>
  </div>
</div></div>
<script>
(function(){
  const SH=@json(\App\Support\Shared::all()), FORMS=@json($__forms), REQ=@json(\App\Support\Required::all());
  const SKEY='hcc-shared', SELKEY='bundle-sel', slugs=Object.keys(FORMS), $=id=>document.getElementById(id);
  const token=document.querySelector('meta[name=csrf-token]').content;
  const rd=k=>{try{return JSON.parse(localStorage.getItem(k)||'null')}catch(e){return null}};
  let S, sel;

  // Rebuild the field list of a form exactly as its page would post it: own draft + shared values
  function params(slug){
    const d=rd('draft-'+slug), p={};
    if(d) Object.entries(d).forEach(([k,v])=>{ if(v===true) p[k]='1'; else if(v!==false&&v!==''&&v!=null) p[k]=v; });
    const cfg=SH[slug]||{fields:{},rows:{}};
    Object.entries(cfg.fields||{}).forEach(([name,key])=>{ if(S[key]) p[name]=S[key]; });
    Object.entries(cfg.rows||{}).forEach(([name,sh])=>{
      Object.keys(S).forEach(k=>{
        const m=k.match(new RegExp('^'+sh.prefix+'\\.(\\d+)\\.(\\w+)$')); if(!m) return;
        const col=Object.keys(sh.cols).find(c=>sh.cols[c]===m[2]); const r=(+m[1])-sh.offset;
        if(col&&r>=0&&S[k]) p[`${name}[${r}][${col}]`]=S[k];
      });
    });
    Object.entries(cfg.inherit||{}).forEach(([name,src])=>{ if(!has(p,name)&&S[src]) p[name]=S[src]; }); // same suggestions the form page shows
    return p;
  }
  const has=(p,k)=>p[k]!=null&&String(p[k]).trim()!=='';
  const ok=(p,spec)=>Array.isArray(spec)?spec.every(k=>has(p,k)):spec.split('|').some(k=>has(p,k));
  const esc=t=>String(t).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

  function render(){
    S=rd(SKEY)||{}; sel=rd(SELKEY)||{};
    const rows=slugs.map(s=>{
      if(!(s in sel)) sel[s]=true;
      const p=params(s), req=Object.entries(REQ[s]||{});
      return {s, miss:req.filter(([,spec])=>!ok(p,spec)).map(([l])=>l), total:req.length};
    });
    $('missList').innerHTML=rows.map(x=>`<div class="m ${sel[x.s]?'':'off'}"><div class="hd"><input type="checkbox" data-s="${x.s}" ${sel[x.s]?'checked':''} aria-label="Chọn mẫu"><b>${esc(FORMS[x.s])}</b><a href="/${x.s}">Mở để sửa</a></div>`
      +(x.miss.length?`<div class="st warn">Thiếu ${x.miss.length}/${x.total} mục</div><ul>${x.miss.map(l=>'<li>'+esc(l)+'</li>').join('')}</ul>`:'<div class="st">Đã đủ thông tin chính</div>')+'</div>').join('');
    const n=rows.filter(x=>sel[x.s]&&x.miss.length).length;
    $('expS').innerHTML=n?`Có ${n} mẫu còn thiếu thông tin. Bạn có thể bổ sung hoặc cứ xuất, chỗ thiếu sẽ để dòng chấm trên bản in.`:'<span class="okc">Thông tin chính của các mẫu đã chọn đã đủ.</span>';
    $('expMsg').textContent='';
  }
  $('missList').addEventListener('change',e=>{ const s=e.target.dataset.s; if(!s) return; sel[s]=e.target.checked; try{localStorage.setItem(SELKEY,JSON.stringify(sel))}catch(x){} e.target.closest('.m').classList.toggle('off',!e.target.checked); });

  const open=()=>{ render(); $('ovExp').classList.add('on'); };
  const close=()=>$('ovExp').classList.remove('on');
  $('bExport').addEventListener('click',e=>{ e.preventDefault(); open(); });
  $('bClose').onclick=close;
  // wipe everything typed so far: every form draft plus the shared store (the chosen-forms ticks are kept)
  $('bWipe').onclick=()=>{
    if(!confirm('Xóa TOÀN BỘ dữ liệu đã nhập ở tất cả các mẫu (kể cả dữ liệu dùng chung)? Không khôi phục được.')) return;
    try{
      Object.keys(localStorage).filter(k=>k.startsWith('draft-')||k===SKEY).forEach(k=>localStorage.removeItem(k));
    }catch(e){}
    location.reload();
  };
  $('ovExp').addEventListener('mousedown',e=>{ if(e.target.id==='ovExp') close(); });
  document.addEventListener('keydown',e=>{ if(e.key==='Escape') close(); });

  // file name typed by the user: no path characters
  const base=()=>$('fname').value.replace(/\.(pdf|docx)$/i,'').replace(/[\\/:*?"<>|\x00-\x1f]/g,'').trim()||'bo-ho-so-nha-dat';
  const btns=['bDocx','bPdf'].map($);
  async function run(format,filename){
    const chosen=slugs.filter(s=>sel[s]); const msg=$('expMsg');
    if(!chosen.length){ msg.textContent='Chọn ít nhất một mẫu.'; return; }
    btns.forEach(b=>b.disabled=true); msg.textContent='Đang tạo hồ sơ… (khoảng '+(3+chosen.length*2)+' giây)';
    try{
      const data={}; chosen.forEach(s=>data[s]=new URLSearchParams(params(s)).toString());
      const res=await fetch('/bundle',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document','X-CSRF-TOKEN':token},body:JSON.stringify({format,forms:chosen,data})});
      if(!res.ok){ const why=res.status===503?(await res.text()):''; throw new Error(why||('HTTP '+res.status)); }
      const url=URL.createObjectURL(await res.blob());
      const a=document.createElement('a'); a.href=url; a.download=filename; document.body.append(a); a.click(); a.remove();
      setTimeout(()=>URL.revokeObjectURL(url),10000);
      msg.textContent='Đã tải '+filename+' ('+chosen.length+' mẫu).';
    }catch(e){ msg.textContent='Không tạo được hồ sơ ('+e.message+').'; }
    btns.forEach(b=>b.disabled=false);
  }
  $('bDocx').onclick=()=>run('docx',base()+'.docx');
  $('bPdf').onclick=()=>run('pdf',base()+'.pdf');
})();
</script>
