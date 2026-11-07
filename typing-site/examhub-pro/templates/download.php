<?php
$lang = (($_GET['lang'] ?? 'en') === 'hi') ? 'hi' : 'en';
$all = eh_passages();
/* Print view: /download-passages/?print=ID&limit=N&unit=keys|words */
if (!empty($_GET['print'])) {
    $id = preg_replace('/[^a-z0-9]/i', '', $_GET['print']);
    $p = null; foreach ($all as $x) if ($x['id'] === $id) $p = $x;
    if (!$p) { wp_safe_redirect(home_url('/download-passages/')); exit; }
    $txt = $p['text']; $lim = max(0, (int) ($_GET['limit'] ?? 0));
    if ($lim) {
        if (($_GET['unit'] ?? 'keys') === 'words') $txt = implode(' ', array_slice(preg_split('/\s+/u', $txt), 0, $lim));
        elseif (mb_strlen($txt) > $lim) { $cut = mb_strrpos(mb_substr($txt, 0, $lim), ' '); $txt = mb_substr($txt, 0, $cut ?: $lim); }
    }
    $words = count(preg_split('/\s+/u', trim($txt))); ?>
<!doctype html><html lang="<?php echo $p['lang'] === 'hi' ? 'hi' : 'en'; ?>"><head><meta charset="utf-8"><title><?php echo esc_html($p['title'] . ' — ' . eh_opt('brand')); ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;600&display=swap">
<style>body{font:17px/1.9 "Times New Roman","Noto Sans Devanagari",serif;color:#000;max-width:780px;margin:30px auto;padding:0 20px}h1{font-size:22px;margin:0 0 4px}.m{font:13px Arial,sans-serif;color:#444;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:18px;display:flex;justify-content:space-between}p.t{text-align:justify}.f{font:12px Arial,sans-serif;color:#555;border-top:1px solid #999;margin-top:24px;padding-top:8px}@media print{.np{display:none}}.np{font:15px Arial;background:#111;color:#fff;border:0;padding:10px 18px;border-radius:8px;cursor:pointer}</style></head>
<body><button class="np" onclick="window.print()">⬇ Save PDF / Print</button>
<h1><?php echo esc_html($p['title']); ?></h1>
<div class="m"><span><?php echo $p['lang'] === 'hi' ? 'Hindi' : 'English'; ?> · <?php echo number_format_i18n(mb_strlen($txt)); ?> characters · <?php echo $words; ?> words · Level <?php echo esc_html($p['level']); ?></span><span><?php echo esc_html(wp_parse_url(home_url(), PHP_URL_HOST)); ?></span></div>
<p class="t"><?php echo esc_html($txt); ?></p>
<div class="f">Free typing practice passage — <?php echo esc_html(home_url('/')); ?> · Telegram: <?php echo esc_html(eh_opt('tg_channel')); ?></div>
<script>setTimeout(function(){window.print()},700)</script></body></html>
<?php exit; }
$days = eh_passage_days($lang, 7);
$special = array_values(array_filter($all, function ($p) use ($lang) { return !empty($p['special']) && $p['lang'] === $lang; }));
$data = [];
foreach ($days as $d) $data[$d['date']] = array_map(function ($p) { return ['id' => $p['id'], 't' => $p['title'], 'l' => $p['level'], 'c' => $p['chars'], 'w' => $p['words']]; }, $d['items']);
get_header(); ?>
<?php echo eh_member_nav('download'); ?>
<section class="wrap eh-section">
  <p class="muted small">Passages for typing exams</p>
  <h1>Free Typing Passage PDF Download</h1>
  <div class="eh-panel eh-dl">
    <p>Download special passages (court orders, office orders) or English and Hindi passages by date. <b>B</b> - Basic, <b>E</b> - Easy, <b>M</b> - Moderate, <b>H</b> - Hard</p>
    <?php if ($special): ?><details class="eh-day"><summary>★ Special Passages (<?php echo count($special); ?>)</summary>
      <?php foreach ($special as $p): ?><div class="eh-prow"><span></span><span class="lv lv-<?php echo esc_attr($p['level']); ?>"><?php echo esc_html($p['level']); ?></span><span class="t"><?php echo esc_html($p['title']); ?></span><a class="btn ghost sm" target="_blank" href="<?php echo esc_url(add_query_arg('print', $p['id'], home_url('/download-passages/'))); ?>">PDF ⬇</a></div><?php endforeach; ?>
    </details><p class="center muted">— or —</p><?php endif; ?>
    <form class="eh-dl-form" target="_blank" action="<?php echo esc_url(home_url('/download-passages/')); ?>">
      <label>Language<select id="dl-lang" onchange="location.href='?lang='+this.value"><option value="en">English</option><option value="hi" <?php selected($lang, 'hi'); ?>>Hindi</option></select></label>
      <label>Date<select id="dl-date"><?php foreach ($days as $i => $d) echo '<option value="' . esc_attr($d['date']) . '">' . esc_html(wp_date('d M Y', strtotime($d['date'] . ' 12:00'))) . ($i === 0 ? ' (today)' : '') . '</option>'; ?></select></label>
      <label>Passage<select id="dl-p" name="print"></select></label>
      <label>Download limit<span class="eh-lim"><input name="limit" type="number" min="0" step="50" placeholder="Full passage"><select name="unit"><option value="keys">Characters</option><option value="words">Words</option></select></span></label>
      <button class="btn dark lg">PDF Download ⬇</button>
    </form>
  </div>
</section>
<script>
(function(){var D=<?php echo wp_json_encode($data, JSON_UNESCAPED_UNICODE); ?>,d=document.getElementById('dl-date'),p=document.getElementById('dl-p');
function fill(){p.innerHTML=(D[d.value]||[]).map(function(x){return '<option value="'+x.id+'">['+x.l+'] '+x.t.replace(/</g,'&lt;')+' — '+x.c+' keys</option>'}).join('')}
d.addEventListener('change',fill);fill();})();
</script>
<?php get_footer();
