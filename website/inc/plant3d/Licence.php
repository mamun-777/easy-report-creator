<?php
declare(strict_types=1);

/**
 * Company trial (7 days) + 1-year licence keys (FB-003).
 * Commercial shape: trial → Moneybird payment → issued key → 365 days from activation.
 */
final class ErcLicence
{
    public const TRIAL_DAYS = 7;
    public const LICENCE_YEARS = 1;
    public const DEFAULT_VALIDITY_DAYS = 365;

    private static bool $migrated = false;

    public static function migrate(): void
    {
        if (self::$migrated) {
            return;
        }
        $pdo = ErcAuth::db();
        $cols = [];
        foreach ($pdo->query('PRAGMA table_info(companies)') as $row) {
            $cols[(string) $row['name']] = true;
        }
        $add = [
            'trial_started_at' => 'TEXT',
            'trial_ends_at' => 'TEXT',
            'licence_status' => "TEXT NOT NULL DEFAULT 'trial'",
            'licence_key' => 'TEXT',
            'licence_activated_at' => 'TEXT',
            'licence_ends_at' => 'TEXT',
        ];
        foreach ($add as $name => $type) {
            if (!isset($cols[$name])) {
                $pdo->exec("ALTER TABLE companies ADD COLUMN {$name} {$type}");
            }
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS licence_keys (
                licence_key TEXT PRIMARY KEY,
                customer_name TEXT NOT NULL DEFAULT \'\',
                customer_email TEXT NOT NULL DEFAULT \'\',
                company_name TEXT NOT NULL DEFAULT \'\',
                validity_days INTEGER NOT NULL DEFAULT 365,
                is_revoked INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                activated_at TEXT,
                activated_company_id INTEGER,
                notes TEXT NOT NULL DEFAULT \'\',
                source TEXT NOT NULL DEFAULT \'admin\'
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS purchase_intents (
                intent_id TEXT PRIMARY KEY,
                full_name TEXT NOT NULL,
                email TEXT NOT NULL,
                company_name TEXT NOT NULL DEFAULT \'\',
                vat_region TEXT NOT NULL DEFAULT \'\',
                seats INTEGER NOT NULL DEFAULT 1,
                status TEXT NOT NULL DEFAULT \'pending\',
                created_at TEXT NOT NULL,
                notes TEXT NOT NULL DEFAULT \'\'
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS admin_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE COLLATE NOCASE,
                password_hash TEXT NOT NULL,
                created_at TEXT NOT NULL,
                last_login_at TEXT
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS admin_sessions (
                token_hash TEXT PRIMARY KEY,
                admin_id INTEGER NOT NULL,
                created_at TEXT NOT NULL,
                expires_at TEXT NOT NULL
            )'
        );

        self::ensureSeedAdmin($pdo);
        self::$migrated = true;
    }

    private static function ensureSeedAdmin(PDO $pdo): void
    {
        require_once dirname(__DIR__) . '/config.php';
        $cfg = erc_licence_config();
        $username = (string) ($cfg['admin_username'] ?? 'admin');
        $password = (string) ($cfg['admin_password'] ?? '');
        if ($username === '' || $password === '') {
            return;
        }
        $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return;
        }
        $ins = $pdo->prepare(
            'INSERT INTO admin_users (username, password_hash, created_at) VALUES (?, ?, ?)'
        );
        $ins->execute([$username, password_hash($password, PASSWORD_DEFAULT), gmdate('c')]);
    }

    public static function startTrial(int $companyId, ?string $fromIso = null, bool $force = true): void
    {
        self::migrate();
        $start = $fromIso ?? gmdate('c');
        $end = gmdate('c', strtotime($start . ' +' . self::TRIAL_DAYS . ' days'));
        if ($force) {
            $stmt = ErcAuth::db()->prepare(
                'UPDATE companies SET
                    trial_started_at = ?,
                    trial_ends_at = ?,
                    licence_status = ?
                 WHERE id = ?'
            );
            $stmt->execute([$start, $end, 'trial', $companyId]);
            return;
        }
        $row = self::companyRow($companyId);
        if ($row && empty($row['trial_ends_at']) && ($row['licence_status'] ?? '') !== 'active') {
            $stmt = ErcAuth::db()->prepare(
                'UPDATE companies SET trial_started_at = ?, trial_ends_at = ?, licence_status = ? WHERE id = ?'
            );
            $stmt->execute([$start, $end, 'trial', $companyId]);
        }
    }

    public static function ensureCompanyTrial(int $companyId): void
    {
        self::migrate();
        $row = self::companyRow($companyId);
        if (!$row) {
            return;
        }
        if (($row['licence_status'] ?? '') === 'active') {
            $ends = self::parseTs($row['licence_ends_at'] ?? null);
            if ($ends !== null && $ends >= time()) {
                return;
            }
        }
        if (empty($row['trial_ends_at'])) {
            self::startTrial($companyId, gmdate('c'), true);
        }
    }

    /**
     * @return array{
     *   status: string,
     *   can_use: bool,
     *   days_remaining: int|null,
     *   trial_ends_at: string|null,
     *   licence_ends_at: string|null,
     *   message: string,
     *   label: string
     * }
     */
    public static function statusForCompany(int $companyId): array
    {
        self::migrate();
        self::ensureCompanyTrial($companyId);
        $row = self::companyRow($companyId);
        if (!$row) {
            return [
                'status' => 'unknown',
                'can_use' => false,
                'days_remaining' => null,
                'trial_ends_at' => null,
                'licence_ends_at' => null,
                'message' => 'Company not found.',
                'label' => 'Unavailable',
            ];
        }

        $now = time();
        $status = (string) ($row['licence_status'] ?? 'trial');
        $licenceEnds = self::parseTs($row['licence_ends_at'] ?? null);
        $trialEnds = self::parseTs($row['trial_ends_at'] ?? null);

        if ($status === 'active' && $licenceEnds !== null) {
            if ($licenceEnds >= $now) {
                $days = (int) max(0, ceil(($licenceEnds - $now) / 86400));
                return [
                    'status' => 'active',
                    'can_use' => true,
                    'days_remaining' => $days,
                    'trial_ends_at' => $row['trial_ends_at'] ?? null,
                    'licence_ends_at' => $row['licence_ends_at'] ?? null,
                    'message' => "Licensed — {$days} day(s) remaining on your 1-year licence.",
                    'label' => 'Licensed',
                ];
            }
            self::setStatus($companyId, 'expired');
            return [
                'status' => 'expired',
                'can_use' => false,
                'days_remaining' => 0,
                'trial_ends_at' => $row['trial_ends_at'] ?? null,
                'licence_ends_at' => $row['licence_ends_at'] ?? null,
                'message' => 'Your 1-year licence has expired. Activate a new licence to continue.',
                'label' => 'Licence expired',
            ];
        }

        if ($trialEnds !== null && $trialEnds >= $now) {
            $days = (int) max(0, ceil(($trialEnds - $now) / 86400));
            return [
                'status' => 'trial',
                'can_use' => true,
                'days_remaining' => $days,
                'trial_ends_at' => $row['trial_ends_at'] ?? null,
                'licence_ends_at' => $row['licence_ends_at'] ?? null,
                'message' => "Trial — {$days} day(s) remaining.",
                'label' => 'Trial',
            ];
        }

        if ($status !== 'expired') {
            self::setStatus($companyId, 'expired');
        }
        return [
            'status' => 'expired',
            'can_use' => false,
            'days_remaining' => 0,
            'trial_ends_at' => $row['trial_ends_at'] ?? null,
            'licence_ends_at' => $row['licence_ends_at'] ?? null,
            'message' => 'Your 7-day trial has ended. Activate a 1-year licence to continue using EasyReportCreator.',
            'label' => 'Trial ended',
        ];
    }

    /** @return array<string,mixed> */
    public static function statusForCurrentCompany(): array
    {
        $user = ErcAuth::currentUser();
        if (!$user) {
            throw new RuntimeException('Not signed in.');
        }
        return self::statusForCompany((int) $user['company_id']);
    }

    /** @return array<string,mixed> */
    public static function requireAccess(): array
    {
        $status = self::statusForCurrentCompany();
        if (empty($status['can_use'])) {
            throw new ErcLicenceException((string) $status['message'], $status);
        }
        return $status;
    }

    /** @return array<string,mixed> */
    public static function activate(int $companyId, string $licenceKey): array
    {
        self::migrate();
        $key = self::normalizeKey($licenceKey);
        if (!self::isValidKeyFormat($key)) {
            throw new InvalidArgumentException(
                'Enter a valid licence key (format ERC-XXXX-XXXX-XXXX).'
            );
        }

        $pdo = ErcAuth::db();
        $stmt = $pdo->prepare('SELECT * FROM licence_keys WHERE licence_key = ?');
        $stmt->execute([$key]);
        $issued = $stmt->fetch();

        // Smoke-test / bootstrap grant when no registry row exists yet.
        $isBootstrap = ($key === 'ERC-ADMIN-GRANT-1YEAR' && !$issued);
        if (!$issued && !$isBootstrap) {
            throw new InvalidArgumentException('This licence key is not recognised. Contact support@tspd.nl.');
        }
        if ($issued) {
            if ((int) ($issued['is_revoked'] ?? 0) === 1) {
                throw new InvalidArgumentException('This licence key has been revoked.');
            }
            if (!empty($issued['activated_company_id']) && (int) $issued['activated_company_id'] !== $companyId) {
                throw new InvalidArgumentException('This licence key is already activated on another account.');
            }
        }

        $validityDays = $issued
            ? max(1, (int) ($issued['validity_days'] ?? self::DEFAULT_VALIDITY_DAYS))
            : self::DEFAULT_VALIDITY_DAYS;
        $now = gmdate('c');
        $ends = gmdate('c', strtotime($now . ' +' . $validityDays . ' days'));

        $upd = $pdo->prepare(
            'UPDATE companies SET
                licence_status = ?,
                licence_key = ?,
                licence_activated_at = ?,
                licence_ends_at = ?
             WHERE id = ?'
        );
        $upd->execute(['active', $key, $now, $ends, $companyId]);

        if ($issued) {
            $mark = $pdo->prepare(
                'UPDATE licence_keys SET activated_at = ?, activated_company_id = ? WHERE licence_key = ?'
            );
            $mark->execute([$now, $companyId, $key]);
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO licence_keys (
                    licence_key, customer_name, validity_days, is_revoked, created_at,
                    activated_at, activated_company_id, source, notes
                 ) VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?)'
            );
            $ins->execute([
                $key,
                'Admin grant',
                $validityDays,
                $now,
                $now,
                $companyId,
                'bootstrap',
                'ERC-ADMIN-GRANT-1YEAR',
            ]);
        }

