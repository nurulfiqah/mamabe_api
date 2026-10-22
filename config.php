<?php
/**
 * MamaBe public registration form - config for the education-server package (form.php + api/submit.php).
 * Nothing is stored here: api/submit.php forwards every registration to odb's API, which saves it.
 *
 * Secrets live in api/.env (never commit it; it is blocked from HTTP by api/.htaccess):
 *   ODB_API_HOST      e.g. https://octopusdb.info/api
 *   ODB_API_USERNAME  odb api_user allowed to register (mamabe_api)
 *   ODB_API_PASSWORD
 * An optional api/.env.local (same keys) overrides it - for local testing against a local odb only.
 */

define('MAMABE_MAX_UPLOAD_BYTES', 5 * 1024 * 1024);   // per photo - odb re-checks everything
define('MAMABE_API_TIMEOUT',      60);                // seconds to wait for odb (two photos + image re-encoding)

date_default_timezone_set('Asia/Kuala_Lumpur');

/** Language codes (must match odb's mamabe_mama.language: 1 English, 2 BM, 3 Chinese, 4 Tamil).
 *  Tamil (4) is not offered on this form (removed by the user, Oct 2026) — odb still knows the code. */
function mamabe_languages() {
    return array(1 => 'English', 2 => 'Bahasa Melayu', 3 => 'Chinese (中文)');
}

/** POST to an odb API script with the api_user credentials. Returns array(http code, decoded JSON or null, curl error). */
function mamabe_odb_call($script, array $fields, $timeout) {
    $host = rtrim(mamabe_env('ODB_API_HOST'), '/');
    $user = mamabe_env('ODB_API_USERNAME');
    $pass = mamabe_env('ODB_API_PASSWORD');
    if ($host === '' || $user === '' || $pass === '') {
        error_log('mamabe proxy: api/.env is missing ODB_API_HOST / ODB_API_USERNAME / ODB_API_PASSWORD');
        return array(0, null, 'not configured');
    }
    $ch = curl_init($host . '/' . $script);
    curl_setopt_array($ch, array(
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => array('username' => $user, 'password' => $pass) + $fields,   // multipart/form-data
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_FOLLOWLOCATION => false,
    ));
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $r = is_string($body) ? json_decode($body, true) : null;
    return array($code, is_array($r) && isset($r['status']) ? $r : null, $err);
}

/** KEY=value lines from api/.env, then api/.env.local on top. Quotes around values are stripped. */
function mamabe_env($key) {
    static $env = null;
    if ($env === null) {
        $env = array();
        foreach (array('.env', '.env.local') as $file) {
            $path = dirname(__FILE__) . '/api/' . $file;
            if (!is_file($path)) continue;
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
                list($k, $v) = explode('=', $line, 2);
                $env[trim($k)] = trim(trim($v), "\"'");
            }
        }
    }
    return isset($env[$key]) ? $env[$key] : '';
}
