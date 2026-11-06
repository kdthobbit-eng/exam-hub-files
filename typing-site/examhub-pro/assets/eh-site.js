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
