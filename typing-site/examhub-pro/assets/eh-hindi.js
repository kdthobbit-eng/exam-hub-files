/* Exam Hub Typing — Hindi keyboard layouts: Inscript (Mangal) + Remington GAIL (Krutidev) */
(function (root) {
  'use strict';

  var US = {
    Backquote: ['`', '~'], Digit1: ['1', '!'], Digit2: ['2', '@'], Digit3: ['3', '#'], Digit4: ['4', '$'],
    Digit5: ['5', '%'], Digit6: ['6', '^'], Digit7: ['7', '&'], Digit8: ['8', '*'], Digit9: ['9', '('],
    Digit0: ['0', ')'], Minus: ['-', '_'], Equal: ['=', '+'],
    BracketLeft: ['[', '{'], BracketRight: [']', '}'], Backslash: ['\\', '|'],
    Semicolon: [';', ':'], Quote: ["'", '"'], Comma: [',', '<'], Period: ['.', '>'], Slash: ['/', '?'],
    Space: [' ', ' ']
  };
  'abcdefghijklmnopqrstuvwxyz'.split('').forEach(function (c) { US['Key' + c.toUpperCase()] = [c, c.toUpperCase()]; });

  var INSCRIPT = {
    Backquote: ['ॊ', 'ऒ'], Digit1: ['1', 'ऍ'], Digit2: ['2', 'ॅ'], Digit3: ['3', '्र'], Digit4: ['4', 'र्'],
    Digit5: ['5', 'ज्ञ'], Digit6: ['6', 'त्र'], Digit7: ['7', 'क्ष'], Digit8: ['8', 'श्र'], Digit9: ['9', '('],
    Digit0: ['0', ')'], Minus: ['-', 'ः'], Equal: ['ृ', 'ऋ'],
    KeyQ: ['ौ', 'औ'], KeyW: ['ै', 'ऐ'], KeyE: ['ा', 'आ'], KeyR: ['ी', 'ई'], KeyT: ['ू', 'ऊ'],
    KeyY: ['ब', 'भ'], KeyU: ['ह', 'ङ'], KeyI: ['ग', 'घ'], KeyO: ['द', 'ध'], KeyP: ['ज', 'झ'],
    BracketLeft: ['ड', 'ढ'], BracketRight: ['़', 'ञ'], Backslash: ['ॉ', 'ऑ'],
    KeyA: ['ो', 'ओ'], KeyS: ['े', 'ए'], KeyD: ['्', 'अ'], KeyF: ['ि', 'इ'], KeyG: ['ु', 'उ'],
    KeyH: ['प', 'फ'], KeyJ: ['र', 'ऱ'], KeyK: ['क', 'ख'], KeyL: ['त', 'थ'], Semicolon: ['च', 'छ'], Quote: ['ट', 'ठ'],
    KeyZ: ['ॆ', 'ऎ'], KeyX: ['ं', 'ँ'], KeyC: ['म', 'ण'], KeyV: ['न', 'ऩ'], KeyB: ['व', 'ऴ'],
    KeyN: ['ल', 'ळ'], KeyM: ['स', 'श'], Comma: [',', 'ष'], Period: ['.', '।'], Slash: ['य', 'य़'],
    Space: [' ', ' ']
  };

  var REPH = '';
  var KD = {
    'q': 'ु', 'w': 'ू', 'e': 'म', 'r': 'त', 't': 'ज', 'y': 'ल', 'u': 'न', 'i': 'प', 'o': 'व', 'p': 'च',
    '[': 'ख्', ']': ',', '\\': '?',
    'a': 'ं', 's': 'े', 'd': 'क', 'f': 'ि', 'g': 'ह', 'h': 'ी', 'j': 'र', 'k': 'ा', 'l': 'स', ';': 'य', "'": 'श्',
    'z': '्र', 'x': 'ग', 'c': 'ब', 'v': 'अ', 'b': 'इ', 'n': 'द', 'm': 'उ', ',': 'ए', '.': 'ण्', '/': 'ध्',
    'Q': 'फ', 'W': 'ॅ', 'E': 'म्', 'R': 'त्', 'T': 'ज्', 'Y': 'ल्', 'U': 'न्', 'I': 'प्', 'O': 'व्', 'P': 'च्',
    '{': 'क्ष्', '}': 'द्व', '|': 'द्य',
    'A': '।', 'S': 'ै', 'D': 'क्', 'F': 'थ्', 'G': 'ळ', 'H': 'भ्', 'J': 'श्र', 'K': 'ज्ञ', 'L': 'स्', ':': 'रू', '"': 'ष्',
    'Z': REPH, 'X': 'ग्', 'C': 'ब्', 'V': 'ट', 'B': 'ठ', 'N': 'छ', 'M': 'ड', '<': 'ढ', '>': 'झ', '?': 'घ्',
    '`': 'ृ', '~': '्', '!': '!', '@': '/', '#': 'रु', '$': '+', '%': 'ः', '^': '‘', '&': '-', '*': '’', '(': ';',
    ')': 'द्ध', '-': '.', '_': 'ऋ', '=': 'त्र', '+': '़', 'Å': 'ऊ', '¡': 'ँ', '¼': '(', '½': ')',
    /* Alt-code letters of Krutidev 010 */
    'Ø': 'क्र', 'Ý': 'फ्र', 'æ': 'द्र', 'ç': 'प्र', 'Á': 'प्र', 'ê': 'ट्ट', 'ë': 'ट्ठ', 'ì': 'ड्ड', 'ï': 'ड्ढ', 'ô': 'क्क', 'Ä': 'घ', '³': 'ङ', '÷': 'झ्', '¥': 'ञ', 'Ë': 'ध्', 'è': 'ध्', 'à': 'ह्न', 'á': 'ह्य', 'â': 'हृ', 'ã': 'ह्म', 'º': 'ह्', 'í': 'द्द', 'Ì': 'द्द', 'Í': 'ट्ट', 'Î': 'ट्ठ', 'Ï': 'ड्ड', 'Ñ': 'कृ', 'Ò': 'भ', 'Ó': '्य', 'Ô': 'ड्ढ', 'Ö': 'झ्', 'Ü': 'श्', '¶': 'फ्', '¸': 'य्', 'È': 'ीं', 'Ã': 'ई', 'Þ': '“', 'ß': '”', '¿': '{', 'À': '}', '¾': '=', 'Œ': '॰', 'ñ': '॰', '•': 'ऽ', '·': 'ऽ', 'ª': '्र', 'î': '्य', 'Ù': 'त्त्', 'é': 'न्न', '™': 'न्न्', '–': 'दृ', '—': 'कृ', 'å': '०', 'ƒ': '१', '„': '२', '…': '३', '†': '४', '‡': '५', 'ˆ': '६', '‰': '७', 'Š': '८', '‹': '९', '‚': 'ॉ',
    '±': REPH + 'ं',
    '1': '1', '2': '2', '3': '3', '4': '4', '5': '5', '6': '6', '7': '7', '8': '8', '9': '9', '0': '0'
  };

  var CONS = '[\\u0915-\\u0939\\u0958-\\u095F]\\u093C?';
  var RE_I = new RegExp('\\u093F((?:' + CONS + '\\u094D)*' + CONS + '(?:\\u094D\\u0930)?)', 'g');
  var RE_REPH = new RegExp('((?:' + CONS + '\\u094D)*' + CONS + '[\\u093E-\\u094C\\u0962\\u0963]*[\\u0901\\u0902]?)' + REPH, 'g');
  var RE_REPH_V = new RegExp('([\\u0905-\\u0914][\\u0901\\u0902]?)' + REPH, 'g');

  function kdToUnicode(ascii) {
    var o = '';
    for (var i = 0; i < ascii.length; i++) {
      var ch = ascii.charAt(i);
      o += Object.prototype.hasOwnProperty.call(KD, ch) ? KD[ch] : ch;
    }
    o = o.replace(/्ा/g, '')
      .replace(/अा/g, 'आ').replace(/आे/g, 'ओ').replace(/आै/g, 'औ')
      .replace(/एे/g, 'ऐ').replace(/ाे/g, 'ो').replace(/ाै/g, 'ौ')
      .replace(new RegExp('इ' + REPH, 'g'), 'ई')
      .replace(/उु/g, 'ऊ').replace(/उू/g, 'ऊ').replace(/ाॅ/g, 'ॉ');
    o = o.replace(RE_I, '$1ि');
    o = o.replace(RE_REPH, 'र्$1').replace(RE_REPH_V, 'र्$1');
    o = o.split(REPH).join('');
    return norm(o);
  }

  function norm(s) { return s && s.normalize ? s.normalize('NFC') : s; }

  function label(layout, code, shift) {
    var u = US[code];
    if (!u) return '';
    var k = shift ? 1 : 0;
    if (layout === 'inscript') return (INSCRIPT[code] || u)[k];
    if (layout === 'remington') {
      var a = u[k];
      if (a === 'Z') return 'र्';
      return Object.prototype.hasOwnProperty.call(KD, a) ? KD[a] : a;
    }
    return u[k];
  }

  root.EHHindi = { US: US, INSCRIPT: INSCRIPT, KD: KD, kdToUnicode: kdToUnicode, norm: norm, label: label };
})(typeof window !== 'undefined' ? window : globalThis);

