<?php

//$doUpload = doWhen(partial('postWhen', 'action', 'upload'), partial('doUpload', $db, $priv, $key, $domain));
//$doView = doWhen(partial('goGet', 'action'), partial('doView', $db));

//$doUpload(null);
//$doView(null);
setcookie('filename', "", time() -1, "/");
if (isset($_REQUEST['swap']))
{ //SWITCH OWNER OF FILE OR JUST UPDATE DESCRIPTION (FILE AMEND BLOCK)
    $button = "update";
    include $db;
    $id = doSanitize($link, $_REQUEST['id']);

    $answer = $_REQUEST['swap']; //$answer used as conditional to load update.html.php
    $email = "{$_SESSION['email']}";
    
    $row = prepUpdate($db, $id);
    //preserves potentially updated filename on description error, cute
    $filename = !empty($_GET['filename']) ? $_GET['filename'] : $row['filename'];
    $diz = $row['description'];
    $userid = $row['userid'];
    
    $colleagues = doGetColleagues($link, $id, $domain);
    $extent = count($colleagues);
    if (!$extent)
    {
        $colleagues = $prepareUserList($db);
    }
    $equals = partial(equality(true), $userid);
    setcookie('filename', $filename, time() + 7200, '/');
} ///

if (isset($_POST['action']) && $_POST['action'] === 'upload')
{
    doUpload($db, $priv, $key, $domain);
}

if (isset($_GET['action']) and isset($_GET['id']))
{
    doView($db);
} // end of download/view

if (isset($_POST['action']) and $_POST['action'] === 'delete')
{
    $id = $_POST['id'];
    $call = "confirm";
    $submit = "choose";
}
if (isset($_POST['confirm']) and $_POST['confirm'] === 'Yes')
{
    $id = $_POST['id'];
    $call = "confirmed";
    $colleagues = array();
    $extent = 0;
    include $db;
    $res = doQuery($link, getColleagues($id, $domain), 'Database error fetching list of users.');
    $colleagues = doProcess($res, 'id', 'name');//for assigning to client
    $extent = count($colleagues);
    $submit = "delete innit";
}

if (isset($_POST['confirm']) and $_POST['confirm'] === 'No')
{ //swap
    include $db;
    
    $id = doSanitize($link, $_POST['id']);
    $qs = "id=$id&swap=No";
    $query_string = $_SERVER['QUERY_STRING'];
    if(!empty($query_string)){
        $query_string = preg_replace('/(\?[a-z0-9=&]*)(&sort)([a-z]*)/', '$1$2', '?' . $query_string);
        $query_string .= "&$qs";
    }
    else {
        $query_string .= "?$qs";
    }
    $colleagues = doGetColleagues($link, $id, $domain);
    $extent = count($colleagues);
    
    if($extent <= 1){
        header("Location: $query_string");
        exit();
    }
    $prompt1 = "Choose <b>YES</b> to select assign a new owner to all ";
    $prompt2 = " files. Choose <b>NO</b> to edit a single file";
    $prompt = "$prompt1 client $prompt2";
    $prompt = !$extent ? "$prompt1 user $prompt2" : $prompt;
    $call = "swap";
    $submit = "choose";
}
///////// WILD ////////////// WILD ////////////// WILD ////////////// WILD ///////
///Present list of users for administrators
$vars = populateList($db, $domain, $user_int, $clientname);
foreach ($vars as $k => $v) { ${$k} = $v; }
//$users and $client required at this point
if(isset($_GET['find'])){
    $findmode = true;
    $zero = $user_int == 2 ? true : null;
}


if (isset($_GET['action']) and $_GET['action'] == 'search')
{
    $vars = doSearch($db, $user_int, $client_domain, $domain, $compose, $order_by, $start, $display);
    foreach ($vars as $k => $v) { ${$k} = $v; }//return $pages an $searched variables
}

// TABLE ORDERING...
$start = (isset($_GET['start']) && is_numeric($_GET['start'])) ? $_GET['start'] : 0;

$sort = (isset($_GET['sort']) ? $_GET['sort'] : '');
$sort = isset($lookup[$sort]) ? $lookup[$sort] : $sort;

foreach ($ordering as $k => $v)
{
    if ($k === $sort) break;
}
switch ($sort)
{
    case $k:
        $order_by = $ordering[$k];
    break;
    default:
        $order_by = 'time DESC';
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

//$doSelected = $compose('optionOpenTag', curry2('invokeArg')(getBestArgs($equals)($always(" selected = 'selected' "), $always(""))));
$doSelected = $compose(partial('completeTag', 'option', 'value'), curry2('invokeArg')(getBestArgs($equals)($always(" selected = 'selected' "), $always(""))));
$doOpt = getBestArgs(negate(partial('isEmpty', $users)))('optGroupOpen', $always(""));
$doOptEnd = getBestArgs(negate(partial('isEmpty', $users)))('optGroupClose', $always(""));
$isTrueClient = partial('array_reduce', [$isClient, $compose(partial('count', $client), curry2('greaterThan')(1))], 'every', true);

include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/ordering.php';