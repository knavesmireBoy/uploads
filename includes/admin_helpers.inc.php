<?php
function dump($arg){
 exit(var_dump($arg));
}

function doExit($location = '.'){
    header('Location: ' . $location);
	exit();
}

function doConfirm($db, $action){
    if($action === 'Yes'){
        include $db;
        $id = mysqli_real_escape_string($link, $_POST['id']);
        $result = doQuery($link, "DELETE FROM user WHERE id = $id", 'Error deleting user.');
    }
}

function assignClient($link, $cid, $id, $email){
    $sql = "SELECT domain FROM client WHERE id = $cid";
    $res = doQuery($link, $sql, "Error getting client domain");
    $email = fixDomain($email, goFetch($res)[0]);
    doQuery($link, "UPDATE user SET user.client_id = $cid, user.email = '$email' WHERE id = $id", "Error setting user details");
}

function assignRole($link, $roleid, $id){
    $roleid = doSanitize($link, $roleid);
    doQuery($link, "INSERT INTO userrole SET userid='$id', roleid='$roleid'", 'Error assigning selected role to user.');
}

function setPassword($link, $pwd, $id){
    $password = doSanitize($link, md5($pwd . 'uploads'));
    doQuery($link,  "UPDATE user SET password = '$password'  WHERE id = '$id'", 'Error setting user password.');
}

function updateUser($db, $priv, $count){
    
    include $db;
    $id = doSanitize($link, $_POST['id']);
	$name = doSanitize($link, $_POST['name']);
	$email = doSanitize($link, $_POST['email']);
    $pwd;
	$sql = "UPDATE user SET name='$name', email='$email' WHERE id='$id'";
    
    doQuery($link, $sql, 'Error setting user details.');
	if (isset($_POST['password']) && !empty($_POST['password']))
	{
		if(strlen($_POST['password']) >= 5) {
            setPassword($link, $_POST['password'], $id);
        }
        else {
            $pwd = 'fail';
        }
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
            assignRole($link, $role, $id);
		} //end foreach
	}

	if (isset($_POST['employer']) && !empty($_POST['employer']))
	{
        assignClient($link, doSanitize($link, $_POST['employer']), $id, $email);
	}
    $location = (isset($pwd)) ? "?pwdlen&id=$id" : ($count == 1 ? '..' : '.');
	doExit($location);
}

function addUser($db){
    include $db;
	$name = doSanitize($link, $_POST['name']);
	$email = doSanitize($link, $_POST['email']);
	doQuery($link, "INSERT INTO user SET name='$name', email='$email' ", 'Error adding user.');
	$id = mysqli_insert_id($link);
	if (isset($_POST['password']) && !empty($_POST['password']))
	{
        setPassword($link, $_POST['password'], $id);
	}
	if (isset($_POST['employer']) && $_POST['employer'] != '')
	{
        assignClient($link, doSanitize($link, $_POST['employer']), $id, $email);
	}
	if (isset($_POST['roles']))
	{
		foreach ($_POST['roles'] as $role)
		{
			assignRole($link, $role, $id);
		}
	}
	doExit();
}


function getClientCount($db, $email, $domain){
	include $db;
    $result = doQuery($link, "SELECT $domain FROM user WHERE user.email='$email'", 'Database error fetching users.');
	$row = goFetch($result);
	$dom = $row[0];
    $sql = "SELECT COUNT(*) AS total FROM user INNER JOIN client ON $domain = client.domain WHERE $domain = '$dom'";
    $result = doQuery($link, $sql, 'Database error getting count.');
	return goFetch($result);
}