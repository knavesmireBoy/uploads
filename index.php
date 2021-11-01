<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$base = 'Log In';
$error = '';
$tmpl_error = '/uploads/includes/error.html.php';
$doError = function (){};

if (!userIsLoggedIn())
{
    include $tmplt . 'base.html.php';
    include $tmplt . 'login.html.php';
    exit();
}
$roleplay = userHasWhatRole();
//public page
$doError = partialDefer('errorHandler', 'Only valid clients may access this page.', $tmplt . 'accessdenied.html.php');
doWhen($always(!$roleplay) , $doError) (null);

$key = $roleplay['id'];
$priv = $roleplay['roleid'];

$domain = "RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))"; //!!?!! V. USEFUL VARIABLE IN GLOBAL SPACE
$fileCount = curry2('fileCountByUser') ($domain);
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';

$myip = '86.133.121.115.';
$text = null;
$suffix = null;
$user = null;
$tel = '';

$start = 1;
$display = 10;
$findmode = false;
$order_by = 'time DESC';

$base = 'File Uploads';

$doDelete = doWhen(partial('goPost', 'extent') , partial('doDelete', $db, $compose));
$doUpdate = doWhen(partial('goPost', 'update') , partial('doUpdate', $db));
//doWhen expects an argument
$doDelete(null);
$doUpdate(null);
$clientname = getClientName($db, "{$_SESSION['email']}", $domain);
$username = getUserName($db, "{$_SESSION['email']}");
$name = $username;

if (isset($_POST['action']) && $_POST['action'] == 'upload' && $priv !== 'Browser')
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
    $result = doQuery($link, getColleagues($id, $domain) , 'Database error fetching colleagues.');

    $prompt1 = "Choose <b>yes</b> to select assign a new owner to all ";
    $prompt2 = " files. Choose <b>no</b> to edit a single file";
    $prompt = "$prompt1 client $prompt2";

    while ($row = mysqli_fetch_array($result))
    {
        $colleagues[$row['id']] = $row['name'];
        $extent++;
    }
    $prompt = !$extent ? "$prompt1 user $prompt2" : $extent === 1 ? "continue" : $prompt;
    $id = $_POST['id'];
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
//D I S P L A Y_______________________________________________________________
include $db; ///Present list of users for administrators
$sql = "SELECT user.id, user.name FROM user LEFT JOIN client ON user.client_id=client.id WHERE client.domain IS NULL ORDER BY name";
$result = doQuery($link, $sql, 'Database error fetching users.');
$users = doProcess($result, 'id', 'name');

$sql = "SELECT name, domain, tel FROM client ORDER BY name";
$result = doQuery($link, $sql, 'Database error fetching clients.');
$client = doProcess($result, 'domain', 'name');

//end of default_______________________________________________________________________
if (isset($_GET['find']))
{
    $isAdmin = partial('equals', $priv, 'Admin');
    $prepFind = partial('prepFind', $users, $client);
    $punter = $compose(partial('doFind', $db, $key, $domain) , $prepFind);
    $admin = $compose('prepFind');
    $perform = getBest(negate($isAdmin));
    $perform($punter, $prepFind) (); //CUSTOMISES SELECT MENU FOR NON ADMIN
    $findmode = true;
}

if (isset($_GET['action']) and $_GET['action'] == 'search')
{
    doSearch($db, $priv, $domain, $compose, $order_by, $start, $display, $client, $users, $myip);
}
//a default block___________________________________________________________________
$vars = array_map(partial('doSanitize', $link) , $_GET);
foreach ($vars as $k => $v)
{
    ${$k} = $v;
}

if (isset($_GET['page']) and is_numeric($_GET['page']))
{
    $pages = $_GET['page'];    
}
else
{ // counts all files
    include $db;
    $sqlc = "SELECT COUNT(upload.id) from upload ";
    if ($priv == 'Client')
    {
        $email = $_SESSION['email'];
        //" INNER JOIN userrole ON user.id=userrole.userid";
        $sqlc .= " INNER JOIN user on upload.userid = user.id WHERE user.email='$email' ";
    }
    elseif (isset($user))
    {
        $sqlc .= $fileCount($user); //user or client
        
    }
    $res = doQuery($link, $sqlc, 'Database error fetching requesting the list of files');
    $row = goFetch($res, MYSQLI_NUM);

    $records = intval($row[0]);
    $pages = ($records > $display) ? ceil($records / $display) : 1;
} //end of IF NOT PAGES SET
$start = (isset($_GET['start']) && is_numeric($_GET['start'])) ? $_GET['start'] : 0;

$lookup = array(
    'tu' => 'ut',
    'fu' => 'uf',
    'utu' => 'uut',
    'uufu' => 'uf',
    'uutu' => 'ut'
);
$sort = (isset($_GET['sort']) ? $_GET['sort'] : '');

$sort = isset($lookup[$sort]) ? $lookup[$sort] : $sort;
$meswitch = array(
    'f' => 'filename ASC',
    'ff' => 'filename DESC',
    'u' => 'user ASC',
    'uu' => 'user DESC',
    'uuu' => 'user ASC',
    'uf' => 'user ASC, filename ASC',
    'uuf' => 'user DESC, filename ASC',
    'uff' => 'user ASC, filename DESC',
    'uuff' => 'user DESC, filename DESC',
    'ut' => 'user ASC, time ASC',
    'utt' => 'user ASC, time DESC',
    'uut' => 'user DESC, time ASC',
    'uutt' => 'user DESC, time DESC',
    't' => 'time ASC',
    'tt' => 'time DESC'
);

foreach ($meswitch as $k => $v)
{
    if ($k == $sort) break;
}
switch ($sort)
{
    case $k:
        $order_by = $meswitch[$k];
    break;
    default:
        $order_by = 'time DESC';
    break;
}

$select = getBaseSelect();
$from = getBaseFrom();
//INITIAL FILE SELECTION
if ($priv == 'Admin')
{
    $select .= ", user.name as user";
    //$from.= " INNER JOIN userrole ON user.id = userrole.userid";
    $where = ' WHERE TRUE';
    $where = getFileTypeQuery($where, $suffix);
    //CLIENTS USE EMAIL DOMAIN AS ID THERFORE NOT A NUMBER
    $where = getIdTypeQuery($where, $user, $domain);
    if (isset($text))
    {
        $where .= " AND upload.filename LIKE '%$text%'";
    }

} //admin
else
{
    $email = $_SESSION['email'];
    //$from.= " INNER JOIN userrole ON user.id = userrole.userid";
    $where = " WHERE user.email='$email' ";
}
//$sql= $select . $from . $where . $order; //DEFAULT; TELEPHONE BLOCK REQUIRED TO OBTAIN CLIENT PHONE NUMBER
$sql = $select;
$select_tel = ", client.tel";
$from .= " LEFT JOIN client ON user.client_id = client.id"; //note LEFT join to include just 'users' also
$order = getBaseOrder($order_by, $start, $display);
$sql .= $select_tel . $from . $where . $order;
//dump($sql);
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