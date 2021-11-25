<?php
$confirmed = ($call === 'confirmed') ? 'confirmed' : '';
if(!isset($prompt)){
    $prompt = ($call === 'confirm') ? "Choose <b>YES</b> for deletion options and <b>NO</b> for editing options" : "Select the extent of deletions:";
}
$delete = $isPriv() ? "Delete all files for this client" : "Delete all files your files";
if(!$confirmed): ?>
<form action="" method="post" name="choice" class="prompt">
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <p class="info"><?php echo $prompt; ?></p>
    <p><label>Yes<input type="radio" name="<?php echo $call; ?>" value="Yes"/></label></p>
    <p><label>No<input type="radio" name="<?php echo $call; ?>" value="No"/></label></p>
    <input type="submit" value = "<?php echo $submit; ?>" >
</form>
<?php else: ?>
<p class="info"><?php echo $prompt; ?></p>
<form action="." method="post" name="deletions" class="commit confirm">
    <div class="fieldset">
    <p><label for="ext_nwf">Delete this file only<input type="radio" id="ext_nwf" name="extent" value="f"/></label></p>
    <?php if(true): ?>
    <p><label for="ext_nwu">Delete all files for this user<input type="radio" id="ext_nwu" name="extent" value="u"/></label></p>
    <p><label for="ext_nwc"><?php echo $delete; ?><input type="radio" id="ext_nwc" name="extent" value="c"/></label></p>
        <?php endif; ?>
    <input type="hidden" name="<?php echo $confirmed; ?>" value="remove"/>
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <input type="submit" value="delete"/></div>
</form>
<?php endif;