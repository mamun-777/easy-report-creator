<?php
/** FB-003: 7-day trial + 1-year licence smoke test. */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/website/inc/bootstrap.php';

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
if ((int) ($licence['days_remaining'] ?? 0) < 1 || (int) $licence['days_remaining'] > 7) {
    echo "FAIL: days_remaining should be 1..7\n";
    $failed++;
}

$companyId = (int) $result['user']['company_id'];

// Expire trial
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
    echo "OK requireAccess blocked: {$e->getMessage()}\n";
}

// Activate 1-year licence
$active = ErcLicence::activate($companyId, 'ERC-ADMIN-GRANT-1YEAR');
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

// Login returns licence
ErcAuth::logout();
$login = ErcAuth::login($email, $password);
echo "Login licence status=" . ($login['licence']['status'] ?? '?') . "\n";
if (($login['licence']['status'] ?? '') !== 'active') {
    echo "FAIL: login should return active licence\n";
    $failed++;
}

echo $failed === 0 ? "FB-003 PASSED\n" : "FB-003 FAILED ({$failed})\n";
exit($failed === 0 ? 0 : 1);
