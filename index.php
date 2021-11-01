<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$base = 'Log In';
$error = '';
$tmpl_error = '/uploads/includes/error.html.php';
$doError = function (){};

if (!userIsLoggedIn())
{
    include $tmplt . 'base.html.php';
    include $tmplt . 'login.html.php';
    exit();
}
$roleplay = userHasWhatRole();
//public page
$doError = partialDefer('errorHandler', 'Only valid clients may access this page.', $tmplt . 'accessdenied.html.php');
doWhen($always(!$roleplay) , $doError) (null);

$key = $roleplay['id'];
$priv = $roleplay['roleid'];

$domain = "RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))"; //!!?!! V. USEFUL VARIABLE IN GLOBAL SPACE
$fileCount = curry2('fileCountByUser') ($domain);
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';

$myip = '86.133.121.115.';
$text = null;
$suffix = null;
$user = null;
$tel = '';

$start = 1;
$display = 10;
$findmode = false;
$order_by = 'time DESC';

$base = 'File Uploads';

$doDelete = doWhen(partial('goPost', 'extent') , partial('doDelete', $db, $compose));
$doUpdate = doWhen(partial('goPost', 'update') , partial('doUpdate', $db));
//doWhen expects an argument
$doDelete(null);
$doUpdate(null);
$clientname = getClientName($db, "{$_SESSION['email']}", $domain);
$username = getUserName($db, "{$_SESSION['email']}");
$name = $username;

include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/control.php';