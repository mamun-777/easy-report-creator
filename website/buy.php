<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/config.php';
$page = 'pricing';
$title = 'Buy licence — EasyReportCreator';
$description = 'Buy a 1-year EasyReportCreator licence. Secure Moneybird checkout — Dutch 21% VAT or non-Dutch 0% VAT.';
$cfg = erc_licence_config();
$price = (float) ($cfg['unit_price_eur'] ?? 59);
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <div class="wrap">
    <div class="eyebrow">Checkout</div>
    <h1>Buy your licence.</h1>
    <p class="sub">Pay via secure Moneybird checkout. Your licence key is issued after payment — activate it in the report app for 365 days. Prefer to try first? <a href="report/register.php">Start a free 7-day trial</a>.</p>
  </div>
</section>

<section class="tight">
  <div class="wrap buy-grid">
    <form id="buy-form" class="buy-form reveal" novalidate>
      <label class="field">
        <span>Full name <span class="req">*</span></span>
        <input id="full_name" name="fullName" autocomplete="name" required />
      </label>
      <label class="field">
        <span>Email address <span class="req">*</span></span>
        <input id="email" type="email" name="email" autocomplete="email" required />
        <span class="hint">Your licence key is sent to this address.</span>
      </label>
      <label class="field">
        <span>Company name</span>
        <input id="company_name" name="companyName" autocomplete="organization" />
      </label>
      <label class="field">
        <span>VAT number (EU businesses)</span>
        <input id="vat_number" name="vatNumber" placeholder="NL123456789B01" />
      </label>
      <div class="field">
        <span>Is your company located in the Netherlands?</span>
        <div class="choice-tabs" id="choice-country" role="group">
          <button type="button" class="choice-tab active" data-vat="nl21">Yes — 21% VAT</button>
          <button type="button" class="choice-tab" data-vat="non_nl0">No — 0% VAT</button>
        </div>
      </div>

      <button class="btn block" id="pay-button" type="submit">Continue to secure payment</button>
      <div class="pay-methods">
        <span>SECURE MONEYBIRD CHECKOUT</span>
        <span>iDEAL</span>
        <span>BANK TRANSFER</span>
      </div>
      <p id="buy-error" class="buy-error" hidden></p>
    </form>

    <aside class="buy-summary reveal">
      <div class="eyebrow">Order</div>
      <h2>1-year licence</h2>
      <dl>
        <div><dt>Product</dt><dd>EasyReportCreator</dd></div>
        <div><dt>Term</dt><dd>365 days from activation</dd></div>
        <div><dt>Price</dt><dd>€<?= h((string) (int) $price) ?> / year <span class="muted">(+ VAT if NL)</span></dd></div>
      </dl>
      <p class="muted">Plant 3D is not required — only your ProcessPower.dcf file.</p>
    </aside>
  </div>
</section>

<div id="consent-backdrop" class="consent-backdrop" hidden>
  <div class="consent-card" role="dialog" aria-modal="true" aria-labelledby="consent-title">
    <h2 id="consent-title">Confirm your details</h2>
    <p>We use this name and email for the Moneybird purchase and to deliver your licence key.</p>
    <p id="consent-summary" class="consent-summary"></p>
    <p id="consent-error" class="consent-error" hidden></p>
    <div class="consent-actions">
      <button type="button" class="btn ghost" id="consent-cancel">Cancel</button>
      <button type="button" class="btn" id="consent-confirm">Agree and continue</button>
    </div>
  </div>
</div>

<script>
(function () {
  let vatRegion = "nl21";
  let pending = null;
  const err = document.getElementById("buy-error");
  const consentErr = document.getElementById("consent-error");
  const backdrop = document.getElementById("consent-backdrop");
  const confirmBtn = document.getElementById("consent-confirm");

  function showBackdrop(show) {
    backdrop.hidden = !show;
    if (!show) {
      consentErr.hidden = true;
      consentErr.textContent = "";
    }
  }

  document.querySelectorAll("#choice-country .choice-tab").forEach((tab) => {
    tab.addEventListener("click", () => {
      vatRegion = tab.getAttribute("data-vat") || "nl21";
      document.querySelectorAll("#choice-country .choice-tab").forEach((other) => {
        other.classList.toggle("active", other === tab);
      });
    });
  });

  function readOrder() {
    return {
      full_name: document.getElementById("full_name").value.trim(),
      email: document.getElementById("email").value.trim(),
      company_name: document.getElementById("company_name").value.trim(),
      vat_number: document.getElementById("vat_number").value.trim(),
      vat_region: vatRegion,
      consent: true,
    };
  }

  document.getElementById("buy-form").addEventListener("submit", (e) => {
    e.preventDefault();
    err.hidden = true;
    const order = readOrder();
    if (!order.full_name || !order.email || !order.email.includes("@")) {
      err.textContent = "Enter your name and a valid email address.";
      err.hidden = false;
      return;
    }
    pending = order;
    document.getElementById("consent-summary").textContent =
      order.full_name + " · " + order.email + (order.company_name ? " · " + order.company_name : "");
    showBackdrop(true);
  });

  document.getElementById("consent-cancel").addEventListener("click", () => {
    showBackdrop(false);
    pending = null;
    document.getElementById("pay-button").disabled = false;
  });
  backdrop.addEventListener("click", (ev) => {
    if (ev.target === backdrop) {
      showBackdrop(false);
      pending = null;
      document.getElementById("pay-button").disabled = false;
    }
  });

  confirmBtn.addEventListener("click", async () => {
    if (!pending) {
      consentErr.textContent = "Missing details — close and try again.";
      consentErr.hidden = false;
      return;
    }
    consentErr.hidden = true;
    confirmBtn.disabled = true;
    confirmBtn.textContent = "Redirecting…";
    const payBtn = document.getElementById("pay-button");
    payBtn.disabled = true;
    try {
      const res = await fetch("admin/api.php?action=purchase_intent", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(pending),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || data.ok === false) {
        throw new Error(data.detail || "Could not start checkout");
      }
      if (!data.payment_url) {
        throw new Error("Payment link is not configured.");
      }
      location.href = data.payment_url;
    } catch (ex) {
      consentErr.textContent = ex.message || String(ex);
      consentErr.hidden = false;
      confirmBtn.disabled = false;
      confirmBtn.textContent = "Agree and continue";
      payBtn.disabled = false;
    }
  });
})();
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
