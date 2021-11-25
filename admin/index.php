<?php
/*mysql_real_escape_string\(([^,]+),([^)]+\);)
 doSanitize($2, $1);*/
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/admin_helpers.inc.php';
include_once '../myconfig.php';
$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$base = 'Log In';
$css = '../css/lofi.css';
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
$id = '';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$manage = "Manage User";
setcookie('eemail', "", time() -1, "/");
//$doWarning = doWhen($always(true), $always('warning'));
$doWarning = getBestArgs($always(true));
$doWarning = $doWarning($always('warning'), $always(''));
$warning = 'commit';
$error = 'User Details:';
$submit = "Delete";

if (!userIsLoggedIn())
{
    $inc_login = true;
    include $tmplt . 'base.html.php';
	exit();
}

$roleplay = validateAccess('Admin', 'Client');
$domain = "RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))";

$key = $roleplay['id'];
$priv = $roleplay['roleid'];

$isPriv = partial('equals', 'Admin', $priv);
$isClient = partial('equals', 'Client', $priv);

$notPriv = negate($isPriv);

$clientdetails = getClientNameFromEmail($db, $domain, "{$_SESSION['email']}");
$clientname = $clientdetails['name'];
$client_id = $clientdetails['id'];
$client_domain = $clientdetails['domain'];

$username = getNameFromEmail($db, "{$_SESSION['email']}");
$admin_status = asAdmin($priv, $clientname);
$isSingleUser = partial('array_reduce', [$isClient, negate(partial('iSet', $clientname))], 'every', true);

$single;

if(!isset($priv)){
    exit();
}

$isAdmin = partial('equals', $priv, 'Admin');
$testPriv = getBestArgs($isAdmin);
$testPriv = $testPriv('chooseAdmin', 'chooseClient');
$manage = $isAdmin() ? 'Manage User' : 'Edit Details';
$data = ['manage' => $manage];//required for edit.html.php after delete is invoked

if (isset($_GET['addform']))
{
    validateUser($db, null);
}

if (isset($_GET['editform']))
{
    validateUser($db, $priv, 'edit');
}

if (isset($_POST['confirm']))
{
	doConfirm($db, $_POST['confirm']);
}

//Admin uses $_POST Non-Admin $_GET
//if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'choose' && isset($_REQUEST['user']))
if (requestWhen('act', 'choose') && isset($_REQUEST['user']))
{
    $domain = strrpos($key, "@") ? " user.email" : $domain;
    $data = $testPriv($db, $key, $_REQUEST['user'], $domain);
    $count = count($data['users']);
    setcookie('extent', $count);
    if($count > 0){
        include 'edit_users.html.php';
        exit();
    }
    else {
        $location = "?extent=Client currently has no employees in the database";
        doExit($location);
    }    
    
} ///CHOOSE________________________________________________________________________

if (goGet('add') || getWhen('action', 'add'))
{
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
	$pagetitle = 'New User';
	$action = 'addform';
	$name = isset($_GET['name']) ? $_GET['name'] : '';
	$email = isset($_COOKIE['eemail']) ? $_COOKIE['eemail'] : '!';
    //setcookie('eemail', "", time() -1);
    //dump($_COOKIE);
    $cid;
	$button = 'add';
    $roles = array();
    $clientlist = array();

	//Build the list of roles
    $res = doQuery($link, "SELECT id, description FROM role", 'Error fetching list of roles.');
	
	while ($row = mysqli_fetch_array($res))
	{
		$roles[] = array(
			'id' => $row['id'],
			'description' => $row['description'],
			'selected' => false
		);
	}
    
    if(!$isAdmin()){
        array_shift($roles);
    }
    
    if (goPost('employer', true))
	{
		$id = doSanitize($link, $_POST['employer']);
        $res = doQuery($link, "SELECT id, domain FROM client WHERE id = $id", "Error retrieving clients from database!");
        $row = goFetch($res);
        $cid = $row['id'];
        $email = $row['domain'];
	}
        
    $res = doQuery($link, "SELECT id, name FROM client ORDER BY name", "Error retrieving clients from database!");
    $clientlist = doProcess($res, 'id', 'name');//for assigning to client
    
    $extent = isset($_COOKIE['extent']) ? $_COOKIE['extent'] : null;
    $data = array();
    if(isset($extent) && $extent > 1){
        $data['ret'] = '.';
        $data['page'] = 'list';
    }
    else {
        $data['ret'] = '..';
        $data['page'] = 'Uploads';
    }
    
	include 'form.html.php';
	exit();
}

