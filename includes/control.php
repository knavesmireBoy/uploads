<?php

$doUpload = doWhen(partial('postWhen', 'action', 'upload'), partial('doUpload', $db, $priv, $key, $domain));
$doView = doWhen(partial('goGet', 'action'), partial('doView', $db));

$doUpload(null);
$doView(null);

if (isset($_POST['action']) and $_POST['action'] === 'delete')
{
    $id = $_POST['id'];
    $call = "confirm";
}
if (isset($_POST['confirm']) and $_POST['confirm'] === 'Yes')
{
    $id = $_POST['id'];
    $call = "confirmed";
    $colleagues = array();
    $extent = 0;
    include $db;
    $result = mysqli_query($link, getColleagues($id, $domain));
    $doError = partialDefer('errorHandler', 'Database error fetching list of users.', $terror);
    doWhen($always(!$result) , $doError) (null);

    while ($row = mysqli_fetch_array($result))
    {
        $colleagues[$row['id']] = $row['name'];
        $extent += 1;
    }
}

if (isset($_POST['confirm']) and $_POST['confirm'] === 'No')
{ //swap
    include $db;
    $extent = 0;//used to determine what views to render
    $id = doSanitize($link, $_POST['id']);
    $result = doQuery($link, getColleagues($id, $domain), 'Database error fetching colleagues.');

    $prompt1 = "Choose <b>yes</b> to select assign a new owner to all ";
    $prompt2 = " files. Choose <b>no</b> to edit a single file";
    $prompt = "$prompt1 client $prompt2";

    while ($row = mysqli_fetch_array($result))
    {
        $colleagues[$row['id']] = $row['name'];
        $extent++;
    }
    $prompt = !$extent ? "$prompt1 user $prompt2" : $prompt;
    $call = "swap";
}
if (isset($_POST['swap']))
{ //SWITCH OWNER OF FILE OR JUST UPDATE DESCRIPTION (FILE AMEND BLOCK)
    //$colleagues = array();
    $button = "Update";
    $extent = 0;
    include $db;
    $id = doSanitize($link, $_POST['id']);
    $answer = $_POST['swap']; //$answer used as conditional to load update.html.php
    $email = "{$_SESSION['email']}";
    $row = prepUpdate($db, $priv, $id, $domain);
    $filename = $row['filename'];
    $diz = $row['description'];
    $userid = $row['userid'];
    $result = doQuery($link, getColleagues($row['id'], $domain) , 'Database error fetching colleagues.');
    while ($row = mysqli_fetch_array($result))
    {
        $colleagues[$row['id']] = $row['name'];
        $extent++;
    }
    if (!$extent)
    {
        $colleagues = prepUpdateUser($db, $priv);
    }
} ///

///////// WILD ////////////// WILD ////////////// WILD ////////////// WILD ///////
///Present list of users for administrators
$vars = getUserList($db, $priv, $domain, $clientname);
foreach ($vars as $k => $v) { ${$k} = $v; }
//$users and $client required at this point
$findmode = isset($_GET['find']) ? true : false;

if (isset($_GET['action']) and $_GET['action'] == 'search')
{
    $vars = doSearch($db, $user_int, $client_domain, $domain, $compose, $order_by, $start, $display);
    foreach ($vars as $k => $v) { ${$k} = $v; }//return $pages an $searched variables
}

$select = getBaseSelect();
$select .= ", user.name as user";
//*the searched SELECT statement has only COUNT(upload.id), but all the current constraints follow in the FROM and WHERE and ORDER clauses, so we simply append the default SELECT which returns all the file info with the constraints
if(!isset($searched)){
$pages = getPages($db, $display, getBestArgs($notPriv)($fileCount, 'emptyString'), $pages);
$from = getBaseFrom();
if ($priv !== 'Admin'){
    if(isset($client_id)){
        $where = " WHERE client.id = $client_id";
    }
    else {
        $email = $_SESSION['email'];
        $where = " WHERE user.email = '$email' ";
    }
}//!Admin
//$select .= ", client.tel";
$sql = $select;
$from .= " LEFT JOIN client ON user.client_id = client.id"; //note LEFT join to include just 'users' also
$order = getBaseOrder($order_by, $start, $display);
$sql .=  $from . $where . $order;
}
else {
    //$select .= ", client.tel";
    $sql = $select;
    $sql .= $searched;
}
include $db;
$result = doQuery($link, $sql, 'Database error fetching files. ' . $sql);
$files = array();
while ($row = mysqli_fetch_array($result))
{
    $files[] = array(
        'id' => $row['id'],
        'user' => (isset($row['user'])) ? $row['user'] : '',
        'email' => $row['email'],
        'filename' => $row['filename'],
        'mimetype' => $row['mimetype'],
        'description' => $row['description'],
        'filepath' => $row['filepath'],
        'file' => $row['file'],
        'origin' => $row['origin'],
        'time' => $row['time'],
        //'tel' => $row['tel'],
        'size' => $row['size']
    );
}
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/ordering.php';