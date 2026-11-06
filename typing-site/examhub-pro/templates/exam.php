<?php get_header();
$e = eh_exam(get_query_var('eh_slug')); $att = eh_attempt_counts(); $board = eh_leaderboard($e['slug'], '', 10);
$rule = $e['unit'] === 'keys'
  ? 'इस परीक्षा में ' . (int) $e['minutes'] . ' मिनट में कम से कम ' . number_format_i18n($e['target']) . ' key depressions (KDPH पैटर्न) करनी होती हैं।'
  : 'इस परीक्षा में ' . (int) $e['minutes'] . ' मिनट में लगभग ' . number_format_i18n($e['target']) . ' शब्द टाइप करने का लक्ष्य रहता है।';
?>
<section class="eh-page-head"><div class="wrap eh-exam-head">
  <div>
    <p class="muted small"><a href="<?php echo esc_url(home_url('/exams/')); ?>">Exams</a> / <?php echo esc_html(eh_cat_label($e['cat'])); ?></p>
    <h1><?php echo esc_html($e['name']); ?></h1>
  </div>
  <a class="btn dark lg" href="<?php echo esc_url(home_url('/typing/' . $e['slug'] . '/')); ?>">Start Typing Test →</a>
</div></section>
<section class="wrap eh-section eh-two">
  <div>
    <h2>About</h2>
    <p><?php echo esc_html($rule); ?> अनुमत गलती सीमा <?php echo esc_html($e['allowed']); ?>% मानी गई है। Full mistake (गलत/छूटा/अतिरिक्त शब्द) और Half mistake (स्पेसिंग, कैपिटल, विराम-चिह्न) अलग-अलग गिने जाते हैं।<?php if (!empty($e['retype'])) echo ' इस परीक्षा में Retyping पैटर्न होता है — दिए गए पैसेज को ही दोबारा टाइप करना होता है।'; ?></p>
    <div class="eh-info">
      <div><span>Language</span><b><?php echo esc_html(eh_lang_label($e['lang'])); ?><?php echo $e['lang'] === 'hi' ? ' (Krutidev / Mangal)' : ''; ?></b></div>
      <div><span><?php echo $e['unit'] === 'keys' ? 'Keystrokes' : 'Words'; ?></span><b><?php echo esc_html(eh_target_label($e)); ?></b></div>
      <div><span>Time duration</span><b><?php echo (int) $e['minutes']; ?>:00 min</b></div>
      <div><span>Students using this exam</span><b><?php echo number_format_i18n($att[$e['slug']] ?? 0); ?> times</b></div>
    </div>
    <?php if (in_array($e['lang'], ['mr', 'or'], true)): ?><p class="eh-tip">ℹ️ इस भाषा के पैसेज जल्द जुड़ेंगे। तब तक English पैसेज पर अभ्यास होगा।</p><?php endif; ?>
    <a class="btn dark lg" href="<?php echo esc_url(home_url('/typing/' . $e['slug'] . '/')); ?>">Start Typing Test →</a>
  </div>
  <div>
    <h2>Top Results</h2>
    <div class="eh-table-wrap"><table class="eh-table"><thead><tr><th>#</th><th>Name</th><th>Net</th><th>Acc.</th><th></th></tr></thead><tbody>
    <?php if (!$board): ?><tr><td colspan="5" class="muted">अभी तक कोई रिज़ल्ट नहीं।</td></tr><?php endif; ?>
    <?php foreach ($board as $i => $r): ?><tr><td><?php echo $i + 1; ?></td><td><?php echo esc_html(eh_display_name($r)); ?></td><td><b><?php echo (int) $r['net']; ?></b></td><td><?php echo esc_html($r['acc']); ?>%</td><td><?php echo $r['pass'] ? '<span class="pill ok">Pass</span>' : '<span class="pill no">Fail</span>'; ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
</section>
<?php get_footer();
