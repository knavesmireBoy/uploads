<?php
function databaseContainsUser($email, $password)
{
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
    $email = doSanitize($link, $email);
    $password = doSanitize($link, $password);
    $sql = "SELECT COUNT(*) FROM user INNER JOIN userrole ON user.id = userrole.userid WHERE email='$email' AND password='$password' ";
    $result = doQuery($link, $sql, 'Error searching for user.');
    $row = goFetch($result);
    return ($row[0] > 0) ? true : false;
}

function userIsLoggedIn()
{
    
    if (isset($_POST['action']) &&  $_POST['action'] == 'login')
    {
        if (!isset($_POST['email']) || $_POST['email'] == '' || !isset($_POST['password']) or $_POST['password'] == '')
        {
            $GLOBALS['loginError'] = 'Please fill in both fields';
            return false;
        }
        $password = md5($_POST['password'] . 'uploads');

        if (databaseContainsUser($_POST['email'], $password))
        {
            session_start();
            $keys = array('loggedIn', 'email', 'password');
            $values = array(true, $_POST['email'], $password);
            array_map('setSessionBridge', array(array_combine($keys, $values)));
            return true;
        }
        else
        {
            session_start();
            array_map('unsetSession', array('loggedIn', 'email', 'password'));
            $GLOBALS['loginError'] = 'The specified email address or password was incorrect.';
            return false;
        }
    } //end of log in attempt
    if (isset($_POST['action']) and $_POST['action'] == 'logout')
    {
        session_start();
        array_map('unsetSession', array('loggedIn', 'email', 'password'));
        header("Location: " . $_POST['goto']);
        exit();
    } //end of logout
    session_start();
    if (isset($_SESSION['loggedIn']))
    {
        return databaseContainsUser($_SESSION['email'], $_SESSION['password']);
    }
} // end of user check

function userHasWhatRole()
{
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
    $email = doSanitize($link, $_SESSION['email']);
    $sql = "SELECT userrole.roleid, user.id, count(user.id) as total FROM userrole INNER JOIN user ON user.id = userrole.userid WHERE user.email = '$email' GROUP BY userrole.roleid, user.id";
    //AND  userrole.roleid != 'Browser'
    $result = doQuery($link, $sql, 'Error searching for user roles.');
    $row = goFetch($result, MYSQLI_ASSOC);
    return $row['total'] > 0 ? $row : false;
}
function email($em, $id)
{
    echo $em;
}

function validateAccess(){
    $roleplay = userHasWhatRole();
    if(!in_array($roleplay['roleid'], func_get_args()))
{
	$error = 'Only Account Administrators may access this page!';
	include '../templates/accessdenied.html.php';
	exit();
}
return $roleplay;
}