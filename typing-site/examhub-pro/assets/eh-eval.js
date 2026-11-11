/* Exam Hub — typing evaluation (shared by test window and saved result page) */
(function () {
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  function stripP(w) { return w.replace(/[.,;:!?'"‘’“”()\-।|/]/g, ''); }
  function lev(a, b) {
    if (a === b) return 0;
    var la = a.length, lb = b.length, prev = [], cur, i, j;
    for (j = 0; j <= lb; j++) prev[j] = j;
    for (i = 1; i <= la; i++) {
      cur = [i];
      for (j = 1; j <= lb; j++) cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
      prev = cur;
    }
    return prev[lb];
  }
  /* costs (x2): same 0 · case/punct 2 · close spelling 4 · different 6 · missing/extra word 4 · joined/split word 2 */
  function compare(src, typ) {
    var m = typ.length;
    var n = Math.min(src.length, m + Math.max(5, Math.ceil(m * 0.1)));
    var W = m + 1, D = new Uint32Array((n + 1) * W), i, j;
    var cost = function (x, y) {
      if (x === y) return 0;
      if (x.toLowerCase() === y.toLowerCase() || (stripP(x) && stripP(x) === stripP(y))) return 2;
      var mx = Math.max(x.length, y.length);
      return lev(x, y) <= Math.max(1, Math.floor(mx / 3)) ? 4 : 6;
    };
    for (i = 0; i <= n; i++) D[i * W] = i * 4;
    for (j = 0; j <= m; j++) D[j] = j * 4;
    for (i = 1; i <= n; i++) for (j = 1; j <= m; j++) {
      var v = D[(i - 1) * W + j - 1] + cost(src[i - 1], typ[j - 1]);
      var b2 = D[(i - 1) * W + j] + 4; if (b2 < v) v = b2;
      var d2 = D[i * W + j - 1] + 4; if (d2 < v) v = d2;
      if (i > 1 && typ[j - 1] === src[i - 2] + src[i - 1]) { var g = D[(i - 2) * W + j - 1] + 2; if (g < v) v = g; }
      if (j > 1 && typ[j - 2] + typ[j - 1] === src[i - 1]) { var h = D[(i - 1) * W + j - 2] + 2; if (h < v) v = h; }
      D[i * W + j] = v;
    }
    var best = 0;
    for (i = 0; i <= n; i++) if (D[i * W + m] <= D[best * W + m]) best = i;
    var ops = []; i = best; j = m;
    while (i > 0 || j > 0) {
      var here = D[i * W + j];
      if (i > 0 && j > 0 && here === D[(i - 1) * W + j - 1] + cost(src[i - 1], typ[j - 1])) { ops.push({ t: src[i - 1] === typ[j - 1] ? 'ok' : 'sub', s: src[i - 1], w: typ[j - 1] }); i--; j--; }
      else if (i > 1 && j > 0 && typ[j - 1] === src[i - 2] + src[i - 1] && here === D[(i - 2) * W + j - 1] + 2) { ops.push({ t: 'half', s: src[i - 2] + ' ' + src[i - 1], w: typ[j - 1], why: 'space' }); i -= 2; j--; }
      else if (j > 1 && i > 0 && typ[j - 2] + typ[j - 1] === src[i - 1] && here === D[(i - 1) * W + j - 2] + 2) { ops.push({ t: 'half', s: src[i - 1], w: typ[j - 2] + ' ' + typ[j - 1], why: 'space' }); i--; j -= 2; }
      else if (i > 0 && here === D[(i - 1) * W + j] + 4) { ops.push({ t: 'om', s: src[i - 1] }); i--; }
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
      /* transposition: two neighbouring words typed in the reverse order count as one half mistake */
      if (o.t === 'sub' && nx && nx.t === 'sub' && o.s === nx.w && o.w === nx.s) { out.push({ t: 'half', s: o.s + ' ' + nx.s, w: o.w + ' ' + nx.w, why: 'trans' }); k++; continue; }
      var n2x = ops[k + 2];
      if (o.t === 'om' && nx && nx.t === 'ok' && n2x && n2x.t === 'add' && n2x.w === o.s) { out.push({ t: 'half', s: o.s + ' ' + nx.s, w: nx.s + ' ' + n2x.w, why: 'trans' }); k += 2; continue; }
      if (o.t === 'add' && nx && nx.t === 'ok' && n2x && n2x.t === 'om' && n2x.s === o.w) { out.push({ t: 'half', s: nx.s + ' ' + n2x.s, w: o.w + ' ' + nx.s, why: 'trans' }); k += 2; continue; }
      if (o.t === 'sub') {
        if (o.s.toLowerCase() === o.w.toLowerCase()) { out.push({ t: 'half', s: o.s, w: o.w, why: 'case' }); continue; }
        if (stripP(o.s) && stripP(o.s) === stripP(o.w)) { out.push({ t: 'half', s: o.s, w: o.w, why: 'punct' }); continue; }
      }
      out.push(o);
    }
    return out;
  }


  var WHY = { space: 'Spacing error', case: 'Capital/small letter error', punct: 'Punctuation error', trans: 'Words in the wrong order' };
  function inline(ops) {
    return ops.map(function (o) {
      if (o.t === 'ok') return '<span class="d-ok">' + esc(o.s) + '</span>';
      if (o.t === 'om') return '<span class="d-om" title="Missed word">' + esc(o.s) + '</span>';
      if (o.t === 'add') return '<span class="d-add" title="Added words">' + esc(o.w) + '</span>';
      if (o.t === 'half') return '<span class="d-half" title="Half mistake: ' + (WHY[o.why] || '') + '">' + esc(o.w) + ' <s>' + esc(o.s) + '</s></span>';
      return '<span class="d-sub" title="Full mistake">' + esc(o.w) + ' <s>' + esc(o.s) + '</s></span>';
    }).join(' ');
  }
  function twoCol(ops) {
    var L = [], R = [];
    ops.forEach(function (o) {
      var cls = o.t === 'ok' ? 'd-ok' : o.t === 'half' ? 'd-half' : o.t === 'om' ? 'd-om' : o.t === 'add' ? 'd-add' : 'd-sub';
      if (o.t !== 'add') L.push('<span class="' + cls + '">' + esc(o.s) + '</span>');
      if (o.t !== 'om') R.push('<span class="' + cls + '">' + esc(o.w) + '</span>');
    });
    return '<div class="eh-cmp"><div><h4>Original Passage</h4><div class="eh-cmp-t">' + L.join(' ') + '</div></div><div><h4>What you typed</h4><div class="eh-cmp-t">' + R.join(' ') + '</div></div></div>';
  }
  function counts(ops) {
    var c = { full: 0, half: 0, om: 0, add: 0, sub: 0, space: 0, case: 0, punct: 0, trans: 0 };
    ops.forEach(function (o) {
      if (o.t === 'half') { c.half++; c[o.why] = (c[o.why] || 0) + 1; }
      else if (o.t !== 'ok') { c.full++; c[o.t]++; }
    });
    return c;
  }
  function mistakes(ops) {
    var m = [];
    ops.forEach(function (o) { if ((o.t === 'sub' || o.t === 'half') && m.length < 60) m.push([o.w, o.s]); });
    return m;
  }

  function fmtMin(m) { var s = Math.round(m * 60); return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2) + ' min'; }
  /* Pass or fail, by the exam's own rule.
     UPSSSC (X.eval === 'upsssc'): words = key depressions ÷ 5 (R.mode 'words' = real words typed, shown only as a second view); mistakes = full + half ÷ 2; the first 5 mistakes are free,
     each mistake after that cuts 5 words; speed = net words ÷ minutes; needs X.wpm.
     Others: key depressions (or words) must reach the target and error % must stay within the limit. */
  function verdict(R, X) {
    var allowed = X.allowed || 5;
    if (X.eval === 'upsssc' && X.wpm) {
      var errs = (+R.full || 0) + (+R.half || 0) / 2, byW = R.mode === 'words' || !(+R.keys), kw = byW ? (+R.words || 0) : (+R.keys) / 5, pen = Math.max(0, errs - 5) * 5, nw = Math.max(0, kw - pen);
      var m = Math.max(+R.minutes || 0, +X.minutes || 5), sp = nw / m;
      return { rule: 'upsssc', pass: sp >= X.wpm, wpm: sp, words: kw, errs: errs, pen: pen, netWords: nw, mins: m, need: +X.wpm, byWords: byW };
    }
    var amount = X.unit === 'keys' ? R.keys : R.words;
    return { rule: 'std', pass: amount >= X.target && R.err <= allowed, amount: amount };
  }
  function n2(v) { return (Math.round(v * 100) / 100).toString(); }
  /* R: net gross acc err keys kerr words full half backspaces minutes pass pending title date ; X: exam */
  function dashboard(R, X, ops) {
    if (!(X.eval === 'upsssc' && X.wpm)) return dashBody(R, X, ops);
    /* official result = key stroke formula (5 key strokes = 1 word). The switch only shows the word-count view. */
    var W = {}; for (var k in R) W[k] = R[k]; W.mode = 'words';
    return '<label class="eh-ksw"><input type="checkbox" checked> <span class="eh-ksw-t"></span> <b>Key Stroke Based Formula</b> <small>5 key strokes = 1 word · used for result and rank</small></label>' +
      '<div class="eh-kv" data-k="keys">' + dashBody(R, X, ops) + '</div><div class="eh-kv" data-k="words" hidden><p class="eh-ksw-note">Word-count view (only for comparison). Your result and rank use the key stroke formula.</p>' + dashBody(W, X, ops) + '</div>';
  }
  function dashBody(R, X, ops) {
    var V = verdict(R, X), up = V.rule === 'upsssc'; R.pass = V.pass ? 1 : 0;
    var c = counts(ops), allowed = X.allowed || 5, keysMode = X.unit === 'keys';
    var tgt = (+X.target).toLocaleString('en-IN') + (keysMode ? ' key depressions' : ' words');
    var errs = R.full + R.half / 2, mins = Math.max(R.minutes, 1 / 60);
    var head = '<div class="eh-dash-head"><div><p class="eh-kicker">Performance Dashboard</p><h2>' + esc(X.name) + '</h2>' +
      '<p class="muted">' + (R.title ? 'Passage: <b>' + esc(R.title) + '</b> · ' : '') + (R.date ? esc(R.date) + ' · ' : '') + (up ? 'Needed ' + V.need + ' WPM in ' + X.minutes + ' min · first 5 mistakes free, then 5 words cut for each mistake' : 'Target ' + tgt + ' in ' + X.minutes + ' min · Allowed error ' + allowed + '%') + '</p></div>' +
      '<div class="eh-verdict ' + (R.pass ? 'ok' : 'no') + '">' + (R.pass ? '✔ Qualified' : '✘ Not Qualified') + '</div></div>';
    var mm = function (m) { m = Math.round((+m || 0) * 60); return ('0' + Math.floor(m / 60)).slice(-2) + ':' + ('0' + m % 60).slice(-2) + ' min.'; };
    var info = '<div class="eh-dash-info">' + [['Exam Title', esc(X.name)], ['Passage Title', esc(R.title || '—')], ['Total Key Depression', R.total ? R.total : '—'], ['Time Duration', mm(R.dur || X.minutes)], ['Typing Date', esc(R.date || '—')], ['Time Taken', mm(R.minutes)]]
      .map(function (k) { return '<div><span>' + k[0] + ':</span> <b>' + k[1] + '</b></div>'; }).join('') + '</div>';
    head += info;
    var cards = [
      ['Net Speed', up ? n2(V.wpm) : R.net, up ? 'WPM / ' + V.need : 'WPM', up && !V.pass ? 'bad' : 'c1'], ['Gross Speed', up ? n2(V.words / V.mins) : R.gross, 'WPM', 'c2'], ['Accuracy', R.acc, '%', 'c3'], up ? ['Mistakes', n2(V.errs), '/ 5 free', V.errs > 5 ? 'bad' : 'c4'] : ['Error', R.err, '%', R.err > allowed ? 'bad' : 'c4'],
      ['Key Depressions', R.keys, keysMode && !up ? '/ ' + X.target : '', keysMode && !up && R.keys < X.target ? 'bad' : 'c5'], ['Full Mistakes', R.full, '', 'c6'], ['Half Mistakes', R.half, '', 'c7'], ['Time Taken', fmtMin(R.minutes), '', 'c8']
    ].map(function (k) { return '<div class="eh-dcard ' + k[3] + '"><span>' + k[0] + '</span><b>' + k[1] + ' <small>' + k[2] + '</small></b></div>'; }).join('');
    var mini = [['Words Typed', R.words], ['Keystroke Errors', R.kerr], ['Backspace', R.backspaces], ['Missed words', c.om], ['Added words', c.add], ['Wrong words', c.sub], ['Space/Case/Punct/Order', c.space + '/' + c.case + '/' + c.punct + '/' + c.trans], ['Pending Words', R.pending || 0]]
      .map(function (k) { return '<div><span>' + k[0] + '</span><b>' + k[1] + '</b></div>'; }).join('');
    var amount = keysMode ? R.keys : R.words;
    var fu = up ? '<div class="eh-formula"><h3>How your result was worked out (UPSSSC rule)</h3><ol>' +
      (V.byWords ? '<li><b>Words typed</b> = real words you typed = <b>' + n2(V.words) + '</b></li>' : '<li><b>Words typed</b> = key depressions ÷ 5 = ' + R.keys + ' ÷ 5 = <b>' + n2(V.words) + '</b></li>') +
      '<li><b>Mistakes</b> = Full ' + R.full + ' + Half ' + R.half + ' ÷ 2 = <b>' + n2(V.errs) + '</b> (the first 5 are free)</li>' +
      '<li><b>Penalty</b> = ' + (V.errs > 5 ? '(' + n2(V.errs) + ' − 5) × 5 = <b>' + n2(V.pen) + ' words</b>' : '<b>0 words</b>, because mistakes are 5 or fewer') + '</li>' +
      '<li><b>Net words</b> = ' + n2(V.words) + ' − ' + n2(V.pen) + ' = <b>' + n2(V.netWords) + '</b></li>' +
      '<li><b>Speed</b> = net words ÷ minutes = ' + n2(V.netWords) + ' ÷ ' + n2(V.mins) + ' = <b>' + n2(V.wpm) + ' WPM</b>, needed ' + V.need + ' → ' + (V.pass ? '✔ done' : '✘ short') + '</li>' +
      '</ol><p class="eh-verdict-line">Result: ' + (V.pass ? '<b class="ok">Qualified</b> — speed is ' + V.need + ' WPM or more after the penalty.' : '<b class="no">Not Qualified</b> — speed after the penalty is below ' + V.need + ' WPM.') + '</p></div>' : '';
    var f = '<div class="eh-formula"><h3>How your result was worked out</h3><ol>' +
      '<li><b>Gross Speed</b> = words typed ÷ minutes = ' + R.words + ' ÷ ' + mins.toFixed(2) + ' = <b>' + R.gross + ' WPM</b></li>' +
      '<li><b>Mistakes</b> = Full ' + R.full + ' + Half ' + R.half + ' ÷ 2 = <b>' + errs + '</b></li>' +
      '<li><b>Net Speed</b> = (words − mistakes) ÷ minutes = (' + R.words + ' − ' + errs + ') ÷ ' + mins.toFixed(2) + ' = <b>' + R.net + ' WPM</b></li>' +
      '<li><b>Error %</b> = mistakes ÷ words × 100 = ' + errs + ' ÷ ' + (R.words || 0) + ' × 100 = <b>' + R.err + '%</b> (allowed ' + allowed + '%)</li>' +
      '<li><b>' + (keysMode ? 'Key depressions' : 'Words') + '</b>: you typed ' + amount + ', needed ' + X.target + ' → ' + (amount >= X.target ? '✔ done' : '✘ short') + '</li>' +
      '</ol><p class="eh-verdict-line">Result: ' + (R.pass ? '<b class="ok">Qualified</b> — target met and errors within the limit.' : '<b class="no">Not Qualified</b> — ' + (amount < X.target ? 'target not met' : '') + (amount < X.target && R.err > allowed ? ' and ' : '') + (R.err > allowed ? 'error ' + allowed + '% limit crossed' : '') + '.') + '</p></div>';
    var legend = '<p class="eh-legend"><span class="d-ok">Correct</span> <span class="d-sub">Wrong word (Full)</span> <span class="d-om">Missed word (Full)</span> <span class="d-add">Added word (Full)</span> <span class="d-half">Half mistake</span></p>';
    var cmp = '<div class="eh-cmp-wrap"><div class="eh-cmp-tabs"><button type="button" class="on" data-v="two">Side by side</button><button type="button" data-v="one">Inline</button></div>' + legend +
      '<div class="eh-cmp-v" data-v="two">' + twoCol(ops) + '</div><div class="eh-cmp-v eh-diff" data-v="one" hidden>' + inline(ops) + '</div></div>';
    return head + '<div class="eh-dash-grid">' + cards + '</div><div class="eh-dash-mini">' + mini + '</div>' + (up ? fu : f) + '<h3 class="eh-cmp-title">Detailed Comparison</h3>' + cmp;
  }
  function bindDash(root) {
    var sw = root.querySelector('.eh-ksw input');
    if (sw) sw.addEventListener('change', function () { root.querySelectorAll('.eh-kv').forEach(function (v) { v.hidden = (v.getAttribute('data-k') === 'keys') !== sw.checked; }); });
    root.querySelectorAll('.eh-cmp-tabs button').forEach(function (b) {
      b.addEventListener('click', function () {
        root.querySelectorAll('.eh-cmp-tabs button').forEach(function (x) { x.classList.toggle('on', x === b); });
        root.querySelectorAll('.eh-cmp-v').forEach(function (v) { v.hidden = v.getAttribute('data-v') !== b.getAttribute('data-v'); });
      });
    });
  }
  window.EHEval = { verdict: verdict, dashboard: dashboard, bindDash: bindDash, lev: lev, compare: compare, stripP: stripP, inline: inline, twoCol: twoCol, counts: counts, mistakes: mistakes, esc: esc };
})();
