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

function validate($db, $priv){
    $msgs = array();
    $f = curryLeft2('preg_match', 'negate');
    //need to fix number of ags in callback otherwise preg_match receives an invalid third argument
    //Actually changed signature, but IF we wanted to curryLeft is the way to go, negate flag (equates to true) to reverse predicate
    $isName = $f('/^[\w.]{2,20}(\s[\w]{2,20})?$/');
    $isEmail = $f('/^[\w][\w.-]+@[\w][\w.-]+\.[A-Za-z]{2,6}$/');
    
    $is_empty = $always('xname;NAME is a required field');
    $is_empty_email = $always('xemail;EMAIL is a required field');
    $is_name = $always('xname;Please supply name in expected format');
    $is_email = $always('xemail;Please supply a valid email address');
    
    $push = function(&$grp){
        return function($v) use (&$grp) {
            $res = explode(';', $v);
            $grp[$res[0]] = $res[1];
        };
    };
    $dopush = curry2($compose)($push($msgs));
    $beEmpty = array('isEmpty', $dopush($is_empty));
    $beEmptyEmail = array('isEmpty', $dopush($is_empty_email));
    $beBadName = array($isName, $dopush($is_name));
    $beBadEmail = array($isEmail, $dopush($is_email));
    $cbs = array('name' => array($beBadName, $beEmpty), 'email' => array($beBadEmail, $beEmptyEmail));    
    $walk = function($grp) {
        foreach ($grp as $k => $gang){
            foreach($gang as $pair){
                call_user_func_array('doWhen', $pair)($_POST["$k"]); 
            }
        }
    };
    $walk($cbs);
    if(empty($msgs)){
        updateUser($db, $priv);  
    }
    else {
        $error = array_values($msgs)[0];
        $warning = implode(' ', array_keys($msgs));
        $warning .= " warning";
        $warning .= " editclient";
        $id = $_POST['id'];
        $location = "?xid=$id&error=$error&warning=$warning";
        doExit($location);
    }
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