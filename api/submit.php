<?php
/**
 * PUBLIC endpoint of the MamaBe registration form (education server). A thin proxy: cheap checks, then the whole
 * registration (fields + photos) is forwarded to odb's API (ODB_API_HOST/mamabeRegister.php) with the api_user
 * credentials from api/.env and the visitor's IP. odb validates everything again, saves it and runs the checks -
 * nothing is stored on this server.
 *
 * POST multipart/form-data from form.php: register_type (pink | baby | both), source, mother_name, ic, phone, email, address, language, outlet_id,
 *   pink: edd, pink_book  |  baby: child_name, baby_dob, baby_mykid, baby_book  |  both: all of the above, both photos;
 *   has_twin (baby/both only) - 1 adds a second baby to the same submission: child_name_2, baby_dob_2 (must equal
 *     baby_dob - that is what lets odb's existing twin-matching auto-pair them), baby_mykid_2, baby_book_2;
 *   website (honeypot - must stay empty)
 * Response JSON to the browser: {"success":true,"message":"Submitted successfully","baby_only":bool}  |  {"success":false,"error":"..."}
 *   baby_only is true when she picked "Both" but was already a registered mama - odb only actually registered the
 *   baby part (see api/register.php); form.php shows an extra note on the success screen for this case.
 */
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

require_once dirname(__FILE__) . '/../config.php';

function mamabe_reply($code, $success, $text, $extra = array()) {
    http_response_code($code);
    echo json_encode($success ? array('success' => true, 'message' => $text) + $extra : array('success' => false, 'error' => $text), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mamabe_reply(405, false, 'Method not allowed.');
}
// Honeypot: humans never see this field, bots fill it. Pretend success, forward nothing.
if (!empty($_POST['website'])) {
    mamabe_reply(200, true, 'Submitted successfully');
}

// Cheap checks so obvious junk never costs a call to odb (odb checks all of this again, properly).
// Only the book(s) of the chosen registration type are forwarded.
$type = isset($_POST['register_type']) ? (string)$_POST['register_type'] : '';
$books = array(
    'pink' => array(array('pink_book', 'pink book')),
    'baby' => array(array('baby_book', 'baby health book')),
    'both' => array(array('pink_book', 'pink book'), array('baby_book', 'baby health book')),
);
if (!isset($books[$type])) mamabe_reply(422, false, 'Please choose how you want to register (pink book, baby health book, or both).');

$hasTwin = ($type === 'baby' || $type === 'both') && !empty($_POST['has_twin']);
if ($hasTwin) $books[$type][] = array('baby_book_2', "twin's baby health book");

$files = array();
foreach ($books[$type] as $book) {
    list($key, $label) = $book;
    $f = isset($_FILES[$key]) ? $_FILES[$key] : null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) mamabe_reply(422, false, "Please add a photo of the $label.");
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE
        || $f['size'] > MAMABE_MAX_UPLOAD_BYTES) mamabe_reply(413, false, 'The photo is too large (max 5 MB).');
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) mamabe_reply(400, false, 'The upload failed. Please try again.');
    $info = @getimagesize($f['tmp_name']);
    if (!$info || !in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) mamabe_reply(415, false, 'Only JPG or PNG photos are accepted.');
    $files[$key] = new CURLFile($f['tmp_name'], $info['mime'], $key . ($info[2] === IMAGETYPE_PNG ? '.png' : '.jpg'));
}

$fields = array('client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', 'register_type' => $type, 'has_twin' => $hasTwin ? '1' : '');
foreach (array('source', 'mother_name', 'ic', 'phone', 'email', 'address', 'language', 'outlet_id', 'edd', 'child_name', 'baby_dob', 'baby_mykid',
               'child_name_2', 'baby_dob_2', 'baby_mykid_2') as $k) {
    $max = $k === 'address' ? 600 : 200;   // odb allows 500 for the address; cap slightly above so odb gives the real message
    $fields[$k] = isset($_POST[$k]) ? mb_substr((string)$_POST[$k], 0, $max, 'UTF-8') : '';
}

list($code, $r, $err) = mamabe_odb_call('mamabeRegister.php', $fields + $files, MAMABE_API_TIMEOUT);
if ($err === 'not configured') {
    mamabe_reply(503, false, 'The service is not available right now. Please try again later.');
}
if (!$r) {
    error_log('mamabe proxy: odb API call failed (HTTP ' . $code . ') ' . $err);
    mamabe_reply(502, false, 'Could not send your details right now. Please try again in a few minutes.');
}
if ($r['status'] === 'success') {
    mamabe_reply(200, true, 'Submitted successfully', array('baby_only' => !empty($r['baby_only']), 'existing_mama' => !empty($r['existing_mama'])));
}
if ($code === 401) {
    // Wrong credentials in api/.env: the visitor can't fix that, so don't show odb's "Invalid User".
    error_log('mamabe proxy: odb API rejected the api_user credentials in api/.env');
    mamabe_reply(503, false, 'The service is not available right now. Please try again later.');
}
// odb's messages are written for the visitor (e.g. "Please enter a valid mobile number").
mamabe_reply($code >= 400 && $code < 600 ? $code : 422, false, isset($r['message']) ? (string)$r['message'] : 'Something went wrong. Please try again.');
