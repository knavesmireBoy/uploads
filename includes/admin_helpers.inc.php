<?php

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

function updateUser($db, $priv){
    
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

		$sql = "DELETE FROM userrole WHERE userid='$id'";
        //clear existing before - optionally - re-assigning
        doQuery($link, $sql, 'Error setting user password.', 'Error removing obsolete user role entries.');
	
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
    $location = (isset($pwd)) ? "?pwdlen&id=$id" : ($priv == 'Client' ? '..' : '.');
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

function fromDomain($db, $key, $domain){
    include $db;
    $sql = "SELECT employer.user_name, employer.user_id FROM (SELECT user.name AS user_name, user.id AS user_id, client.domain FROM user INNER JOIN client ON $domain = client.domain) AS employer WHERE employer.domain='$key'";
    $res = doQuery($link, $sql, 'Database error fetching users.');
    return doProcess($res, 'user_id', 'user_name');
}

function testDomain($db, $key){
    include $db;
	$key = doSanitize($link, $key);
	$res = doQuery($link, "SELECT domain, name FROM client WHERE domain = '$key' ", 'Database error fetching clients.');
	return goFetch($res);
}

function getUsers($db, $key){
        include $db;
        $res = doQuery($link, "SELECT id, name FROM user where id ='$key' ORDER BY name", "Error retrieving users from the database!");
        return doProcess($res, 'id', 'name');
}

function chooseAdmin($db, $key, $user, $domain){
	$row = testDomain($db,  $user);
    $client = null;
    include $db;
	if (isset($row))
	{
        $c = getClientName($db, $user, null);
        $manage = "Manage users of $c";
        $users = fromDomain($db, $user, $domain);
	}
    else {
        $manage = "Edit details";
        $users = getUsers($db, $user);
    }
    return array('users' => $users, 'manage' => $manage, 'ret' => '.', 'page' => 'list', 'client' => $client);
}

function chooseClient($db, $key, $user, $domain){
    include $db;
    $client = null;
    $user = domainFromUserID($link, $key);//list of members
    if($user){
        $users = fromDomain($db, $user, $domain);
        $client = true;
    }
    else {
        $users = getUsers($db, $key);
    }
    return array('users' => $users, 'manage' => 'Edit Details', 'ret' => '..', 'page' => 'uploads', 'client' => $client);
}