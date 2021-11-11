<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/client_helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$base = 'Log In';
$css = '../css/lofi.css';

if (!userIsLoggedIn())
{
    $inc_login = true;
    include $tmplt . 'base.html.php';
	exit();
}
$roleplay = validateAccess('Admin');
$key = $roleplay['id'];
$priv = $roleplay['roleid'];
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';

$doCreate = doWhen(partial('goGet', 'addform'), partial('validateClient', $db));
$doUpdate = doWhen(partial('goGet', 'editform'), partial('validateClient', $db, 'edit'));
$doDelete = doWhen(partial('goPost', 'confirm'), partial('deleteClient', $db));

$doCreate(null);
$doDelete(null);
$doUpdate(null);

if (isset($_GET['add']))
{
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
    $id = '';
    $pagetitle = 'New Client';
    $action = 'addform';
    $name = isset($_GET['name']) ? $_GET['name'] : '';
    $domain = isset($_GET['domain']) ? 'domain already in DB' : '';
    $tel = isset($_GET['name']) ? $_GET['tel'] : '';
    $button = 'Add Client';
    include 'form.html.php';
    exit();
} 

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'Edit')
{
    include $db;
    //$id = isset($_POST['id']) ? $_POST['id'] : null;
    $selects = array("SELECT id, name, domain, tel ", "SELECT id, name, tel ", "SELECT id, domain, tel ");
    $select = $selects[0];
    $id = doSanitize($link, $_POST['id']);
    $from = "FROM client WHERE id = $id";
    $vars = array_map(partial('doSanitize', $link), $_POST);    
    $result = doQuery($link, $select .= $from, 'Error fetching user details.');

    $row = goFetch($result);
    $pagetitle = 'Edit Client';
    $action = 'editform';
    $button = 'Update Client';
    
     foreach($row as $k => $v) {
        ${$k} = $v;
    }    
    include 'form.html.php';
    exit();
}

if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
    $id = $_POST['id'];
    $title = "Prompt for deletion";
    $prompt = "Are you sure you want to delete this client? ";
    $call = "confirm";
    $pos = "Yes";
    $neg = "No";
    $action = '';
}

include $db;
$sql = "SELECT id, name, domain from client"; // THE DEFAULT QUERY
if (isset($_POST['act']) and $_POST['act'] == 'Choose' && !empty($_POST['client']))
{
    $id = doSanitize($link, $_POST['client']);
    $sql .= " WHERE id = $id";
}

$sql .= " ORDER BY name";
//display clients
$result = doQuery($link, $sql, "Error retrieving clients from database!");
//echo mysqli_errno($link) . ": " . mysqli_error($link). "\n";
$clients = doProcess($result, 'id', 'name', MYSQLI_ASSOC);
include 'clients.html.php';
?>
