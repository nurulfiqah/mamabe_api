<?php
/**
 * PUBLIC endpoint for form.php's outlet pickers: the normal "Preferred outlet to redeem" list, and - with
 * ?type=avida - the "Which Avida outlet?" list shown when "Confinement center" is chosen for "How do you know
 * us?". A thin proxy: asks odb's API (ODB_API_HOST/mamabeOutlets.php) with the api_user credentials from
 * api/.env - nothing is stored on this server.
 *
 * GET, optional ?type=avida. Response JSON (normal): {"success":true,"outlets":[{"id":3,"name":"Alpro Pharmacy Ampangan","state":"Negeri Sembilan"}, ...]}
 *                            Response JSON (avida):  {"success":true,"outlets":[{"id":12,"name":"Avida Confinement Care Centre"}, ...]}   (no "state")
 *      |  {"success":false,"error":"..."}
 * The browser may cache each variant for 10 minutes (the lists are public and change rarely).
 */
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__FILE__) . '/../config.php';

$isAvida = (isset($_GET['type']) ? (string)$_GET['type'] : '') === 'avida';

list($code, $r, $err) = mamabe_odb_call('mamabeOutlets.php', $isAvida ? array('type' => 'avida') : array(), 20);
if (!$r || $r['status'] !== 'success' || !isset($r['outlets']) || !is_array($r['outlets'])) {
    error_log('mamabe proxy: odb outlet list failed (HTTP ' . $code . ') ' . $err);
    header('Cache-Control: no-store');
    http_response_code(502);
    echo json_encode(array('success' => false, 'error' => 'Could not load the outlet list. Please reload the page.'));
    exit;
}

$outlets = array();
foreach ($r['outlets'] as $o) {
    if (isset($o['id'], $o['name'])) {
        $entry = array('id' => (int)$o['id'], 'name' => (string)$o['name']);
        if (!$isAvida) $entry['state'] = isset($o['state']) ? (string)$o['state'] : 'Others';
        $outlets[] = $entry;
    }
}
header('Cache-Control: public, max-age=600');
echo json_encode(array('success' => true, 'outlets' => $outlets), JSON_UNESCAPED_UNICODE);
