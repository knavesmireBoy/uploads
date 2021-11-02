<?php

if (isset($_POST['action']) && $_POST['action'] == 'upload')
{
    doUpload($db, $priv, $key, $domain);
}

if (isset($_GET['action']) and isset($_GET['id']))
{
    doView($db);
} // end of download/view
if (isset($_POST['action']) and $_POST['action'] == 'delete')
{
    $id = $_POST['id'];
    $call = "confirm";
}
if (isset($_POST['confirm']) and $_POST['confirm'] == 'Yes')
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

if (isset($_POST['confirm']) and $_POST['confirm'] == 'No')
{ //swap
    include $db;
    $extent = 0;
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

 ///Present list of users for administrators
$vars = getUserList($db, $priv, $domain, $clientname);
foreach ($vars as $k => $v)
{
    ${$k} = $v;
}

//$users and $client required at this point
if (isset($_GET['find']))
{
    $findmode = true;
}

if (isset($_GET['action']) and $_GET['action'] == 'search')
{
    $pages = doSearch($db, $priv, $domain, $compose, $order_by, $start, $display);
}
include $db;
$vars = array_map(partial('doSanitize', $link) , $_GET);
//obtain vars from $_GET array, after potential search
foreach ($vars as $k => $v)
{
    ${$k} = $v;
}
$pages = getPages($db, $display, getBestArgs($notPriv)($fileCount, 'emptyString'), $pages);
$start = (isset($_GET['start']) && is_numeric($_GET['start'])) ? $_GET['start'] : 0;

$sort = (isset($_GET['sort']) ? $_GET['sort'] : '');
$sort = isset($lookup[$sort]) ? $lookup[$sort] : $sort;

foreach ($ordering as $k => $v)
{
    if ($k == $sort) break;
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
$from = getBaseFrom();
$select .= ", user.name as user";
//INITIAL FILE SELECTION/////// WILD ////////////// WILD ////////////// WILD ////////////// WILD ///////
//bear in mind, as we are including prompt and update forms BELOW the file list, as opposed to exiting and directing to a separate prompt.html.php or update.html.php ANY vars MAY get overwritten by these $vars in the wild
//possible constraints from search
if ($priv !== 'Admin'){
    if(isset($client_id)){
        $where = " WHERE client.id = $client_id";
    }
    else {
        $email = $_SESSION['email'];
        $where = " WHERE user.email='$email' ";
    }
}//!Admin

$where = getIdTypeQuery($where, $user, $domain);//by user
$where = getFileTypeQuery($where, $suffix);// by file type
$w = isset($text) ? " AND upload.filename LIKE '%$text%'" : '';
$where .= $w;

$sql = $select;
$sql .= ", client.tel";
$from .= " LEFT JOIN client ON user.client_id = client.id"; //note LEFT join to include just 'users' also
$order = getBaseOrder($order_by, $start, $display);
$sql .=  $from . $where . $order;
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
        'tel' => $row['tel'], // ONLY REQUIRED FOR TELEPHONE BLOCK
        'size' => $row['size']
    );
}
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/ordering.php';