<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Tờ khai nhà đất')</title>
<style>
@include('partials.style')
</style>
</head>
<body>
<div class="wrap">
<div class="layout" id="layout">
<div class="main">
  @include('partials.nav')
  <h1>@yield('title')</h1>
  <p class="sub">@yield('sub')</p>
  <p class="hint"><i></i>Ô có vạch xanh bên trái là <b>dữ liệu dùng chung</b>: nhập ở một mẫu sẽ tự có ở các mẫu khác (tên, địa chỉ, CCCD, mã số thuế, điện thoại, đại lý thuế, nơi ký...). Ngày tháng và số liệu (diện tích, giá trị, số hợp đồng, số thửa...) nhập riêng từng mẫu.</p>
  @if($errors->any())<div class="errs">{{ $errors->first() }}</div>@endif

  <form method="post" action="{{ route('form.generate', $slug) }}" id="frm" data-draft="draft-{{ $slug }}">
    @csrf
    <input type="hidden" name="format" id="format" value="pdf">
    @yield('fields')
    <div class="bar"><div class="in">
      <button type="button" class="sec" id="pvToggle">Xem trước</button>
      <button type="submit" data-fmt="print" formtarget="_blank" class="sec">Mở tab mới / In</button>
      <button type="submit" data-fmt="pdf">Tải PDF</button>
      <button type="submit" data-fmt="docx" class="sec">Tải Word (.docx)</button>
      <button type="button" class="sec" id="clear" style="margin-left:auto">Xóa mẫu này</button>
      <button type="button" class="sec" id="clearShared">Xóa dữ liệu dùng chung</button>
    </div></div>
  </form>
</div>
<aside id="pv">
  <div class="pv-head">
    <b>Xem trước</b><span id="pvStatus"></span>
    <label><input type="checkbox" id="pvAuto" checked> Tự cập nhật</label>
    <button type="button" class="sec" id="pvRefresh">Cập nhật</button>
    <button type="button" class="sec pv-close" id="pvClose">Đóng</button>
  </div>
  <div id="pvBody"><div class="pv-msg">Đang tạo bản xem trước…</div></div>
