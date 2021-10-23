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
		<?php foreach ($users as $k => $v): ?>
		<form action="" class="clientlist" id="edituserform" method="post" name="edituserform">
			<ul>
				<li class="name"><label><?php htmlout($v); ?></label></li>
				<li><label>edit<input name="action" type="radio" value="Edit"></label><label>delete<input name="action" type="radio" value="Delete"></label></li>
				<li><input name="id" type="hidden" value="<?php echo $k ;?>"></li>
				<li><input type="submit" value="submit"></li>
			</ul>
		</form>
        <?php endforeach; ?>
		<p><a href="..">Return to uploads</a></p>
		<?php
		if (isset($prompt)) {
            //exit('nnn');
		include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/prompt.html.php';
		}
		?>
	</div>
</body>
</html>