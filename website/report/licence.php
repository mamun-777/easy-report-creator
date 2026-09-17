<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/bootstrap.php';
$user = ErcAuth::currentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}
$licence = ErcLicence::statusForCompany((int) $user['company_id']);
$companyName = htmlspecialchars($user['company_name'], ENT_QUOTES, 'UTF-8');
$status = (string) ($licence['status'] ?? 'unknown');
$isActive = $status === 'active' && !empty($licence['can_use']);
$isTrial = $status === 'trial' && !empty($licence['can_use']);
$canUse = !empty($licence['can_use']);
$days = isset($licence['days_remaining']) ? (int) $licence['days_remaining'] : null;
$endsAt = (string) ($licence['licence_ends_at'] ?? $licence['trial_ends_at'] ?? '');
$endsLabel = '';
if ($endsAt !== '') {
    $ts = strtotime($endsAt);
    if ($ts !== false) {
        $endsLabel = gmdate('j M Y', $ts) . ' UTC';
    }
}
$message = htmlspecialchars(
    (string) ($licence['message'] ?? ''),
    ENT_QUOTES,
    'UTF-8'
);
$label = htmlspecialchars((string) ($licence['label'] ?? 'Licence'), ENT_QUOTES, 'UTF-8');
if ($isActive) {
    $heading = 'Your licence';
} elseif ($isTrial) {
    $heading = 'Activate your 1-year licence';
} else {
    $heading = 'Continue with a 1-year licence';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="color-scheme" content="light" />
  <title>Licence — EasyReportCreator</title>
  <link rel="stylesheet" href="../assets/fonts.css?v=20260908c" />
  <link rel="stylesheet" href="assets/report.css?v=20260917a" />
</head>
<body class="auth-body">
  <main class="licence-panel panel">
    <div class="panel-bar"><span class="key"><?= $label ?></span><span class="file"><?= $companyName ?></span></div>
    <div class="licence-body">
      <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="muted"><?= $message !== '' ? $message : 'Licence status for your company account.' ?></p>

      <dl class="licence-status">
        <div>
          <dt>Status</dt>
          <dd><?= $label ?></dd>
        </div>
        <?php if ($days !== null): ?>
        <div>
          <dt>Days remaining</dt>
          <dd><?= (int) $days ?></dd>
        </div>
        <?php endif; ?>
        <?php if ($endsLabel !== ''): ?>
        <div>
          <dt><?= $isActive ? 'Licence ends' : ($isTrial ? 'Trial ends' : 'Ended') ?></dt>
          <dd><?= htmlspecialchars($endsLabel, ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
        <?php endif; ?>
      </dl>

      <?php if (!$isActive): ?>
      <p class="muted">A <strong>7-day trial</strong>, then a <strong>1-year licence</strong> (365 days from activation). Need a key? <a href="../buy.php">Buy online</a>.</p>
      <label class="field">
        <span>Licence key</span>
        <input id="licence-key" type="text" placeholder="ERC-XXXX-XXXX-XXXX" autocomplete="off" spellcheck="false" maxlength="32" />
      </label>
      <p id="licence-error" class="licence-error" hidden></p>
      <p id="licence-ok" class="licence-ok" hidden>Licence activated. Opening the report app…</p>
      <div class="licence-actions">
        <button id="btn-activate" class="btn primary" type="button">Activate 1-year licence</button>
        <?php if ($canUse): ?>
        <a class="btn ghost" href="index.php">Back to report app</a>
        <?php else: ?>
        <button id="btn-logout" class="btn ghost" type="button">Log out</button>
        <?php endif; ?>
      </div>
      <p class="side-foot muted">Need a key? <a href="../buy.php">Buy a 1-year licence</a> or contact <a href="mailto:support@tspd.nl">support@tspd.nl</a>.</p>
      <?php else: ?>
      <div class="licence-actions">
        <a class="btn primary" href="index.php">Back to report app</a>
        <button id="btn-logout" class="btn ghost" type="button">Log out</button>
      </div>
      <p class="side-foot muted">To renew early, contact <a href="mailto:support@tspd.nl">support@tspd.nl</a>.</p>
      <?php endif; ?>
    </div>
  </main>
  <script>
    async function api(action, options = {}) {
      const res = await fetch(`api.php?action=${encodeURIComponent(action)}`, options.fetch || undefined);
      const data = await res.json().catch(() => ({}));
      if (!res.ok || data.ok === false) throw new Error(data.detail || res.statusText || "Request failed");
      return data;
    }
    function normalizeKey(raw) {
      const cleaned = String(raw || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
      if (cleaned.startsWith("ERCADMINGRANT1YEAR") || cleaned === "ERCADMINGRANT1YEAR") {
        return "ERC-ADMIN-GRANT-1YEAR";
      }
      if (!cleaned.startsWith("ERC") || cleaned.length < 15) return String(raw || "").trim().toUpperCase();
      const body = cleaned.slice(3);
      const parts = [];
      for (let i = 0; i < body.length && parts.length < 3; i += 4) {
        parts.push(body.slice(i, i + 4));
      }
      return "ERC-" + parts.join("-");
    }
    async function activate() {
      const err = document.getElementById("licence-error");
      const ok = document.getElementById("licence-ok");
      const keyInput = document.getElementById("licence-key");
      const btn = document.getElementById("btn-activate");
      if (!keyInput || !btn) return;
      err.hidden = true;
      if (ok) ok.hidden = true;
      keyInput.value = normalizeKey(keyInput.value);
      btn.disabled = true;
      try {
        await api("activate_licence", {
          fetch: {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ licence_key: keyInput.value }),
          },
        });
        if (ok) ok.hidden = false;
        setTimeout(() => { location.href = "index.php"; }, 700);
      } catch (e) {
        err.textContent = e.message || String(e);
        err.hidden = false;
        btn.disabled = false;
      }
    }
    document.getElementById("btn-activate")?.addEventListener("click", activate);
    document.getElementById("licence-key")?.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        activate();
      }
    });
    document.getElementById("btn-logout")?.addEventListener("click", async () => {
      try { await api("logout", { fetch: { method: "POST", headers: { "Content-Type": "application/json" }, body: "{}" } }); } catch (_) {}
      location.href = "login.php";
    });
  </script>
</body>
</html>
