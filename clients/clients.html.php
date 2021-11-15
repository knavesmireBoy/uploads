<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Manage Clients</title>
        <link href="../css/lofi.css" type="text/css" rel="stylesheet" media="all"/>
    </head>
    <body>
        <div>
            <h1>Manage Clients</h1>
            <p><a href="./?add">Add New Client</a></p>
            <?php if (!isset($_POST['act']) && !isset($prompt)): ?>
            <form action="" method="post" name="clientsform">
                <label for="the_client">Client: </label>
                <select name="client" id="the_client"><option value="">Select one</option>
                    <?php foreach ($clients as $k => $v): ?>
                    <option value="<?php echo $k; ?>">
                        <?php htmlout($v) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" name="act" value="Choose"/>
            </form>
            <?php  elseif(isset($_POST['act']) and $_POST['act'] == 'Choose'):

            foreach ($clients as $k => $v):?>
            <form action="" method="post" name="editclientform" class="clientlist">
                <ul><li class="name"><label><?php htmlout($v); ?></label></li>
                    <li><label>edit<input type="radio" name="action" value="Edit"/></label>
                        <label>delete<input type="radio" name="action" value="Delete"/></label></li>
                    <li>
                        <input type="hidden" name="id" value="<?php htmlout($k); ?>"/>
                        <input type="submit" value = "Submit"/>
                    </li>
                </ul></form>
            <?php
            endforeach;
            endif;
            ?>
             <?php if(isset($prompt)) :
            include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/prompt.html.php';
            endif; ?>
            <p><a href="../admin/">Return to users</a></p>
            <p><a href="..">Return to uploads</a></p>
            <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/logout.inc.html.php';
            exit(); 
            ?>
        </div>
    </body>
</html>