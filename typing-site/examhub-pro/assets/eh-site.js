(function(){
  document.querySelectorAll('[data-drawer]').forEach(function(b){var d=document.getElementById(b.getAttribute('data-drawer'));if(!d)return;
    b.addEventListener('click',function(){d.hidden=false;});
    d.addEventListener('click',function(e){if(e.target===d||e.target.closest('[data-close]'))d.hidden=true;});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')d.hidden=true;});});
  var tk=document.querySelector('.eh-ticker');
  if(tk){var it=tk.querySelectorAll('.tk'),i=0,tm;function show(k){it[i].classList.remove('on');i=(k+it.length)%it.length;it[i].classList.add('on');}
    function auto(){clearInterval(tm);tm=setInterval(function(){show(i+1)},5000);}
    tk.querySelector('.tk-prev').onclick=function(){show(i-1);auto()};tk.querySelector('.tk-next').onclick=function(){show(i+1);auto()};if(it.length>1)auto();}
  var up=document.getElementById('eh-upc');
  if(up){up.querySelectorAll('.eh-upc-tabs button').forEach(function(b){b.addEventListener('click',function(){up.querySelectorAll('.eh-upc-tabs button').forEach(function(x){x.classList.toggle('on',x===b)});up.querySelectorAll('.eh-upc-list').forEach(function(l){l.hidden=l.getAttribute('data-t')!==b.getAttribute('data-t')})})});}
})();
(function(){
  var q=document.getElementById('eh-exam-search'); if(!q) return;
  var cards=[].slice.call(document.querySelectorAll('.eh-card.exam')), cat=document.getElementById('eh-f-cat'), lang=document.getElementById('eh-f-lang'), cnt=document.getElementById('eh-count');
  function run(){var s=q.value.trim().toLowerCase(),c=cat?cat.value:'',l=lang?lang.value:'',n=0;
    cards.forEach(function(el){var ok=(!s||el.dataset.name.indexOf(s)>-1)&&(!c||el.dataset.cat===c)&&(!l||el.dataset.lang===l);el.style.display=ok?'':'none';if(ok)n++;});
    if(cnt)cnt.textContent=n;}
  [q,cat,lang].forEach(function(e){if(e){e.addEventListener('input',run);e.addEventListener('change',run);}});
  document.querySelectorAll('[data-q]').forEach(function(b){b.addEventListener('click',function(){q.value=b.dataset.q;run();q.scrollIntoView({behavior:'smooth',block:'center'});});});
  var u=new URLSearchParams(location.search); if(u.get('q')){q.value=u.get('q');} run();
})();
