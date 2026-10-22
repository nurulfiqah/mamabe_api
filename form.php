<?php
/**
 * PUBLIC MamaBe registration form (education server). Self-contained: its own copy of odb's framework CSS (css/),
 * and it only talks to api/submit.php, which forwards everything to odb's API - nothing is stored on this server.
 * The visitor is only ever told "Submitted successfully".
 */
require_once dirname(__FILE__) . '/config.php';
$today    = date('Y-m-d');
$eddMin   = date('Y-m-d', strtotime('-6 years'));
$eddMax   = date('Y-m-d', strtotime('+10 months'));
$dobMin   = date('Y-m-d', strtotime('-6 years'));
function mamabe_h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Alpro MamaBe – Registration</title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/page.css">
    <link rel="stylesheet" href="css/alpro-core.css">
    <link rel="stylesheet" href="css/alpro-components.css">
    <link rel="stylesheet" href="css/alpro-utils.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(dirname(__FILE__) . '/css/style.css'); ?>">
</head>
<body class="mamabe-public-body">
<div class="mamabe-public">
    <div class="mamabe-hero">
        <div class="mamabe-hero-title">
            <img src="images/mamabe-logo.png" alt="">
            <h1>Alpro MamaBe</h1>
        </div>
    </div>

    <div class="mamabe-card" id="formCard">
        <form id="mamabeForm" novalidate>
            <!-- ── 1. Which book ───────────────────────────────────── -->
            <div class="mamabe-step"><span>1</span> Register using <span class="mamabe-required">*</span></div>
            <div class="mamabe-choices" role="radiogroup" aria-label="Register using">
                <label class="mamabe-choice mamabe-choice-pink">
                    <input type="radio" name="register_type" value="pink" required>
                    <b>Pregnancy Pink Book</b>
                    <small>I'm currently pregnant <span class="mamabe-hint">Buku pink ibu mengandung</span></small>
                </label>
                <label class="mamabe-choice mamabe-choice-baby">
                    <input type="radio" name="register_type" value="baby">
                    <b>Baby Health Book</b>
                    <small>My baby is below 2 years old <span class="mamabe-hint">Buku kesihatan bayi</span></small>
                </label>
                <label class="mamabe-choice mamabe-choice-both">
                    <input type="radio" name="register_type" value="both">
                    <b>Both</b>
                    <small>I'm pregnant and having baby below 2 years old <span class="mamabe-hint">Mengandung &amp; ada bayi</span></small>
                </label>
            </div>

            <!-- ── 2. Mama ─────────────────────────────────────────── -->
            <div class="mamabe-step"><span>2</span> Mama's details</div>
            <div class="alpro-field mamabe-mb">
                <label id="source_label">How do you know us? <span class="mamabe-required">*</span> <span class="mamabe-hint">Dari mana anda tahu tentang kami?</span></label>
                <div class="mamabe-chips" id="source_chips" role="radiogroup" aria-labelledby="source_label">
                    <span style="color:var(--alpro-muted);font-size:14px;">Loading…</span>
                </div>
            </div>
            <div class="alpro-field mamabe-mb">
                <label for="mother_name">Mama name <span class="mamabe-required">*</span> <span class="mamabe-hint">Nama ibu (as per IC)</span></label>
                <input type="text" class="alpro-input" id="mother_name" name="mother_name" maxlength="100" required autocomplete="name" placeholder="As in your IC">
            </div>
            <div class="alpro-grid mamabe-mb">
                <div class="alpro-field">
                    <label for="ic">IC number <span class="mamabe-required">*</span> <span class="mamabe-hint">No. Kad Pengenalan / Passport</span></label>
                    <input type="text" class="alpro-input" id="ic" name="ic" required autocomplete="off" placeholder="e.g. 900101145678">
                </div>
                <div class="alpro-field">
                    <label for="phone">Phone <span class="mamabe-required">*</span> <span class="mamabe-hint">No. telefon</span></label>
                    <input type="tel" class="alpro-input" id="phone" name="phone" required autocomplete="tel" inputmode="numeric" pattern="[0-9]*" placeholder="e.g. 0123456789">
                </div>
            </div>
            <div class="alpro-field mamabe-mb">
                <label for="email">Email <span class="mamabe-required">*</span> <span class="mamabe-hint">We'll send your voucher here once verified</span></label>
                <input type="email" class="alpro-input" id="email" name="email" maxlength="100" required autocomplete="email" placeholder="e.g. mama@email.com">
            </div>
            <div class="alpro-field mamabe-mb">
                <label for="address">Address <span class="mamabe-hint">Alamat (optional)</span></label>
                <textarea class="alpro-input mamabe-textarea" id="address" name="address" rows="3" maxlength="500" autocomplete="street-address" placeholder="House no., street, area, postcode, city, state"></textarea>
            </div>
            <div class="alpro-field mamabe-mb">
                <label for="language">Preferred language <span class="mamabe-required">*</span> <span class="mamabe-hint">Bahasa pilihan</span></label>
                <select class="alpro-input" id="language" name="language" required>
                    <option value="">— Choose —</option>
                    <?php foreach (mamabe_languages() as $code => $label): ?>
                    <option value="<?php echo (int)$code; ?>"><?php echo mamabe_h($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="alpro-grid">
                <div class="alpro-field">
                    <label for="outlet_state">State <span class="mamabe-required">*</span> <span class="mamabe-hint">Negeri</span></label>
                    <select class="alpro-input" id="outlet_state" required disabled>
                        <option value="">Loading…</option>
                    </select>
                </div>
                <div class="alpro-field">
                    <label for="outlet_id">Preferred outlet to redeem <span class="mamabe-required">*</span> <span class="mamabe-hint">Outlet pilihan untuk tebus</span></label>
                    <select class="alpro-input" id="outlet_id" name="outlet_id" required disabled>
                        <option value="">— Choose a state first —</option>
                    </select>
                </div>
            </div>
            <div class="alpro-field mamabe-mb" id="avida_outlet_box" hidden>
                <label for="avida_outlet_id">Which Confinement Center? <span class="mamabe-required">*</span> <span class="mamabe-hint">Pusat Konfinan mana?</span></label>
                <select class="alpro-input" id="avida_outlet_id" name="outlet_id" disabled>
                    <option value="">Loading…</option>
                </select>
            </div>

            <!-- ── 3. The book: pink (EDD) or baby health book (baby details) ── -->
            <div class="mamabe-step"><span>3</span> <strong id="step3Title">Your book</strong></div>
            <p class="mamabe-help" id="pathNone">Choose <b>Pregnancy Pink Book</b>, <b>Baby Health Book</b> or <b>Both</b> in step 1 first.</p>

            <fieldset class="mamabe-path" id="path_pink" disabled hidden>
                <div class="alpro-field mamabe-mb">
                    <label for="edd">EDD <span class="mamabe-required">*</span> <span class="mamabe-hint">Jangkaan tarikh bersalin</span></label>
                    <input type="date" class="alpro-input" id="edd" name="edd" required min="<?php echo $eddMin; ?>" max="<?php echo $eddMax; ?>">
                </div>
                <label class="mamabe-upload-label">Attach pink book with clear Estimated Delivery Date (EDD) <span class="mamabe-required">*</span> <span class="mamabe-hint">Buku pink / Rekod Kesihatan Ibu</span></label>
                <p class="mamabe-help">A clear photo of the page that shows <b>your name</b>.</p>
                <?php $upload = array('id' => 'pink_book', 'required' => true); include dirname(__FILE__) . '/form_upload.php'; ?>
            </fieldset>

            <fieldset class="mamabe-path" id="path_baby" disabled hidden>
                <div class="alpro-field mamabe-mb">
                    <label for="child_name">Baby name <span class="mamabe-required">*</span> <span class="mamabe-hint">Nama bayi (as per IC)</span></label>
                    <input type="text" class="alpro-input" id="child_name" name="child_name" maxlength="100" required autocomplete="off" placeholder="As in the MyKid / birth certificate">
                </div>
                <div class="alpro-grid mamabe-mb">
                    <div class="alpro-field">
                        <label for="baby_dob">Baby date of birth <span class="mamabe-required">*</span> <span class="mamabe-hint">Tarikh lahir</span></label>
                        <input type="date" class="alpro-input" id="baby_dob" name="baby_dob" required min="<?php echo $dobMin; ?>" max="<?php echo $today; ?>">
                    </div>
                    <div class="alpro-field">
                        <label for="baby_mykid">Baby IC <span class="mamabe-required">*</span> <span class="mamabe-hint">No. MyKid</span></label>
                        <input type="text" class="alpro-input" id="baby_mykid" name="baby_mykid" required inputmode="numeric" pattern="[0-9]*" autocomplete="off" placeholder="e.g. 240101145678">
                    </div>
                </div>
                <label class="mamabe-upload-label">Attach baby health book with clear baby date of birth (D.O.B) <span class="mamabe-required">*</span> <span class="mamabe-hint">Buku kesihatan bayi</span></label>
                <p class="mamabe-help">A clear photo of the page that shows <b>your baby's name and your name</b>.</p>
                <?php $upload = array('id' => 'baby_book', 'required' => true); include dirname(__FILE__) . '/form_upload.php'; ?>

                <label class="mamabe-checkbox mamabe-mb">
                    <input type="checkbox" id="has_twin" name="has_twin" value="1">
                    <span>More baby? <span class="mamabe-hint">Ada bayi lagi?</span></span>
                </label>

                <fieldset class="mamabe-path" id="path_twin" disabled hidden>
                    <div class="mamabe-step"><span>&#128118;</span> Twin's details</div>
                    <div class="alpro-field mamabe-mb">
                        <label for="child_name_2">Twin's name <span class="mamabe-required">*</span> <span class="mamabe-hint">Nama bayi kembar (as per IC)</span></label>
                        <input type="text" class="alpro-input" id="child_name_2" name="child_name_2" maxlength="100" required autocomplete="off" placeholder="As in the MyKid / birth certificate">
                    </div>
                    <div class="alpro-grid mamabe-mb">
                        <div class="alpro-field">
                            <label for="baby_dob_2">Twin's date of birth <span class="mamabe-hint">Sama dengan bayi pertama</span></label>
                            <input type="date" class="alpro-input" id="baby_dob_2" name="baby_dob_2" readonly>
                        </div>
                        <div class="alpro-field">
                            <label for="baby_mykid_2">Twin's IC <span class="mamabe-required">*</span> <span class="mamabe-hint">No. MyKid</span></label>
                            <input type="text" class="alpro-input" id="baby_mykid_2" name="baby_mykid_2" required inputmode="numeric" pattern="[0-9]*" autocomplete="off" placeholder="e.g. 240101145679">
                        </div>
                    </div>
                    <label class="mamabe-upload-label">Attach twin's baby health book with clear baby date of birth (D.O.B) <span class="mamabe-required">*</span> <span class="mamabe-hint">Buku kesihatan bayi kembar</span></label>
                    <p class="mamabe-help">A clear photo of the page that shows <b>the twin's name and your name</b>.</p>
                    <?php $upload = array('id' => 'baby_book_2', 'required' => true); include dirname(__FILE__) . '/form_upload.php'; ?>
                </fieldset>
            </fieldset>

            <div class="mamabe-tip">&#128161; Make sure the names in the photos are <b>clear and easy to read</b> &mdash; good lighting, no glare, not blurry.</div>

            <!-- honeypot: leave empty -->
            <div class="mamabe-hp" aria-hidden="true">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div id="alertBox" class="alpro-alert alpro-danger mamabe-submit-alert" role="alert" hidden></div>
            <button type="submit" class="alpro-btn mamabe-submit" id="btnSubmit">Submit</button>
        </form>
    </div>

    <div class="mamabe-card mamabe-done" id="doneCard" hidden>
        <div class="mamabe-done-mark"><img src="images/mamabe-logo.png" alt="Alpro MamaBe" style="width:44px;height:auto;"></div>
        <h2>Thank you for joining Alpro MamaBe!</h2>
        <p>Your submission is under processing. Please check your email for status updates.</p>
        <p class="mamabe-done-note" id="doneNote"></p>
        <div class="mamabe-redeem-guide" id="redeemGuideBox" hidden>
            <p class="mamabe-redeem-guide-caption">How to redeem your voucher</p>
            <img id="redeemGuideImg" alt="How to redeem your voucher">
        </div>
        <button type="button" class="alpro-btn alpro-btn-grey" id="btnAgain">Submit another</button>
    </div>
</div>

<!-- click-to-enlarge preview -->
<div class="mamabe-lightbox" id="imgModal" hidden>
    <div class="mamabe-lightbox-box">
        <div class="mamabe-lightbox-head">
            <b>Selected photo</b>
            <button type="button" id="imgModalClose" class="alpro-btn alpro-btn-grey">Close</button>
        </div>
        <div class="mamabe-lightbox-body"><img id="imgModalImg" src="" alt=""></div>
    </div>
</div>

<script>
(function () {
    var API_URL = 'api/submit.php';
    var MAX_BYTES = 5 * 1024 * 1024;

    var form = document.getElementById('mamabeForm');
    var alertBox = document.getElementById('alertBox');
    var btn = document.getElementById('btnSubmit');
    var modal = document.getElementById('imgModal');
    var modalImg = document.getElementById('imgModalImg');
    function $(id) { return document.getElementById(id); }

    function showError(msg, focusEl) {
        alertBox.textContent = msg;
        alertBox.hidden = false;
        if (focusEl) { focusEl.focus(); focusEl.scrollIntoView({ block: 'center' }); }
    }
    function clearError() { alertBox.hidden = true; alertBox.textContent = ''; }
    function closeModal() { modal.hidden = true; modalImg.removeAttribute('src'); }

    /** One photo box (see form_upload.php): tap/drag to choose, preview inside, "View larger", "Remove" when optional. */
    function setupUpload(id) {
        var input = $(id), zone = $(id + '_zone'), empty = $(id + '_empty'), filled = $(id + '_filled');
        var img = $(id + '_img'), enlarge = $(id + '_enlarge'), remove = $(id + '_remove');
        var camInput = $(id + '_cam'), camBtn = $(id + '_camBtn');
        var url = null;

        function reset() {
            if (url) { URL.revokeObjectURL(url); url = null; }
            img.removeAttribute('src');
            filled.hidden = true;
            empty.hidden = false;
            enlarge.hidden = true;
            if (remove) remove.hidden = true;
            zone.classList.remove('has-file');
        }
        function chosen() {
            clearError();
            reset();
            var f = input.files[0];
            if (!f) return;
            if (f.type !== 'image/jpeg' && f.type !== 'image/png') { showError('Only JPG or PNG photos are accepted.'); input.value = ''; return; }
            if (f.size > MAX_BYTES) { showError('The photo is too large (max 5 MB).'); input.value = ''; return; }
            url = URL.createObjectURL(f);
            img.src = url;
            empty.hidden = true;
            filled.hidden = false;
            enlarge.hidden = false;
            if (remove) remove.hidden = false;
            zone.classList.add('has-file');
        }
        input.addEventListener('change', chosen);
        ['dragenter', 'dragover'].forEach(function (t) {
            zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.add('is-drag'); });
        });
        ['dragleave', 'drop'].forEach(function (t) {
            zone.addEventListener(t, function () { zone.classList.remove('is-drag'); });
        });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            if (e.dataTransfer && e.dataTransfer.files.length) { input.files = e.dataTransfer.files; chosen(); }
        });
        enlarge.addEventListener('click', function () { modalImg.src = url; modal.hidden = false; });
        if (remove) remove.addEventListener('click', function () { input.value = ''; reset(); });

        /** "Take a photo" button: opens the camera-only input, then copies its result onto the real input above
         *  (DataTransfer is the only way to set input.files from another File) so it goes through the exact same
         *  preview/validate/submit path as a gallery pick - no separate logic to keep in sync. */
        // The button sits inside the upload box's <label>: stop the label from also opening the gallery picker.
        function openCamera(e) { e.preventDefault(); e.stopPropagation(); camInput.click(); }
        camBtn.addEventListener('click', openCamera);
        camBtn.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') openCamera(e); });
        camInput.addEventListener('change', function () {
            var f = camInput.files[0];
            camInput.value = '';
            if (!f) return;
            var dt = new DataTransfer();
            dt.items.add(f);
            input.files = dt.files;
            chosen();
        });

        return { reset: reset, has: function () { return !!input.files[0]; }, zone: zone };
    }
    var pink = setupUpload('pink_book');
    var baby = setupUpload('baby_book');
    var twin = setupUpload('baby_book_2');

    /** Step 1 picks which step-3 section(s) show; a hidden one is disabled so its fields are not sent. "Both" shows both. */
    var TITLES = { pink: 'Pregnancy – pink book', baby: 'Baby – baby health book', both: 'Pregnancy & baby – both books' };
    function registerType() {
        var r = form.querySelector('input[name="register_type"]:checked');
        return r ? r.value : '';
    }
    function showPath() {
        var t = registerType();
        ['pink', 'baby'].forEach(function (k) {
            var fs = $('path_' + k);
            var show = t === k || t === 'both';
            fs.hidden = fs.disabled = !show;
        });
        if (t !== 'baby' && t !== 'both') { $('has_twin').checked = false; }
        showTwin();
        $('pathNone').hidden = t !== '';
        $('step3Title').textContent = TITLES[t] || 'Your book';
    }

    /** Twin's date of birth always mirrors the first baby's - that's what lets the backend auto-link them as twins. */
    function syncTwinDob() { $('baby_dob_2').value = $('baby_dob').value; }
    function showTwin() {
        var show = $('has_twin').checked;
        var fs = $('path_twin');
        fs.hidden = fs.disabled = !show;
        if (show) syncTwinDob();
    }
    $('has_twin').addEventListener('change', function () { clearError(); showTwin(); });
    $('baby_dob').addEventListener('change', syncTwinDob);
    Array.prototype.forEach.call(form.querySelectorAll('input[name="register_type"]'), function (r) {
        r.addEventListener('change', function () { clearError(); showPath(); });
    });

    /** Outlet list from odb (through api/outlets.php, sorted by state then name): pick the state (negeri), then its outlets. */
    var outletsByState = {};
    function addOption(sel, value, text) {
        var opt = document.createElement('option');
        opt.value = value;
        opt.textContent = text;
        sel.appendChild(opt);
    }
    function fillOutlets() {
        var sel = $('outlet_id'), list = outletsByState[$('outlet_state').value];
        sel.innerHTML = '';
        addOption(sel, '', list ? '— Choose —' : '— Choose a state first —');
        (list || []).forEach(function (o) { addOption(sel, o.id, o.name); });
        sel.disabled = !list;
    }
    function loadOutlets() {
        var st = $('outlet_state');
        fetch('api/outlets.php?v=<?php echo filemtime(dirname(__FILE__) . '/api/outlets.php'); ?>')   // new URL whenever the proxy changes, so a cached old list is never used
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success || !d.outlets.length) throw new Error();
                d.outlets.forEach(function (o) {
                    var s = o.state || 'Others';
                    (outletsByState[s] = outletsByState[s] || []).push(o);
                });
                st.innerHTML = '';
                addOption(st, '', '— Choose —');
                Object.keys(outletsByState).forEach(function (s) { addOption(st, s, s); });
                st.disabled = false;
            })
            .catch(function () {
                st.innerHTML = '<option value="">Could not load – please reload the page</option>';
            });
    }
    $('outlet_state').addEventListener('change', fillOutlets);
    loadOutlets();

    /**
     * Avida confinement center outlets (through api/outlets.php?type=avida) - shown instead of the normal State/Outlet
     * pair when "Confinement center" is picked below. Same <select name="outlet_id"> field either way: only one of
     * the two is ever enabled at a time, and a disabled <select> is simply left out of the submitted form data, so
     * register.php always receives exactly one outlet_id regardless of which picker was used.
     */
    var avidaOutlets = null;
    function loadAvidaOutlets() {
        var sel = $('avida_outlet_id');
        fetch('api/outlets.php?type=avida&v=<?php echo filemtime(dirname(__FILE__) . '/api/outlets.php'); ?>')
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) throw new Error();
                avidaOutlets = d.outlets;   // [] is a legitimate "none configured yet" answer, not a failure
                sel.innerHTML = '';
                if (avidaOutlets.length) {
                    addOption(sel, '', '— Choose —');
                    avidaOutlets.forEach(function (o) { addOption(sel, o.id, o.name); });
                    sel.disabled = false;
                } else {
                    addOption(sel, '', 'No Avida outlets set up yet');
                    sel.disabled = true;
                }
            })
            .catch(function () {
                avidaOutlets = null;   // keep null so switching chips back and forth retries, unlike a confirmed-empty list
                sel.innerHTML = '<option value="">Could not load – please reload the page</option>';
            });
    }

    /** Switches the outlet picker: "Confinement center" uses the Avida list, every other source uses the normal one. */
    function showOutletPicker() {
        var checked = form.querySelector('input[name="source"]:checked');
        var isConfinement = !!checked && checked.value === 'confinement_center';
        var normalBox = $('outlet_state').closest('.alpro-grid');
        var avidaBox  = $('avida_outlet_box');

        normalBox.hidden = isConfinement;
        $('outlet_state').disabled = isConfinement;
        $('outlet_id').disabled = isConfinement || !outletsByState[$('outlet_state').value];

        avidaBox.hidden = !isConfinement;
        $('avida_outlet_id').disabled = !isConfinement;
        if (isConfinement && avidaOutlets === null) loadAvidaOutlets();
    }

    /** "How do you know us?" chips, loaded from odb (through api/sources.php) - admin-editable on
     *  registration/settings.php, same live-fetch pattern as the outlet list above. If the live list can't be
     *  reached (e.g. odb's /api/mamabeSources.php entry point isn't deployed yet on a given environment), fall
     *  back to a fixed default rather than leaving mamas unable to register at all - this field is required. */
    var SOURCE_FALLBACK = [{ value: 'outlet', label: 'Outlet' }, { value: 'social_media', label: 'Social media' }];
    function renderChips(box, list) {
        box.innerHTML = '';
        list.forEach(function (s) {
            var label = document.createElement('label');
            label.className = 'mamabe-chip';
            var input = document.createElement('input');
            input.type = 'radio';
            input.name = 'source';
            input.value = s.value;
            var span = document.createElement('span');
            span.textContent = s.label;
            label.appendChild(input);
            label.appendChild(span);
            box.appendChild(label);
        });
    }
    function loadSources() {
        var box = $('source_chips');
        fetch('api/sources.php?v=<?php echo filemtime(dirname(__FILE__) . '/api/sources.php'); ?>')   // new URL whenever the proxy changes, so a cached old list is never used
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success || !d.sources.length) throw new Error();
                renderChips(box, d.sources);
            })
            .catch(function () { renderChips(box, SOURCE_FALLBACK); })
            .then(function () { box.addEventListener('change', function () { clearError(); showOutletPicker(); }); });
    }
    loadSources();

    $('imgModalClose').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });

    /**
     * Field markers: a missing / wrong field gets a red border and a "⚠ message" right under it, so the mama can see every
     * field she left out at once. A marker goes away as soon as she changes that field.
     */
    function markField(box, msg) {
        box.classList.add('mamabe-invalid');
        var note = document.createElement('div');
        note.className = 'mamabe-field-error';
        note.textContent = msg;
        box.insertAdjacentElement(box.classList.contains('alpro-field') ? 'beforeend' : 'afterend', note);
    }
    function unmarkField(box) {
        if (!box || !box.classList.contains('mamabe-invalid')) return;
        box.classList.remove('mamabe-invalid');
        var note = box.classList.contains('alpro-field') ? box.querySelector('.mamabe-field-error') : box.nextElementSibling;
        if (note && note.classList.contains('mamabe-field-error')) note.remove();
        if (!form.querySelector('.mamabe-invalid')) clearError();
    }
    function unmarkAll() {
        Array.prototype.forEach.call(form.querySelectorAll('.mamabe-invalid'), unmarkField);
    }
    // Phone and baby IC take digits only: anything else (letters, spaces, dashes) is dropped as it is typed or pasted.
    ['phone', 'baby_mykid', 'baby_mykid_2'].forEach(function (id) {
        var el = $(id);
        el.addEventListener('input', function () {
            var digits = el.value.replace(/\D/g, '').slice(0, 12);   // 601x mobile and MyKid are both at most 12 digits
            if (digits !== el.value) el.value = digits;
        });
    });
    // Mama IC: letters and digits only - no dashes, spaces or other punctuation, typed or pasted (a passport can
    // have letters, so this can't just be digits-only like phone/MyKid above).
    (function () {
        var el = $('ic');
        el.addEventListener('input', function () {
            var clean = el.value.replace(/[^A-Za-z0-9]/g, '');
            if (clean !== el.value) el.value = clean;
        });
    })();

    ['input', 'change'].forEach(function (t) {
        form.addEventListener(t, function (e) {
            var el = e.target;
            if (el.type === 'file') return unmarkField($(el.id + '_zone'));
            unmarkField(el.closest('.mamabe-invalid'));
        });
    });

    /** Quick checks in the browser; api/submit.php checks everything again. Marks every problem; false when there is one. */
    function validate() {
        var problems = [];
        function need(ok, box, msg) { if (!ok) problems.push([box, msg]); }
        function field(id) { return $(id).closest('.alpro-field'); }

        var type = registerType();
        var phone = $('phone').value.replace(/\D/g, '');
        need(type, form.querySelector('.mamabe-choices'), 'Choose Pregnancy Pink Book, Baby Health Book or Both.');
        need(form.querySelector('input[name="source"]:checked'), $('source_label').closest('.alpro-field'), 'Choose how you know about us.');
        need($('mother_name').value.trim(), field('mother_name'), 'Enter your name as in your IC.');
        var ic = $('ic').value.replace(/[^A-Za-z0-9]/g, '');
        need(ic.length >= 6 && ic.length <= 14, field('ic'), ic ? 'Enter a valid IC number (12 digits) or passport number.' : 'Enter your IC number.');
        need(/^(01\d{8,9}|601\d{8,9})$/.test(phone), field('phone'), phone ? 'Enter a valid mobile number, e.g. 0123456789.' : 'Enter your mobile number.');
        var email = $('email').value.trim();
        need(/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email), field('email'), email ? 'Enter a valid email address.' : 'Enter your email address - this is where your voucher will be sent.');
        need($('language').value, field('language'), 'Choose your preferred language.');
        var sourceChecked = form.querySelector('input[name="source"]:checked');
        if (sourceChecked && sourceChecked.value === 'confinement_center') {
            need($('avida_outlet_id').value, field('avida_outlet_id'), 'Choose your confinement center.');
        } else {
            need($('outlet_state').value, field('outlet_state'), 'Choose your state.');
            if ($('outlet_state').value) need($('outlet_id').value, field('outlet_id'), 'Choose an outlet.');
        }
        if (type === 'pink' || type === 'both') {
            need($('edd').value, field('edd'), 'Enter your expected delivery date (EDD).');
            need(pink.has(), pink.zone, 'Add a photo of your pink book.');
        }
        if (type === 'baby' || type === 'both') {
            var mykid = $('baby_mykid').value.replace(/\D/g, '');
            need($('child_name').value.trim(), field('child_name'), "Enter your baby's name as in the MyKid / birth certificate.");
            need($('baby_dob').value, field('baby_dob'), "Enter your baby's date of birth.");
            need(mykid.length === 12, field('baby_mykid'), mykid ? 'The MyKid number must be 12 digits.' : "Enter your baby's IC / MyKid number.");
            need(baby.has(), baby.zone, 'Add a photo of the baby health book.');

            if ($('has_twin').checked) {
                var mykid2 = $('baby_mykid_2').value.replace(/\D/g, '');
                need($('child_name_2').value.trim(), field('child_name_2'), "Enter the twin's name as in the MyKid / birth certificate.");
                need(mykid2.length === 12, field('baby_mykid_2'), mykid2 ? "The twin's MyKid number must be 12 digits." : "Enter the twin's IC / MyKid number.");
                need(!mykid2 || mykid2 !== mykid, field('baby_mykid_2'), "The twin's MyKid number must be different from the first baby's.");
                need(twin.has(), twin.zone, "Add a photo of the twin's baby health book.");
            }
        }

        unmarkAll();
        if (!problems.length) return true;
        problems.forEach(function (p) { markField(p[0], p[1]); });
        showError(problems.length === 1
            ? problems[0][1]
            : 'Please complete the ' + problems.length + ' fields marked in red above.');
        var first = problems[0][0];
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        var focusable = first.querySelector('input:not([type="file"]), select, textarea');
        if (focusable) focusable.focus({ preventScroll: true });
        return false;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearError();
        if (!validate()) return;

        btn.disabled = true;
        btn.textContent = 'Submitting…';
        fetch(API_URL, { method: 'POST', body: new FormData(form) })
            .then(function (r) { return r.json().catch(function () { return { success: false, error: 'Unexpected response. Please try again.' }; }); })
            .then(function (d) {
                if (d.success) {
                    $('formCard').hidden = true;
                    $('doneCard').hidden = false;
                    $('doneNote').textContent = d.baby_only
                        ? "You're already registered with us, so we've only added your baby's details this time."
                        : d.existing_mama
                            ? "You're already registered with us, so we've updated your details - no need to register again."
                            : 'Once your details are verified, your MamaBe voucher will be sent to the email address you provided.';
                    // Loaded only now (not on page load) so every visitor doesn't cost odb a proxy call - only those
                    // who actually submit. Hidden by default; shown only once the image has actually loaded (odb may
                    // have no guide image configured, in which case api/redeem-guide.php 404s and the box stays hidden).
                    var guideBox = $('redeemGuideBox'), guideImg = $('redeemGuideImg');
                    guideImg.onload  = function () { guideBox.hidden = false; };
                    guideImg.onerror = function () { guideBox.hidden = true; };
                    guideImg.src = 'api/redeem-guide.php';
                    window.scrollTo(0, 0);
                } else {
                    showError(d.error || 'Something went wrong. Please try again.');
                }
            })
            .catch(function () { showError('Could not reach the server. Please try again.'); })
            .then(function () {
                btn.disabled = false;
                btn.textContent = 'Submit';
            });
    });

    $('btnAgain').addEventListener('click', function () {
        form.reset();
        pink.reset();
        baby.reset();
        twin.reset();
        showPath();
        fillOutlets();
        unmarkAll();
        clearError();
        $('doneCard').hidden = true;
        $('formCard').hidden = false;
    });
})();
</script>
</body>
</html>
