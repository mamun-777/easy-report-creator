<?php
/** FB-003: 7-day trial + issued 1-year key + admin registry smoke test. */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/website/inc/bootstrap.php';
require_once $root . '/website/inc/config.php';

$failed = 0;
$ts = gmdate('YmdHis');
$email = "trial+{$ts}@example.com";
$password = "Trial-Ok9-{$ts}";

echo "Register {$email}\n";
$result = ErcAuth::register("Trial Co {$ts}", $email, $password, "Trial User");
$licence = $result['licence'] ?? ErcLicence::statusForCompany((int) $result['user']['company_id']);
echo "After register: status={$licence['status']} can_use=" . ($licence['can_use'] ? '1' : '0') . " days={$licence['days_remaining']}\n";
if (($licence['status'] ?? '') !== 'trial' || empty($licence['can_use'])) {
    echo "FAIL: expected active trial\n";
    $failed++;
}

$companyId = (int) $result['user']['company_id'];

$past = gmdate('c', time() - 86400);
$stmt = ErcAuth::db()->prepare('UPDATE companies SET trial_ends_at = ?, licence_status = ? WHERE id = ?');
$stmt->execute([$past, 'trial', $companyId]);
$expired = ErcLicence::statusForCompany($companyId);
echo "After expire: status={$expired['status']} can_use=" . ($expired['can_use'] ? '1' : '0') . "\n";
if (!empty($expired['can_use']) || ($expired['status'] ?? '') !== 'expired') {
    echo "FAIL: expected expired\n";
    $failed++;
}

try {
    ErcLicence::requireAccess();
    echo "FAIL: requireAccess should throw when expired\n";
    $failed++;
} catch (ErcLicenceException $e) {
    echo "OK requireAccess blocked\n";
}

try {
    ErcLicence::activate($companyId, 'ERC-AAAA-BBBB-CCCC');
    echo "FAIL: unissued key should reject\n";
    $failed++;
} catch (InvalidArgumentException $e) {
    echo "OK unissued key rejected\n";
}

$issued = ErcLicence::createKey([
    'customer_name' => 'Trial User',
    'customer_email' => $email,
    'company_name' => "Trial Co {$ts}",
    'validity_days' => 365,
    'source' => 'test',
]);
$key = (string) $issued['licence_key'];
echo "Issued key {$key}\n";

$active = ErcLicence::activate($companyId, $key);
echo "After activate: status={$active['status']} can_use=" . ($active['can_use'] ? '1' : '0') . " days={$active['days_remaining']}\n";
if (($active['status'] ?? '') !== 'active' || empty($active['can_use'])) {
    echo "FAIL: expected active licence\n";
    $failed++;
}
if ((int) ($active['days_remaining'] ?? 0) < 360) {
    echo "FAIL: 1-year licence should have ~365 days remaining\n";
    $failed++;
}

try {
    ErcLicence::activate($companyId, 'BAD-KEY');
    echo "FAIL: bad key should reject\n";
    $failed++;
} catch (InvalidArgumentException $e) {
    echo "OK bad key rejected\n";
}

$intent = ErcLicence::recordPurchaseIntent([
    'full_name' => 'Buyer Test',
    'email' => "buyer+{$ts}@example.com",
    'company_name' => 'Buyer Co',
    'vat_region' => 'nl21',
    'consent' => true,
]);
if (empty($intent['success']) || empty($intent['payment_url'])) {
    echo "FAIL: purchase intent\n";
    $failed++;
} else {
    echo "OK purchase intent → {$intent['payment_url']}\n";
}

$admin = ErcAdmin::login(
    (string) erc_licence_config()['admin_username'],
    (string) erc_licence_config()['admin_password']
);
echo "OK admin login as {$admin['username']}\n";
ErcAdmin::requireAdmin($admin['token']);
$stats = ErcLicence::dashboardStats();
echo "Stats keys_total={$stats['keys_total']} intents_pending={$stats['intents_pending']}\n";

echo $failed === 0 ? "FB-003 PASSED\n" : "FB-003 FAILED ({$failed})\n";
exit($failed === 0 ? 0 : 1);
