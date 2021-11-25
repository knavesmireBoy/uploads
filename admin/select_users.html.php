<!DOCTYPE html>
<html lang="en" class="no-js">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Manage Users</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="../css/lofi.css" media="all" rel="stylesheet" type="text/css">
</head>
    <body>
        <div>            
            <h1><?php echo $manage; ?></h1>
            <?php
            if(isset($_GET['extent'])){
                echo "<p class='info'>{$_GET['extent']}</p>";
            }
        if(isset($_COOKIE['success'])) { echo "<h2>{$_COOKIE['success']}</h2>"; } ?>
            <p><a href="?add">Add New User</a></p>
            <form action="" id="userform" method="post" name="userform" class="select">
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
                        <input name="act" type="submit" value="choose">
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