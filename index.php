<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';

$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$db = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
$base = 'Log In';
$error = '';
$tmpl_error = '/uploads/includes/error.html.php';
$myip = '86.133.121.115.';
$doError = function(){};
function getRemoteAddr()
{
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
    {
        $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    return $ipAddress;
}

function getColleaguesFromUploadId($id, $dom) {
        return "SELECT employer.id, employer.name FROM upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $dom = client.domain) AS employer ON $dom = employer.domain WHERE upload.id= $id ORDER BY name";
}

function getColleaguesFromUploadIdVerbose($id, $dom) {
        return "SELECT employer.id, employer.name FROM upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email)) = client.domain) AS employer ON RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email)) = employer.domain WHERE upload.id = 1926 ORDER BY name";
}

function assignColleague($upload_id, $user_id) {
    return "UPDATE upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT  user.client_id FROM upload INNER JOIN user 
ON upload.userid = user.id WHERE upload.id = $upload_id) AS tgt ON user.client_id = tgt.client_id SET upload.userid = $user_id WHERE user.client_id = tgt.client_id";
}

$uploadedfile = function ($arg)
{
    return $_FILES['upload'][$arg];
};


if (!userIsLoggedIn())
{
    include $tmplt . 'base.html.php';
    include $tmplt . 'login.html.php';
    exit();
}
//public page
if (!$roleplay = userHasWhatRole())
{
    //$doError = partialDefer('errorHandler', 'Only valid clients may access this page.', $tmplt . 'accessdenied.html.php');
    //doWhen($always(!$roleplay = userHasWhatRole()), $doError)(null);
    $error = 'Only valid clients may access this page.';
    include $tmplt . 'accessdenied.html.php';
    exit(); // endof OBTAIN access level

}
else
{
    foreach ($roleplay as $key => $priv)
    { // $roleplay is an array, use foreach to obtain value and index

    }
    $domain = "RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))"; //!!?!! V. USEFUL VARIABLE IN GLOBAL SPACE
    $isPositive = curry22('equals')('Yes');
}

