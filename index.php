<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$base = 'Log In';
$error = '';
$tmpl_error = '/uploads/includes/error.html.php';
$myip = '86.133.121.115.';
$display = 10;
$findmode = false;
$lookup = array( 'tu' => 'ut');
$doError = function () {};

if (!userIsLoggedIn()) {
    include $tmplt . 'base.html.php';
    include $tmplt . 'login.html.php';
    exit();
}
$roleplay = userHasWhatRole();
//public page
$doError = partialDefer('errorHandler', 'Only valid clients may access this page.', $tmplt . 'accessdenied.html.php');
doWhen($always(!$roleplay), $doError)(null);
$key = $roleplay['id'];
$priv = $roleplay['roleid'];
$domain = "RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))"; //!!?!! V. USEFUL VARIABLE IN GLOBAL SPACE
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';

if (isset($_POST['action']) and $_POST['action'] == 'upload') {
    doUpload($db, $priv, $key, $domain);
}

if (isset($_GET['action']) and isset($_GET['id'])) {
   doView($db);
} // end of download/view
if (isset($_POST['action']) and $_POST['action'] == 'delete') {
     $id = $_POST['id'];
    $call = "confirm";
}
if (isset($_POST['confirm']) and $_POST['confirm'] == 'Yes') {
    $id = $_POST['id'];
    $call = "confirmed";
    $colleagues = array();
    $extent = 0;
    include $db;
    $result = mysqli_query($link, getColleagues($id, $domain));
    $doError = partialDefer('errorHandler', 'Database error fetching list of users.', $terror);
    doWhen($always(!$result), $doError) (null);
    
    while ($row = mysqli_fetch_array($result)) {
        $colleagues[$row['id']] = $row['name'];
        $extent+= 1;
    }
}
if (isset($_POST['extent'])) {
    doDelete($db, $compose);
}
if (isset($_POST['confirm']) and $_POST['confirm'] == 'No') { //swap
    include $db;
    $extent = 0;
    $id = doSanitize($link, $_POST['id']);
    $result = mysqli_query($link, getColleagues($id, $domain));
    $doError = partialDefer('errorHandler', 'Database error fetching colleagues.', $terror);
    doWhen($always(!$result), $doError) (null);
    
    $prompt1 = "Choose <b>yes</b> to select assign a new owner to all ";
    $prompt2 = " files. Choose <b>no</b> to edit a single file";
    $prompt = "$prompt1 client $prompt2";
    doWhen($always(!$result), $doError) (null);
    while ($row = mysqli_fetch_array($result)) {
        $colleagues[$row['id']] = $row['name'];
        $extent++;
    }
    $prompt = !$extent ? "$prompt1 user $prompt2" : $extent === 1 ? "continue" : $prompt;
    $id = $_POST['id'];
    $call = "swap";
}
if (isset($_POST['swap'])) { //SWITCH OWNER OF FILE OR JUST UPDATE DESCRIPTION (FILE AMEND BLOCK)
    //$colleagues = array();
    $button = "Update";
    $extent = 0;
    include $db;
    $id = doSanitize($link, $_POST['id']);
    
    $answer = $_POST['swap'];//$answer used as conditional to load update.html.php
    $email = "{$_SESSION['email']}";
    $row = prepUpdate($db, $priv, $id, $domain);
    $filename = $row['filename'];
    $diz = $row['description'];
    $userid = $row['userid'];
    $result = mysqli_query($link, getColleagues($row['id'], $domain));
    $doError = partialDefer('errorHandler', 'Database error fetching colleagues.', $terror);
    doWhen(partial('doAlways',!$result), $doError) (null);
    
    while ($row = mysqli_fetch_array($result)) {
        $colleagues[$row['id']] = $row['name'];
        $extent++;
    }
    if(!$extent){
        $colleagues = prepUpdateUser($db, $priv);
    }
} ///
if (isset($_POST['update'])) { // 'update' is common to both options of file amend block
    doUpdate($db);
}
//a default block___________________________________________________________________

if (isset($_GET['p']) and is_numeric($_GET['p'])) {
    $pages = $_GET['p'];
} else { // counts all files
    include $db;
    $sql = "SELECT COUNT(upload.id) from upload ";
    if ($priv == 'Client') {
        $email = $_SESSION['email'];
        $sql.= " INNER JOIN user on upload.userid = user.id WHERE user.email='$email'";
    }
    $r = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Database error fetching requesting the list of files', $terror);
    doWhen($always(!$r), $doError) (null);

    $row = mysqli_fetch_array($r, MYSQLI_NUM);
    $records = $row[0];
    $pages = ($records > $display) ? ceil($records / $display) : 1;
} //end of IF NOT PAGES SET
$start = (isset($_GET['s']) and is_numeric($_GET['s'])) ? $_GET['s'] : 0;
$sort = (isset($_GET['sort']) ? $_GET['sort'] : '1');

