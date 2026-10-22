<?php
/**
 * One photo box for form.php (included twice: pink book, baby book). Expects $upload = array('id' => ..., 'required' => bool).
 * The real file input is visually hidden; the label is the tap target. form.php's setupUpload() wires it up.
 */
if (!isset($upload['id'])) exit;   // only ever included by form.php
$uid = $upload['id'];
?>
<input type="file" id="<?php echo $uid; ?>" name="<?php echo $uid; ?>" accept="image/*" class="mamabe-file"<?php echo $upload['required'] ? ' required' : ''; ?>>
<!-- Separate camera-only input (capture="environment" forces the camera app to open directly - relying on the
     main input's bare accept="image/*" to also offer a camera option is NOT reliable on Android: several
     Chrome/Android versions open the Photos picker straight away with no camera tile at all. iOS does not need
     this - its native picker already offers Take Photo / Photo Library either way - but this input works there
     too. Never submitted directly (no name); its file is copied onto the real input above via DataTransfer so
     one shared preview/validation/submit path (setupUpload() in form.php) handles both. It is opened by the
     "Take a photo" button INSIDE the one upload box; the rest of the box (a label) opens the gallery. The user
     compared a separate button / this / a tap-then-choose pop-up and picked this one (Oct 2026). -->
<input type="file" id="<?php echo $uid; ?>_cam" accept="image/*" capture="environment" class="mamabe-file" tabindex="-1" aria-hidden="true">
<label for="<?php echo $uid; ?>" class="mamabe-drop" id="<?php echo $uid; ?>_zone" tabindex="-1">
    <span class="mamabe-drop-empty" id="<?php echo $uid; ?>_empty">
        <span class="mamabe-drop-icon">&#128196;</span>
        <b>Add a clear photo</b>
        <small>JPG or PNG, up to 5 MB</small>
        <span class="mamabe-drop-choices">
            <span class="mamabe-drop-choice" id="<?php echo $uid; ?>_camBtn" role="button" tabindex="0">&#128247; Take a photo</span>
            <span class="mamabe-drop-choice mamabe-drop-choice-alt">&#128444; Gallery</span>
        </span>
    </span>
    <span class="mamabe-drop-filled" id="<?php echo $uid; ?>_filled" hidden>
        <img id="<?php echo $uid; ?>_img" alt="Selected photo">
        <span class="mamabe-drop-change">&#8635; Change photo</span>
    </span>
</label>
<div class="mamabe-upload-actions">
    <button type="button" class="mamabe-link" id="<?php echo $uid; ?>_enlarge" hidden>View larger</button>
    <?php if (!$upload['required']): ?>
    <button type="button" class="mamabe-link mamabe-link-muted" id="<?php echo $uid; ?>_remove" hidden>Remove</button>
    <?php endif; ?>
</div>
