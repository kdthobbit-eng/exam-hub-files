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
    ')': 'द्ध', '-': '.', '_': 'ऋ', '=': 'त्र', '+': '़',
    '1': '1', '2': '2', '3': '3', '4': '4', '5': '5', '6': '6', '7': '7', '8': '8', '9': '9', '0': '0'
  };

  var CONS = '[\\u0915-\\u0939\\u0958-\\u095F]\\u093C?';
  var RE_I = new RegExp('\\u093F((?:' + CONS + '\\u094D)*' + CONS + ')', 'g');
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
      .replace(/उु/g, 'ऊ');
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
