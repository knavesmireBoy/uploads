<form action="." method="post" name="updateFileInfo">
<?php if($_REQUEST['swap'] == 'No'): ?>
	<div>
		<label for="filename">Name: <input id="filename" type="text" name="filename" value="<?php htmlout($filename); ?>"/></label></div>
	<div>
		<label for="description">Description: <input id="description" type="text" name="description" value="<?php htmlout($diz); ?>"/></label></div>
	<div>
	<?php $update = true;
        endif; 
        //allows Admin to associate a single USER to a single file, USER may/may not belong to a client
if(!$extent && $priv === 'Admin') : ?>
<label for="user">User:&nbsp;</label>
<select id="user" name="user">
<option value="">Select one like</option>
<?php foreach ($colleagues as $k => $v): ?>
<option value="<?php htmlout($k); ?>"
    <?php if($k == $userid){ ?> selected='selected' <?php }; ?>>
    <?php htmlout($v); ?></option>
<?php endforeach; ?>
</select></div>
<?php
    //has colleagues..
    elseif($extent > 1) : ?>
    <?php if(isset($answer) && $answer == 'Yes') : ?>
    <p>Select checkbox to re-assign all client files to selected user. Leave unchecked to simply swap all instances of original owner to selected user</p>
    <p><label for="blanket">swap/re-assign</label><input type = "checkbox" name="blanket" id="blanket"></p>
    <?php endif; ?>
		<div>
		<label for="colleagues">Colleagues:&nbsp;</label><select id="colleagues" name="colleagues"><option value="">Select one</option><?php foreach ($colleagues as $k => $v): ?>
			<option value="<?php htmlout($k); ?>"
                    <?php if($k == $userid): ?>
                selected='selected'<?php endif; ?>>
                <?php htmlout($v); ?></option>
            <?php endforeach; ?>
		</select></div>
		<?php $update = true; endif;  
    if(isset($update)) { ?>
	<div>
		<input type="hidden" name="fileid" value="<?php htmlout($id); ?>"/>
		<input type="hidden" name="answer" value="<?php htmlout($answer); ?>"/>
		<input type="hidden" name="update" value="<?php htmlout($userid); ?>"/>
		<input type="submit" value="<?php htmlout($button); ?>"/></div>
    <?php } ?>
</form>