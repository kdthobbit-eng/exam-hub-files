<?php
/* Left brand panel for /login/ and /signup/. Shows a Super TET mock note when the visitor came from the mock. */
$eh_rt = rawurldecode((string) ($_REQUEST['redirect_to'] ?? ''));
$eh_mock = strpos($eh_rt, 'super-tet-mock') !== false;
$eh_ic = function ($p) { return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="' . $p . '"/></svg>'; };
?>
<aside class="eh-au-side">
  <div class="eh-au-glow"></div>
  <?php if ($eh_mock): ?>
  <span class="eh-au-chip"><i></i>UP Super TET 2026 · Live Mock</span>
  <h2>Log in once, then take the <em>live mock</em>.</h2>
  <p>Your answers save on the server, so the paper resumes from the same question if the net drops.</p>
  <ul class="eh-au-perks">
    <li><?php echo $eh_ic('M12 8v4l3 2M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18z'); ?><div><b>120 questions · 120 minutes</b><span>TCS-style exam screen, Hindi + English</span></div></li>
    <li><?php echo $eh_ic('M4 19V9m6 10V5m6 14v-7m4 7H2'); ?><div><b>Rank and percentile</b><span>Live score graph after you submit</span></div></li>
    <li><?php echo $eh_ic('M12 3v12m0 0-4-4m4 4 4-4M4 19h16'); ?><div><b>PDF with solutions</b><span>One-click download with every explanation</span></div></li>
  </ul>
  <?php else: ?>
  <span class="eh-au-chip"><i></i>Alert Typing · XL Study</span>
  <h2>Your typing practice, <em>saved and ranked.</em></h2>
  <p>One free account for typing tests, live typing ranks and online mock tests.</p>
  <ul class="eh-au-perks">
    <li><?php echo $eh_ic('M4 6h16M4 12h16M4 18h10'); ?><div><b>Result history</b><span>Every test and every result in one place</span></div></li>
    <li><?php echo $eh_ic('M4 19V9m6 10V5m6 14v-7m4 7H2'); ?><div><b>Live Typing rank</b><span>A new passage every day</span></div></li>
    <li><?php echo $eh_ic('M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z'); ?><div><b>100% free</b><span>No plan, no card</span></div></li>
  </ul>
  <?php endif; ?>
  <p class="eh-au-trust"><b>60,000+</b> students already practise with us</p>
</aside>
