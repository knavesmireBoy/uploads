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

function checkExistingDomain($db){
    include $db;
    $sql = "SELECT domain FROM client";
    $dom = isset($_POST['domain']) ? $_POST['domain'] : null;
    $res = doQuery($link, $sql, 'Error retrieving domain from clients.');
    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
    {
        $gang [] = $row['domain'];
    }
    return $dom ? in_array($_POST['domain'], $gang) : false;
}

function prepareChecks($dom){
    return function() use($dom) {
    $msgs = array();
    $compose = curry2(compose('reduce'));
        $dom_reg = '/^[^.]+\.[^.]*\.?[A-Za-z]{2,6}$/';
    $negate = curryLeft2('preg_match', 'negate');
    $match = curryLeft2('preg_match');
    $replace = partial('preg_replace', '/[^0-9]/', '');
    $always = function($arg){
        return function() use($arg) {
            return $arg;
        };
    };
    //need to fix number of ags in callback otherwise preg_match receives an invalid third argument
    //Actually changed signature, but IF we wanted to curryLeft is the way to go, negate flag (equates to true) to reverse predicat    
    $dopush = $compose(populateArray($msgs, ';'));
    //predicates...
    //allows for a word of 2 to 15 characters, followed by up to four words of 1(eg: ampersand) to 15 characters, ie Tom Dick & Harry
    $isName = $negate('/^[\w.]{2,15}(\s[\w&.]{1,15}){0,4}$/');
    $isDomain = $dom ? $match($dom_reg) : $negate($dom_reg);
    $phone_reg = compose('reduce')($replace, $negate("/^[0-9]{10,15}$/"));
    $isPhone = getBestArgs('isEmpty')(partial('doAlways', false), $phone_reg);
    //messages..CONSTANTS supplied as arguments ORDER is critical    
    $checks = array_map($always, func_get_args());
    $beEmpty = array('isEmpty', $dopush($checks[0]));
    $beBadName = array($isName, $dopush($checks[1]));
    $beEmptyDomain = array('isEmpty', $dopush($checks[2]));
    $beBadDomain = array($isDomain, $dopush($checks[3]));
    $beBadPhone = array($isPhone, $dopush($checks[4]));
    
    $name_checks = array($beBadName, $beEmpty);
    $domain_checks = array($beBadDomain, $beEmptyDomain);
    $cbs = array('name' => $name_checks, 'domain' => $domain_checks, 'tel' => array($beBadPhone));
    doWhenLoop($cbs);
    return $msgs;
};
}

function validateClient($db, $edit = false){
    $location = '.';
    $domain_exists = $edit ? false : checkExistingDomain($db);
    $constant = $domain_exists ? "xdomain;DOMAIN <b>{$_POST['domain']}</b> already exists" : VALIDATE_DOMAIN;
    $msgs = prepareChecks($domain_exists)(REQUIRED_NAME, VALIDATE_NAME, REQUIRED_DOMAIN, $constant, VALIDATE_PHONE);   
    $action = !empty($edit) ? 'Edit' : 'Add';
    
    if(empty($msgs)){
        if(!empty($edit)){
            updateClient($db, $priv);
        }
        else {
            createClient($db);
        }
    }
    else {
        if($action === 'Add'){
            $location = reLoad($msgs, "editclient", "&action=$action");
            $helper = preserveValidFormValues($location, '&', 'x', '=');
            $location = $helper("&name={$_POST['name']}", "&domain={$_POST['domain']}", "&tel={$_POST['tel']}");

        }
        else {
            $location = reLoad($msgs, "editclient", "&xid={$_POST['id']}&action=$action");
        }
        doExit($location);
    }
    
}