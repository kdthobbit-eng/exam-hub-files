/* Exam Hub Typing — exam-style typing window */
(function () {
  'use strict';
  var CFG = window.EH_TEST;
  if (!CFG) return;
  var H = window.EHHindi;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var store = {
    get: function (k, d) { try { var v = localStorage.getItem('eh_' + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } },
    set: function (k, v) { try { localStorage.setItem('eh_' + k, JSON.stringify(v)); } catch (e) {} }
  };

  var exam = CFG.exam;
  var isHindi = exam.lang === 'hi';
  var LAYOUTS = isHindi
    ? [['krutidev', 'Hindi - Krutidev', 'Remington', 'Hindi - Krutidev Regular'], ['inscript', 'Hindi - Inscript', 'Inscript', 'Hindi - Mangal'], ['remington', 'Hindi - Remington Gail', 'Remington Gail', 'Hindi - Mangal'], ['external', 'Hindi - Inscript (अपना कीबोर्ड)', 'Inscript', 'Hindi - Mangal']]
    : [['english', 'English', 'QWERTY', 'English']];
  var KD_FONT = '';
  function hasFont(name) {
    try {
      var c = document.createElement('canvas').getContext('2d'), t = 'abcdefghijklmnopqrstuvwxyzABCDEF0123456789';
      c.font = '40px monospace'; var a = c.measureText(t).width;
      c.font = '40px "' + name + '", monospace'; return c.measureText(t).width !== a;
    } catch (e) { return false; }
  }
  function detectKD() {
    ['EHKruti', 'Kruti Dev 010', 'KrutiDev010', 'Kruti Dev 010 Regular', 'KRDV010'].some(function (n) { if (hasFont(n)) { KD_FONT = n; return true; } return false; });
  }
  if (document.fonts && document.fonts.load) document.fonts.load('20px EHKruti').then(detectKD, detectKD); else detectKD();
  function native() { return S.layout === 'english' || S.layout === 'external'; }
  function kdMode() { return S.layout === 'krutidev' && !!KD_FONT; }

  var S = {
    layout: LAYOUTS[0][0], minutes: exam.minutes,
    set: store.get('set', { tcs: true, bks: true, lbks: false, hl: false, as: false, xs: true, scr: true, snd: false, fs: 0 }),
    started: false, done: false, t0: 0, left: 0, timer: null,
    buf: '', keys: 0, bks: 0, words: [], text: '', passageId: '', title: ''
  };

  var setup = $('#eh-setup'), test = $('#eh-test'), result = $('#eh-result');
  var pv = $('#eh-passage'), ta = $('#eh-input'), tm = $('#eh-timer');
  var pool = CFG.passages.filter(function (p) { return p.lang === (isHindi ? 'hi' : 'en'); });

  /* ---------- setup ---------- */
  (function fillSetup() {
    $('#eh-opt-layout').innerHTML = LAYOUTS.map(function (l) { return '<option value="' + l[0] + '">' + l[1] + '</option>'; }).join('');
    var last = store.get('layout', '');
    if (LAYOUTS.some(function (l) { return l[0] === last; })) $('#eh-opt-layout').value = last;
    var ps = $('#eh-opt-passage');
    var pick = function () { var r = setup.querySelector('input[name="eh-psg"]:checked'); ps.value = r ? r.value : ''; };
    setup.querySelectorAll('input[name="eh-psg"]').forEach(function (r) { r.addEventListener('change', function () {
      setup.querySelectorAll('input[name="eh-psg"]').forEach(function (o) { if (o !== r && o.value === r.value) o.checked = true; });
      pick(); var m = $('#eh-sp-modal'); if (m && r.closest('#eh-sp-modal')) m.hidden = true;
    }); });
    pick();
    if (CFG.fixedPassage) ps.value = CFG.fixedPassage;
    var mi = $('#eh-opt-min'); mi.value = String(exam.minutes); if (CFG.lockTime) mi.disabled = true;
    var un = $('#eh-opt-unit'); if (un) un.value = exam.unit === 'keys' ? 'keys' : 'words';
    var nm = $('#eh-guest-name'); if (nm) nm.value = store.get('name', '');
    /* steps */
    var agree = $('#eh-agree'), next = $('#eh-next'), akey = 'agree_' + exam.slug;
    function go(n) {
      $('#eh-step1').hidden = n !== 1; $('#eh-step2').hidden = n !== 2;
      setup.querySelectorAll('.eh-steps-nav li').forEach(function (li) { li.classList.toggle('on', li.getAttribute('data-go') === String(n)); });
      if (n === 2) store.set(akey, 1);
      window.scrollTo({ top: setup.getBoundingClientRect().top + window.scrollY - 90, behavior: 'smooth' });
    }
    agree.checked = !!store.get(akey, 0); next.disabled = !agree.checked;
    agree.addEventListener('change', function () { next.disabled = !agree.checked; });
    next.addEventListener('click', function () { if (agree.checked) go(2); });
    setup.querySelectorAll('[data-go]').forEach(function (b) { b.addEventListener('click', function () { var n = +b.getAttribute('data-go'); if (n === 2 && !agree.checked) return; go(n); }); });
    S.goStep = go;
    var so = $('#eh-sp-open'), sm = $('#eh-sp-modal');
    if (so && sm) {
      so.addEventListener('click', function () { sm.hidden = false; });
      sm.addEventListener('click', function (e) { if (e.target === sm || e.target.hasAttribute('data-x')) sm.hidden = true; });
    }
  })();

  function buildText(firstId) {
    var need = exam.unit === 'keys' ? Math.ceil(exam.target * 1.3) : Math.ceil(exam.target * 1.3 * 6.2);
    need = Math.max(need, S.minutes * 60 * 6);
    if (S.limit > 0) need = S.limitUnit === 'words' ? S.limit * 8 : S.limit + 10;
    var order = pool.slice();
    if (firstId) order.sort(function (a, b) { return (a.id === firstId ? -1 : 0) - (b.id === firstId ? -1 : 0); });
    else for (var i = order.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)); var t = order[i]; order[i] = order[j]; order[j] = t; }
    var parts = [], len = 0, k = 0;
    while (len < need && order.length && k < 40) { var p = order[k % order.length]; parts.push(p.text); len += p.chars + 1; k++; }
    S.passageId = order[0] ? order[0].id : ''; S.title = order[0] ? order[0].title : '';
    var txt = H.norm(parts.join(' '));
    if (S.limit > 0) {
      if (S.limitUnit === 'words') txt = txt.split(/\s+/).slice(0, S.limit).join(' ');
      else if (txt.length > S.limit) { var cut = txt.lastIndexOf(' ', S.limit); txt = txt.slice(0, cut > 0 ? cut : S.limit); }
    }
    return txt;
  }

  /* ---------- settings ---------- */
  function applySettings() {
    var st = S.set;
    test.classList.toggle('tcs', !!st.tcs);
    var pbase = isHindi ? 22 : 20, tbase = 24;
    pv.style.fontSize = (pbase + st.fs * 2) + 'px'; ta.style.fontSize = (tbase + st.fs * 2) + 'px';
    $('#set-fs-v').textContent = Math.round((pbase + st.fs * 2) * 0.75);
    pv.style.visibility = st.scr ? '' : 'hidden';
    test.classList.toggle('snd-on', !!st.snd);
    if (!st.hl) clearMark(); else mark(true);
    store.set('set', st);
  }
  [['set-tcs', 'tcs'], ['set-bks', 'bks'], ['set-lbks', 'lbks'], ['set-hl', 'hl'], ['set-as', 'as'], ['set-xs', 'xs'], ['set-scr', 'scr'], ['set-snd', 'snd']].forEach(function (p) {
    var el = $('#' + p[0]); el.checked = !!S.set[p[1]];
    el.addEventListener('change', function () { S.set[p[1]] = el.checked; applySettings(); });
  });
  $('#eh-fs-plus').addEventListener('click', function () { S.set.fs = Math.min(8, S.set.fs + 1); applySettings(); });
  $('#eh-fs-minus').addEventListener('click', function () { S.set.fs = Math.max(-4, S.set.fs - 1); applySettings(); });
  var drawer = $('#eh-drawer');
  $('#eh-gear').addEventListener('click', function () { drawer.hidden = false; });
  drawer.querySelectorAll('[data-close]').forEach(function (b) { b.addEventListener('click', function () { drawer.hidden = true; ta.focus(); }); });
  $('#eh-full').addEventListener('click', function () {
    if (!document.fullscreenElement) (test.requestFullscreen || test.webkitRequestFullscreen || function () {}).call(test);
    else document.exitFullscreen();
  });
  test.querySelectorAll('.ex-tool [data-act]').forEach(function (b) {
    b.addEventListener('click', function () {
      var a = b.getAttribute('data-act');
      if (a === 'mute') { S.set.snd = !S.set.snd; $('#set-snd').checked = S.set.snd; }
      else if (a === 'full') { $('#eh-full').click(); }
      else if (a === 'minus') S.set.fs = Math.max(-4, S.set.fs - 1);
      else if (a === 'plus') S.set.fs = Math.min(8, S.set.fs + 1);
      else if (a === 'reset') S.set.fs = 0;
      applySettings(); ta.focus();
    });
  });
  $('#eh-st-on').addEventListener('change', function () { $('#eh-stats').classList.toggle('off', !this.checked); });

  var actx = null;
  function click() {
    if (!S.set.snd) return;
    try {
      actx = actx || new (window.AudioContext || window.webkitAudioContext)();
      var o = actx.createOscillator(), g = actx.createGain();
      o.type = 'square'; o.frequency.value = 1700 + Math.random() * 300;
      g.gain.setValueAtTime(0.04, actx.currentTime); g.gain.exponentialRampToValueAtTime(0.0001, actx.currentTime + 0.035);
      o.connect(g); g.connect(actx.destination); o.start(); o.stop(actx.currentTime + 0.04);
    } catch (e) {}
  }

  /* ---------- start ---------- */
  function startTest() {
    S.layout = $('#eh-opt-layout').value; store.set('layout', S.layout);
    var mi = $('#eh-opt-min'); if (!mi.disabled) S.minutes = Math.max(1, Math.min(60, parseInt(mi.value, 10) || exam.minutes));
    var nm = $('#eh-guest-name'); if (nm) store.set('name', nm.value.trim());
    var lim = $('#eh-opt-limit'); S.limit = lim ? Math.max(0, parseInt(lim.value, 10) || 0) : 0;
    S.limitUnit = $('#eh-opt-unit') ? $('#eh-opt-unit').value : 'keys';
    S.text = buildText(CFG.fixedPassage || $('#eh-opt-passage').value);
    S.words = S.text.split(/\s+/);
    S.show = kdMode() ? S.words.map(function (w) { return H.unicodeToKD(w); }) : S.words;
    S.left = S.minutes * 60; S.buf = ''; S.keys = 0; S.bks = 0; S.started = false; S.done = false; lastIdx = -1;

    var L = LAYOUTS.filter(function (l) { return l[0] === S.layout; })[0];
    $('#eh-layout-name').textContent = L[3];
    $('#eh-kb').textContent = L[2];
    var pidx = 1; pool.forEach(function (p, i) { if (p.id === S.passageId) pidx = i + 1; });
    $('#eh-ex-title').textContent = 'Typing Test Id ' + pidx + ' - ' + (S.title || exam.name);
    var nmv = CFG.userName || (nm && nm.value.trim()) || 'Candidate';
    $('#eh-cand-name').textContent = nmv.toUpperCase();
    $('#eh-cand-av').innerHTML = CFG.avatar ? '<img src="' + esc(CFG.avatar) + '" alt="">' : esc(nmv.charAt(0).toUpperCase());

    var kd = kdMode();
    pv.className = 'eh-passage' + (isHindi ? ' hi' : '') + (kd ? ' kd' : '');
    ta.className = 'eh-input' + (isHindi ? ' hi' : '') + (kd ? ' kd' : '');
    if (kd) { pv.style.fontFamily = ta.style.fontFamily = '"' + KD_FONT + '"'; } else { pv.style.fontFamily = ta.style.fontFamily = ''; }
    pv.innerHTML = S.show.map(function (w, i) { return '<span data-i="' + i + '">' + esc(w) + '</span>'; }).join(' ');
    pv.scrollTop = 0;
    ta.value = ''; ta.disabled = false;
    setup.hidden = true; result.hidden = true; test.hidden = false;
    document.documentElement.classList.add('eh-exam-on');
    applySettings(); drawTime(); stats(true);
    $('#st-kc').textContent = S.show.join(' ').length; $('#st-wc').textContent = S.words.length;
    setTimeout(function () { ta.focus(); }, 50);
  }

  function closeExam() {
    test.hidden = true; drawer.hidden = true;
    document.documentElement.classList.remove('eh-exam-on');
    if (document.fullscreenElement) document.exitFullscreen();
  }

  function drawTime() {
    var m = Math.floor(S.left / 60), s = S.left % 60;
    tm.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    tm.parentNode.classList.toggle('low', S.left <= 60);
  }

  function begin() {
    if (S.started) return;
    S.started = true; S.t0 = Date.now();
    S.timer = setInterval(function () {
      S.left = Math.max(0, S.minutes * 60 - Math.floor((Date.now() - S.t0) / 1000));
      drawTime();
      if (S.left <= 0) finish();
    }, 250);
  }

  function current() {
    if (S.layout === 'krutidev' || S.layout === 'remington') return H.kdToUnicode(S.buf);
    return S.layout === 'inscript' ? H.norm(S.buf) : ta.value;
  }
  function rawText() { return native() ? ta.value : S.buf; }

  /* ---------- highlight + scroll ---------- */
  var lastIdx = -1;
  function clearMark() { if (lastIdx >= 0) { var o = pv.querySelector('[data-i="' + lastIdx + '"]'); if (o) o.classList.remove('cur'); } }
  function curIndex() {
    var typed = current();
    var n = typed.trim() === '' ? 0 : typed.trim().split(/\s+/).length;
    if (typed.length && !/\s$/.test(typed)) n--;
    return Math.max(0, n);
  }
  function mark(force) {
    var n = curIndex();
    if (n === lastIdx && !force) return;
    clearMark();
    var b = pv.querySelector('[data-i="' + n + '"]');
    if (b && S.set.hl) b.classList.add('cur');
    if (b && (S.set.as || S.set.hl)) {
      var top = b.offsetTop - pv.offsetTop;
      if (top > pv.scrollTop + pv.clientHeight - 50 || top < pv.scrollTop) pv.scrollTop = top - 30;
    }
    lastIdx = n;
  }

  /* ---------- live stats ---------- */
  var stT = 0;
  function lev(a, b) {
    if (a === b) return 0;
    var m = a.length, n = b.length; if (!m) return n; if (!n) return m;
    var p = new Array(n + 1), c = new Array(n + 1), i, j;
    for (j = 0; j <= n; j++) p[j] = j;
    for (i = 1; i <= m; i++) { c[0] = i; for (j = 1; j <= n; j++) c[j] = Math.min(p[j] + 1, c[j - 1] + 1, p[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1)); var t = p; p = c; c = t; }
    return p[n];
  }
  function errorsOf(ops) {
    var we = 0, ke = 0;
    ops.forEach(function (o) {
      if (o.t === 'ok') return;
      we++;
      if (o.t === 'om') ke += o.s.length; else if (o.t === 'add') ke += o.w.length; else ke += Math.max(1, lev(o.s, o.w));
    });
    return { we: we, ke: ke };
  }
  function stats(now) {
    var run = function () {
      var typed = current().trim(), tw = typed ? typed.split(/\s+/) : [];
      var done = /\s$/.test(current()) ? tw : tw.slice(0, -1);
      var e = errorsOf(compare(S.words, done));
      $('#st-kt').textContent = S.keys; $('#st-bk').textContent = S.bks;
      $('#st-ke').textContent = e.ke; $('#st-we').textContent = e.we;
      $('#st-tw').textContent = tw.length; $('#st-pw').textContent = Math.max(0, S.words.length - tw.length);
    };
    clearTimeout(stT);
    if (now) run(); else stT = setTimeout(run, 180);
  }

  function render() { ta.value = kdMode() ? S.buf : current(); ta.scrollTop = ta.scrollHeight; ta.setSelectionRange(ta.value.length, ta.value.length); }
  function after() { mark(); stats(); }

  /* ---------- keyboard ---------- */
  function onKey(e) {
    if (S.done) return;
    if (e.ctrlKey || e.metaKey || e.altKey) { if (/^(KeyV|KeyA|KeyZ|KeyY|KeyX)$/.test(e.code)) e.preventDefault(); return; }
    var raw = rawText();
    if (e.key === 'Backspace') {
      if (!S.set.bks || !raw.length) { e.preventDefault(); return; }
      if (S.set.lbks && /\s$/.test(raw)) { e.preventDefault(); return; }
      S.bks++; click();
      if (!native()) { e.preventDefault(); S.buf = Array.from(S.buf).slice(0, -1).join(''); render(); }
      setTimeout(after, 0);
      return;
    }
    if (/^(ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Home|End|PageUp|PageDown|Delete|Tab|Enter)$/.test(e.key)) { e.preventDefault(); return; }
    if (e.key === ' ' || e.code === 'Space') {
      if (!raw.length || (!S.set.xs && /\s$/.test(raw))) { e.preventDefault(); return; }
    }
    if (native()) {
      if (e.key.length === 1) { begin(); S.keys++; click(); setTimeout(after, 0); }
      return;
    }
    var map = S.layout === 'inscript' ? H.INSCRIPT : H.US;
    var pair = map[e.code];
    if (!pair) { if (e.key.length === 1) e.preventDefault(); return; }
    e.preventDefault();
    begin(); S.keys++; click();
    S.buf += pair[e.shiftKey ? 1 : 0];
    render(); after();
  }

  /* ---------- evaluation ---------- */
  function stripP(w) { return w.replace(/[.,;:!?'"‘’“”()\-।|/]/g, ''); }
  function compare(src, typ) {
    var m = typ.length;
    var n = Math.min(src.length, m + Math.max(5, Math.ceil(m * 0.1)));
    var D = new Uint32Array((n + 1) * (m + 1)), i, j;
    var cost = function (x, y) { return x === y ? 0 : (x.toLowerCase() === y.toLowerCase() || (stripP(x) && stripP(x) === stripP(y))) ? 1 : 3; };
    for (i = 0; i <= n; i++) D[i * (m + 1)] = i * 2;
    for (j = 0; j <= m; j++) D[j] = j * 2;
    for (i = 1; i <= n; i++) for (j = 1; j <= m; j++) {
      var a = D[(i - 1) * (m + 1) + j - 1] + cost(src[i - 1], typ[j - 1]), b = D[(i - 1) * (m + 1) + j] + 2, d = D[i * (m + 1) + j - 1] + 2;
      D[i * (m + 1) + j] = a < b ? (a < d ? a : d) : (b < d ? b : d);
    }
    var best = 0;
    for (i = 0; i <= n; i++) if (D[i * (m + 1) + m] < D[best * (m + 1) + m]) best = i;
    var ops = []; i = best; j = m;
    while (i > 0 || j > 0) {
      if (i > 0 && j > 0 && D[i * (m + 1) + j] === D[(i - 1) * (m + 1) + j - 1] + cost(src[i - 1], typ[j - 1])) { ops.push({ t: src[i - 1] === typ[j - 1] ? 'ok' : 'sub', s: src[i - 1], w: typ[j - 1] }); i--; j--; }
      else if (i > 0 && D[i * (m + 1) + j] === D[(i - 1) * (m + 1) + j] + 2) { ops.push({ t: 'om', s: src[i - 1] }); i--; }
      else { ops.push({ t: 'add', w: typ[j - 1] }); j--; }
    }
    ops.reverse();
    var out = [];
    for (var k = 0; k < ops.length; k++) {
      var o = ops[k], nx = ops[k + 1];
      if (o.t === 'om' && nx && nx.t === 'sub' && nx.w === o.s + nx.s) { out.push({ t: 'half', s: o.s + ' ' + nx.s, w: nx.w, why: 'space' }); k++; continue; }
      if (o.t === 'sub' && nx && nx.t === 'om' && o.w === o.s + nx.s) { out.push({ t: 'half', s: o.s + ' ' + nx.s, w: o.w, why: 'space' }); k++; continue; }
      if (o.t === 'sub' && nx && nx.t === 'add' && o.w + nx.w === o.s) { out.push({ t: 'half', s: o.s, w: o.w + ' ' + nx.w, why: 'space' }); k++; continue; }
      if (o.t === 'add' && nx && nx.t === 'sub' && o.w + nx.w === nx.s) { out.push({ t: 'half', s: nx.s, w: o.w + ' ' + nx.w, why: 'space' }); k++; continue; }
      if (o.t === 'sub') {
        if (o.s.toLowerCase() === o.w.toLowerCase()) { out.push({ t: 'half', s: o.s, w: o.w, why: 'case' }); continue; }
        if (stripP(o.s) && stripP(o.s) === stripP(o.w)) { out.push({ t: 'half', s: o.s, w: o.w, why: 'punct' }); continue; }
      }
      out.push(o);
    }
    return out;
  }

  function finish() {
    if (S.done) return;
    S.done = true; clearInterval(S.timer); ta.disabled = true;
    var secs = S.started ? Math.min(S.minutes * 60, (Date.now() - S.t0) / 1000) : 0;
    var mins = Math.max(secs / 60, 1 / 60);
    var typed = H.norm(current()).trim();
    var tw = typed ? typed.split(/\s+/) : [];
    var ops = compare(S.words, tw);
    var full = 0, half = 0;
    ops.forEach(function (o) { if (o.t === 'half') half++; else if (o.t !== 'ok') full++; });
    var e = errorsOf(ops);
    var words = tw.length, errs = full + half / 2;
    var errPct = words ? Math.min(100, (errs / words) * 100) : 100;
    var gross = Math.round(words / mins), net = Math.max(0, Math.round((words - errs) / mins));
    var acc = words ? Math.max(0, 100 - errPct) : 0;
    var amount = exam.unit === 'keys' ? S.keys : words;
    var pass = amount >= exam.target && errPct <= (exam.allowed || 5);
    var R = {
      exam: exam.slug, layout: S.layout, passage: S.passageId, minutes: +(secs / 60).toFixed(2),
      keys: S.keys, chars: typed.length, words: words, full: full, half: half, backspaces: S.bks, kerr: e.ke,
      gross: gross, net: net, acc: +acc.toFixed(2), err: +errPct.toFixed(2), pass: pass ? 1 : 0, live: CFG.live || ''
    };
    closeExam();
    showResult(R, ops);
    save(R);
  }

  function showResult(R, ops) {
    result.hidden = false; setup.hidden = true;
    var tgt = exam.unit === 'keys' ? exam.target + ' keys' : exam.target + ' words';
    $('#eh-res-head').innerHTML =
      '<div class="eh-verdict ' + (R.pass ? 'ok' : 'no') + '">' + (R.pass ? '✔ Qualified' : '✘ Not Qualified') + '</div>' +
      '<p class="muted">Target: ' + tgt + ' in ' + exam.minutes + ' min · Allowed error ' + (exam.allowed || 5) + '%</p>';
    var cells = [
      ['Net Speed', R.net + ' WPM'], ['Gross Speed', R.gross + ' WPM'], ['Accuracy', R.acc + '%'], ['Error', R.err + '%'],
      ['Keystrokes Typed', R.keys], ['Keystrokes Error', R.kerr], ['Words Typed', R.words], ['Full Mistakes', R.full],
      ['Half Mistakes', R.half], ['Backspace', R.backspaces], ['Time', Math.floor(R.minutes) + 'm ' + Math.round((R.minutes % 1) * 60) + 's'], ['Pending Words', Math.max(0, S.words.length - R.words)]
    ];
    $('#eh-res-grid').innerHTML = cells.map(function (c) { return '<div class="eh-stat"><span>' + c[0] + '</span><b>' + c[1] + '</b></div>'; }).join('');
    $('#eh-res-diff').innerHTML = ops.map(function (o) {
      if (o.t === 'ok') return '<span class="d-ok">' + esc(o.s) + '</span>';
      if (o.t === 'om') return '<span class="d-om" title="छूटा शब्द">' + esc(o.s) + '</span>';
      if (o.t === 'add') return '<span class="d-add" title="फालतू शब्द">' + esc(o.w) + '</span>';
      if (o.t === 'half') return '<span class="d-half" title="Half mistake: ' + o.why + '">' + esc(o.w) + ' <s>' + esc(o.s) + '</s></span>';
      return '<span class="d-sub" title="Full mistake">' + esc(o.w) + ' <s>' + esc(o.s) + '</s></span>';
    }).join(' ');
    window.scrollTo({ top: result.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
  }

  function save(R) {
    if (!CFG.loggedIn) R.name = store.get('name', '');
    fetch(CFG.rest + 'result', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': CFG.nonce }, body: JSON.stringify(R) })
      .then(function (r) { return r.json(); })
      .then(function (j) { var s = $('#eh-res-saved'); if (s) s.textContent = j && j.ok ? (j.rank ? 'रिज़ल्ट सेव हो गया · आज आपकी रैंक #' + j.rank : 'रिज़ल्ट सेव हो गया') : ''; document.dispatchEvent(new Event('eh:saved')); })
      .catch(function () {});
  }

  /* ---------- buttons ---------- */
  function twoStep(btn, label, fn) {
    btn.addEventListener('click', function () {
      if (btn.dataset.arm === '1') { btn.dataset.arm = ''; btn.textContent = label; fn(); return; }
      btn.dataset.arm = '1'; btn.textContent = 'पक्का? फिर दबाएं';
      setTimeout(function () { btn.dataset.arm = ''; btn.textContent = label; }, 3000);
    });
  }
  $('#eh-start').addEventListener('click', startTest);
  twoStep($('#eh-submit'), 'Submit', finish);
  twoStep($('#eh-cancel'), 'Cancel', function () { S.done = true; clearInterval(S.timer); closeExam(); setup.hidden = false; });
  $('#eh-again').addEventListener('click', function () { result.hidden = true; setup.hidden = false; if (S.goStep) S.goStep(2); });
  ta.addEventListener('keydown', onKey);
  ta.addEventListener('paste', function (e) { e.preventDefault(); });
  ta.addEventListener('drop', function (e) { e.preventDefault(); });
  ta.addEventListener('contextmenu', function (e) { e.preventDefault(); });
  ta.addEventListener('input', function () { if (!native()) render(); else after(); });
  ta.addEventListener('mousedown', function (e) { if (!native()) { e.preventDefault(); ta.focus(); } });
  pv.addEventListener('copy', function (e) { e.preventDefault(); });
  window.addEventListener('beforeunload', function (e) { if (S.started && !S.done) { e.preventDefault(); e.returnValue = ''; } });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !drawer.hidden) drawer.hidden = true; });
})();
