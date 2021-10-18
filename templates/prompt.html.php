<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';?>

<?php 
/*NOT REQUIRED : WAS USED TO PROVIDE A CLIENT LIST DROP DOWN MENU
FOR PRE-SELECTING A DOMAIN PRIOR TO ADDING A NEW USER TO AN EXISITING CLIENT
NOT REALLY USED IN PRACTICE*/
if(isset($clientlist)):?>
<form action="." method="post" name="clientform">
<div><label for="employer">If existing client:</label>
<select name="employer" id="employer">
<option value="">Set email domain</option>
<?php foreach ($clientlist as  $i => $client): ?>
<option value="<?php echo $i; ?>">
<?php htmlout($client);?></option>
<?php endforeach; ?>
</select>
<input type="submit" name="action" value="continue"/></div>
</form> 


<?php elseif(!isset($clientlist) and !isset($confirmed)):?>

<form action="." method="post" name="choice">
<input type="hidden" name="id" value="<?php echo $id; ?>"/>
<p><?php echo $prompt; ?></p>
<p><label>Yes<input type="radio" name="<?php echo $call; ?>" value="Yes"/></label></p>
<p><label>No<input type="radio" name="<?php echo $call; ?>" value="No"/></label></p>
<input type="submit" value = "Submit"/>
</form>
<?php endif;  ?>


<?php if(isset($confirmed)):?>

<form action="." method="post" name="deletions" class="block">
<input type="hidden" name="id" value="<?php echo $id; ?>"/>
<p><label for="ext_nwf">Delete this file only<input type="radio" id="ext_nwf" name="extent" value="f"/></label></p>	
<p><label for="ext_nwu">Delete all files for this user<input type="radio" id="ext_nwu" name="extent" value="u"/></label></p>
<?php if($priv == 'Admin'):?>
<p><label for="ext_nwc">Delete all files for this client<input type="radio" id="ext_nwc" name="extent" value="c"/></label></p>
<?php endif; ?>
<input type="hidden" name="<?php echo $confirmed; ?>" value="remove"/>
<input type="submit" value="Remove Files"/>
</form>
<?php endif; ?>