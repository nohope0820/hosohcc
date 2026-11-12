@extends('layouts.plain')
@section('title', 'Xuất trọn bộ hồ sơ')
@section('sub', 'Gộp các mẫu đã chọn thành một file PDF (đúng thứ tự bộ hồ sơ) hoặc tải riêng từng file Word trong một file nén. Dữ liệu lấy từ những gì bạn đã nhập ở từng mẫu trong trình duyệt này.')
@section('content')
<style>
  .bl{display:grid;gap:8px;margin-bottom:16px}
  .bi{display:flex;gap:12px;align-items:center;background:var(--card);border:1px solid var(--line);border-radius:10px;padding:10px 14px}
  .bi input{width:18px;height:18px} .bi .t{flex:1} .bi .t b{display:block}
  .bi .st{font-size:13px;color:var(--mut)} .bi .st.ok{color:#1a7f45} .bi .st.warn{color:#b26a00}
  .bi a{font-size:13px;color:var(--acc)}
  .acts{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px}
  #msg{font-size:14px;color:var(--mut);min-height:20px;margin-bottom:10px}
  #pvf{width:100%;height:80vh;border:1px solid var(--line);border-radius:10px;background:#fff;display:none}
</style>
<div class="bl" id="list"></div>
<div class="acts">
  <button type="button" id="bPdf">Tải PDF trọn bộ</button>
  <button type="button" id="bPrev" class="sec">Xem trước / In</button>
  <button type="button" id="bZip" class="sec">Tải file nén Word (.zip)</button>
</div>
<div id="msg"></div>
<iframe id="pvf" title="Xem trước trọn bộ"></iframe>
@endsection
@section('scripts')
<script>
(function(){
  const SH=@json($shared), FORMS=@json(collect($forms)->map(fn($f)=>$f[1])), SKEY='hcc-shared', SELKEY='bundle-sel';
  const slugs=Object.keys(FORMS);
  const token=document.querySelector('meta[name=csrf-token]').content;
  let S={}; try{S=JSON.parse(localStorage.getItem(SKEY)||'{}')||{}}catch(e){}
  let sel={}; try{sel=JSON.parse(localStorage.getItem(SELKEY)||'{}')||{}}catch(e){}

  // Rebuild the field list of a form exactly as its page would post it: own draft + shared values
  function params(slug){
    let d=null; try{d=JSON.parse(localStorage.getItem('draft-'+slug)||'null')}catch(e){}
    const p={}; if(d) Object.entries(d).forEach(([k,v])=>{ if(v===true) p[k]='1'; else if(v!==false&&v!==''&&v!=null) p[k]=v; });
    const cfg=SH[slug]||{fields:{},rows:{}};
    Object.entries(cfg.fields||{}).forEach(([name,key])=>{ if(S[key]) p[name]=S[key]; });
    Object.entries(cfg.rows||{}).forEach(([name,sh])=>{
      Object.keys(S).forEach(k=>{
        const m=k.match(new RegExp('^'+sh.prefix+'\\.(\\d+)\\.(\\w+)$')); if(!m) return;
        const col=Object.keys(sh.cols).find(c=>sh.cols[c]===m[2]); const r=(+m[1])-sh.offset;
        if(col&&r>=0&&S[k]) p[`${name}[${r}][${col}]`]=S[k];
      });
    });
    if(!d && ['lptb','tncn','sddpnn'].includes(slug)) p.lan_dau='1'; // same default as the form page
    return {p,hasDraft:!!d};
  }
  function filled(p){ return Object.keys(p).filter(k=>k!=='lan_dau'&&k!=='uy_quyen').length; }

  const list=document.getElementById('list');
  slugs.forEach(s=>{
    const {p,hasDraft}=params(s), n=filled(p);
    if(!(s in sel)) sel[s]=true;
    const row=document.createElement('label'); row.className='bi';
    row.innerHTML=`<input type="checkbox" data-s="${s}" ${sel[s]?'checked':''}>
      <span class="t"><b>${FORMS[s]}</b><span class="st ${n?'ok':'warn'}">${n? 'Đã điền '+n+' ô' : 'Chưa nhập gì (xuất ra mẫu trống)'}</span></span>
      <a href="/${s}">Mở để sửa</a>`;
    row.querySelector('a').onclick=e=>e.stopPropagation();
    list.append(row);
  });
  list.addEventListener('change',e=>{ if(e.target.dataset.s){ sel[e.target.dataset.s]=e.target.checked; try{localStorage.setItem(SELKEY,JSON.stringify(sel))}catch(x){} }});

  const msg=document.getElementById('msg'), pvf=document.getElementById('pvf');
  const btns=[...document.querySelectorAll('.acts button')];
  let url=null;
  async function run(format,filename){
    const chosen=slugs.filter(s=>sel[s]); if(!chosen.length){ msg.textContent='Chọn ít nhất một mẫu.'; return; }
    btns.forEach(b=>b.disabled=true); msg.textContent='Đang tạo hồ sơ… (khoảng '+(3+chosen.length*2)+' giây)';
    try{
      const data={}; chosen.forEach(s=>data[s]=new URLSearchParams(params(s).p).toString());
      const res=await fetch('/bundle',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/pdf,application/zip','X-CSRF-TOKEN':token},body:JSON.stringify({format,forms:chosen,data})});
      if(!res.ok) throw new Error('HTTP '+res.status);
      const blob=await res.blob(); if(url) URL.revokeObjectURL(url); url=URL.createObjectURL(blob);
      if(format==='print'){ pvf.style.display='block'; pvf.src=url; msg.textContent='Đã tạo bản xem trước ('+chosen.length+' mẫu). Dùng nút in của khung PDF để in.'; pvf.scrollIntoView({behavior:'smooth'}); }
      else { const a=document.createElement('a'); a.href=url; a.download=filename; document.body.append(a); a.click(); a.remove(); msg.textContent='Đã tải '+filename+' ('+chosen.length+' mẫu).'; }
    }catch(e){ msg.textContent='Không tạo được hồ sơ ('+e.message+').'; }
    btns.forEach(b=>b.disabled=false);
  }
  document.getElementById('bPdf').onclick=()=>run('pdf','bo-ho-so-nha-dat.pdf');
  document.getElementById('bPrev').onclick=()=>run('print','');
  document.getElementById('bZip').onclick=()=>run('zip','bo-ho-so-nha-dat.zip');
})();
</script>
@endsection
