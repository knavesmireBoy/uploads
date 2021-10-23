<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html>
<head>
	<meta content="text/html; charset=utf-8" http-equiv="content-type">
	<link href="../css/lofi.css" media="all" rel="stylesheet" type="text/css">
	<link href="../css/sap.css" media="all" rel="stylesheet" type="text/css">
	<title>Manage Users</title>
</head>
<body>
	<div>
		<h1><?php echo $manage; ?></h1>
		<p><a href="?add">Add New User</a></p>
		<form action="" id="userform" method="post" name="userform">
			<ul>
				<li><label for="user">User:</label> <select id="user" name="user">
					<option value="">
						Select one
					</option>
					<optgroup label="clients">
						<?php foreach ($client as $k => $v): ?>
						<option value="<?php htmlout($k); ?>">
							<?php htmlout($v); ?>
						</option><?php endforeach; ?>
					</optgroup>
					<optgroup label="users">
						<?php  foreach ($users as $k => $v): ?>
						<option value="<?php htmlout($k); ?>">
							<?php htmlout($v); ?>
						</option><?php endforeach; ?>
					</optgroup>
				</select><input name="act" type="submit" value="Choose"></li>
			</ul>
		</form>
		<p><a href="..">Return to uploads</a></p>
	</div>
</body>
</html>