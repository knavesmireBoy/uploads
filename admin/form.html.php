<!DOCTYPE html>
<?php
$error = isset($_GET['error']) ? $_GET['error'] : $error;
$warning = isset($_GET['warning']) ? $_GET['warning'] : $warning;
?>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Edit user details</title>
        <link href="../css/lofi.css" type="text/css" rel="stylesheet" media="all"/>
    </head>
    <body><div>
        <h1><?php htmlout($pagetitle); ?></h1>
        <form action="?<?php htmlout($action); ?>" method="post" name="usersform" class="<?php echo $warning; ?>">
            <fieldset><legend><?php echo $error; ?></legend>
                <ul>
                <li>
                    <label for="name">Name:</label><input id="name" name="name" value="<?php htmlout($name); ?>" size="32" /></li>
			<li><label for="email">Email:</label><input id="email" placeholder="eg@eg.com" name="email" value="<?php htmlout($email); ?>" size="32"/></li>
                <?php if(isset($pwderror)) : ?>
                <li><?php echo $pwderror; endif; ?></li>
                <li><label for="password">Set password:</label><input id="password" type="password" name="password"/><input type="hidden" name="employer" value="<?php if(isset($cid)) { htmlout($cid); } ?>" size="32"/></li>
            </ul>
                </fieldset>
            <?php if(!$isSingleUser()) : ?>
            <fieldset>
                <legend>Roles:</legend> <?php for ($i = 0; $i < count($roles); $i++): ?>
				<div>
                    <label for="role<?php echo $i; ?>">
                        <input id="role<?php echo $i; ?>" type="checkbox" name="roles[]" value="<?php htmlout($roles[$i]['id']); ?>" <?php if ($roles[$i]['selected']) { echo 'checked="checked"'; } ?>/>
                        <?php htmlout($roles[$i]['id']); ?></label>: <?php htmlout($roles[$i]['description']); ?></div>
                <?php endfor; ?>
            </fieldset>
            <?php endif;
            if($isPriv()) { ?>
            <div><label for="employer">Company: </label>
                <select name="employer" id="employer">
                    <option value="">Assign to Client?</option>
                    <?php foreach ($clientlist as  $i => $client): ?>
                    <option value="<?php echo $i; ?>" <?php if(isset($job) && $job == $i) echo 'selected="selected"' ?>>
                        <?php htmlout($client);?></option>
                    <?php endforeach; ?>
                </select></div>
            <?php } ?>
            <div><input type="hidden" name="id" value="<?php htmlout($id); ?>"/><input type="submit" value="<?php htmlout($button); ?>"/></div></form>
        <?php if($isPriv()) { ?>
        <p><a href="../clients/">Edit Clients</a></p>
        <?php } ?>
        <p><a href="<?php echo $data['ret']; ?>">Return to <?php echo $data['page']; ?></a></p>
        </div></body>
</html>