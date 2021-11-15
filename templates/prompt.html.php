<?php
$confirmed = ($call === 'confirmed') ? 'confirmed' : '';
if(!isset($prompt)){
    $prompt = ($call === 'confirm') ? "Choose <b>YES</b> for deletion options and <b>NO</b> for editing options" : "Select the extent of deletions:";
}
if(!$confirmed): ?>
<form action="" method="post" name="choice" class="block">
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <p class="info"><?php echo $prompt; ?></p>
    <p><label>Yes<input type="radio" name="<?php echo $call; ?>" value="Yes"/></label></p>
    <p><label>No<input type="radio" name="<?php echo $call; ?>" value="No"/></label></p>
    <input type="submit" value = "<?php echo $submit; ?>" >
</form>
<?php else: ?>
<p class="info"><?php echo $prompt; ?></p>
<form action="." method="post" name="deletions" class="commit">
    <div class="fieldset">
    <p><label for="ext_nwf" title="<?php echo $extent; ?>">Delete this file only<input type="radio" id="ext_nwf" name="extent" value="f"/></label></p>
    <?php if($extent): ?>
    <p><label for="ext_nwu">Delete all files for this user<input type="radio" id="ext_nwu" name="extent" value="u"/></label></p>
    <?php endif;
    if($isPriv() && $extent): ?>
    <p><label for="ext_nwc">Delete all files for this client<input type="radio" id="ext_nwc" name="extent" value="c"/></label></p>
    <?php endif; ?>
    <input type="hidden" name="<?php echo $confirmed; ?>" value="remove"/>
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <input type="submit" value="delete"/></div>
</form>
<?php endif; ?>