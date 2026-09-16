<?php
declare(strict_types=1);
$page = 'pricing';
$title = 'Pricing — EasyReportCreator | easyreportcreator.com';
$description = 'EasyReportCreator: 7-day trial, then a 1-year licence — same commercial model as PropertiesManager. Hosted web app for AutoCAD Plant 3D lists.';
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <div class="wrap">
    <div class="eyebrow">Pricing</div>
    <h1>7-day trial. Then a 1-year licence.</h1>
    <p class="sub">Same model as PropertiesManager: try the hosted report app free for seven days, then activate a one-year licence for ongoing use. Desktop installer stays deferred for now.</p>
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
          <li>All core Plant 3D list types</li>
          <li>Column picker + company templates</li>
          <li>English Excel export with header &amp; logo</li>
          <li>Trial countdown in the app</li>
        </ul>
        <a href="report/register.php" class="btn block">Start free trial</a>
      </div>

      <div class="plan">
        <div class="plan-name">1-year licence</div>
        <div class="plan-desc">Annual licence / use for the hosted EasyReportCreator web app (PropertiesManager-aligned).</div>
        <div class="plan-price-row">Annual</div>
        <div class="plan-price custom">On request</div>
        <div class="plan-sub">Activate with a licence key</div>
        <ul class="plan-feats">
          <li>Full access for 12 months</li>
          <li>Key activation in the report app</li>
          <li>Company account + saved profile</li>
          <li>Hosted on easyreportcreator.com (STRATO)</li>
          <li>Renew before expiry to stay uninterrupted</li>
        </ul>
        <a href="contact.php" class="btn ghost block">Ask for a licence key</a>
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
        <h3>Is this the same model as PropertiesManager?</h3>
        <p>Yes. EasyReportCreator uses a <strong>7-day trial</strong> and then a <strong>1-year licence / use</strong>, matching PropertiesManager’s commercial shape. Pricing for the annual key is arranged with TSPD.</p>
      </div>
      <div class="faq-item">
        <h3>What happens when the trial ends?</h3>
        <p>The report app shows a clear expired screen. Upload, lists, and Excel export stay locked until you activate a 1-year licence key. You can still log in and enter a key.</p>
      </div>
      <div class="faq-item">
        <h3>Is there a desktop installer?</h3>
        <p>Not yet. A desktop / no-upload option is deferred. The product today is the hosted web app on STRATO.</p>
      </div>
      <div class="faq-item">
        <h3>Where do I get a licence key?</h3>
        <p>Contact <a href="mailto:support@tspd.nl">support@tspd.nl</a> or use the contact form. Keys activate in the report app under Licence.</p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
