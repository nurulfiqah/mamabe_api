<?php
/**
 * PUBLIC endpoint for form.php's "How to redeem" guide image on the success screen. A thin proxy: asks odb's API
 * (ODB_API_HOST/mamabeRedeemGuide.php) with the api_user credentials from api/.env, streams back whatever it gets -
 * nothing is stored on this server. 404 (odb has no image configured, or odb is unreachable) is a normal, silent
 * case here: form.php hides the image block on load error, so visitors just don't see the section.
 */
error_reporting(0);   // like the other proxies: a PHP warning must never be printed into the response
require_once dirname(__FILE__) . '/../config.php';

$host = rtrim(mamabe_env('ODB_API_HOST'), '/');
$user = mamabe_env('ODB_API_USERNAME');
$pass = mamabe_env('ODB_API_PASSWORD');
if ($host === '' || $user === '' || $pass === '') {
    http_response_code(404);
    exit;
}

$ch = curl_init($host . '/mamabeRedeemGuide.php');
curl_setopt_array($ch, array(
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => array('username' => $user, 'password' => $pass),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_FOLLOWLOCATION => false,
));
$resp = curl_exec($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

if ($resp === false || $code !== 200) {
    http_response_code(404);
    exit;
}

$headers = substr($resp, 0, $headerSize);
$body    = substr($resp, $headerSize);
$type    = 'application/octet-stream';
if (preg_match('/^Content-Type:\s*(.+)$/mi', $headers, $m)) $type = trim($m[1]);

header('Content-Type: ' . $type);
header('Cache-Control: public, max-age=600'); // the browser may cache it for 10 minutes (admin-uploaded, changes rarely)
echo $body;
