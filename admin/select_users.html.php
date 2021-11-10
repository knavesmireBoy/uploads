<!DOCTYPE html>
<html>
    <head>
	<meta content="text/html; charset=utf-8" http-equiv="content-type">
	<link href="../css/lofi.css" media="all" rel="stylesheet" type="text/css">
	<title>Manage Users</title>
</head>
    <body>
        <div>
            <?php
            if(isset($_COOKIE['success'])) { echo "<h2>{$_COOKIE['success']}</h2>"; } ?>
            <h1><?php echo $manage; ?></h1>
            <p><a href="?add">Add New User</a></p>
            <form action="" id="userform" method="post" name="userform">
                <ul>
                    <li><label for="user">User:</label> <select id="user" name="user">
                        <option value="">Select one</option>
                        <?php 
                        echo $doOpt('client');
                        foreach ($client as $k => $v){
                            echo $doSelected($k, $v);
                        }
                        echo $doOptEnd();
                        echo $doOpt('users');
                        foreach ($users as $k => $v) {
                            echo $doSelected($k, $v);
                        }
                        echo $doOptEnd(); ?>
                        </select>
                        <input name="act" type="submit" value="Choose">
                    </li>
                </ul>
            </form>
            <p><a href="..">Return to uploads</a></p>
            <?php if($isAdmin()) { ?>
            <p><a href="../clients/">Edit Clients</a></p>
            <?php } ?>
        </div>
    </body>
</html>