$sort = isset($lookup[$sort]) ? $lookup[$sort] : $sort;
$meswitch = array('f' => 'filename ASC', 'ff' => 'filename DESC', 'u' => 'user ASC', 'uu' => 'user DESC', 'uf' => 'user ASC, filename ASC', 'uuf' => 'user DESC, filename ASC', 'uff' => 'user ASC, filename DESC', 'uuff' => 'user DESC, filename DESC', 'ut' => 'user ASC, time ASC', 'utt' => 'user ASC, time DESC', 'uut' => 'user DESC, time ASC', 'uutt' => 'user DESC, time DESC', 't' => 'time ASC', 'tt' => 'time DESC');

foreach ($meswitch as $k => $v) {
    if ($k == $sort) break;
}
switch ($sort) {
    case $k:
        $order_by = $meswitch[$k];
    break;
    default:
        $order_by = 'time DESC';
        $sort = 'tt';
    break;
}
//D I S P L A Y_______________________________________________________________
include $db; ///Present list of users for administrators
$user_id = null;
$text = null;
$suffix = null;
$sql = "SELECT user.id, user.name FROM user LEFT JOIN client ON user.client_id=client.id WHERE client.domain IS NULL ORDER BY name";
$result = mysqli_query($link, $sql);
$doError = partialDefer('errorHandler', 'Database error fetching users.', $terror);
doWhen(partial('doAlways', !$result), $doError) (null);

while ($row = mysqli_fetch_array($result)) {
    $users[$row['id']] = $row['name'];
}
$sql = "SELECT name, domain, tel FROM client ORDER BY name";
$result = mysqli_query($link, $sql);
$doError = partialDefer('errorHandler', 'Database error fetching clients.', $terror);
doWhen(partial('doAlways', !$result), $doError) (null);


while ($row = mysqli_fetch_array($result)) {
    $client[$row['domain']] = $row['name'];
}
//end of default_______________________________________________________________________
if (isset($_GET['find'])) {
    
    $isAdmin = partial('equals', $priv, 'Admin');
    $prepFind = partial('prepFind', $users, $client);
    $punter = $compose(partial('doFind', $db, $key, $domain), $prepFind);
    $admin = $compose('prepFind');
    $perform = getBest(negate($isAdmin));
    $perform($punter, $prepFind)();//CUSTOMISES SELECT MENU FOR NON ADMIN
    $findmode = true;
}

if (isset($_GET['action']) and $_GET['action'] == 'search') {
    doSearch($db, $priv, $domain, $compose, $order_by, $start, $display, $client, $users, $myip);
}
//INITIAL FILE SELECTION
if ($priv == 'Admin') {
    $select = getBaseSelect();
    
    $select .= ", user.name as user"; 
    $from = getBaseFrom();
    $from.= " INNER JOIN userrole ON user.id = userrole.userid";
    $where = ' WHERE TRUE';
    $ext = isset($_GET['ext']) ? doSanitize($link, $_GET['ext']) : null;
    $where = getFileTypeQuery($where, $ext);
    
    //pagination stuff for users??
    if (isset($useroo) && is_numeric($useroo)) { //CLIENTS USE EMAIL DOMAIN AS ID THERFORE NOT A NUMBER
        if($useroo = doSanitize($link, $_GET['u'])){
            $where.= " AND user.id=$useroo";
        }
    } else if (isset($_GET['u'])) {
        if($useroo = doSanitize($link, $_GET['u'])){
            $where .= " AND $domain = '$useroo'";
        }
    }
    
    if (isset($_GET['t'])){
        if($textme = doSanitize($link, $_GET['t'])){
           $where.= " AND upload.filename LIKE '%$textme%'"; 
        }
    }
    
}//admin
else {
    $select = getBaseSelect();
    $from = getBaseFrom();
    $email = $_SESSION['email'];
    $from.= " INNER JOIN userrole ON user.id = userrole.userid";
    $where = " WHERE user.email='$email' ";
}
//$sql= $select . $from . $where . $order; //DEFAULT; TELEPHONE BLOCK REQUIRED TO OBTAIN CLIENT PHONE NUMBER
$sql = $select;
$select_tel = ", client.tel";
$from .= " LEFT JOIN client ON user.client_id = client.id"; //note LEFT join to include just 'users' also
$order = getBaseOrder($order_by, $start, $display);
$sql .= $select_tel . $from . $where . $order;

$result = doFetch($link, $sql, 'Database error fetching files. ' . $sql);
$files = array();
while ($row = mysqli_fetch_array($result)) {
    $files[] = array('id' => $row['id'], 'user' => (isset($row['user'])) ? $row['user'] : '', 'email' => $row['email'], 'filename' => $row['filename'], 'mimetype' => $row['mimetype'], 'description' => $row['description'], 'filepath' => $row['filepath'], 'file' => $row['file'], 'origin' => $row['origin'], 'time' => $row['time'], 'tel' => $row['tel'], // ONLY REQUIRED FOR TELEPHONE BLOCK
    'size' => $row['size']);
}
$base = 'North Wolds Printers | The File Uploads';

include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/files.html.php';
if($findmode){
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/search.html.php';
}

?>