if (isset($_POST['action']) and $_POST['action'] == 'upload')
{
    //Bail out if the file isn't really an upload
    if (!is_uploaded_file($_FILES['upload']['tmp_name']))
    {
    //$doError = partialDefer('errorHandler', 'There was no file uploaded!', $terror);
    //doWhen($always(!is_uploaded_file($_FILES['upload']['tmp_name'])), $doError)(null);
        $error = 'There was no file uploaded!';
        include $terror;
        exit();
    }

    $uploadfile = $uploadedfile('tmp_name');
    $realname = $uploadedfile('name');
    $ext = preg_replace('/(.*)(\.[^0-9.]+$)/i', '$2', $realname);
    $time = time();
    $uploadname = $time . getRemoteAddr() . $ext;
    $path = '../../filestore/';
    $filedname = $path . $uploadname;
    // Copy the file (if it is deemed safe)
    if (!copy($uploadfile, $filedname))
    {
        //$doError = partialDefer('errorHandler', "Could not  save file as $filedname!", $terror);
        //doWhen($always(!copy($uploadfile, $filedname)), $doError)(null);
        $error = "Could not  save file as $filedname!";
        include $terror;
        exit();
    }
    echo $key;
    if ($priv == 'Admin' and !empty($_POST['user']))
    { //ie Admin selects user
        $key = $_POST['user'];
        include $db;
        $sql = "SELECT domain FROM client WHERE domain='$key'"; //will either return empty set(no error) or produce count. Test to see if a client has been selected.
        $row = doSafeFetch($link, $sql);
        
        if (count($row[0]) > 0)
        {
            $sql = "SELECT employer.user_name, employer.user_id FROM (SELECT user.name AS user_name, user.id AS user_id, client.domain FROM user INNER JOIN client ON $domain = client.domain) AS employer WHERE employer.domain='$key' LIMIT 1"; //RETURNS one user, as relationship between file and user is one to one.
            $row = doSafeFetch($link, $sql);
            $key = $row['user_id'];
            if (!$key)
            {
                $key = $_POST['user']; //$key will be empty if above query returned empty set, reset
                $sql = "SELECT user.id from user INNER JOIN client ON user.client_id=client.id WHERE user.email='$key'";
                $row = doSafeFetch($link, $sql);
                $key = $row['id'];
            } // @ clients use domain or full email as key if neither tests produce a result key refers to a user only

        } //END OF COUNT

    }

    // Prepare user-submitted values for safe database insert
    include $db;
    $realname = doSanitize($link, $realname);
    $size = doSanitize($link, $uploadedfile('size'));
    $uploadname = doSanitize($link, $uploadname);
    $uploadtype = doSanitize($link, $uploadedfile('type'));
    $uploaddesc = doSanitize($link, isset($_POST['desc']) ? $_POST['desc'] : '');
    $path = doSanitize($link, $path);
    //$time = doSanitize($link, $time);

    $sql = "INSERT INTO upload SET filename = '$realname', mimetype = '$uploadtype', description = '$uploaddesc', filepath = '$path', file = '$uploadname', size ='$size'/1024, userid='$key', time=NOW()";
    doFetch($link, $sql, 'Database error storing file information!' . $sql);
    /*$sql2 = "select user.whatever from user INNER JOIN upload ON user.id=upload.userid INNER JOIN (SELECT MAX(id) AS big FROM upload) AS last ON last.big = upload.id";
    NOT REQUIRED - USING mysqli_INSERT_ID INSTEAD - BUT KEPT AS AN EXAMPLE OF A SUBQUERY
    */
    $id = mysqli_insert_id($link);
    $sql = "select user.email, user.name, upload.id, upload.filename from user INNER JOIN upload ON user.id=upload.userid WHERE upload.id=$id";

    doFetch($link, $sql, 'Error selecting email address.');

    $row = doSafeFetch($link, $sql);
    $email = $row['email'];
    $file = $row['filename'];
    $name = $row['name'];

    if ($priv == 'Admin')
    {
        $body = 'We have just uploaded the file' . $file . 'for checking.';
        $body = wordwrap($body, 70);
        //mail($email, $file, $body, "From: $name <{$_SESSION['email']}>");
    }
    /*
    else {
    $body =  '<html><body><p>We have just uploaded the file <a href='.
    '"http://northwolds.serveftp.net/uploads/" /><strong>' . $file . '</strong></a> for printing.</p></body></html>';
    if (!@mail('north.wolds@btinternet.com', 'Files to North Wolds | ' . $file,
    $body,
    "From: $name <{$_SESSION['email']}>\n" .
     "cc:  $name <files@northwolds.co.uk>\n" .
    "MIME-Version: 1.0\n" .
    "Content-type: text/html; charset=iso-8859-1"))
    {
    exit('<p>The file uploaded but an email could not be sent.</p>');
    }
    }
    */
    header('Location: .');
    exit();
} // end of upload_____________________________________________________________________


if (isset($_GET['action']) and isset($_GET['id']))
{
    include $db;
    $id = doSanitize($link, $_GET['id']);
    $sql = "SELECT filename, mimetype, filepath, file, size FROM upload WHERE id = '$id'";
    $result = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Database error fetching requested file.', $terror);
    doWhen($always(!$result), $doError)(null);
    
    $file = mysqli_fetch_array($result);
    $doError = partialDefer('errorHandler', 'File with specified ID not found in the database!', $terror);
    doWhen($always(!$file), $doError)(null);

    $filename = $file['filename'];
    $mimetype = $file['mimetype'];
    $filepath = $file['filepath'];
    $uploadfile = $file['file'];
    $size = $file['size'];
    $filepath .= $uploadfile;
    $fullpath = $_SERVER['DOCUMENT_ROOT'] . $filepath;
    $filedata = file_get_contents($fullpath);
    $disposition = ($_GET['action'] == 'download') ? 'attachment' : 'inline';
    $ext = preg_replace('/(.*)(\.[^0-9.]+$)/i', '$2', $filename);
    //$mimetype = 'application/x-unknown'; application/octet-stream
    //Content-type must come before Content-disposition
    header("Content-type: $mimetype");
    header('Content-disposition: ' . $disposition . '; filename=' . '"' . $filename . '"'); //this works
    //header("Content-Transfer-Encoding: binary");
    header('Content-length:' . strlen($filedata));//optional?
    echo $filedata;
    exit();
}// end of download/view
if (isset($_POST['action']) and $_POST['action'] == 'delete')
{
    $id = $_POST['id'];
    $title = "Prompt";
    $prompt = "Choose <b>yes</b> for deletion options and <b>no</b> for editing options";
    $call = "confirm";
}

