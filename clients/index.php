<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/client_helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once '../myconfig.php';
$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$base = 'Log In';
$css = '../css/lofi.css';
$warning = 'editclient';
$error = 'Client Details';

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

$doCreate = doWhen(partial('goGet', 'addform'), partial('validateClient', $db, null));
$doUpdate = doWhen(partial('goGet', 'editform'), partial('validateClient', $db, 'edit'));
$doDelete = doWhen(partial('goPost', 'confirm'), partial('deleteClient', $db));

$doCreate(null);
$doDelete(null);
$doUpdate(null);

if (isset($_GET['add']) || (isset($_GET['action']) && $_GET['action'] === 'Add'))
{
    //if submission fails form is reloaded with validated variables
    //dump($_GET['action']);
    $id = '';
    $pagetitle = 'New Client';
    $action = 'addform';
    $name = isset($_GET['name']) ? $_GET['name'] : '';
    $domain = isset($_GET['domain']) ? $_GET['domain'] : '';
    $tel = isset($_GET['tel']) ? $_GET['tel'] : '';
    $button = 'Add Client';
    include 'form.html.php';
    exit();
} 

if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'Edit')
{
    include $db;
     $name = '!';
    $domain = '!';
    $tel = '';
        $pagetitle = 'Edit Client';
        $action = 'editform';
        $button = 'Update Client';
    //$id = isset($_POST['id']) ? $_POST['id'] : null;
    $selects = array("SELECT id, name, domain, tel ", "SELECT id, name, tel ", "SELECT id, domain, tel ");
    $select = $selects[0];
     $id = isset($_POST['id']) ? $_POST['id'] : null;
    if(isset($_GET['error'])){
        $n = strpos($_GET['warning'], 'xname');
        $d = strpos($_GET['warning'], 'xdomain');
        if(is_int($n) && is_int($d)){
            $select = null;
        }
        elseif(is_int($d)){
            $select = $selects[1];
        }
         elseif(is_int($n)){
            $select = $selects[2];
        }
        $id = $_GET['xid'];
    }
    if($select){
        $id = doSanitize($link, $id);
        $from = "FROM client WHERE id = $id";
        $vars = array_map(partial('doSanitize', $link), $_REQUEST);    
        $result = doQuery($link, $select .= $from, 'Error fetching user details.');
        $row = goFetch($result, MYSQLI_ASSOC);
        foreach($row as $k => $v) {
            ${$k} = $v;
        } 
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
