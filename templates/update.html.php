<?php
$error = isset($_GET['error']) ? $_GET['error'] : 'Edit file details';
$warning = isset($_GET['warning']) ? $_GET['warning'] : $warning;
?>

<form action="." method="post" name="updateFileInfo" class="<?php echo $warning; ?>">
<?php
    
    if($_REQUEST['swap'] == 'No'): ?>
    <fieldset><legend><?php echo $error; ?></legend>
        <div><label for="filename">Name: <input id="filename" type="text" name="filename" value="<?php htmlout($filename); ?>"/></label></div><div><label for="desc">Description: <input id="desc" type="text" name="desc" value="<?php htmlout($diz); ?>"/></label></div><div>
	<?php endif; 
    //allows Admin to associate a single USER to a single file, USER may optionally belong to a client
    if(($extent < 1) && ($priv === 'Admin')) : ?>
    <div><label for="user">User:&nbsp;</label>
        <select id="user" name="user">
            <?php
            $update = true;
            endif;
            if($extent > 1) : 
            if(isset($answer) && $answer == 'Yes') : ?>
            <p>Select checkbox to re-assign all client files to selected user. Leave unchecked to simply swap all instances of original owner to selected user</p>
            <p><label for="blanket">swap/re-assign</label><input type = "checkbox" name="blanket" id="blanket"></p>
            <?php endif; ?>
            <div><label for="colleagues">Colleagues:&nbsp;</label>
                <select id="colleagues" name="colleagues">
                    <?php $update = true;
                    endif;
                    if(isset($update)): ?>
                    <option value="">Select one</option>
                    <?php
                    foreach($colleagues as $k => $v){
                        echo $doSelected($k, $v);//htmlout messes up here
                    } ?>
                </select></div>
            <?php endif; ?>
            <div>
                <input type="hidden" name="fileid" value="<?php htmlout($id); ?>"/>
                <input type="hidden" name="answer" value="<?php htmlout($answer); ?>"/>
                <input type="hidden" name="update" value="<?php htmlout($userid); ?>"/>
                <input type="submit" value="<?php htmlout($button); ?>"/></div>
            <?php echo '</fieldset></form>';