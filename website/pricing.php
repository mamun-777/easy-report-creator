<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
$page = 'pricing';
$title = 'Pricing — EasyReportCreator | easyreportcreator.com';
$description = 'EasyReportCreator: 7-day trial, then a 1-year licence. Plant 3D is not required; only ProcessPower.dcf.';
$cfg = erc_licence_config();
$price = (float) ($cfg['unit_price_eur'] ?? 59);
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <div class="wrap">
    <div class="eyebrow">Pricing</div>
    <h1>7-day trial. Then a 1-year licence.</h1>
    <p class="sub">Try the hosted report app free for seven days, then activate a one-year licence for ongoing use. Plant 3D is not required — only your <code>ProcessPower.dcf</code> file. A local app will follow later.</p>
  </div>
</section>

<section class="tight">
  <div class="wrap">
    <div class="plans reveal">
      <div class="plan featured">
        <div class="plan-name">7-day trial</div>
        <div class="plan-desc">Full report app access for your company — starts when you create an account.</div>
        <div class="plan-price-row">Try first</div>
        <div class="plan-price">7<span class="unit"> days</span></div>
        <div class="plan-sub">No card required to start</div>
        <ul class="plan-feats">
          <li>Upload ProcessPower.dcf in the browser</li>
          <li>Plant 3D not required — only the .dcf</li>
          <li>All core list types + Excel export</li>
          <li>Column picker + company templates</li>
          <li>Trial countdown in the app</li>
        </ul>
        <a href="report/register.php" class="btn block">Start free trial</a>
      </div>

      <div class="plan">
        <div class="plan-name">1-year licence</div>
        <div class="plan-desc">Annual licence for the hosted EasyReportCreator web app. One company account, full access for 12 months from activation.</div>
        <div class="plan-price-row">Annual</div>
        <div class="plan-price">€<?= h((string) (int) $price) ?><span class="unit"> / year</span></div>
        <div class="plan-sub">NL 21% VAT or 0% outside NL</div>
        <ul class="plan-feats">
          <li>Full access for 12 months</li>
          <li>Secure Moneybird checkout</li>
          <li>Activate with your licence key in the app</li>
          <li>Company account + saved profile</li>
          <li>Local / desktop app will follow later</li>
        </ul>
        <a href="buy.php" class="btn ghost block">Buy 1-year licence</a>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap prose">
    <div class="section-head reveal">
      <div class="eyebrow">Questions</div>
      <h2>How trial and licence work.</h2>
    </div>
    <div class="faq">
      <div class="faq-item">
        <h3>Do I need AutoCAD Plant 3D installed?</h3>
        <p><strong>No.</strong> EasyReportCreator only needs the <code>ProcessPower.dcf</code> file from the project. Plant 3D does not need to be installed or running.</p>
      </div>
      <div class="faq-item">
        <h3>How does payment work?</h3>
        <p>Buy online via secure Moneybird checkout (Dutch 21% VAT or non-Dutch 0% VAT). After payment you receive a licence key to activate in the report app.</p>
      </div>
      <div class="faq-item">
        <h3>When does the 1-year period start?</h3>
        <p>The licence runs for <strong>365 days from activation</strong> in the report app — not from the payment date alone.</p>
      </div>
      <div class="faq-item">
        <h3>Is there a local / desktop app?</h3>
        <p>Not yet — it will follow later. Today the product is the hosted web app on easyreportcreator.com.</p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
