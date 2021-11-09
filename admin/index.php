<?php
/*mysql_real_escape_string\(([^,]+),([^)]+\);)
 doSanitize($2, $1);*/
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/admin_helpers.inc.php';
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
$id = '';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$manage = "Manage User";

//$doWarning = doWhen($always(true), $always('warning'));
$doWarning = getBestArgs($always(true))($always('warning'), $always(''));
$warning = '';

if (!userIsLoggedIn())
{
	include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/login.html.php';
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
$testPriv = getBestArgs($isAdmin)('chooseAdmin', 'chooseClient');
$manage = $isAdmin() ? 'Manage User' : 'Edit Details';
$data = ['manage' => $manage];//required for edit.html.php after delete is invoked

if(isset($_GET['pwdlen'])) {
$pwderror = 'Password must contain at least 5 characters';
}

if (isset($_GET['addform']))
{
	addUser($db);
}

if (isset($_GET['editform']))
{
    
    /*
    $cb = function (&$item, $i) {
        $item = isset($item[1]) && andNotEmpty($item[1], $i);
};
*/
    
    $eq = equality(true);
    $msgs = array();
    $isEmpty = $always('This is a required field');
    $push = function(&$grp){
        return function($arg) use(&$grp) {
            $grp[] = $arg;
    };
    };
    $pusher = $push($msgs);
    $cbs = array('name' => array('isEmpty', $compose($isEmpty, $pusher)));
    //$cbs = array('name' => array('isEmpty', $always('fra')));
    
    $once = getBestArgs(doOne())($always('danger'), $always('warning'));
    $walk = function($grp) {
        array_walk($grp, function($v, $k) use($grp) {
            call_user_func_array('doWhen', $v)($_POST["$k"]);
                 });
    };
                 
    $walk($cbs);
   dump($msgs);
    $warning = "Please supply a valid email address innit";
    updateUser($db, $priv);
}

if (isset($_POST['confirm']))
{
	doConfirm($db, $_POST['confirm']);
}

//Admin uses $_POST Non-Admin $_GET
if (isset($_REQUEST['act']) and $_REQUEST['act'] == 'Choose' && isset($_REQUEST['user']))
{
    $domain = strrpos($key, "@") ? " user.email" : $domain;
    $data = $testPriv($db, $key, $_REQUEST['user'], $domain);
    setcookie('extent', count($data['users']));
    include 'edit_users.html.php';
    exit();
    
} ///CHOOSE________________________________________________________________________

if (isset($_GET['add']))
{
	include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
	$pagetitle = 'New User';
	$action = 'addform';
	$name = '';
	$email = '';
    $cid;
	$button = 'Add User';
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

	if (isset($_POST['employer']) && !empty($_POST['employer']))
	{
		$id = doSanitize($link, $_POST['employer']);
        $res = doQuery($link, "SELECT id, domain FROM client WHERE id = $id", "Error retrieving clients from database!");
	}
    
	$row = goFetch($res);
	$cid = $row['id'];
	$email = $row['domain'];
    
    $res = doQuery($link, "SELECT id, name FROM client ORDER BY name", "Error retrieving clients from database!");
    $clientlist = doProcess($res, 'id', 'name');//for assigning to client
    
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
}


if ((isset($_POST['action']) and ($_POST['action'] == 'Edit')) || isset($pwderror))
{
    $pagetitle = 'Edit User';
	$action = 'editform';
    $button = 'Update User';
    $name;
    $email;
    $clientlist;
    $job;
    $roles = array();
    $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
   
    include $db;
	$id = doSanitize($link, $id);
    $res = doQuery($link, "SELECT id, name, email FROM user WHERE id = $id", 'Error fetching user details.');
	$row = goFetch($res);
	$name = $row['name'];
	$email = $row['email'];
	$id = $row['id'];
	
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


if (isset($_POST['action']) and $_POST['action'] == 'Delete')
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
    $doSelected = $compose(partial('completeTag', 'option', 'value'), curry2('invokeArg')(getBestArgs($equals)($always(" selected = 'selected' "), $always(""))));
    $doOpt = getBestArgs(negate(partial('isEmpty', $users)))('optGroupOpen', $always(""));
    $doOptEnd = getBestArgs(negate(partial('isEmpty', $users)))('optGroupClose', $always(""));
    include 'select_users.html.php';
}
else {
    $email = "{$_SESSION['email']}";
    $res = doQuery($link, "SELECT id from user WHERE email = '$email'", 'Error getting id from email');
    $row = goFetch($res);
    $id = $row['id'];
    doExit("?act=Choose&user=$id");//bypass drop down for non-admin users
}

