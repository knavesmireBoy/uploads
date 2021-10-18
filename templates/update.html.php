<form action="." method="post" name="updateFileInfo">
<?php if($_POST['swap'] == 'No'): ?>
	<div>
		<label for="filename">Name: <input id="filename" type="text" name="filename" value="<?php htmlout($filename); ?>"/></label></div>
	<div>
		<label for="description">Description: <input id="description" type="text" name="description" value="<?php htmlout($diz); ?>"/></label></div>
	<div>
	<?php endif; ?>
	<?php if(count($colleagues) == 0 && $priv == 'Admin') : ?>
	<label for="user">User:&nbsp;</label><select id="user" name="user">
<option value="">Select one like</option><?php foreach ($all_users as $k => $v): ?>
<option value="<?php htmlout($k); ?>"
    <?php if($k == $userid) : ?>
        selected='selected'<?php endif; ?>>
    <?php htmlout($v); ?></option>
        <?php endforeach; ?>
</select></div>
<?php elseif(($priv == 'Admin') && count($colleagues) > 1) : ?>
		<div>
		<label for="colleagues">Colleagues:&nbsp;</label> <select id="colleagues" name="colleagues"><option value="">Select one</option><?php foreach ($colleagues as $k => $v): ?>
			<option value="<?php htmlout($k); ?>"
                    <?php if($k == $userid): ?>
                selected='selected'<?php endif; ?>>
                <?php htmlout($v); ?></option>
            <?php endforeach; ?>
		</select></div>
		<?php endif; ?>
	<div>
		<input type="hidden" name="fileid" value="<?php htmlout($id); ?>"/>
		<input type="hidden" name="answer" value="<?php htmlout($answer); ?>"/>
		<input type="hidden" name="original" value="<?php htmlout($userid); ?>"/>
		<input type="submit" value="<?php htmlout($button); ?>"/></div>
</form>