<?php include_once $_SERVER['DOCUMENT_ROOT'] .    '/uploads/includes/helpers.inc.php'; ?>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Access Denied</title>
        <link href="../css/lofi.css" media="all" rel="stylesheet" type="text/css">
    </head>
    <body>
        <h1>Access Denied</h1>
        <p><?php echo htmlout($error); ?></p>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/logout.inc.html.php'; ?>
        <p><a href="..">Return to uploads</a></p>
    </body>
</html>