<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';

function updateClient($db){
    include $db;    
    $vars = array_map(partial('doSanitize', $link), $_POST);
    foreach($vars as $k => $v) {
        ${$k} = $v;
    }
    $sql = "UPDATE client SET name='$name', domain='$domain', tel='$tel' WHERE id=$id";
    doQuery($link, $sql, 'Error setting client details');
    doExit();
}

function createClient($db){
    include $db;    
    $vars = array_map(partial('doSanitize', $link), $_POST);
    foreach($vars as $k => $v) {
        ${$k} = $v;
    }
    
    $sql = "SELECT domain FROM client";
    $res = doQuery($link, $sql, 'Error retrieving domain from clients.');
    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)){
        $gang [] = $row['domain'];
    }
    if(in_array($domain, $gang)){
        doExit("?add=true&name=$name&domain=true&tel=$tel");
    }
    $sql = "INSERT INTO client SET name='$name', domain='$domain', tel='$tel'";
    //$sql = "INSERT INTO client VALUES ('?', '$name', '$domain', '$tel')";
    //alert required for non unique domains. I attempted to enter uni.com
    doQuery($link, $sql, 'Error adding client.');
    doExit();
}

function deleteClient($db){
    if ($_POST['confirm'] == 'Yes')
{
    include $db;
    $id = doSanitize($link, $_POST['id']);
    doQuery($link, "DELETE FROM client WHERE id = $id", 'Error deleting client.');
    //echo mysqli_errno($link) . ": " . mysqli_error($link) . "\n";
}
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
    $isName = $f('/^[\w.]{2,20}(\s[\w]{2,20}){,4}$/');
    //$isEmail = $f('/^[\w][\w.-]+@[\w][\w.-]+\.[A-Za-z]{2,6}$/');
    $isDomain = $f('/^[\w][\w.-]+\.[A-Za-z]{2,6}$/');
    //$isPwd = compose('reduce')('strlen', curry2('lesserThan')(3));
    //messages..CONSTANTS supplied as arguments ORDER is critical    
    $checks = array_map($always, func_get_args());
    $beEmpty = array('isEmpty', $dopush($checks[0]));
    $beBadName = array($isName, $dopush($checks[1]));
    $beEmptyDomain = array('isEmpty', $dopush($checks[2]));
    $beBadDomain = array($isEmail, $dopush($checks[3]));
    
    $name_checks = array($beBadName, $beEmpty);
    $domain_checks = array($beBadDomain, $beEmptyDomain);
    $cbs = array('name' => $name_checks, 'email' => $email_checks, 'password' => $password_checks);
    doWhenLoop($cbs);
    return $msgs;
}

function validateClient($db, $edit = false){
    $location = '.';
    $msgs = prepareChecks(REQUIRED_NAME, VALIDATE_NAME, REQUIRED_DOMAIN, VALIDATE_DOMAIN);
    if(empty($msgs)){
        if(!empty($edit)){
            updateClient($db, $priv);
        }
        else {
            createClient($db);
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
                $domain = $_POST['domain'];
                setcookie('ddomain', $domain, time() + 7200, '/');
            }
        }
    }
    doExit($location);
}