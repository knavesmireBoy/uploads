<?php
/*mysql_real_escape_string\(([^,]+),([^)]+\);)
 mysqli_real_escape_string($2, $1);*/
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/admin_helpers.inc.php';
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
$users = [];
$id = '';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$manage = "Manage Users";


if (!userIsLoggedIn())
{
	include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/login.html.php';
	exit();
}
//admin page
if (!$roleplay = userHasWhatRole())
{
	$error = 'Only Account Administrators may access this page!!';
	include 'accessdenied.html.php';
	exit();
}

$key = $roleplay['id'];
$priv = $roleplay['roleid'];

if(!isset($priv)){
    exit();
}
// THE DEFAULT QUERY___________________________________
$sql = $priv === 'Client' ? "SELECT id, name FROM user where id ='$key' ORDER BY name" : "SELECT id, name FROM user "; 

if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
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

if (isset($_POST['confirm']))
{
	doConfirm($db, $_POST['confirm']);
}

if (isset($_GET['add']))
{
	include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
	$pagetitle = 'New User';
	$action = 'addform';
	$name = '';
	$email = '';
	$button = 'Add User';

	//Build the list of roles
	$sql = "SELECT id, description FROM role";
	$result = mysqli_query($link, $sql);
	if (!$result)
	{
		$error = 'Error fetching list of roles.';
		include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
		exit();
	}
	while ($row = mysqli_fetch_array($result))
	{
		$roles[] = array(
			'id' => $row['id'],
			'description' => $row['description'],
			'selected' => false
		);
	}

	if (isset($_POST['employer']) && !empty($_POST['employer']))
	{
		$id = mysqli_real_escape_string($link, $_POST['employer']);
		$sql = "SELECT id, domain FROM client WHERE id=$id";
		$result = mysqli_query($link, $sql);
	}
	if (!$result)
	{
		$error = "Error retrieving clients from database!";
		include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
		exit();
	}
	$row = mysqli_fetch_array($result);
	$cid = $row['id'];
	$email = $row['domain'];

	$sql = "SELECT id, name FROM client ORDER BY name";
	$result = mysqli_query($link, $sql);
	if (!$result)
	{
		$error = "Error retrieving clients from database!";
		include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
		exit();
	}
	while ($row = mysqli_fetch_array($result))
	{
		$clientlist[$row['id']] = $row['name'];
	}
	include 'form.html.php';
	exit();
} //////////////END OF ASSIGN


if (isset($_GET['addform']))
{
	include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
	$name = mysqli_real_escape_string($link, $_POST['name']);
	$email = mysqli_real_escape_string($link, $_POST['email']);
	$sql = "INSERT INTO user SET name='$name', email='$email' ";
	if (!mysqli_query($link, $sql))
	{
		$error = 'Error adding user.';
		include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
		exit();
	}
	$aid = mysqli_insert_id($link);
	if (isset($_POST['password']) && $_POST['password'] != '')
	{
		$password = md5($_POST['password'] . 'uploads');
		$password = mysqli_real_escape_string($link, $password);
		$sql = "UPDATE user SET password = '$password'  WHERE id = '$aid'";
		if (!mysqli_query($link, $sql))
		{
			$error = 'Error setting user password.';
			include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
			exit();
		}
	}
	if (isset($_POST['employer']) && $_POST['employer'] != '')
	{
		$client = $_POST['employer'];
		$cid = mysqli_real_escape_string($link, $client);
		$sql = "UPDATE user SET client_id=$cid WHERE id=$aid";
		if (!mysqli_query($link, $sql))
		{
			$error = 'Error setting client id 152.';
			include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
			exit();
		}
	}
	if (isset($_POST['roles']))
	{
		foreach ($_POST['roles'] as $role)
		{
			$roleid = mysqli_real_escape_string($link, $role);
			$sql = "INSERT INTO userrole SET userid='$aid', roleid='$roleid'";
			if (!mysqli_query($link, $sql))
			{
				$error = 'Error assigning selected role to user.';
				include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
				exit();
			}
		}
	}
	header('Location: . ');
	exit();
} //end of addform
if (isset($_POST['action']) and $_POST['action'] == 'Edit')
{
    $pagetitle = 'Edit User';
	$action = 'editform';
    $button = 'Update User';
    $name;
    $email;
    $clientlist;
    $job;
    $roles = array();
    
    include $db;
	$id = doSanitize($link, $_POST['id']);
    $res = doQuery($link, "SELECT id, name, email FROM user WHERE id = $id", 'Error fetching user details.');
	$row = goFetch($res);
	$name = $row['name'];
	$email = $row['email'];
	$id = $row['id'];
	
	// Get list of roles assigned to this user
	$res = doQuery($link, "SELECT roleid FROM userrole WHERE userid='$id'", 'Error fetching list of assigned roles.');
    $selectedRoles = doBuild($res, 'roleid');
    
	// Build the list of all roles
    $res = doQuery($link, "SELECT id, description FROM role", 'Error fetching list of roles.');
    
    dump($selectedRoles);
    
	while ($row = mysqli_fetch_array($res))
	{
		$roles[] = array(
			'id' => $row['id'],
			'description' => $row['description'],
			'selected' => in_array($row['id'], $selectedRoles)
		);
	}
	$res = doQuery($link, "SELECT id, name FROM client ORDER BY name", "Error retrieving clients from database!");
    $clientlist = doProcess($res, 'id', 'name');//for assigning to client
    $res = doQuery($link, "SELECT client_id FROM user WHERE id=$id", "Error retrieving client id from user!");
	$row = goFetch($res);
	$job = $row['client_id']; //selects client in drop down menu
	include 'form.html.php';
	exit();
} //edit
if (isset($_GET['editform']))
{
	include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
	$id = mysqli_real_escape_string($link, $_POST['id']);
	$name = mysqli_real_escape_string($link, $_POST['name']);
	$email = mysqli_real_escape_string($link, $_POST['email']);
	$sql = "UPDATE user SET name='$name', email='$email' WHERE id='$id'";
	if (!mysqli_query($link, $sql))
	{
		$error = 'Error setting user details.';
		include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
		exit();
	}

	if (isset($_POST['password']) && $_POST['password'] != '')
	{
		$password = md5($_POST['password'] . 'uploads');
		$password = mysqli_real_escape_string($link, $password);
		$sql = "UPDATE user SET password = '$password' WHERE id = '$id'";
		if (!mysqli_query($link, $sql))
		{
			$error = 'Error setting user password.';
			include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
			exit();
		}
	}

	if ($priv && $priv == 'Admin')
	{
		$sql = "DELETE FROM userrole WHERE userid='$id'";
		if (!mysqli_query($link, $sql))
		{
			$error = 'Error removing obsolete user role entries.';
			include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
			exit();
		}
	}
	if (isset($_POST['roles']))
	{
		foreach ($_POST['roles'] as $role)
		{
			$roleid = mysqli_real_escape_string($link, $role);
			$sql = "INSERT INTO userrole SET userid='$id', roleid='$roleid'";
			if (!mysqli_query($link, $sql))
			{
				$error = 'Error assigning selected role to user.';
				include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
				exit();
			}
		} //end foreach
		
	}

	if (isset($_POST['employer']) && !empty($_POST['employer']))
	{
		$client = $_POST['employer'];
		$cid = mysqli_real_escape_string($link, $client);
		$sql = "UPDATE user SET client_id=$cid WHERE id = $id";
		if (!mysqli_query($link, $sql))
		{
			$error = 'Error setting client id 287.';
			include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
			exit();
		}
	}
	header('Location: . ');
	exit();
} ///END OF EDIT


