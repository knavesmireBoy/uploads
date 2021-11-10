<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$css = 'css/lofi.css';
$base = 'Log In';
$error = '';
$tmpl_error = '/uploads/includes/error.html.php';
$doError = function (){};

setcookie('success', "", time() -1, '/');
unset($_COOKIE['success']); 

//dump($_COOKIE['success']);
if (!userIsLoggedIn())
{
    $inc_login = true;
    include $tmplt . 'base.html.php';
    //include $tmplt . 'login.html.php';
    exit();
}
$roleplay = userHasWhatRole();
//public page
$doError = partialDefer('errorHandler', 'Only valid clients may access this page.', $tmplt . 'accessdenied.html.php');
doWhen($always(!$roleplay) , $doError) (null);

$key = $roleplay['id'];
$priv = $roleplay['roleid'];

$isPriv = partial('equals', 'Admin', $priv);
$isClient = partial('equals', 'Client', $priv);
$notPriv = negate($isPriv);

$domain = "RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))"; //!!?!! V. USEFUL VARIABLE IN GLOBAL SPACE

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
$pages = null;

$doDelete = doWhen(partial('goPost', 'extent'), partial('doDelete', $db, $compose));
$doUpdate = doWhen(partial('goPost', 'update'), partial('doUpdate', $db));
$prepareUserList = doWhen($isPriv, partial('prepUpdateUser'));
//doWhen expects an argument
$doDelete(null);
$doUpdate(null);
$clientdetails = getClientNameFromEmail($db, $domain, "{$_SESSION['email']}");
$clientname = $clientdetails['name'];
$client_id = $clientdetails['id'];
$client_domain = $clientdetails['domain'];

$username = getNameFromEmail($db, "{$_SESSION['email']}");

$isSingleUser = partial('equals', 'Client', $priv);


$keytype = isset($client_domain) ? $client_domain : $key;
$fileCount = curry22('fileCountByUser')($domain)($keytype);

$doZero = doWhen($isPriv, $always(0));
$doOne = doWhen($always($client_id), $always(1));
$doTwo = doWhen($always(!$client_id), $always(2));

$user_int = array_reduce([$doZero, $doOne, $doTwo], 'getOne');

$isSingleUser = partial('array_reduce', [$isClient, negate(partial('iSet', $clientname))], 'every', true);
$userid = -1;
$equals = partial(equality(true), $userid);

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
$nonBrowser = negate(partial('equals', $priv, 'Browser'));

include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/control.php';

/*
scp storm.csv andrewsykes@northwolds.serveftp.net:/Users/andrewsykes
 LOAD DATA LOCAL INFILE '../../../../users/andrewsykes/storm.csv' INTO TABLE fname FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY '\r';
 
 SELECT * from fchar  INTO OUTFILE '../../../../users/andrewsykes/sunday.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY '/n';
 */