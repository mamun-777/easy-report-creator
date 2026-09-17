<?php
declare(strict_types=1);
$page = 'terms';
$title = 'Terms & delivery — EasyReportCreator | easyreportcreator.com';
$description = 'Delivery scope for EasyReportCreator Version 1: web application, English Excel, sample project acceptance, one feedback round.';
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <div class="wrap">
    <div class="eyebrow">Terms</div>
    <h1>What Version 1 delivers.</h1>
    <p class="sub">Short delivery and licence terms for the hosted EasyReportCreator web app.</p>
  </div>
</section>

<section class="tight">
  <div class="wrap prose">
    <h2>Scope</h2>
    <p>EasyReportCreator is a hosted web application that reads a <strong>ProcessPower.dcf</strong> file and issues English Excel lists. <strong>Plant 3D is not required</strong> — only the .dcf file. Acceptance uses the sample project MN-P-RHN-PID-0001. The public site at easyreportcreator.com includes product pages and the report app.</p>

    <h2>Trial and licence</h2>
    <p>New companies receive a <strong>7-day trial</strong>. Ongoing use requires a <strong>1-year licence</strong>, paid via Moneybird and activated with a licence key (365 days from activation).</p>

    <h2>What is not included yet</h2>
    <p>A local / desktop installer will follow later. Live write-back into ProcessPower.dcf, SSO / multi-tenant enterprise hosting of your project files, and full 3D isometric lists are optional later packages.</p>

    <h2>Support</h2>
    <p>Email <?= h(SITE['support_email']) ?>. Extra UAT rounds beyond agreed feedback are a change request.</p>

    <h2>Trademarks</h2>
    <p>AutoCAD, Plant 3D and Inventor are trademarks of Autodesk, Inc. EasyReportCreator is not affiliated with Autodesk.</p>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
