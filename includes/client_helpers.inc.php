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

function checkExistingDomain($db){
     include $db;
    $sql = "SELECT domain FROM client";
    $res = doQuery($link, $sql, 'Error retrieving domain from clients.');
    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)){
        $gang [] = $row['domain'];
    }
    return in_array($_POST['domain'], $gang);
}

function prepareChecks(){
    $msgs = array();
    $compose = curry2(compose('reduce'));
    $match = curryLeft2('preg_match', 'negate');
    $match2 = curryLeft2('preg_match');
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
    $isName = $match('/^[\w.]{2,15}(\s[\w&.]{1,15}){0,4}$/');
    $isDomain = $match2('/^[^.]+\.[^.]*\.?[A-Za-z]{2,6}$/');
    $phone_reg = compose('reduce')($replace, $match("/^[0-9]{10,15}$/"));
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
}

function validateClient($db, $edit = false){
    $location = '.';
    $domain_exists = checkExistingDomain($db);
    $constant = $domain_exists ? "xdomain;DOMAIN {$_POST['domain']} already exists" : VALIDATE_DOMAIN;
    $msgs = prepareChecks(REQUIRED_NAME, VALIDATE_NAME, REQUIRED_DOMAIN, $constant, VALIDATE_PHONE);
    
   
    
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
        $action = !empty($edit) ? 'Edit' : 'Add';
        $default = "action=$action&error=$error&warning=$warning";
        
        if($action === 'Add'){
            $location = "?$default";
            if(!inString('xname', $warning) && $_POST['name']){
                $name = $_POST['name'];
                $location .= "&name=$name";
            }
            if(!inString('xdomain', $warning) && $_POST['domain']){
                $domain = $_POST['domain'];
                $location .= "&domain=$domain";
            }
            if(!inString('xphone', $warning) && isset($_POST['tel'])){
                $tel = $_POST['tel'];
                $location .= "&tel=$tel";
            }
        }
        else {
            $location = "xid=$default";
        }
        
    }
    doExit($location);
}