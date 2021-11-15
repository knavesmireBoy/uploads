<?php

function doConfirm($db, $action){
    if($action === 'Yes'){
        include $db;
        $id = doSanitize($link, $_POST['id']);
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
    $msgs = array();
    $compose = curry2(compose('reduce'));
    $f = curryLeft2('preg_match', 'negate');
    $always = function($arg){
        return function() use($arg) {
            return $arg;
        };
    };
    //need to fix number of ags in callback otherwise preg_match receives an invalid third argument
    //Actually changed signature, but IF we wanted to curryLeft is the way to go, negate flag (equates to true) to reverse predicat    
    $dopush = $compose(populateArray($msgs, ';'));
    //predicates...
    $isName = $f('/^[\w.]{2,20}(\s[\w]{2,20})?$/');
    $isEmail = $f('/^[\w][\w.-]+@[\w][\w.-]+\.[A-Za-z]{2,6}$/');
    $isPwd = compose('reduce')('strlen', curry2('lesserThan')(3));
    //messages..CONSTANTS supplied as arguments ORDER is critical    
    $checks = array_map($always, func_get_args());
    $beEmpty = array('isEmpty', $dopush($checks[0]));
    $beBadName = array($isName, $dopush($checks[1]));
    $beEmptyEmail = array('isEmpty', $dopush($checks[2]));
    $beBadEmail = array($isEmail, $dopush($checks[3]));
    $beEmptyPwd = array('isEmpty', $dopush($checks[4]));
    $beBadPwd = array($isPwd, $dopush($checks[5]));
    
    $name_checks = array($beBadName, $beEmpty);
    $email_checks = array($beBadEmail, $beEmptyEmail);
    $password_checks = array($beBadPwd, $beEmptyPwd);
    //leave password AS IS if field not set
    $password_checks = empty($_POST['password'] && !empty($edit)) ? array() : $password_checks;        
    $cbs = array('name' => $name_checks, 'email' => $email_checks, 'password' => $password_checks);
    doWhenLoop($cbs);
    return $msgs;
}

function validateUser($db, $priv, $edit = false){
    $location = '.';
    $msgs = prepareChecks(REQUIRED_NAME, VALIDATE_NAME, REQUIRED_EMAIL, VALIDATE_EMAIL, REQUIRED_PWD, VALIDATE_PWD);
    if(empty($msgs)){
        if(!empty($edit)){
            updateUser($db, $priv);
        }
        else {
            addUser($db);
        }
    }
    else {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $action = !empty($edit) ? 'Edit' : 'add';
        $location = reLoad($msgs, "editclient", "&xid=$id&action=$action");
        if($action === 'add'){
            $helper = preserveValidFormValues($location, '&', 'x', '=');
            $location = $helper("&name={$_POST['name']}");
            if(!empty($_POST['email'])){
              setcookie('eemail', $_POST['email'], time() + 7200, '/');  
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
    $location = $priv == 'Client' ? '..' : '.';
    setcookie('success', 'Details successfully updated', time() + 7200, "/");
	doExit($location);
}