//display users___________________________________________________________________
$domain = "RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))";
$sql = "SELECT user.id, user.name FROM user LEFT JOIN (SELECT user.name, client.domain FROM user INNER JOIN client ON $domain = client.domain) AS employer ON $domain=employer.domain WHERE employer.domain IS NULL"; //this overwrites above query to filter out users as employees
$sql = "SELECT user.id, user.name FROM user LEFT JOIN client ON user.client_id=client.id WHERE client.domain IS NULL"; //USING ID NOT DOMAIN
include $db;
//_______________________________________________________________________________
if (isset($_POST['act']) and $_POST['act'] == 'Choose' && isset($_POST['user']))
{
	$return = "Return to users";
	$key = doSanitize($link, $_POST['user']);
	$result = doQuery($link, "SELECT domain, name FROM client WHERE domain = '$key' ", 'Database error fetching clients.');
	$row = goFetch($result);
	// some clients need full domain for identification, in which case the query is simplified to a straight match to a users email address which corresponds to the client domain. ???
    $domain = strrpos($key, "@") ? " user.email" : $domain;
    
	if (isset($row[0]))
	{
		$sql = "SELECT employer.user_name, employer.user_id FROM (SELECT user.name AS user_name, user.id AS user_id, client.domain FROM user INNER JOIN client ON $domain = client.domain) AS employer WHERE employer.domain='$key'";
		$result = doQuery($link, $sql, 'Database error fetching users.');
        $clientname = $row['name'];
        $users = doProcess($result, 'user_id', 'user_name');
		$flag = true;
		$class = "edit";
	}
	else
	{
		$sql .= " AND user.id = $key";
	}
    if($priv === 'Admin' && isset($clientname)){
        $manage = "Manage members of $clientname";
    }
    else if($priv === 'Admin'){
        $sql .= " AND user.id = $key";
        $sql .= " ORDER BY name";//
        $manage = "Edit details";
        $result = doQuery($link, $sql, "Error retrieving users from the database!");
        $users = doProcess($result, 'id', 'name');
    }
     include 'edit_users.html.php';
    exit();
    
} ///CHOOSE________________________________________________________________________
////\\\\\/////\\\\\////\\\\\/////\\\\\////\\\\\ WILD /////\\\\\////\\\\\/////\\\\\////\\\\\/////\\\\\////\\\\\/////\\\\\

if ($priv != "Admin")
{
	$email = "{$_SESSION['email']}";
	include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
    $result = doQuery($link, "SELECT $domain FROM user WHERE user.email='$email'", 'Database error fetching users.');
	$row = goFetch($result);
	$dom = $row[0];
	$sqlc = "SELECT COUNT(*) AS dom FROM user INNER JOIN client ON $domain=client.domain WHERE $domain='$dom' AND client.domain='$dom'";
    
    $result = doQuery($link, $sqlc, 'Database error getting count.');
	$row = goFetch($result);
    $count = $row['dom'];
    $domain = $count == 0 ? "user.email" : $domain;
	if ($count > 0)
	{
		$sql = "SELECT employer.id, employer.name FROM user INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $domain=client.domain) AS employer ON $domain=employer.domain WHERE user.email='$email'";
        $result = doQuery($link, $sql, 'Database error fetching client list.');
        $users = doProcess($result, 'id', 'name');
		include 'select_users.html.php';
        exit();
	}
} //NOT ADMIN
if ($priv == "Admin")
{
    $result = doQuery($link, "SELECT client.domain, client.name FROM client ORDER BY name", 'Database error fetching client list.' . $sql);
	$client = doProcess($result, 'domain', 'name');
    include 'select_users.html.php';//used for drop down and edit
}