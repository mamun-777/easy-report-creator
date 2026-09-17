<?php
declare(strict_types=1);
$page = 'pricing';
$title = 'Payment — EasyReportCreator';
$description = 'Payment status for your EasyReportCreator licence.';
require __DIR__ . '/inc/header.php';
?>

<section>
  <div class="wrap" style="max-width:640px;padding:48px 0">
    <div class="result-card reveal">
      <div class="result-icon">✓</div>
      <h1>Thank you</h1>
      <p>If payment completed successfully, we will issue your <strong>1-year licence key</strong> to the email you used at checkout. Activate the key in the report app under Licence — access then runs for 365 days from activation.</p>
      <p class="muted">Plant 3D is not required — only your ProcessPower.dcf file.</p>
      <div class="download-actions" style="margin-top:22px">
        <a class="btn" href="report/login.php">Open report app</a>
        <a class="btn ghost" href="contact.php">Contact support</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
