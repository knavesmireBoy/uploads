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
	if (isset($_POST['employer']) && !empty($_POST['employer']))
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
    setcookie('success', 'User succesfully added', time() + 7200, "/");
	doExit();
}

function prepareChecks(){
    
    $compose = curry2(compose('reduce'));
    $f = curryLeft2('preg_match', 'negate');
    //need to fix number of ags in callback otherwise preg_match receives an invalid third argument
    //Actually changed signature, but IF we wanted to curryLeft is the way to go, negate flag (equates to true) to reverse predicate
    $isName = $f('/^[\w.]{2,20}(\s[\w]{2,20})?$/');
    $isEmail = $f('/^[\w][\w.-]+@[\w][\w.-]+\.[A-Za-z]{2,6}$/');
    $isPwd = compose('reduce')('strlen', curry2('lesserThan')(3));
            
    $is_empty = partial('doAlways','xname;NAME is a required field');
    $is_empty_email = partial('doAlways','xemail;EMAIL is a required field');
    $is_empty_pwd = partial('doAlways','xpassword;a PASSWORD is required to login');
    $is_password = partial('doAlways','xpassword;PASSWORD should be at least three characters in length');
    $is_name = partial('doAlways','xname;Please supply name in expected format');
    $is_email = partial('doAlways','xemail;Please supply a valid email address');
    
    $push = function(&$grp){
        return function($v) use (&$grp) {
            $res = explode(';', $v);
            $grp[$res[0]] = $res[1];
        };
    };
    $dopush = $compose($push($msgs));
    $beEmpty = array('isEmpty', $dopush($is_empty));
    $beEmptyEmail = array('isEmpty', $dopush($is_empty_email));
    $beEmptyPwd = array('isEmpty', $dopush($is_empty_pwd));
    $beBadName = array($isName, $dopush($is_name));
    $beBadEmail = array($isEmail, $dopush($is_email));
    $beBadPwd = array($isPwd, $dopush($is_password));
    $name_checks = array($beBadName, $beEmpty);
    $email_checks = array($beBadEmail, $beEmptyEmail);
    $password_checks = array($beBadPwd, $beEmptyPwd);
    //leave password AS IS if field not set
    $password_checks = empty($_POST['password'] && !empty($edit)) ? array() : $password_checks;
    return array('name' => $name_checks, 'email' => $email_checks, 'password' => $password_checks);
    
}

function validateUser($db, $priv, $edit = false){
    $msgs = array();
    $location = '.';
    $compose = curry2(compose('reduce'));
    $f = curryLeft2('preg_match', 'negate');
    //need to fix number of ags in callback otherwise preg_match receives an invalid third argument
    //Actually changed signature, but IF we wanted to curryLeft is the way to go, negate flag (equates to true) to reverse predicate
    $isName = $f('/^[\w.]{2,20}(\s[\w]{2,20})?$/');
    $isEmail = $f('/^[\w][\w.-]+@[\w][\w.-]+\.[A-Za-z]{2,6}$/');
    $isPwd = compose('reduce')('strlen', curry2('lesserThan')(3));
            
    $is_empty = partial('doAlways','xname;NAME is a required field');
    $is_empty_email = partial('doAlways','xemail;EMAIL is a required field');
    $is_empty_pwd = partial('doAlways','xpassword;a PASSWORD is required to login');
    $is_password = partial('doAlways','xpassword;PASSWORD should be at least three characters in length');
    $is_name = partial('doAlways','xname;Please supply name in expected format');
    $is_email = partial('doAlways','xemail;Please supply a valid email address');
    
    $push = function(&$grp){
        return function($v) use (&$grp) {
            $res = explode(';', $v);
            $grp[$res[0]] = $res[1];
        };
    };
    $dopush = $compose($push($msgs));
    $beEmpty = array('isEmpty', $dopush($is_empty));
    $beEmptyEmail = array('isEmpty', $dopush($is_empty_email));
    $beEmptyPwd = array('isEmpty', $dopush($is_empty_pwd));
    $beBadName = array($isName, $dopush($is_name));
    $beBadEmail = array($isEmail, $dopush($is_email));
    $beBadPwd = array($isPwd, $dopush($is_password));
    $password_checks = array($beBadPwd, $beEmptyPwd);
    //leave password AS IS if field not set
    $password_checks = empty($_POST['password'] && !empty($edit)) ? array() : $password_checks;
    
    //$checks = prepareChecks();
    $cbs = array('name' => array($beBadName, $beEmpty), 'email' => array($beBadEmail, $beEmptyEmail), 'password' => $password_checks);    
    //$cbs = array('name' => $checks['name'], 'email' => $checks['email'], 'password' => $checks['password']);    
    $walk = function($grp) {
        foreach ($grp as $k => $gang){
            foreach($gang as $pair){
                call_user_func_array('doWhen', $pair)($_POST["$k"]); 
            }
        }
    };
    $walk($cbs);
    if(empty($msgs)){
        if(!empty($edit)){
            updateUser($db, $priv);
        }
        else {
            addUser($db);
        }
    }
    else {
        $error = array_values($msgs)[0];
        $warning = implode(' ', array_keys($msgs));
        $warning .= " warning";
        $warning .= " editclient";
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $action = !empty($edit) ? 'edit' : 'add';
        $location = "?xid=$id&action=$action&error=$error&warning=$warning";
        if($action === 'add'){
            if(!inString('xname', $warning)){
                $name = $_POST['name'];
                $location .= "&name=$name";
            }
            if(!inString('xemail', $warning)){
                $email = $_POST['email'];
                setcookie('eemail', $email, time() + 7200, '/');
            }
        }
    }
    doExit($location);
}

function updateUser($db, $priv){
    //dump($_POST);
    include $db;
    $id = doSanitize($link, $_POST['id']);
	$name = doSanitize($link, $_POST['name']);
	$email = doSanitize($link, $_POST['email']);
    $pwd;
	$sql = "UPDATE user SET name='$name', email='$email' WHERE id='$id'";
    
    doQuery($link, $sql, 'Error setting user details.');
	if (isset($_POST['password']) && !empty($_POST['password']))
	{
		setPassword($link, $_POST['password'], $id);
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
    //$location = (isset($pwd)) ? "?pwdlen&id=$id" : ($priv == 'Client' ? '..' : '.');
    $location = $priv == 'Client' ? '..' : '.';
    setcookie('success', 'Details successfully updated', time() + 7200, "/");
	doExit($location);
}