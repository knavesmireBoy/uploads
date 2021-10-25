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
    $sql= "INSERT INTO client SET name='$name', domain='$domain', tel='$tel'";
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
