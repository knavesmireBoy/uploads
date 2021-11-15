<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Edit user list</title>
        <link href="../css/lofi.css" type="text/css" rel="stylesheet" media="all"/>
    </head>
<body>
	<div class="clientlist" >
		<h1><?php echo $data['manage']; ?></h1>
        <!-- A flag, clients forego select page which includes an "add user link" so we put that here for them-->
        <?php if(isset($data['client'])): ?>
        <p><a href="?add">Add New User</a></p>
        <?php endif; ?>
		<?php if(isset($data['users'])): foreach ($data['users'] as $k => $v): ?>
		<form action="?" method="post" name="edituserform">
			<ul>
				<li class="name"><label><?php htmlout($v); ?></label></li>
				<li><label>edit<input name="action" type="radio" value="Edit"></label>
                    <label>delete<input name="action" type="radio" value="Delete"></label></li>
				<li><input name="id" type="hidden" value="<?php echo $k ;?>"></li>
				<li><input type="submit" value="submit"></li>
			</ul>
		</form>
        <?php endforeach;?>
        <p><a href="<?php echo $data['ret']; ?>">Return to <?php echo $data['page']; ?></a></p>
		<?php
        endif;
		if (isset($prompt)) {
            include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/prompt.html.php';
        }
		?>
	</div>
</body>
</html>