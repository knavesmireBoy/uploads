<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="content-type">
        <link href="../css/lofi.css" media="all" rel="stylesheet" type="text/css">
        <link href="../css/sap.css" media="all" rel="stylesheet" type="text/css">
        <title>Manage Users innit</title>
</head>
    <body>
        <div>
            <h1><?php echo $manage; ?></h1><?php if ($priv =='Admin') : ?>
            <p><a href="?add">Add New User</a></p>
            <?php endif; ?><?php if ($priv =='Admin' && !isset($_POST['act'])): ?>
            <form action="" id="userform" method="post" name="userform">
                <ul>
                    <li><label for="user">User:</label>
                        <select id="user" name="user">
                            <option value="">Any User</option>
                            <?php 
                            echo $doOpt('clients');
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
                        <input name="act" type="submit" value="Choose"></li>
                </ul>
            </form>
            <?php 
            elseif($priv == 'Client' || (isset($_POST['act']) and $_POST['act'] == 'Choose')):
            foreach ($users as $k => $v):?>
            <form action="" class="clientlist" id="edituserform" method="post" name="edituserform">
                <ul>
                    <li class="name"><label><?php htmlout($v); ?></label></li>
                    <li><label>edit<input name="action" type="radio" value="Edit"></label>
                        <label>delete<input name="action" type="radio" value="Delete"></label></li>
                    <li style="list-style: none"><input name="id" type="hidden" value="<?php echo $k ;?>"></li>
                    <li><input type="submit" value="submit"></li>
                </ul>
            </form>
            <?php
            endforeach;
            endif;
            ?>
            <p><a href="..">Return to uploads</a></p>
            <?php
            if (isset($prompt)) {
                include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/prompt.html.php';
            }
            ?>
        </div>
    </body>
</html>