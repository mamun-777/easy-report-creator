<?php
declare(strict_types=1);
$page = 'download';
$title = 'Open the app — EasyReportCreator | easyreportcreator.com';
$description = 'Upload ProcessPower.dcf in the browser. Plant 3D is not required — only the .dcf file. Local app will follow later.';
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <div class="wrap">
    <div class="eyebrow">Report app</div>
    <h1>Upload ProcessPower.dcf — get Excel lists.</h1>
    <p class="sub"><strong>Plant 3D is not needed</strong> — only your <code>ProcessPower.dcf</code> file. Start a <strong>7-day trial</strong>, then a <strong>1-year licence</strong>. A local / desktop app will follow later.</p>
  </div>
</section>

<section class="tight">
  <div class="wrap">
    <div class="download-card reveal">
      <div class="download-card-main">
        <div class="eyebrow">Hosted web app</div>
        <h2>EasyReportCreator</h2>
        <p class="download-meta">
          <span>Version <?= h(SITE['product_version']) ?></span>
          <span>| Browser</span>
          <span>| Upload .dcf</span>
        </p>
        <p class="download-note">Open the report app, upload <code>ProcessPower.dcf</code> from your project folder, then preview and export. Typical file size is only a few MB. Plant 3D does not need to be installed or running.</p>
        <div class="download-actions">
          <a class="btn" href="report/register.php">Start 7-day trial</a>
          <a class="btn ghost" href="report/login.php">Open report app</a>
          <a href="pricing.php" class="btn ghost">Pricing</a>
        </div>
      </div>
      <div class="download-card-side">
        <div class="key">STEPS</div>
        <ul>
          <li>Open the report app on this site</li>
          <li>Upload ProcessPower.dcf</li>
          <li>Choose a list → preview rows</li>
          <li>Export English Excel</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Hosting</div>
      <h2>Browser upload today. Local app later.</h2>
      <p>V1 uses upload so everything works in the browser with only the .dcf file. A local / no-upload desktop option will follow later when ready.</p>
    </div>
    <div class="spec-table reveal">
      <div class="spec-row"><span class="k">This website</span><span class="v">Product pages + report app on easyreportcreator.com</span></div>
      <div class="spec-row"><span class="k">Input</span><span class="v">ProcessPower.dcf only — Plant 3D not required</span></div>
      <div class="spec-row"><span class="k">Sample</span><span class="v">MN-P-RHN-PID-0001 (sample project)</span></div>
      <div class="spec-row"><span class="k">Local app</span><span class="v">Will follow later — not available yet</span></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
