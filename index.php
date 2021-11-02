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

$start = 0;
$display = 5;
$findmode = false;
$order_by = 'time DESC';
$base = 'File Uploads';
$users = array();
$client = array();

$doDelete = doWhen(partial('goPost', 'extent') , partial('doDelete', $db, $compose));
$doUpdate = doWhen(partial('goPost', 'update') , partial('doUpdate', $db));
//doWhen expects an argument
$doDelete(null);
$doUpdate(null);
$clientdetails = getClientName($db, $domain, "{$_SESSION['email']}");
$clientname = $clientdetails['name'];
$client_id = $clientdetails['id'];
$client_domain = $clientdetails['domain'];
$username = getUserName($db, "{$_SESSION['email']}");
$name = isset($clientname) ? $clientname : $username;
$where = ' WHERE TRUE';
$ordering = array(
    'f' => 'filename ASC',
    'ff' => 'filename DESC',
    'u' => 'user ASC',
    'uu' => 'user DESC',
    'uuu' => 'user ASC',
    'uf' => 'user ASC, filename ASC',
    'uuf' => 'user DESC, filename ASC',
    'uff' => 'user ASC, filename DESC',
    'uuff' => 'user DESC, filename DESC',
    'ut' => 'user ASC, time ASC',
    'utt' => 'user ASC, time DESC',
    'uut' => 'user DESC, time ASC',
    'uutt' => 'user DESC, time DESC',
    't' => 'time ASC',
    'tt' => 'time DESC'
);
$lookup = array(
    'tu' => 'ut',
    'fu' => 'uf',
    'utu' => 'uut',
    'uufu' => 'uf',
    'uutu' => 'ut'
);

$admin_status = asAdmin($priv, $clientname);

include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/control.php';