if (isset($_POST['confirm']) and $_POST['confirm'] == 'Yes')
{
    $prompt = "Select the extent of deletions";
    $id = $_POST['id'];
    $confirmed = "confirmed";
}

if (isset($_POST['extent']))
{
    include $db;
    $findIndex = curry2('array_search')(array("c", "u", "f"));
    $id = doSanitize($link, $_POST['id']);
    $routes = array("SELECT c.file FROM user INNER JOIN client ON user.client_id = client.id INNER JOIN upload AS c ON user.id = c.userid  INNER JOIN upload AS d ON d.userid=user.id WHERE d.id=$id", "SELECT upload.file FROM upload INNER JOIN user ON upload.userid=user.id INNER JOIN upload AS d ON upload.userid=d.userid WHERE d.id=$id", "SELECT file FROM upload WHERE id=$id");
    $getRoute = partial('getProperty', $routes);
    $sql = $compose($findIndex, $getRoute)($_POST['extent']);
    if (!$sql)
    {
        header('Location: .');
    }
    $result = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Database error fetching stored files.', $terror);
    doWhen($always(!$result), $doError)(null);

    while ($row = mysqli_fetch_array($result))
    {
        $file = $row['file'];
        $sql = "DELETE FROM upload WHERE file = '$file'";
        $doError = partialDefer('errorHandler',  'Error deleting file.', $terror);
        doWhen($always(!mysqli_query($link, $sql)), $doError)(null);
        
        unlink('../../filestore/' . $file);
    }
    header('Location: .');
    exit();
} //________________________end of confirm/delete
if (isset($_POST['confirm']) and $_POST['confirm'] == 'No')
{ //swap
    include $db;
    $id = doSanitize($link, $_POST['id']);
    $result = mysqli_query($link, getColleaguesFromUploadId($id, $domain));
    $doError = partialDefer('errorHandler', 'Database error fetching colleagues.', $terror);
    $prompt1 = "Choose <b>yes</b> to select assign a new owner to all "; 
    $prompt2 =  " files. Choose <b>no</b> to edit a single file"; 
    $prompt = "$prompt1 client $prompt2"; 
    doWhen($always(!$result), $doError)(null);
     while ($row = mysqli_fetch_array($result))
        {
            $colleagues[$row['id']] = $row['name'];
        }
    if(isset($colleagues) && count($colleagues) === 1){
        $prompt = "continue";
    }
    else if(!isset($colleagues)) {
        $prompt = "$prompt1 user $prompt2";
    }
    
    $id = $_POST['id'];
    $call = "swap";
}

