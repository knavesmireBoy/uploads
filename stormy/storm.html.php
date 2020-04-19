<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Bluestorm</title>
		<style type="text/css" media="screen"><!--
table { border: solid 1px }
td { background-color: orange }
--></style>
	</head>
<body>
<form action="" method="post">
<input type="submit" name="action" value="split"/>
<?php
foreach($users as $user):
$char = str_split($user['name']);
$length=count($char);?>
<?php
for($i = 0; $i < $length; $i++): ?>
<p>
<input id="chars<?php echo $char[$i];?>" type="hidden" name="<?php echo $user['id'];?>[]" value="<?php echo $char[$i];?>" size="1"/>
<?php endfor; ?>
<?php  endforeach;?>
</p></form>
<table><tr>
<?php
foreach($users as $user):?>
<td><?php echo $user['id'];?><td><?php echo $user['name'];?></td>
<?php foreach($words as $word):
if($word['id']!=$user['id']) continue; ?>
<td><?php echo $word['letter']; ?></td>
<?php  endforeach;
?>
</tr>
<?php  endforeach;?>
</table>




</body>
</html>

