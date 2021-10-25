<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/client_helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';

if (!userIsLoggedIn())
{
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/login.html.php';
    exit();
}
$roleplay = validateAccess('Admin', 'Client');
$key = $roleplay['id'];
$priv = $roleplay['roleid'];
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';

$doCreate = doWhen(partial('goGet', 'addform'), partial('createClient', $db));
$doUpdate = doWhen(partial('goGet', 'editform'), partial('updateClient', $db));
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
    $name = '';
    $domain = '';
    $tel = '';
    $button = 'Add Client';
    include 'form.html.php';
    exit();
} 

if (isset($_POST['action']) and $_POST['action'] == 'Edit')
{
    include $db;
    $id = doSanitize($link, $_POST['id']);
    
    $vars = array_map(partial('doSanitize', $link), $_POST);
    
    $sql = "SELECT id, name, domain, tel FROM client WHERE id = $id";
    $result = doQuery($link, "SELECT id, name, domain, tel FROM client WHERE id = $id", 'Error fetching user details.');

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