if (isset($_POST['swap']))
{ //SWITCH OWNER OF FILE OR JUST UPDATE DESCRIPTION (FILE AMEND BLOCK)
    $colleagues = array();
    $all_users = array();
    include $db;
    $id = doSanitize($link, $_POST['id']);
    $answer = $_POST['swap'];
    $email = "{$_SESSION['email']}";

    if (true/*$priv == 'Admin'*/) {
        $sql = "SELECT upload.id, filename, description, upload.userid, user.name FROM upload INNER JOIN user ON upload.userid=user.id  WHERE upload.id=$id";
        $result = mysqli_query($link, $sql);
        $doError = partialDefer('errorHandler', 'Database error fetching stored files', $terror);
        doWhen($always(!$result), $doError)(null);//doWhen expects an argument to pass to predicate and action functions
        $row = mysqli_fetch_array($result);
        $id = $row['id'];
        $filename = $row['filename'];
        $diz = $row['description'];
        $userid = $row['userid'];
        $aname = $row['name'];
        $button = "Update";
        $answer = $_POST['swap'];
        $result = mysqli_query($link, getColleaguesFromUploadId($row['id'], $domain));
        $doError = partialDefer('errorHandler', 'Database error fetching colleagues.', $terror);
        doWhen($always(!$result), $doError)(null);
        
        while ($row = mysqli_fetch_array($result))
        {
            $colleagues[$row['id']] = $row['name'];
        }
       
        if ($priv == 'Admin' && count($colleagues) == 0)
        {
            $sql = "SELECT user.name, user.id FROM user LEFT JOIN client ON user.client_id=client.id  WHERE client.domain IS NULL UNION SELECT user.name, user.id FROM user INNER JOIN client ON user.client_id=client.id ORDER BY name";
            $result = mysqli_query($link, $sql);
            $doError = partialDefer('errorHandler', 'Database error fetching users.', $terror);
            doWhen($always(!$result), $doError)(null);
            while ($row = mysqli_fetch_array($result))
            {
                $all_users[$row['id']] = $row['name'];
            }
        }
    //exit($filename);
    } //if Admin
    else
    {
        header('Location: . ');
        exit();
    }
} ///


if (isset($_POST['original']))
{ // 'original' is common to both options of file amend block
    include $db;
    
    $fid = doSanitize($link, $_POST['fileid']);
    $orig = doSanitize($link, $_POST['original']);
    $user = isset($_POST['user']) ? doSanitize($link, $_POST['user']) : null;
    $user = isset($_POST['colleagues']) ? doSanitize($link, $_POST['colleagues']) : $user;
    $diz = doSanitize($link, $_POST['description']);
    $fname = doSanitize($link, $_POST['filename']);
    $user = !(isset($user)) ? $orig : $user;
    $single = "UPDATE upload SET userid='$user', description='$diz', filename='$fname' WHERE id ='$fid'";
    $extent = $_POST['blanket'] ? assignColleague($fid, $user) : "UPDATE upload SET userid='$user' WHERE userid='$orig'";
    
    $options = array($extent, $single);
    
    $getBest = getBestThunk($isPositive($_POST['answer']));
    $sql = $getBest($options[0], $options[1]);    
    
    $doError = partialDefer('errorHandler', 'error updating details', $terror);
    doWhen($always(!mysqli_query($link, $sql)), $doError)(null);
    header('Location: . ');
    exit();
}
///end of UPDATE FILE BLOCK___________________________________________________________________
//a default block___________________________________________________________________
$display = 10;
if (isset($_GET['p']) and is_numeric($_GET['p']))
{
    $pages = $_GET['p'];
}
else
{ // counts all files
    include $db;
    $sql = "SELECT COUNT(upload.id) from upload ";
    if ($priv == 'Client')
    {
        $email = $_SESSION['email'];
        $sql .= " INNER JOIN user on upload.userid = user.id WHERE user.email='$email'";
    }
    $r = mysqli_query($link, $sql);
    if (!$r)
    {
        $error = 'Database error fetching requesting the list of files.';
        include $terror;
        exit();
    }
    $row = mysqli_fetch_array($r, MYSQLI_NUM);
    $records = $row[0];
    if ($records > $display)
    {
        $pages = ceil($records / $display);
    }
    else $pages = 1; //INITIAL SETTING OF PAGES

} //end of IF NOT PAGES SET
if (isset($_GET['s']) and is_numeric($_GET['s']))
{
    $start = $_GET['s'];
}
else
{
    $start = 0;
}