</aside>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" onerror="window.pdfjsLib=undefined"></script>
<script>
(function(){
  const frm=document.getElementById('frm'), KEY=frm.dataset.draft;

  // ---- shared data (same value in several forms) ----------------------------
  const SH=@json($shared), SKEY='hcc-shared';
  let S={}; try{S=JSON.parse(localStorage.getItem(SKEY)||'{}')||{}}catch(e){S={}}
  function saveS(){try{localStorage.setItem(SKEY,JSON.stringify(S))}catch(e){}}
  function setShared(key,val){ if(val===''||val==null) delete S[key]; else S[key]=val; saveS(); }
  function markShared(el,key){ el.dataset.skey=key; el.classList.add('shared'); }

  // ---- repeating rows -------------------------------------------------
  const reps={};
  document.querySelectorAll('.rows').forEach(box=>{
    const name=box.dataset.name, max=+box.dataset.max, cols=box.dataset.cols.split('|').map(c=>c.split(':'));
    reps[name]={box,max,cols,sh:SH.rows[name]||null};
  });
  function rowKey(name,i,col){ const sh=reps[name].sh; return sh&&sh.cols[col] ? `${sh.prefix}.${i+sh.offset}.${sh.cols[col]}` : null; }
  function reindex(name){
    [...reps[name].box.children].forEach((r,n)=>{
      r.querySelector('.rn').textContent=n+1;
      r.querySelectorAll('input[name]').forEach(i=>{
        i.name=i.name.replace(/\[\d+\]/,`[${n}]`);
        const col=(i.name.match(/\[(\w+)\]$/)||[])[1], k=rowKey(name,n,col);
        if(k) markShared(i,k);
      });
    });
  }
  // rows needed to show every person already stored for this repeater
  function neededRows(name){
    const sh=reps[name].sh; if(!sh) return 0; let n=0;
    Object.keys(S).forEach(k=>{const m=k.match(new RegExp('^'+sh.prefix+'\\.(\\d+)\\.(\\w+)$'));
      if(m && Object.values(sh.cols).includes(m[2])) n=Math.max(n,(+m[1])-sh.offset+1);});
    return n;
  }
  function addRow(name,v={}){
    const {box,max,cols}=reps[name], i=box.children.length; if(i>=max) return;
    const r=document.createElement('div'); r.className='row';
    r.style.gridTemplateColumns=`24px repeat(${cols.length},minmax(0,1fr)) 34px`;
    r.innerHTML='<span class="rn"></span>';
    cols.forEach(([k,label,type])=>{
      const x=document.createElement('input'); x.name=`${name}[${i}][${k}]`;
      if(type==='chk'){const w=document.createElement('label'); w.className='rc'; x.type='checkbox'; x.value='1'; x.checked=!!v[k]; w.append(x,label); r.append(w);}
      else{
        x.type='text'; x.placeholder=label; x.title=label;
        const sk=rowKey(name,i,k); x.value=(sk&&S[sk]!=null)?S[sk]:(v[k]||'');
        if(sk&&S[sk]==null&&x.value) S[sk]=x.value; // migrate older per-form drafts into the shared store
        r.append(x);
      }
    });
    const b=document.createElement('button'); b.type='button'; b.className='sec'; b.textContent='×';
    b.onclick=()=>{ // drop the row, then rewrite the shared people list so the others shift up too
      const before=box.children.length; r.remove(); reindex(name); syncRows(name,before); save();
    };
    r.append(b); box.append(r); reindex(name);
  }
  function syncRows(name,before){
    const {box,sh,cols}=reps[name]; if(!sh) return;
    const rows=[...box.children];
    rows.forEach((r,n)=>cols.forEach(([k])=>{const key=rowKey(name,n,k); if(key){const el=r.querySelector(`[name="${name}[${n}][${k}]"]`); setShared(key,el?el.value:'');}}));
    for(let n=rows.length;n<before;n++) cols.forEach(([k])=>{const key=rowKey(name,n,k); if(key) delete S[key];});
    saveS();
  }
  document.querySelectorAll('.add-row').forEach(b=>b.onclick=()=>{addRow(b.dataset.for); save();});

  // ---- submit format --------------------------------------------------
  frm.querySelectorAll('button[data-fmt]').forEach(b=>b.addEventListener('click',()=>document.getElementById('format').value=b.dataset.fmt));

  // ---- draft kept only in this browser (survives refresh) ----------------
  // shared fields live in the shared store, not in this form's own draft
  function save(){schedule();try{
    const o={}; frm.querySelectorAll('input[name],textarea[name],select[name]').forEach(i=>{
      if(i.name==='_token'||i.name==='format'||i.dataset.skey) return;
      o[i.name]= i.type==='checkbox' ? i.checked : i.value; });
    localStorage.setItem(KEY,JSON.stringify(o));}catch(e){}}
  function load(){
    let o=null; try{o=JSON.parse(localStorage.getItem(KEY)||'null')}catch(e){}
    if(o){
      const rows={};
      Object.entries(o).forEach(([k,v])=>{
        const m=k.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
        if(m){(rows[m[1]]=rows[m[1]]||{})[m[2]]=(rows[m[1]][m[2]]||{}); rows[m[1]][m[2]][m[3]]=v; return;}
        const el=frm.elements[k]; if(!el) return;
        if(el.type==='checkbox') el.checked=!!v; else el.value=v;
      });
      Object.entries(rows).forEach(([n,rs])=>{if(reps[n]) Object.keys(rs).sort((a,b)=>a-b).forEach(i=>addRow(n,rs[i]));});
    }
    // single shared fields: stored value wins; an older value typed before sharing existed is migrated
    Object.entries(SH.fields).forEach(([name,key])=>{
      const el=frm.elements[name]; if(!el) return; markShared(el,key);
      if(S[key]!=null) el.value=S[key]; else if(el.value) S[key]=el.value;
    });
    Object.keys(reps).forEach(n=>{
      const want=Math.max(1,neededRows(n)); while(reps[n].box.children.length<Math.min(want,reps[n].max)) addRow(n);
    });
    saveS();
    if(!o){const ld=frm.elements['lan_dau']; if(ld) ld.checked=true;}
  }
  // typing in a shared field updates the store and every other field that carries the same key
  function onShared(e){
    const el=e.target, key=el.dataset&&el.dataset.skey; if(!key) return;
    setShared(key,el.value);
    frm.querySelectorAll('[data-skey="'+key+'"]').forEach(o=>{ if(o!==el) o.value=el.value; });
  }
  frm.addEventListener('input',onShared);
  frm.addEventListener('input',save); frm.addEventListener('change',save);
  document.getElementById('clear').onclick=()=>{if(confirm('Xóa dữ liệu riêng của mẫu này? (dữ liệu dùng chung với các mẫu khác được giữ lại)')){try{localStorage.removeItem(KEY)}catch(e){} location.reload();}};
  document.getElementById('clearShared').onclick=()=>{if(confirm('Xóa dữ liệu dùng chung (tên, địa chỉ, CCCD, MST, điện thoại, đại lý thuế...) ở TẤT CẢ các mẫu?')){try{localStorage.removeItem(SKEY)}catch(e){} location.reload();}};
  load();

  // ---- live preview ------------------------------------------------------
  const layout=document.getElementById('layout'), pvBody=document.getElementById('pvBody'),
        pvStatus=document.getElementById('pvStatus'), pvAuto=document.getElementById('pvAuto');
  const wide=window.matchMedia('(min-width:1100px)');
  let pvOn=false; try{pvOn = wide.matches ? localStorage.getItem('pv-on')!=='0' : false;}catch(e){pvOn=wide.matches;}
  let timer=null, ctl=null, seq=0, blobUrl=null;
  if(window.pdfjsLib){ pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'; }

  function setPv(on){
    pvOn=on; layout.classList.toggle('pv-on',on);
    if(wide.matches){try{localStorage.setItem('pv-on',on?'1':'0')}catch(e){}}
    if(on) refresh();
  }
  async function showPdf(blob){
    if(window.pdfjsLib){
      const doc=await pdfjsLib.getDocument({data:await blob.arrayBuffer()}).promise;
      const w=Math.max(200,pvBody.clientWidth-32) /* leave room for the scrollbar */, dpr=window.devicePixelRatio||1, frag=document.createDocumentFragment();
      for(let n=1;n<=doc.numPages;n++){
        const page=await doc.getPage(n), v0=page.getViewport({scale:1}), sc=w/v0.width, vp=page.getViewport({scale:sc*dpr});
        const c=document.createElement('canvas'); c.width=vp.width; c.height=vp.height;
        c.style.width=w+'px'; c.style.height=(vp.height/dpr)+'px';
        await page.render({canvasContext:c.getContext('2d'),viewport:vp}).promise; frag.append(c);
      }
      const top=pvBody.scrollTop; pvBody.replaceChildren(frag); pvBody.scrollTop=top; // keep reading position
    }else{
      if(blobUrl) URL.revokeObjectURL(blobUrl);
      blobUrl=URL.createObjectURL(blob);
      pvBody.innerHTML=''; const f=document.createElement('iframe'); f.src=blobUrl+'#toolbar=0&view=FitH'; pvBody.append(f);
    }
  }
  async function refresh(){
    if(!pvOn) return;
    clearTimeout(timer); if(ctl) ctl.abort(); ctl=new AbortController(); const my=++seq;
    pvStatus.textContent='Đang cập nhật…';
    try{
      const fd=new FormData(frm); fd.set('format','print');
      const res=await fetch(frm.action,{method:'POST',body:fd,signal:ctl.signal,headers:{'Accept':'application/pdf'}});
      if(!res.ok||!(res.headers.get('content-type')||'').includes('pdf')){ const why=res.status===503?(await res.text()):''; throw new Error(why||('HTTP '+res.status)); }
      const blob=await res.blob(); if(my!==seq) return;
      await showPdf(blob); if(my!==seq) return;
      pvStatus.textContent='Đã cập nhật '+new Date().toLocaleTimeString('vi-VN');
    }catch(e){
      if(e.name==='AbortError') return;
      pvStatus.textContent='Không tạo được bản xem trước';
      if(!pvBody.querySelector('canvas,iframe')) pvBody.innerHTML='<div class="pv-msg">Không tạo được bản xem trước ('+e.message+').</div>';
    }
  }
  function schedule(){ if(pvOn&&pvAuto.checked){ clearTimeout(timer); pvStatus.textContent='Chờ bạn nhập xong…'; timer=setTimeout(refresh,1200); } }
  frm.addEventListener('input',schedule); frm.addEventListener('change',schedule);
  document.getElementById('pvRefresh').onclick=refresh;
  document.getElementById('pvToggle').onclick=()=>setPv(!pvOn);
  document.getElementById('pvClose').onclick=()=>setPv(false);
  wide.addEventListener('change',()=>setPv(wide.matches?(localStorage.getItem('pv-on')!=='0'):false));
  window.addEventListener('resize',()=>{clearTimeout(window.__pvr);window.__pvr=setTimeout(()=>{if(pvOn)refresh()},600)});
  setPv(pvOn);
})();
</script>
</body>
</html>
