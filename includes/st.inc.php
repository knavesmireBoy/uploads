<?php$linkst = mysql_connect('localhost', 'roo', 'krauq');if (!$linkst ){$error = 'Unable to connect to the database server.'. mysql_error();include '../error.html.php';exit();}if (!mysql_select_db('storm', $linkst )){$error = 'Unable to locate the storm database.' . mysql_error();include '../error.html.php';exit();}}?>