if (requestWhen('action', 'Edit') || postWhen('confirm', 'No'))
{
    $pagetitle = 'Edit User';
	$action = 'editform';
    $button = 'update';
    $name = '';
    $email = '!';
    $clientlist;
    $job;
    $selects = array("SELECT id, name, email ", "SELECT id, name ", "SELECT id, email ");
    $select = $selects[0];
    $roles = array();
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    if(isset($_GET['error'])){
        $n = strpos($_GET['warning'], 'xname');
        $e = strpos($_GET['warning'], 'xemail');
        if(is_int($n) && is_int($e)){
            $select = null;
        }
        elseif(is_int($e)){
            $select = $selects[1];
        }
         elseif(is_int($n)){
            $select = $selects[2];
        }
        $id = $_GET['xid'];
    }
    
    include $db;
  
    if($select){
	$id = doSanitize($link, $id);
    $res = doQuery($link, $select .= "FROM user WHERE id = $id", 'Error fetching user details.');
	$row = goFetch($res);
	$name = isset($row['name']) ? $row['name'] : '!';
	$email = isset($row['email']) ? $row['email'] : '!';
	$id = $row['id'];
    }
	// Get list of roles assigned to this user
	$res = doQuery($link, "SELECT roleid FROM userrole WHERE userid = '$id'", 'Error fetching list of assigned roles.');
    $selectedRoles = doBuild($res, 'roleid');
	// Build the list of all roles
    $res = doQuery($link, "SELECT id, description FROM role", 'Error fetching list of roles.');    
	while ($row = mysqli_fetch_array($res))
	{
		$roles[] = array(
			'id' => $row['id'],
			'description' => $row['description'],
			'selected' => in_array($row['id'], $selectedRoles)
		);
	}
    
     if(!$isAdmin()){
        array_shift($roles);
    }

	$res = doQuery($link, "SELECT id, name FROM client ORDER BY name", "Error retrieving clients from database!");
    $clientlist = doProcess($res, 'id', 'name');//for assigning to client
    $res = doQuery($link, "SELECT client_id FROM user WHERE id = $id", "Error retrieving client id from user!");
	$row = goFetch($res);
	$job = $row['client_id']; //selects client in drop down menu
    $extent = $_COOKIE['extent'];
    $data = array();
    if(isset($extent) && $extent > 1){
        $data['ret'] = '.';
        $data['page'] = 'list';
    }
    else {
        $data['ret'] = '..';
        $data['page'] = 'Uploads';
    }
	include 'form.html.php';
	exit();
} //edit


if (postWhen("action", "Delete"))
{
	include $db;
    $id = $_POST['id'];
	$title = "Prompt";
	$prompt = "Are you sure you want to delete this user? ";
	$call = "confirm";
	$pos = "Yes";
	$neg = "No";
	$action = '';
	include 'edit_users.html.php';
    exit();
} //DELETE


include $db;

if ($priv == "Admin")
{
    $res = doQuery($link,  "SELECT client.domain, client.name FROM client ORDER BY name", 'Database error fetching client list.');
	$client = doProcess($res, 'domain', 'name');
    $sql = "SELECT user.id, user.name FROM user LEFT JOIN client ON user.client_id = client.id WHERE client.domain IS NULL ORDER BY name"; 
    $res = doQuery($link, $sql, 'Database error fetching user list.');
    $users = doProcess($res, 'id', 'name');
    $userid = -1;
    $equals = partial(equality(true), $userid);
    $invoke = curry2('invokeArg');
    $doEquals = getBestArgs($equals);
    $doSelected = $compose(partial('completeTag', 'option', 'value'), $invoke($doEquals($always(" selected = 'selected' "), $always(""))));
    $negate = getBestArgs(negate(partial('isEmpty', $users)));
    $doOpt = $negate('optGroupOpen', $always(""));
    $doOptEnd = $negate('optGroupClose', $always(""));
    include 'select_users.html.php';
}
else {
    $email = "{$_SESSION['email']}";
    $res = doQuery($link, "SELECT id from user WHERE email = '$email'", 'Error getting id from email');
    $row = goFetch($res);
    $id = $row['id'];
    doExit("?act=choose&user=$id");//bypass drop down for non-admin users
}