/* Exam Hub Typing — typing test engine */
(function () {
  'use strict';
  var CFG = window.EH_TEST;
  if (!CFG) return;
  var H = window.EHHindi;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };

  var exam = CFG.exam;
  var isHindi = exam.lang === 'hi';
  var state = {
    layout: isHindi ? 'remington' : 'english',
    minutes: exam.minutes,
    backspace: true,
    highlight: true,
    passageId: '',
    started: false, done: false,
    t0: 0, left: exam.minutes * 60, timer: null,
    buf: '', keys: 0, bks: 0,
    words: [], text: ''
  };

  /* ---------- setup screen ---------- */
  var setup = $('#eh-setup'), test = $('#eh-test'), result = $('#eh-result');
  var pool = CFG.passages.filter(function (p) { return p.lang === (isHindi ? 'hi' : 'en'); });

  function fillSetup() {
    var lay = $('#eh-opt-layout');
    if (lay) {
      lay.innerHTML = isHindi
        ? '<option value="remington">Krutidev / Remington GAIL</option><option value="inscript">Mangal / Inscript</option>'
        : '<option value="english">English (QWERTY)</option>';
    }
    var ps = $('#eh-opt-passage');
    if (ps) {
      ps.innerHTML = '<option value="">Random Passage</option>' + pool.map(function (p) {
        return '<option value="' + esc(p.id) + '">' + esc(p.title) + '</option>';
      }).join('');
      if (CFG.fixedPassage) { ps.value = CFG.fixedPassage; ps.disabled = true; }
    }
    var mi = $('#eh-opt-min');
    if (mi) { mi.value = exam.minutes; if (CFG.lockTime) mi.disabled = true; }
  }

  function buildText(firstId) {
    var need = exam.unit === 'keys' ? Math.ceil(exam.target * 1.25) : Math.ceil(exam.target * 1.25 * (isHindi ? 6 : 6.2));
    need = Math.max(need, state.minutes * 60 * 6);
    var order = pool.slice();
    if (firstId) {
      order.sort(function (a, b) { return (a.id === firstId ? -1 : 0) - (b.id === firstId ? -1 : 0); });
    } else {
      for (var i = order.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)); var t = order[i]; order[i] = order[j]; order[j] = t; }
    }
    var parts = [], len = 0, k = 0;
    while (len < need && order.length) { var p = order[k % order.length]; parts.push(p.text); len += p.chars + 1; k++; if (k > 40) break; }
    state.passageId = order[0] ? order[0].id : '';
    state.title = order[0] ? order[0].title : '';
    return H ? H.norm(parts.join(' ')) : parts.join(' ');
  }

  /* ---------- test screen ---------- */
  var pv, ta, tm, kc;
  function startTest() {
    var lay = $('#eh-opt-layout'); if (lay) state.layout = lay.value;
    var mi = $('#eh-opt-min'); if (mi && !mi.disabled) state.minutes = Math.max(1, Math.min(60, parseInt(mi.value, 10) || exam.minutes));
    state.backspace = !$('#eh-opt-bks') || $('#eh-opt-bks').checked;
    state.highlight = !$('#eh-opt-hl') || $('#eh-opt-hl').checked;
    var ps = $('#eh-opt-passage');
    state.text = buildText(CFG.fixedPassage || (ps ? ps.value : ''));
    state.words = state.text.split(/\s+/);
    state.left = state.minutes * 60;
    state.buf = ''; state.keys = 0; state.bks = 0; state.started = false; state.done = false;

    setup.hidden = true; result.hidden = true; test.hidden = false;
    pv = $('#eh-passage'); ta = $('#eh-input'); tm = $('#eh-timer'); kc = $('#eh-live');
    pv.className = 'eh-passage' + (isHindi ? ' hi' : '');
    ta.className = 'eh-input' + (isHindi ? ' hi' : '');
    pv.innerHTML = state.words.map(function (w, i) { return '<span data-i="' + i + '">' + esc(w) + '</span>'; }).join(' ');
    $('#eh-layout-name').textContent = { english: 'English', remington: 'Krutidev (Remington GAIL)', inscript: 'Mangal (Inscript)' }[state.layout];
    ta.value = ''; ta.disabled = false; ta.focus();
    drawTime(); mark();
    window.scrollTo({ top: test.offsetTop - 70, behavior: 'smooth' });
  }

  function drawTime() {
    var m = Math.floor(state.left / 60), s = state.left % 60;
    tm.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    tm.classList.toggle('low', state.left <= 30);
  }

  function begin() {
    if (state.started) return;
    state.started = true; state.t0 = Date.now();
    state.timer = setInterval(function () {
      state.left = Math.max(0, state.minutes * 60 - Math.floor((Date.now() - state.t0) / 1000));
      drawTime(); liveStats();
      if (state.left <= 0) finish();
    }, 250);
  }

  function current() {
    if (state.layout === 'remington') return H.kdToUnicode(state.buf);
    return state.layout === 'inscript' ? H.norm(state.buf) : ta.value;
  }

  var lastIdx = -1;
  function mark() {
    if (!state.highlight) { if (lastIdx >= 0) { var o = pv.querySelector('[data-i="' + lastIdx + '"]'); if (o) o.classList.remove('cur'); } return; }
    var typed = current();
    var n = typed.trim() === '' ? 0 : typed.trim().split(/\s+/).length;
    if (typed.length && !/\s$/.test(typed)) n = n - 1;
    if (n < 0) n = 0;
    if (n === lastIdx) return;
    if (lastIdx >= 0) { var a = pv.querySelector('[data-i="' + lastIdx + '"]'); if (a) a.classList.remove('cur'); }
    var b = pv.querySelector('[data-i="' + n + '"]');
    if (b) {
      b.classList.add('cur');
      var top = b.offsetTop - pv.offsetTop;
      if (top > pv.scrollTop + pv.clientHeight - 60 || top < pv.scrollTop) pv.scrollTop = top - 40;
    }
    lastIdx = n;
  }

  function liveStats() {
    var el = (Date.now() - state.t0) / 60000;
    var w = current().trim() ? current().trim().split(/\s+/).length : 0;
    kc.innerHTML = '<b>' + state.keys + '</b> keys · <b>' + w + '</b> words · <b>' + (el > 0.05 ? Math.round(w / el) : 0) + '</b> WPM';
  }

  function render() { ta.value = current(); ta.scrollTop = ta.scrollHeight; ta.setSelectionRange(ta.value.length, ta.value.length); mark(); }

  function onKey(e) {
    if (state.done) return;
    if (e.ctrlKey || e.metaKey || e.altKey) {
      if (/^(KeyV|KeyA|KeyZ|KeyY)$/.test(e.code)) e.preventDefault();
      return;
    }
    if (e.key === 'Backspace') {
      if (!state.backspace) { e.preventDefault(); return; }
      state.bks++;
      if (state.layout !== 'english') { e.preventDefault(); state.buf = Array.from(state.buf).slice(0, -1).join(''); render(); }
      else setTimeout(mark, 0);
      return;
    }
    if (/^(ArrowLeft|ArrowRight|ArrowUp|ArrowDown|Home|End|PageUp|PageDown|Delete|Tab)$/.test(e.key)) { e.preventDefault(); return; }
    if (e.key === 'Enter') { e.preventDefault(); return; }
    var ch = null;
    if (state.layout === 'english') {
      if (e.key.length === 1) { begin(); state.keys++; setTimeout(mark, 0); }
      return;
    }
    var map = state.layout === 'inscript' ? H.INSCRIPT : H.US;
    var pair = map[e.code];
    if (!pair) { if (e.key.length === 1) e.preventDefault(); return; }
    ch = pair[e.shiftKey ? 1 : 0];
    e.preventDefault();
    begin(); state.keys++;
    state.buf += ch;
    render();
  }

  /* ---------- evaluation ---------- */
  function stripP(w) { return w.replace(/[.,;:!?'"‘’“”()\-।|/]/g, ''); }
  function compare(src, typ) {
    var n = src.length, m = typ.length;
    var lim = Math.min(n, m + Math.max(5, Math.ceil(m * 0.1))); n = lim;
    var W = n + 1, D = new Uint32Array((n + 1) * (m + 1));
    for (var i = 0; i <= n; i++) D[i * (m + 1)] = i;
    for (var j = 0; j <= m; j++) D[j] = j;
    for (i = 1; i <= n; i++) for (j = 1; j <= m; j++) {
      var c = src[i - 1] === typ[j - 1] ? 0 : 1;
      var a = D[(i - 1) * (m + 1) + j - 1] + c, b = D[(i - 1) * (m + 1) + j] + 1, d = D[i * (m + 1) + j - 1] + 1;
      D[i * (m + 1) + j] = a < b ? (a < d ? a : d) : (b < d ? b : d);
    }
    var ops = []; i = n; j = m;
    while (i > 0 || j > 0) {
      if (i > 0 && j > 0 && D[i * (m + 1) + j] === D[(i - 1) * (m + 1) + j - 1] + (src[i - 1] === typ[j - 1] ? 0 : 1)) {
        ops.push({ t: src[i - 1] === typ[j - 1] ? 'ok' : 'sub', s: src[i - 1], w: typ[j - 1] }); i--; j--;
      } else if (i > 0 && D[i * (m + 1) + j] === D[(i - 1) * (m + 1) + j] + 1) { ops.push({ t: 'om', s: src[i - 1] }); i--; }
      else { ops.push({ t: 'add', w: typ[j - 1] }); j--; }
    }
    ops.reverse();
    while (ops.length && ops[ops.length - 1].t === 'om') ops.pop();
    // merge spacing errors
    var out = [];
    for (var k = 0; k < ops.length; k++) {
      var o = ops[k], nx = ops[k + 1];
      if (o.t === 'sub' && nx && nx.t === 'om' && o.w === o.s + nx.s) { out.push({ t: 'half', s: o.s + ' ' + nx.s, w: o.w, why: 'space' }); k++; continue; }
      if (o.t === 'sub' && nx && nx.t === 'add' && o.w + nx.w === o.s) { out.push({ t: 'half', s: o.s, w: o.w + ' ' + nx.w, why: 'space' }); k++; continue; }
      if (o.t === 'add' && nx && nx.t === 'sub' && o.w + nx.w === nx.s) { out.push({ t: 'half', s: nx.s, w: o.w + ' ' + nx.w, why: 'space' }); k++; continue; }
      if (o.t === 'sub') {
        if (o.s.toLowerCase() === o.w.toLowerCase()) { out.push({ t: 'half', s: o.s, w: o.w, why: 'case' }); continue; }
        if (stripP(o.s) === stripP(o.w) && stripP(o.s)) { out.push({ t: 'half', s: o.s, w: o.w, why: 'punct' }); continue; }
      }
      out.push(o);
    }
    return out;
  }

  function finish() {
    if (state.done) return;
    state.done = true; clearInterval(state.timer); ta.disabled = true;
    var secs = state.started ? Math.min(state.minutes * 60, (Date.now() - state.t0) / 1000) : 0;
    var mins = Math.max(secs / 60, 1 / 60);
    var typed = (H ? H.norm(current()) : current()).trim();
    var tw = typed ? typed.split(/\s+/) : [];
    var ops = compare(state.words, tw);
    var full = 0, half = 0, ok = 0;
    ops.forEach(function (o) { if (o.t === 'ok') ok++; else if (o.t === 'half') half++; else full++; });
    var words = tw.length, errs = full + half / 2;
    var errPct = words ? Math.min(100, (errs / words) * 100) : 100;
    var gross = Math.round(words / mins);
    var net = Math.max(0, Math.round((words - errs) / mins));
    var acc = words ? Math.max(0, 100 - errPct) : 0;
    var chars = typed.length;
    var allowed = exam.allowed || 5;
    var amount = exam.unit === 'keys' ? state.keys : words;
    var pass = amount >= exam.target && errPct <= allowed;
    var R = {
      exam: exam.slug, layout: state.layout, passage: state.passageId, minutes: +(secs / 60).toFixed(2),
      keys: state.keys, chars: chars, words: words, full: full, half: half, backspaces: state.bks,
      gross: gross, net: net, acc: +acc.toFixed(2), err: +errPct.toFixed(2), pass: pass ? 1 : 0, live: CFG.live || ''
    };
    showResult(R, ops);
    save(R);
  }

  function showResult(R, ops) {
    test.hidden = true; result.hidden = false;
    var tgt = exam.unit === 'keys' ? exam.target + ' keys' : exam.target + ' words';
    $('#eh-res-head').innerHTML =
      '<div class="eh-verdict ' + (R.pass ? 'ok' : 'no') + '">' + (R.pass ? '✔ Qualified' : '✘ Not Qualified') + '</div>' +
      '<p class="muted">Target: ' + tgt + ' in ' + exam.minutes + ' min · Allowed error ' + (exam.allowed || 5) + '%</p>';
    var cells = [
      ['Net Speed', R.net + ' WPM'], ['Gross Speed', R.gross + ' WPM'], ['Accuracy', R.acc + '%'], ['Error', R.err + '%'],
      ['Key Depressions', R.keys], ['Words Typed', R.words], ['Full Mistakes', R.full], ['Half Mistakes', R.half],
      ['Backspace', R.backspaces], ['Time', Math.floor(R.minutes) + 'm ' + Math.round((R.minutes % 1) * 60) + 's']
    ];
    $('#eh-res-grid').innerHTML = cells.map(function (c) { return '<div class="eh-stat"><span>' + c[0] + '</span><b>' + c[1] + '</b></div>'; }).join('');
    $('#eh-res-diff').innerHTML = ops.map(function (o) {
      if (o.t === 'ok') return '<span class="d-ok">' + esc(o.s) + '</span>';
      if (o.t === 'om') return '<span class="d-om" title="छूटा शब्द">' + esc(o.s) + '</span>';
      if (o.t === 'add') return '<span class="d-add" title="फालतू शब्द">' + esc(o.w) + '</span>';
      if (o.t === 'half') return '<span class="d-half" title="Half mistake: ' + o.why + '">' + esc(o.w) + ' <s>' + esc(o.s) + '</s></span>';
      return '<span class="d-sub" title="Full mistake">' + esc(o.w) + ' <s>' + esc(o.s) + '</s></span>';
    }).join(' ');
    window.scrollTo({ top: result.offsetTop - 70, behavior: 'smooth' });
  }

  function save(R) {
    var nameEl = $('#eh-guest-name');
    if (!CFG.loggedIn && nameEl && nameEl.value.trim()) R.name = nameEl.value.trim();
    try { localStorage.setItem('eh_name', R.name || ''); } catch (e) {}
    fetch(CFG.rest + 'result', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': CFG.nonce }, body: JSON.stringify(R) })
      .then(function (r) { return r.json(); })
      .then(function (j) { var s = $('#eh-res-saved'); if (s) s.textContent = j && j.ok ? (j.rank ? 'रिज़ल्ट सेव हो गया · आज आपकी रैंक #' + j.rank : 'रिज़ल्ट सेव हो गया') : ''; })
      .catch(function () {});
  }

  /* ---------- wire up ---------- */
  fillSetup();
  try { var nm = localStorage.getItem('eh_name'); if (nm && $('#eh-guest-name')) $('#eh-guest-name').value = nm; } catch (e) {}
  $('#eh-start').addEventListener('click', startTest);
  var sb = $('#eh-submit');
  sb.addEventListener('click', function () {
    if (sb.dataset.arm === '1') { sb.dataset.arm = ''; sb.textContent = 'Submit'; finish(); return; }
    sb.dataset.arm = '1'; sb.textContent = 'पक्का? फिर दबाएं';
    setTimeout(function () { sb.dataset.arm = ''; sb.textContent = 'Submit'; }, 3000);
  });
  $('#eh-again').addEventListener('click', function () { result.hidden = true; setup.hidden = false; window.scrollTo({ top: setup.offsetTop - 70, behavior: 'smooth' }); });
  var input = $('#eh-input');
  input.addEventListener('keydown', onKey);
  input.addEventListener('paste', function (e) { e.preventDefault(); });
  input.addEventListener('drop', function (e) { e.preventDefault(); });
  input.addEventListener('input', function () { if (state.layout !== 'english') render(); });
  input.addEventListener('mousedown', function (e) { if (state.layout !== 'english') { e.preventDefault(); input.focus(); } });
  window.addEventListener('beforeunload', function (e) { if (state.started && !state.done) { e.preventDefault(); e.returnValue = ''; } });
})();
