<?php
declare(strict_types=1);

if (!defined('SITE')) {
    define('SITE', [
        'name' => 'EasyReportCreator',
        'domain' => 'easyreportcreator.com',
        'url' => 'https://easyreportcreator.com',
        'support_email' => 'support@tspd.nl',
        'company' => 'TSPD',
        'company_url' => 'https://www.tspd.nl',
        'region' => 'Netherlands / EU',
        'product_version' => '1.0.0',
        'year' => '2026',
    ]);
}

if (!defined('ERC_LICENCE')) {
    /** Defaults — override secrets via website/inc/config.local.php on the server. */
    define('ERC_LICENCE', [
        'unit_price_eur' => 59,
        'payment_links' => [
            'nl21' => 'https://mnbrd.com/p/djAw5AJqaPV4',
            'non_nl0' => 'https://mnbrd.com/p/6Y6pDxlL08AE',
        ],
        'admin_username' => 'admin',
        'admin_password' => '', // set in config.local.php on the server
        'admin_api_key' => '', // master key — set in config.local.php
    ]);
}

/**
 * @return array{
 *   unit_price_eur:int|float,
 *   payment_links:array{nl21:string,non_nl0:string},
 *   admin_username:string,
 *   admin_password:string,
 *   admin_api_key:string
 * }
 */
function erc_licence_config(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }
    $cfg = ERC_LICENCE;
    $localFile = __DIR__ . '/config.local.php';
    if (is_file($localFile)) {
        $local = require $localFile;
        if (is_array($local)) {
            $cfg = array_replace_recursive($cfg, $local);
        }
    }
    return $cfg;
}

if (!function_exists('h')) {
    function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('is_active')) {
    function is_active(string $page, string $current): bool
    {
        return $page === $current;
    }
}
