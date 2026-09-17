<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/bootstrap.php';
require_once dirname(__DIR__) . '/inc/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = (string) ($_GET['action'] ?? '');
$body = [];
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    $body = is_array($decoded) ? $decoded : $_POST;
}

function admin_token(): ?string
{
    $hdr = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '';
    if (is_string($hdr) && $hdr !== '') {
        return $hdr;
    }
    return isset($_SESSION['erc_admin_token']) ? (string) $_SESSION['erc_admin_token'] : null;
}

try {
    switch ($action) {
        case 'login':
            if ($method !== 'POST') {
                erc_error('POST required', 405);
            }
            $masterKey = trim((string) ($body['master_key'] ?? $body['api_key'] ?? ''));
            if ($masterKey !== '') {
                $result = ErcAdmin::loginMasterKey($masterKey);
            } else {
                $result = ErcAdmin::login(
                    (string) ($body['username'] ?? ''),
                    (string) ($body['password'] ?? '')
                );
            }
            $_SESSION['erc_admin_token'] = $result['token'];
            erc_json(['ok' => true] + $result);

        case 'logout':
            ErcAdmin::logout(admin_token());
            unset($_SESSION['erc_admin_token']);
            erc_json(['ok' => true]);

        case 'me':
            $admin = ErcAdmin::requireAdmin(admin_token());
            erc_json(['ok' => true, 'admin' => $admin]);

        case 'stats':
            ErcAdmin::requireAdmin(admin_token());
            erc_json(['ok' => true, 'stats' => ErcLicence::dashboardStats()]);

        case 'licenses':
            ErcAdmin::requireAdmin(admin_token());
            if ($method === 'GET') {
                erc_json(['ok' => true, 'licenses' => ErcLicence::listKeys()]);
            }
            if ($method === 'POST') {
                $created = ErcLicence::createKey([
                    'licence_key' => (string) ($body['licence_key'] ?? ''),
                    'customer_name' => (string) ($body['customer_name'] ?? 'Customer'),
                    'customer_email' => (string) ($body['customer_email'] ?? ''),
                    'company_name' => (string) ($body['company_name'] ?? ''),
                    'validity_days' => (int) ($body['validity_days'] ?? 365),
                    'notes' => (string) ($body['notes'] ?? ''),
                    'source' => 'admin',
                ]);
                $intentId = trim((string) ($body['intent_id'] ?? ''));
                if ($intentId !== '') {
                    ErcLicence::markIntentFulfilled($intentId, (string) $created['licence_key']);
                }
                erc_json(['ok' => true, 'license' => $created]);
            }
            erc_error('Method not allowed', 405);

        case 'revoke':
            ErcAdmin::requireAdmin(admin_token());
            if ($method !== 'POST') {
                erc_error('POST required', 405);
            }
            ErcLicence::revokeKey((string) ($body['licence_key'] ?? ''));
            erc_json(['ok' => true]);

        case 'intents':
            ErcAdmin::requireAdmin(admin_token());
            erc_json(['ok' => true, 'intents' => ErcLicence::listIntents()]);

        case 'fulfil_intent':
            ErcAdmin::requireAdmin(admin_token());
            if ($method !== 'POST') {
                erc_error('POST required', 405);
            }
            $created = ErcLicence::fulfilIntent((string) ($body['intent_id'] ?? ''));
            erc_json(['ok' => true, 'license' => $created]);

        case 'purchase_intent':
            // Public — used by buy.php before redirect to Moneybird
            if ($method !== 'POST') {
                erc_error('POST required', 405);
            }
            $result = ErcLicence::recordPurchaseIntent($body);
            if (empty($result['success'])) {
                erc_json(['ok' => false, 'detail' => $result['message'] ?? 'Could not start purchase'], 400);
            }
            erc_json(['ok' => true] + $result);

        default:
            erc_error('Unknown action', 404);
    }
} catch (InvalidArgumentException $e) {
    erc_json(['ok' => false, 'detail' => $e->getMessage()], 400);
} catch (RuntimeException $e) {
    $code = str_contains(strtolower($e->getMessage()), 'auth') ? 401 : 400;
    if (str_contains(strtolower($e->getMessage()), 'sign in') || str_contains(strtolower($e->getMessage()), 'authentication')) {
        $code = 401;
    }
    erc_json(['ok' => false, 'detail' => $e->getMessage()], $code);
} catch (Throwable $e) {
    erc_json(['ok' => false, 'detail' => 'Server error'], 500);
}