        return self::statusForCompany($companyId);
    }

    public static function createKey(array $opts = []): array
    {
        self::migrate();
        $key = self::normalizeKey((string) ($opts['licence_key'] ?? ''));
        if ($key === '') {
            $key = self::generateKey();
        }
        if (!self::isValidKeyFormat($key)) {
            throw new InvalidArgumentException('Invalid licence key format.');
        }
        $validityDays = (int) ($opts['validity_days'] ?? self::DEFAULT_VALIDITY_DAYS);
        if ($validityDays < 1) {
            $validityDays = self::DEFAULT_VALIDITY_DAYS;
        }
        $now = gmdate('c');
        try {
            $stmt = ErcAuth::db()->prepare(
                'INSERT INTO licence_keys (
                    licence_key, customer_name, customer_email, company_name,
                    validity_days, is_revoked, created_at, notes, source
                 ) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)'
            );
            $stmt->execute([
                $key,
                trim((string) ($opts['customer_name'] ?? 'Customer')),
                strtolower(trim((string) ($opts['customer_email'] ?? ''))),
                trim((string) ($opts['company_name'] ?? '')),
                $validityDays,
                $now,
                trim((string) ($opts['notes'] ?? '')),
                trim((string) ($opts['source'] ?? 'admin')),
            ]);
        } catch (PDOException $e) {
            throw new InvalidArgumentException('Licence key already exists.');
        }
        return self::keyRow($key) ?? ['licence_key' => $key];
    }

    public static function revokeKey(string $licenceKey): void
    {
        self::migrate();
        $key = self::normalizeKey($licenceKey);
        $stmt = ErcAuth::db()->prepare('UPDATE licence_keys SET is_revoked = 1 WHERE licence_key = ?');
        $stmt->execute([$key]);

        $co = ErcAuth::db()->prepare(
            "UPDATE companies SET licence_status = 'expired'
             WHERE licence_key = ? AND licence_status = 'active'"
        );
        $co->execute([$key]);
    }

    /** @return list<array<string,mixed>> */
    public static function listKeys(): array
    {
        self::migrate();
        $rows = ErcAuth::db()->query(
            'SELECT * FROM licence_keys ORDER BY created_at DESC LIMIT 500'
        )->fetchAll();
        return array_map([self::class, 'formatKeyRow'], $rows ?: []);
    }

    /** @return array<string,int> */
    public static function dashboardStats(): array
    {
        self::migrate();
        $pdo = ErcAuth::db();
        $q = static function (string $sql) use ($pdo): int {
            return (int) $pdo->query($sql)->fetchColumn();
        };
        return [
            'keys_total' => $q('SELECT COUNT(*) FROM licence_keys'),
            'keys_available' => $q(
                'SELECT COUNT(*) FROM licence_keys WHERE is_revoked = 0 AND activated_company_id IS NULL'
            ),
            'keys_active' => $q(
                'SELECT COUNT(*) FROM licence_keys WHERE is_revoked = 0 AND activated_company_id IS NOT NULL'
            ),
            'keys_revoked' => $q('SELECT COUNT(*) FROM licence_keys WHERE is_revoked = 1'),
            'companies_trial' => $q("SELECT COUNT(*) FROM companies WHERE licence_status = 'trial'"),
            'companies_active' => $q("SELECT COUNT(*) FROM companies WHERE licence_status = 'active'"),
            'intents_pending' => $q("SELECT COUNT(*) FROM purchase_intents WHERE status = 'pending'"),
        ];
    }

    /** @return array{success:bool,intent_id?:string,message?:string,payment_url?:string} */
    public static function recordPurchaseIntent(array $body): array
    {
        self::migrate();
        require_once dirname(__DIR__) . '/config.php';
        $cfg = erc_licence_config();

        $fullName = trim((string) ($body['full_name'] ?? ''));
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $vatRegion = trim((string) ($body['vat_region'] ?? 'nl21'));
        if ($fullName === '' || $email === '' || !str_contains($email, '@')) {
            return ['success' => false, 'message' => 'Enter your name and a valid email address.'];
        }
        if (empty($body['consent'])) {
            return ['success' => false, 'message' => 'Consent is required to continue.'];
        }
        if (!in_array($vatRegion, ['nl21', 'non_nl0'], true)) {
            $vatRegion = 'nl21';
        }
        $links = $cfg['payment_links'] ?? [];
        $paymentUrl = (string) ($links[$vatRegion] ?? $links['nl21'] ?? '');
        if ($paymentUrl === '') {
            return ['success' => false, 'message' => 'Payment link is not configured yet.'];
        }

        $intentId = bin2hex(random_bytes(16));
        $notes = trim((string) ($body['vat_number'] ?? ''));
        if ($notes !== '') {
            $notes = 'vat=' . $notes;
        }
        $stmt = ErcAuth::db()->prepare(
            'INSERT INTO purchase_intents (
                intent_id, full_name, email, company_name, vat_region, seats, status, created_at, notes
             ) VALUES (?, ?, ?, ?, ?, 1, \'pending\', ?, ?)'
        );
        $stmt->execute([
            $intentId,
            $fullName,
            $email,
            trim((string) ($body['company_name'] ?? '')),
            $vatRegion,
            gmdate('c'),
            $notes,
        ]);

        return [
            'success' => true,
            'intent_id' => $intentId,
            'payment_url' => $paymentUrl,
        ];
    }

    /** @return list<array<string,mixed>> */
    public static function listIntents(): array
    {
        self::migrate();
        $rows = ErcAuth::db()->query(
            'SELECT * FROM purchase_intents ORDER BY created_at DESC LIMIT 200'
        )->fetchAll();
        $out = [];
        foreach ($rows ?: [] as $r) {
            $out[] = [
                'intent_id' => $r['intent_id'],
                'full_name' => $r['full_name'],
                'email' => $r['email'],
                'company_name' => $r['company_name'],
                'vat_region' => $r['vat_region'],
                'seats' => (int) $r['seats'],
                'status' => $r['status'],
                'created_at' => $r['created_at'],
                'notes' => $r['notes'],
            ];
        }
        return $out;
    }

    public static function markIntentFulfilled(string $intentId, string $licenceKey = ''): void
    {
        self::migrate();
        $notes = $licenceKey !== '' ? ('key=' . $licenceKey) : '';
        $stmt = ErcAuth::db()->prepare(
            'UPDATE purchase_intents SET status = ?, notes = ? WHERE intent_id = ?'
        );
        $stmt->execute(['fulfilled', $notes, $intentId]);
    }

    /**
     * Issue a key for a purchase intent and email it to the buyer (best effort).
     *
     * @return array<string,mixed>
     */
    public static function fulfilIntent(string $intentId): array
    {
        self::migrate();
        $stmt = ErcAuth::db()->prepare('SELECT * FROM purchase_intents WHERE intent_id = ?');
        $stmt->execute([$intentId]);
        $intent = $stmt->fetch();
        if (!$intent) {
            throw new InvalidArgumentException('Purchase not found.');
        }
        if (($intent['status'] ?? '') === 'fulfilled') {
            throw new InvalidArgumentException('This purchase was already fulfilled.');
        }
        $created = self::createKey([
            'customer_name' => (string) $intent['full_name'],
            'customer_email' => (string) $intent['email'],
            'company_name' => (string) $intent['company_name'],
            'validity_days' => self::DEFAULT_VALIDITY_DAYS,
            'source' => 'purchase',
            'notes' => 'intent=' . $intentId,
        ]);
        $key = (string) $created['licence_key'];
        self::markIntentFulfilled($intentId, $key);
        $emailed = self::emailLicenceKey(
            (string) $intent['email'],
            (string) $intent['full_name'],
            $key
        );
        $created['emailed'] = $emailed;
        return $created;
    }

    public static function emailLicenceKey(string $email, string $name, string $licenceKey): bool
    {
        require_once dirname(__DIR__) . '/config.php';
        $to = strtolower(trim($email));
        if ($to === '' || !str_contains($to, '@')) {
            return false;
        }
        $safeName = trim($name) !== '' ? trim($name) : 'customer';
        $subject = 'Your EasyReportCreator licence key';
        $body = "Hello {$safeName},\n\n"
            . "Thank you for your purchase.\n\n"
            . "Your 1-year licence key:\n{$licenceKey}\n\n"
            . "Sign in at https://easyreportcreator.com/report/login.php\n"
            . "Open Licence and activate the key. Access then runs for 365 days from activation.\n\n"
            . "Plant 3D is not required — only your ProcessPower.dcf file.\n\n"
            . "Regards,\nEasyReportCreator / TSPD\n";
        $headers = 'From: ' . SITE['support_email'] . "\r\n"
            . 'Reply-To: ' . SITE['support_email'] . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n";
        return @mail($to, $subject, $body, $headers);
    }

    public static function generateKey(): string
    {
        $seg = static fn (): string => strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        return sprintf('ERC-%s-%s-%s', $seg(), $seg(), $seg());
    }

    public static function normalizeKey(string $key): string
    {
        return strtoupper(trim($key));
    }

    public static function isValidKeyFormat(string $key): bool
    {
        $key = self::normalizeKey($key);
        if ($key === 'ERC-ADMIN-GRANT-1YEAR') {
            return true;
        }
        return (bool) preg_match('/^ERC-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);
    }

    /** @return array<string,mixed>|null */
    private static function keyRow(string $key): ?array
    {
        $stmt = ErcAuth::db()->prepare('SELECT * FROM licence_keys WHERE licence_key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? self::formatKeyRow($row) : null;
    }

    /** @param array<string,mixed> $row */
    private static function formatKeyRow(array $row): array
    {
        return [
            'licence_key' => $row['licence_key'],
            'customer_name' => $row['customer_name'],
            'customer_email' => $row['customer_email'],
            'company_name' => $row['company_name'],
            'validity_days' => (int) $row['validity_days'],
            'is_revoked' => (int) $row['is_revoked'] === 1,
            'created_at' => $row['created_at'],
            'activated_at' => $row['activated_at'],
            'activated_company_id' => $row['activated_company_id'] !== null
                ? (int) $row['activated_company_id'] : null,
            'notes' => $row['notes'],
            'source' => $row['source'],
            'status' => (int) $row['is_revoked'] === 1
                ? 'revoked'
                : ($row['activated_company_id'] ? 'in_use' : 'available'),
        ];
    }

    /** @return array<string,mixed>|null */
    private static function companyRow(int $companyId): ?array
    {
        $stmt = ErcAuth::db()->prepare('SELECT * FROM companies WHERE id = ?');
        $stmt->execute([$companyId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private static function setStatus(int $companyId, string $status): void
    {
        $stmt = ErcAuth::db()->prepare('UPDATE companies SET licence_status = ? WHERE id = ?');
        $stmt->execute([$status, $companyId]);
    }

    private static function parseTs(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $t = strtotime((string) $value);
        return $t === false ? null : $t;
    }
}

final class ErcLicenceException extends RuntimeException
{
    /** @param array<string,mixed> $licence */
    public function __construct(
        string $message,
        public array $licence = []
    ) {
        parent::__construct($message, 402);
    }
}

/**
 * Admin auth for the licence dashboard (username/password session or master API key).
 */
final class ErcAdmin
{
    public static function masterApiKey(): string
    {
        require_once dirname(__DIR__) . '/config.php';
        return trim((string) (erc_licence_config()['admin_api_key'] ?? ''));
    }

    public static function login(string $username, string $password): array
    {
        ErcLicence::migrate();
        $stmt = ErcAuth::db()->prepare('SELECT * FROM admin_users WHERE username = ?');
        $stmt->execute([trim($username)]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($password, (string) $row['password_hash'])) {
            throw new InvalidArgumentException('Invalid username or password.');
        }
        $token = bin2hex(random_bytes(24));
        $hash = hash('sha256', $token);
        $now = gmdate('c');
        $expires = gmdate('c', time() + 60 * 60 * 12);
        ErcAuth::db()->prepare(
            'INSERT INTO admin_sessions (token_hash, admin_id, created_at, expires_at) VALUES (?, ?, ?, ?)'
        )->execute([$hash, (int) $row['id'], $now, $expires]);
        ErcAuth::db()->prepare('UPDATE admin_users SET last_login_at = ? WHERE id = ?')
            ->execute([$now, (int) $row['id']]);
        return [
            'token' => $token,
            'username' => (string) $row['username'],
            'kind' => 'user',
            'role' => 'admin',
            'expires_at' => $expires,
        ];
    }

    /**
     * Validate master API key (returned token is the key itself, like PropertiesManager).
     *
     * @return array{token:string,username:string,kind:string,role:string}
     */
    public static function loginMasterKey(string $apiKey): array
    {
        $expected = self::masterApiKey();
        $provided = trim($apiKey);
        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            throw new InvalidArgumentException('Invalid master API key.');
        }
        return [
            'token' => $provided,
            'username' => 'master-key',
            'kind' => 'master',
            'role' => 'admin',
        ];
    }

    public static function logout(?string $token): void
    {
        if (!$token) {
            return;
        }
        // Master key is not a DB session — nothing to delete.
        $expected = self::masterApiKey();
        if ($expected !== '' && hash_equals($expected, $token)) {
            return;
        }
        ErcLicence::migrate();
        ErcAuth::db()->prepare('DELETE FROM admin_sessions WHERE token_hash = ?')
            ->execute([hash('sha256', $token)]);
    }

    /**
     * @return array{id:int,username:string,kind:string,role:string}
     */
    public static function requireAdmin(?string $token): array
    {
        ErcLicence::migrate();
        if (!$token) {
            throw new RuntimeException('Admin authentication required.');
        }

        $expected = self::masterApiKey();
        if ($expected !== '' && hash_equals($expected, $token)) {
            return [
                'id' => 0,
                'username' => 'master-key',
                'kind' => 'master',
                'role' => 'admin',
            ];
        }

        $hash = hash('sha256', $token);
        $stmt = ErcAuth::db()->prepare(
            'SELECT s.expires_at, u.id, u.username
             FROM admin_sessions s
             JOIN admin_users u ON u.id = s.admin_id
             WHERE s.token_hash = ?'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new RuntimeException('Admin session expired. Sign in again.');
        }
        $exp = strtotime((string) $row['expires_at']);
        if ($exp === false || $exp < time()) {
            ErcAuth::db()->prepare('DELETE FROM admin_sessions WHERE token_hash = ?')->execute([$hash]);
            throw new RuntimeException('Admin session expired. Sign in again.');
        }
        return [
            'id' => (int) $row['id'],
            'username' => (string) $row['username'],
            'kind' => 'user',
            'role' => 'admin',
        ];
    }
}