/* Unicode -> Krutidev 010 (keyboard-typable codes) */
(function (root) {
  'use strict';
  var H = root.EHHindi;
  var R = '';
  // longest first
  var MAP = [
    ['ड़', 'M+'], ['ढ़', '<+'], ['क्ष्', '{'], ['क्ष', '{k'], ['त्र्', '=~'], ['त्र', '='], ['ज्ञ', 'K'], ['श्र', 'J'],
    ['द्ध', ')'], ['द्व', '}'], ['द्य', '|'], ['रु', '#'], ['रू', ':'],
    ['क्', 'D'], ['ख्', '['], ['ग्', 'X'], ['घ्', '?'], ['च्', 'P'], ['ज्', 'T'], ['ण्', '.'], ['त्', 'R'], ['थ्', 'F'],
    ['ध्', '/'], ['न्', 'U'], ['प्', 'I'], ['ब्', 'C'], ['भ्', 'H'], ['म्', 'E'], ['ल्', 'Y'], ['व्', 'O'], ['श्', "'"],
    ['ष्', '"'], ['स्', 'L'], ['य्', ';~'],
    ['्र', 'z'],
    ['क', 'd'], ['ख', '[k'], ['ग', 'x'], ['घ', '?k'], ['ङ', 'M+'], ['च', 'p'], ['छ', 'N'], ['ज', 't'], ['झ', '>'], ['ञ', '¥'],
    ['ट', 'V'], ['ठ', 'B'], ['ड', 'M'], ['ढ', '<'], ['ण', '.k'], ['त', 'r'], ['थ', 'Fk'], ['द', 'n'], ['ध', '/k'], ['न', 'u'],
    ['प', 'i'], ['फ', 'Q'], ['ब', 'c'], ['भ', 'Hk'], ['म', 'e'], ['य', ';'], ['र', 'j'], ['ल', 'y'], ['व', 'o'], ['श', "'k"],
    ['ष', '"k'], ['स', 'l'], ['ह', 'g'], ['ळ', 'G'],
    ['औ', 'vkS'], ['ओ', 'vks'], ['आ', 'vk'], ['अ', 'v'], ['ई', 'bZ'], ['इ', 'b'], ['ऊ', 'Å'], ['उ', 'm'], ['ऐ', ',s'], ['ए', ','], ['ऋ', '_'],
    ['ॉ', 'kW'], ['ो', 'ks'], ['ौ', 'kS'], ['ा', 'k'], ['ी', 'h'], ['ु', 'q'], ['ू', 'w'], ['ृ', '`'], ['े', 's'], ['ै', 'S'],
    ['ं', 'a'], ['ँ', '¡'], ['ः', '%'], ['ॅ', 'W'], ['़', '+'], ['्', '~'], ['।', 'A'],
    [',', ']'], ['.', '-'], ['-', '&'], ['?', '\\'], ['(', '¼'], [')', '½'], [';', '('], ['/', '@'], ['‘', '^'], ['’', '*'], ['+', '$']
  ];
  var CONS = '[\\u0915-\\u0939]\\u093C?\\uE002?';
  var CL = '(?:' + CONS + '\\u094D)*' + CONS;
  var RE_REPH = new RegExp('\\u0930\\u094D(' + CL + '[\\u093E-\\u094C\\u0962\\u0963]*)', 'g');
  var RE_I = new RegExp('(' + CL + ')\\u093F', 'g');
  function unicodeToKD(s) {
    s = H.norm(s).replace(/([\u0915-\u0939])\u093C/g, function (m, c) { return (c === '\u0921' || c === '\u0922') ? m : c; });
    s = s.replace(/([\u0915-\u0939]\u093C?)\u094D\u0930/g, '$1\uE002');
    s = s.replace(RE_REPH, '$1' + R);
    s = s.replace(RE_I, 'ि$1');
    var out = '', i = 0;
    while (i < s.length) {
      var hit = null;
      for (var k = 0; k < MAP.length; k++) { var u = MAP[k][0]; if (s.substr(i, u.length) === u) { hit = MAP[k]; break; } }
      if (s.charAt(i) === 'ि') { out += 'f'; i++; continue; }
      if (s.charAt(i) === R) { out += 'Z'; i++; continue; }
      if (s.charAt(i) === '\uE002') { out += 'z'; i++; continue; }
      if (hit) { out += hit[1]; i += hit[0].length; } else { out += s.charAt(i); i++; }
    }
    // reph must sit before anusvara: ...Za not ...aZ
    return out.replace(/([a¡])Z/g, 'Z$1');
  }
  H.unicodeToKD = unicodeToKD;
})(typeof window !== 'undefined' ? window : globalThis);
