<?php
/**
 * PUBLIC endpoint for form.php's "How do you know us?" chips. A thin proxy: asks odb's API
 * (ODB_API_HOST/mamabeSources.php) with the api_user credentials from api/.env - nothing is stored on this server.
 *
 * GET. Response JSON: {"success":true,"sources":[{"value":"outlet","label":"Outlet"}, ...]}
 *      |  {"success":false,"error":"..."}
 * The browser may cache it for 10 minutes (the list is public and changes rarely).
 */
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__FILE__) . '/../config.php';

list($code, $r, $err) = mamabe_odb_call('mamabeSources.php', array(), 20);
if (!$r || $r['status'] !== 'success' || !isset($r['sources']) || !is_array($r['sources'])) {
    error_log('mamabe proxy: odb source list failed (HTTP ' . $code . ') ' . $err);
    header('Cache-Control: no-store');
    http_response_code(502);
    echo json_encode(array('success' => false, 'error' => 'Could not load the list. Please reload the page.'));
    exit;
}

$sources = array();
foreach ($r['sources'] as $s) {
    if (isset($s['value'], $s['label'])) {
        $sources[] = array('value' => (string)$s['value'], 'label' => (string)$s['label']);
    }
}
header('Cache-Control: public, max-age=600');
echo json_encode(array('success' => true, 'sources' => $sources), JSON_UNESCAPED_UNICODE);
