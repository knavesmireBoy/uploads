<!DOCTYPE html>
<?php
$error = isset($_GET['error']) ? $_GET['error'] : $error;
$warning = isset($_GET['warning']) ? $_GET['warning'] : $warning;
?>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Edit Client</title>
        <link href="../css/lofi.css" type="text/css" rel="stylesheet" media="all"/>
    </head>
    <body>
        <div>
            <h1><?php htmlout($pagetitle) ; ?></h1>
            <form action="?<?php htmlout($action); ?>" method="post" name="clientform" class="<?php echo $warning; ?>">
                <fieldset><legend><?php echo $error; ?></legend>
                <div>
                    <label for="the_name">Name: <input id="the_name" type="text" name="name" value="<?php htmlout($name);  ?>"/></label></div>
				<div>
                    <label for="the_domain">Domain: <input id="the_domain" type="text" name="domain" value="<?php  htmlout($domain); ?>"/></label></div>
                <div>
                    <label for="the_tel">Phone: <input id="the_tel" type="text" name="tel" value="<?php htmlout($tel); ?>"/></label></div>
                </fieldset>
                <input type="hidden" name="id" value="<?php htmlout($id); ?>"/>
                <input type="submit" value="<?php htmlout($button); ?>"/>
            </form>
            <p><a href="./">Return to Client List</a></p></div>
    </body>
</html>