$meswitch = array(
    'f' => 'filename ASC',
    'ff' => 'filename DESC',
    'u' => 'user ASC',
    'uu' => 'user DESC',
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
$sort = (isset($_GET['sort']) ? $_GET['sort'] : '1');
foreach ($meswitch as $ix => $u)
{
    if ($ix == $sort) break;
}
switch ($sort)
{
    case $ix:
        $order_by = $meswitch[$ix];
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
$sqlu = "SELECT user.id, user.name FROM user LEFT JOIN client ON user.client_id=client.id WHERE client.domain IS NULL ORDER BY name";
$result = mysqli_query($link, $sqlu);
if (!$result)
{
    $error = 'Database error fetching users.';
    include $terror;
    exit();
}
while ($row = mysqli_fetch_array($result))
{
    $users[$row['id']] = $row['name'];
}
/*$sqlc ="SELECT employer.user_id, employer.name from
 (SELECT user.name, user.id as user_id, client.domain FROM user INNER JOIN client ON RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))=client.domain) AS employer";*/

$sqlc = "SELECT name, domain, tel FROM client ORDER BY name";
$result = mysqli_query($link, $sqlc);
if (!$result)
{
    $error = 'Database error fetching clients.';
    include $terror;
    exit();
}
while ($row = mysqli_fetch_array($result))
{
    $client[$row['domain']] = $row['name'];
}
//end of default_______________________________________________________________________


if (isset($_GET['find']))
{
    if ($priv != "Admin"): //CUSTOMISES SELECT MENU
        $email = "{$_SESSION['email']}";
        include $db;
        $sql = "SELECT $domain  FROM user WHERE user.email='$email'";
        $result = mysqli_query($sql);
        $row = mysqli_fetch_array($result);
        $dom = $row[0];
        $sql = "SELECT COUNT(*) AS dom FROM user INNER JOIN client ON $domain=client.domain WHERE $domain='$dom' AND client.domain='$dom'";
        $result = mysqli_query($sql);
        $row = mysqli_fetch_array($result);
        $count = $row['dom'];
        if (count($count) > 0)
        {
            $where = " WHERE user.email='$email'"; //client

        }
        else
        {
            $where = " WHERE user.id=$key"; //user

        }
        $sql = "SELECT employer.id, employer.name  FROM user INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $domain=client.domain) AS employer ON $domain=employer.domain $where";
        $result = mysqli_query($link, $sql);
        if (!$result)
        {
            $error = 'Database error fetching clients.';
            include $terror;
            exit();
        }
        $users = array(); //resets user array to display users of current client
        while ($row = mysqli_fetch_array($result))
        {
            $users[$row['id']] = $row['name'];
        }
        if ($count <= 1)
        { //SELECT MENU in SEARCH for only more than one "employee"
            $users = array();
            $zero = true;
        }
        $client = array();
    endif;
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/search.html.php';
    exit();
}
/// S E A R C H  M E !!
//INITIAL FILE SELECTION
//_______//_______//_______//_______//_______//_______//_______//_______//_______//_____
$select = "SELECT upload.id, filename, mimetype, description, filepath, file, size, time,  MID(file, 11, 14) AS origin, user.email";
$from = " FROM upload INNER JOIN user ON upload.userid=user.id";
$order = " ORDER BY $order_by LIMIT $start, $display";
$domain = "RIGHT(user.email, LENGTH(user.email) - LOCATE('@', user.email))";
//_______//_______//_______//_______//_______//_______//_______//_______//_______//_____
if (isset($_GET['action']) and $_GET['action'] == 'search')
{
    include $db;
    $tel = '';
    $from .= " INNER JOIN userrole ON user.id=userrole.userid";
    $user_id = doSanitize($link, $_GET['user']);
    if ($priv == 'Admin')
    {
        $sql = "SELECT domain FROM client WHERE domain='" . $user_id . "'"; //will either return empty set(no error) or produce count. Test to see if a client has been selected.
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_array($result);
        if (count($row[0]) > 0 and !is_numeric($user_id))
        { //user_id is text(domain) for Clients
            $from .= " INNER JOIN client ON $domain =client.domain ";
            $where = " WHERE domain='" . $user_id . "'";
            $check = count($row[0]);
        }
        else
        {
            $where = ' WHERE TRUE';
        }
        $select .= ", user.name as user";
    } //admin
    else
    {
        $email = $_SESSION['email'];
        $where .= " WHERE user.email='$email' ";
    }
    if ($user_id != '')
    { // A user is selected
        if (!isset($check)) $where .= " AND user.id=$user_id";
    }
    $text = doSanitize($link, $_GET['text']);
    if ($text != '')
    { // Some search text was specified
        $where .= " AND upload.filename LIKE '%$text%'";
    }
    $suffix = doSanitize($link, $_GET['suffix']);
    if (isset($suffix))
    {
        if ($suffix == 'owt')
        {
            $where .= " AND (upload.filename NOT LIKE '%pdf' AND upload.filename NOT LIKE '%zip')";
        }
        elseif ($suffix == 'pdf' or $suffix == 'zip')
        {
            $where .= " AND upload.filename LIKE '%$suffix'";
            //$where .= sprintf(" AND upload.filename LIKE %s", GetSQLValueString('%'.$suffix, "text"));//Tricky percent symbol
        }
    }

    $sql = $select . $from . $where . $order;

    $result = mysqli_query($link, $sql);
    if (!$result)
    {
        $error = 'Error fetching file details1.' . $sql;
        include $terror;
        exit();
    }
    $sqlcount = $select . ', COUNT(upload.id) as total ' . $from . $where . $order;
    //exit($sqlcount);
    $r = mysqli_query($link, $sqlcount);
    if (!$r)
    {
        $error = 'Error getting file count.';
        include $terror;
        exit();
    }
    $row = mysqli_fetch_array($r);
    $records = $row['total'];
    if ($records > $display)
    {
        $pages = ceil($records / $display);
    }
    else $pages = 1;

    $files = array();
    while ($row = mysqli_fetch_array($result))
    {
        $files[] = array(
            'id' => $row['id'],
            'user' => $row['user'],
            'email' => $row['email'],
            'filename' => $row['filename'],
            'mimetype' => $row['mimetype'],
            'description' => $row['description'],
            'filepath' => $row['filepath'],
            'file' => $row['file'],
            'origin' => $row['origin'],
            'time' => $row['time'],
            'size' => $row['size']
        );
    }
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/files.html.php';
    exit();
}
//ENDEND S E A R C H//ENDEND S E A R C H//ENDEND S E A R C H//ENDEND S E A R C H
if ($priv == 'Admin')
{
    $select .= ", user.name as user"; //append to line 465(ish)
    $from .= " INNER JOIN userrole ON user.id=userrole.userid";
    $where = ' WHERE TRUE';
    if (isset($_GET['ext']) && $ext = doSanitize($link, $_GET['ext']))
    {
        if ($ext == 'owt')
        {
            $where .= " AND (upload.filename NOT LIKE '%pdf' AND upload.filename NOT LIKE '%zip')";
        }
        else $where .= " AND upload.filename LIKE '%$ext'";
    }
    if (isset($useroo) && is_numeric($useroo))
    { //CLIENTS USE EMAIL DOMAIN AS ID THERFORE NOT A NUMBER
        if ($useroo = doSanitize($_GET['u'])) $where .= " AND user.id=$useroo";
    }
    else
    {
        if (isset($_GET['u']) && $useroo = doSanitize($link, $_GET['u'])) $where .= " AND $domain='$useroo'";
    }
    if (isset($_GET['t']) && $textme = doSanitize($link, $_GET['t'])) $where .= " AND upload.filename LIKE '%$textme%'";
} //admin
else
{
    $email = $_SESSION['email'];
    $from .= " INNER JOIN userrole ON user.id=userrole.userid";
    //$where .=" WHERE user.email='$email' ";
    $where = " WHERE user.email='$email' ";
}

//$sql= $select . $from . $where . $order; //DEFAULT; TELEPHONE BLOCK REQUIRED TO OBTAIN CLIENT PHONE NUMBER
$sql = $select;
$select_tel = ", client.tel";
$from .= " LEFT JOIN client ON user.client_id=client.id"; //note LEFT join to include just 'users' also
$sql .= $select_tel . $from . $where . $order;
//____________________________________________________________________________________________END OF TELEPHONE
$result = doFetch($link, $sql, 'Database error fetching files. ' . $sql);

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
$base = 'North Wolds Printers | The File Uploads';
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/files.html.php';
?>
