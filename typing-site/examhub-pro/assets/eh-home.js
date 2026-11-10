/* UPSSSC Alert home page: language switch, scroll reveal, counters, typing demo, YouTube corner player */
(function () {
  var root = document.documentElement, reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } }

  /* language */
  var lb = document.querySelectorAll('.uh-lang button');
  function setLang(l) {
    if (l === 'hi') root.setAttribute('data-lang', 'hi'); else root.removeAttribute('data-lang');
    lb.forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-lang') === l); });
  }
  setLang(root.getAttribute('data-lang') === 'hi' ? 'hi' : 'en');
  lb.forEach(function (b) { b.addEventListener('click', function () { var l = b.getAttribute('data-lang'); setLang(l); store('eh_lang', l); }); });

  /* counters */
  function count(el) {
    var to = +el.getAttribute('data-count'), suf = el.getAttribute('data-suffix') || '', t0 = null, dur = 1600;
    if (reduce || !to) return;
    function step(t) { if (!t0) t0 = t; var p = Math.min(1, (t - t0) / dur), v = Math.round(to * (1 - Math.pow(1 - p, 3)));
      el.textContent = v.toLocaleString('en-IN') + suf; if (p < 1) requestAnimationFrame(step); }
    requestAnimationFrame(step);
  }

  /* scroll reveal */
  if ('IntersectionObserver' in window && !reduce) {
    root.classList.add('uh-anim');
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    document.querySelectorAll('.uh [data-rv]').forEach(function (el) { io.observe(el); });
    setTimeout(function () { document.querySelectorAll('.uh [data-rv]:not(.in)').forEach(function (el) { var r = el.getBoundingClientRect(); if (r.top < innerHeight) el.classList.add('in'); }); }, 1200);
  }
  document.querySelectorAll('.uh-stats b[data-count]').forEach(count);

  /* hero photo moves a little slower than the page */
  var bg = document.querySelector('.uh-hero-bg');
  if (bg && !reduce) {
    var tick = false;
    addEventListener('scroll', function () { if (tick) return; tick = true; requestAnimationFrame(function () { var y = scrollY; if (y < 1200) bg.style.translate = '0 ' + (y * 0.18) + 'px'; tick = false; }); }, { passive: true });
  }

  /* typing demo */
  var psg = document.getElementById('uh-psg'), out = document.getElementById('uh-typed');
  if (psg && out) {
    var words = psg.textContent.trim().split(/\s+/), text = words.join(' '), i = 0;
    function paint() {
      var done = text.slice(0, i), wi = done.split(' ').length - 1;
      psg.innerHTML = words.map(function (w, k) { return k < wi ? '<span class="ok">' + w + '</span>' : k === wi ? '<span class="cur">' + w + '</span>' : w; }).join(' ');
      out.textContent = done;
    }
    if (reduce) { i = 46; paint(); }
    else (function type() { paint(); i++; if (i > text.length) { i = 0; setTimeout(type, 2600); } else setTimeout(type, 70 + Math.random() * 90); })();
  }

  /* recent results table pages */
  var t = document.getElementById('hx-recent'), pg = document.querySelector('.hx-pg');
  if (t && pg) {
    var rows = [].slice.call(t.tBodies[0].rows), p = 0, n = Math.ceil(rows.length / 5), b = pg.querySelectorAll('button');
    pg.addEventListener('click', function (e) { var d = e.target.getAttribute('data-d'); if (!d) return; p = Math.max(0, Math.min(n - 1, p + (+d)));
      rows.forEach(function (r, k) { r.hidden = Math.floor(k / 5) !== p; }); pg.querySelector('b').textContent = p + 1; b[0].disabled = p === 0; b[1].disabled = p === n - 1; });
  }

  /* YouTube corner player: muted, loads after the page settles, remembers close for this visit */
  var yt = document.getElementById('uh-yt');
  if (yt && yt.getAttribute('data-list')) {
    var closed = false; try { closed = sessionStorage.getItem('eh_yt') === 'x'; } catch (e) {}
    var loaded = false;
    var lists = yt.getAttribute('data-list').split(','), li = 0, player = null;
    function play() {
      var box = yt.querySelector('.uh-yt-v'); box.innerHTML = '<div id="uh-yt-p"></div>';
      player = new YT.Player('uh-yt-p', { width: '100%', height: '100%', host: 'https://www.youtube-nocookie.com',
        playerVars: { listType: 'playlist', list: lists[li], autoplay: 1, mute: 1, playsinline: 1, rel: 0 },
        events: { onReady: function (e) { e.target.mute(); e.target.playVideo(); },
          onError: function () { if (++li < lists.length) play(); else yt.hidden = true; } } });
    }
    function load() {
      if (loaded || closed) return; loaded = true; yt.hidden = false;
      var prev = window.onYouTubeIframeAPIReady;
      window.onYouTubeIframeAPIReady = function () { if (prev) prev(); play(); };
      if (window.YT && YT.Player) play();
      else { var sc = document.createElement('script'); sc.src = 'https://www.youtube.com/iframe_api'; document.head.appendChild(sc); }
    }
    if (innerWidth > 700) setTimeout(load, 3500); /* on phones it waits for the first scroll so it does not cover the hero */
    addEventListener('scroll', load, { once: true, passive: true });
    yt.querySelector('.uh-yt-x').addEventListener('click', function () { yt.hidden = true; yt.querySelector('.uh-yt-v').innerHTML = ''; closed = true; try { sessionStorage.setItem('eh_yt', 'x'); } catch (e) {} });
    yt.querySelector('.uh-yt-min').addEventListener('click', function () { yt.classList.toggle('min'); });
  }
})();
