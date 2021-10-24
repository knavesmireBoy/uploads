<?php
function dump($arg){
 exit(var_dump($arg));
}

function doConfirm($db, $action){
    if($action === 'Yes'){
        include $db;
        $id = mysqli_real_escape_string($link, $_POST['id']);
        $result = doQuery($link, "DELETE FROM user WHERE id = $id", 'Error deleting user.');
    }
	header('Location: . ');
	exit();
}


function updateUser($db, $priv){
    include $db;
    $id = doSanitize($link, $_POST['id']);
	$name = doSanitize($link, $_POST['name']);
	$email = doSanitize($link, $_POST['email']);    
	$sql = "UPDATE user SET name='$name', email='$email' WHERE id='$id'";
    
    doQuery($link, $sql, 'Error setting user details.');
	if (isset($_POST['password']) && !empty($_POST['password']))
	{
		$password = md5($_POST['password'] . 'uploads');
		$password = doSanitize($link, $password);
		$sql = "UPDATE user SET password = '$password' WHERE id = '$id'";
        doQuery($link, $sql, 'Error setting user password.');
	}

	if ($priv == 'Admin')
	{
		$sql = "DELETE FROM userrole WHERE userid='$id'";
        //clear existing before - optionally - re-assigning
        doQuery($link, $sql, 'Error setting user password.', 'Error removing obsolete user role entries.');
	}
	if (isset($_POST['roles']))
	{
		foreach ($_POST['roles'] as $role)
		{
			$roleid = doSanitize($link, $role);
			$sql = "INSERT INTO userrole SET userid='$id', roleid='$roleid'";
            doQuery($link, $sql, 'Error setting user password.', 'Error assigning selected role to user.');
		} //end foreach
	}

	if (isset($_POST['employer']) && !empty($_POST['employer']))
	{
		$client = $_POST['employer'];
		$cid = doSanitize($link, $client);
        $sql = "SELECT domain FROM client WHERE id = $cid";
        $res = doQuery($link, $sql, "Error getting client domain");
        $email = fixDomain($email, goFetch($res)[0]);
		$sql = "UPDATE user SET user.client_id = $cid, user.email = '$email' WHERE id = $id";
        doQuery($link, $sql, "Error setting user details");        
	}
	header('Location: . ');
	exit();
}

function addUser($db){
    include $db;
	$name = doSanitize($link, $_POST['name']);
	$email = doSanitize($link, $_POST['email']);
	doQuery($link, "INSERT INTO user SET name='$name', email='$email' ", 'Error adding user.');
	$id = mysqli_insert_id($link);
	if (isset($_POST['password']) && !empty($_POST['password']))
	{
		$password = md5($_POST['password'] . 'uploads');
		$password = doSanitize($link, $password);
        doQuery($link,  "UPDATE user SET password = '$password'  WHERE id = '$id'", 'Error setting user password.');
	}
	if (isset($_POST['employer']) && $_POST['employer'] != '')
	{
		$client = $_POST['employer'];
		$cid = doSanitize($link, $client);
		$sql = "UPDATE user SET client_id = $cid WHERE id = $id";
        doQuery($link,  "UPDATE user SET client_id = $cid WHERE id = $id", 'Error setting client id.');
	}
	if (isset($_POST['roles']))
	{
		foreach ($_POST['roles'] as $role)
		{
			$roleid = doSanitize($link, $role);
            doQuery($link, "INSERT INTO userrole SET userid='$id', roleid='$roleid'", 'Error assigning selected role to user.');
		}
	}
	header('Location: . ');
	exit();
}