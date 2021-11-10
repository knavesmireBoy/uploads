<?php include_once $_SERVER['DOCUMENT_ROOT'] .    '/uploads/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $base; ?></title>
<link href="<?php echo $css; ?>" type="text/css" rel="stylesheet" media="all"/>
</head>
<body>
   <?php if(isset($inc_login)){
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/login.html.php';
    }
    ?>
<div id="upload">