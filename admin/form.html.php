<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';?>
<!DOCTYPE html>
<html>
<head>
	<meta content="text/html; charset=utf-8" http-equiv="content-type">
	<link href="../css/lofi.css" media="all" rel="stylesheet" type="text/css">
	<title></title>
</head>
<body>
	<div>
		<h1><?php htmlout($pagetitle); ?></h1>
		<form action="?%3C?php%20htmlout($action);%20?%3E" class="editclient" id="usersform" method="post" name="usersform">
			<ul>
				<li><label for="name">Name:</label><input id="name" name="name" size="32" type="text" value="<?php htmlout($name); ?>"></li>
				<li><label for="email">Email:</label><input id="email" name="email" size="32" value="<?php htmlout($email); ?>"></li>
				<li><label for="password">Set password:</label><input id="password" name="password" type="password"><input name="employer" size="32" type="hidden" value="<?php if(isset($cid)) {htmlout($cid);} ?>"></li>
			</ul><?php if ($priv =='Admin') : ?>
			<fieldset>
				<legend>Roles:</legend> <?php for ($i = 0; $i < count($roles); $i++): ?>
				<div>
					<label for="role&lt;?php echo $i; ?&gt;"><input id="role&lt;?php echo $i; ?&gt;" name="roles[]" type="checkbox" value="<?php htmlout($roles[$i]['id']); ?>"> <?php htmlout($roles[$i]['id']); ?></label>: <?php htmlout($roles[$i]['description']); ?>
				</div><?php endfor; ?>
			</fieldset>
			<div>
				<label for="employer">Company:</label> <select id="employer" name="employer">
					<option value="">
						Assign to Client?
					</option><?php foreach ($clientlist as  $i => $client): ?>
					<option value="<?php echo $i; ?>">
						<?php htmlout($client);?>
					</option><?php endforeach; ?>
				</select>
			</div><?php endif; ?>
			<div>
				<input name="id" type="hidden" value="<?php htmlout($id); ?>"><input type="submit" value="<?php htmlout($button); ?>">
			</div>
		</form>
		<p><a href=".">Return to User List</a></p><!--<p><a href="<?php //$_SERVER['DOCUMENT_ROOT'] . '/admin/index.php';?>">Return to User List</a></p>-->
		<?php if ($priv =='Admin') : ?>
		<p><a href="../clients/">Edit Clients</a></p><!--<p><a href="<?php //$_SERVER['DOCUMENT_ROOT'] . '/clients/';?>">Edit Clients</a></p>-->
		<?php endif;  ?>
	</div>
</body>
</html>