<?php
declare(strict_types=1);

/**
 * Company trial (7 days) + 1-year licence — PropertiesManager-aligned (FB-003 / WP11).
 */
final class ErcLicence
{
    public const TRIAL_DAYS = 7;
    public const LICENCE_YEARS = 1;

    /** @var bool */
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
        self::$migrated = true;
    }

    /**
     * Start a 7-day trial for a new company (forced).
     */
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

    /**
     * Ensure legacy companies get a trial window once.
     */
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
            // Legacy accounts (pre-FB-003): grant a fresh 7-day window from now, not created_at.
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
            // Licence expired
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

        // Trial path
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

    /**
     * @return array<string,mixed>
     */
    public static function statusForCurrentCompany(): array
    {
        $user = ErcAuth::currentUser();
        if (!$user) {
            throw new RuntimeException('Not signed in.');
        }
        return self::statusForCompany((int) $user['company_id']);
    }

    /**
     * Block report use when trial/licence is not active.
     *
     * @return array<string,mixed>
     */
    public static function requireAccess(): array
    {
        $status = self::statusForCurrentCompany();
        if (empty($status['can_use'])) {
            throw new ErcLicenceException((string) $status['message'], $status);
        }
        return $status;
    }

    /**
     * Activate a 1-year licence with a key (PropertiesManager-style annual use).
     *
     * @return array<string,mixed>
     */
    public static function activate(int $companyId, string $licenceKey): array
    {
        self::migrate();
        $key = strtoupper(trim($licenceKey));
        if (!self::isValidKeyFormat($key)) {
            throw new InvalidArgumentException(
                'Enter a valid licence key (format ERC-XXXX-XXXX-XXXX).'
            );
        }
        $now = gmdate('c');
        $ends = gmdate('c', strtotime($now . ' +' . self::LICENCE_YEARS . ' year'));
        $stmt = ErcAuth::db()->prepare(
            'UPDATE companies SET
                licence_status = ?,
                licence_key = ?,
                licence_activated_at = ?,
                licence_ends_at = ?
             WHERE id = ?'
        );
        $stmt->execute(['active', $key, $now, $ends, $companyId]);
        return self::statusForCompany($companyId);
    }

    public static function isValidKeyFormat(string $key): bool
    {
        $key = strtoupper(trim($key));
        // Production-style keys + admin smoke-test key
        if ($key === 'ERC-ADMIN-GRANT-1YEAR') {
            return true;
        }
        return (bool) preg_match('/^ERC-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);
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
