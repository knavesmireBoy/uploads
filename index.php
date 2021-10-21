<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/magicquotes.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/access.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
$tmplt = $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/';
$terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
$base = 'Log In';
$error = '';
$tmpl_error = '/uploads/includes/error.html.php';
$myip = '86.133.121.115.';
$doError = function () {
};
function getRemoteAddr() {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    return $ipAddress;
}
function getColleagues($id, $dom) {
    return "SELECT employer.id, employer.name FROM upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $dom = client.domain) AS employer ON $dom = employer.domain WHERE upload.id= $id ORDER BY name";
}
function getColleagues2($id) {
    return "SELECT user.id, user.name FROM user INNER JOIN (SELECT tgt.client_id FROM user 
INNER JOIN upload ON user.id = upload.userid 
INNER JOIN (SELECT user.client_id FROM user INNER JOIN upload ON user.id = upload.userid  WHERE upload.id = $id)  AS tgt 
ON user.client_id  =  tgt.client_id LIMIT 1)  AS client ON client.client_id = user.client_id ORDER BY name";
}

function assignColleague($upload_id, $user_id) {
    return "UPDATE upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT user.client_id FROM upload INNER JOIN user 
ON upload.userid = user.id WHERE upload.id = $upload_id) AS tgt ON user.client_id = tgt.client_id SET upload.userid = $user_id WHERE user.client_id = tgt.client_id";
}

function doInsert($link){
    return function() use($link){
        $doSanitize = partial('doSanitize', $link);
        $args = array_map($doSanitize, func_get_args());
        return "INSERT INTO upload SET filename = '$args[0]', mimetype = '$args[1]', description = '$args[2]', filepath = '$args[3]', file = '$args[4]', size ='$args[5]'/1024, userid='$args[6]', time=NOW()";
    };
}

function getBaseSelect(){
    return "SELECT upload.id, filename, mimetype, description, filepath, file, size, time,  MID(file, 11, 14) AS origin, user.email";
}

function getBaseFrom(){
    return " FROM upload INNER JOIN user ON upload.userid=user.id";
}
function getBaseOrder($o, $s, $d){
    return " ORDER BY $o LIMIT $s, $d";
}

function concatString($str){
    return function($opt = '') use($str) {
        return $str .= $opt;
    };
}

function concatString2($str, $opt = ''){
        return $str .= $opt;
}

function myadd($a, $b) {
    return $a + $b;
}

function myMult($a, $b) {
    return $a * $b;
}

function doEmail($link, $id){
    $sql = "select user.email, user.name, upload.id, upload.filename from user INNER JOIN upload ON user.id=upload.userid WHERE upload.id=$id";
    doFetch($link, $sql, 'Error selecting email address.');
    $row = doSafeFetch($link, $sql);
    $email = $row['email'];
    $file = $row['filename'];
    $name = $row['name'];
    if ($priv == 'Admin') {
        $body = 'We have just uploaded the file' . $file . 'for checking.';
        $body = wordwrap($body, 70);
        //mail($email, $file, $body, "From: $name <{$_SESSION['email']}>");
    }
}

function getInitialKey($conn, $privilege, $user, $domn){
    function assignInitialUser($id, $dom){
        return "SELECT employer.name, employer.id FROM (SELECT user.name, user.id, client.domain FROM user INNER JOIN client ON $dom = client.domain) AS employer WHERE employer.domain='$id' LIMIT 1"; 
    }
    
    if ($privilege == 'Admin' and !empty($user)) { //ie Admin selects user
        $key = $user;
        include $conn;
         //will either return empty set(no error) or produce count. Test to see if a client has been selected.
        $row = doSafeFetch($link, "SELECT domain FROM client WHERE domain='$key'");
        if (isset($row[0])) {
            //RETURNS one user, as relationship between file and user is one to one.
            $row = doSafeFetch($link, assignInitialUser($key, $domn));
            $key = $row['id'];
            if (!$key) {
                $key = $user; //$key will be empty if above query returned empty set, reset
                $sql = "SELECT user.id from user INNER JOIN client ON user.client_id = client.id WHERE user.email='$key'";
                $row = doSafeFetch($link, $sql);
                $key = $row['id'];
            } // @ clients use domain or full email as key if neither tests produce a result key refers to a user only
        } //END OF COUNT
        return $key;
    }
    return null;
}

function uploadedfile($arg) {
    return $_FILES['upload'][$arg];
}
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
    //Bail out if the file isn't really an upload
    $doError = partialDefer('errorHandler', 'There was no file uploaded!', $terror);
    doWhen($always(!is_uploaded_file($_FILES['upload']['tmp_name'])), $doError)(null);
    
    $realname = uploadedfile('name');
    $ext = preg_replace('/(.*)(\.[^0-9.]+$)/i', '$2', $realname);
    $uploadname = time() . getRemoteAddr() . $ext;
    $path = '../../filestore/';
    $filedname = $path . $uploadname;
    // Copy the file (if it is deemed safe)
    $doError = partialDefer('errorHandler', "Could not  save file as $filedname!", $terror);
    doWhen($always(!copy(uploadedfile('tmp_name'), $filedname)), $doError)(null);
    
    $theKey = getInitialKey($db, $priv, $_POST['user'], $domain);
    $mykey = $theKey ? $theKey : $key;
    // Prepare user-submitted values for safe database insert
    include $db;
    $uploaddesc = isset($_POST['desc']) ? $_POST['desc'] : '';
    $doInsert = doInsert($link);//older versions of php may need to capture closure in a variable as opposed to func()();
    $sql = $doInsert($realname, uploadedfile('type'), $uploaddesc, $path, $uploadname, uploadedfile('size'), $mykey);          
    doFetch($link, $sql, 'Database error storing file information!' . $sql);
    
    doEmail($link, mysqli_insert_id($link));
    header('Location: .');
    exit();
} // end of upload_____________________________________________________________________

if (isset($_GET['action']) and isset($_GET['id'])) {
    include $db;
    $id = doSanitize($link, $_GET['id']);
    $result = mysqli_query($link, "SELECT filename, mimetype, filepath, file, size FROM upload WHERE id = '$id'");
    $doError = partialDefer('errorHandler', 'Database error fetching requested file.', $terror);
    doWhen($always(!$result), $doError) (null);
    
    $file = mysqli_fetch_array($result);
    $doError = partialDefer('errorHandler', 'File with specified ID not found in the database!', $terror);
    doWhen($always(!$file), $doError)(null);
    
    $filename = $file['filename'];
    $mimetype = $file['mimetype'];

    $filedata = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $file['filepath'] . $file['file']);
    $disposition = ($_GET['action'] == 'download') ? 'attachment' : 'inline';
    //$mimetype = 'application/x-unknown'; application/octet-stream
    //Content-type must come before Content-disposition
    header("Content-type: $mimetype");
    header('Content-disposition: ' . $disposition . '; filename=' . '"' . $filename . '"'); //this works
    //header("Content-Transfer-Encoding: binary");
    header('Content-length:' . strlen($filedata)); //optional?
    echo $filedata;
    exit();
} // end of download/view
if (isset($_POST['action']) and $_POST['action'] == 'delete') {
    $id = $_POST['id'];
    $title = "Prompt";
    $prompt = "Choose <b>yes</b> for deletion options and <b>no</b> for editing options";
    $call = "confirm";
}
if (isset($_POST['confirm']) and $_POST['confirm'] == 'Yes') {
    $prompt = "Select the extent of deletions";
    $id = $_POST['id'];
    $confirmed = "confirmed";
    $colleagues = array();
    $extent = 0;
    include $db;
    $result = mysqli_query($link, getColleagues($id, $domain));
    while ($row = mysqli_fetch_array($result)) {
        $colleagues[$row['id']] = $row['name'];
        $extent+= 1;
    }
}
if (isset($_POST['extent'])) {
    include $db;
    $findIndex = curry2('array_search') (array("c", "u", "f"));
    $id = doSanitize($link, $_POST['id']);
    $routes = array("SELECT c.file FROM user INNER JOIN client ON user.client_id = client.id INNER JOIN upload AS c ON user.id = c.userid  INNER JOIN upload AS d ON d.userid=user.id WHERE d.id=$id", "SELECT upload.file FROM upload INNER JOIN user ON upload.userid=user.id INNER JOIN upload AS d ON upload.userid=d.userid WHERE d.id=$id", "SELECT file FROM upload WHERE id=$id");
    $getRoute = partial('getProperty', $routes);
    $sql = $compose($findIndex, $getRoute) ($_POST['extent']);
    if (!$sql) {
        header('Location: .');
    }
    $result = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Database error fetching stored files.', $terror);
    doWhen($always(!$result), $doError) (null);
    while ($row = mysqli_fetch_array($result)) {
        $file = $row['file'];
        $sql = "DELETE FROM upload WHERE file = '$file'";
        $doError = partialDefer('errorHandler', 'Error deleting file.', $terror);
        doWhen($always(!mysqli_query($link, $sql)), $doError) (null);
        
        unlink('../../filestore/' . $file);
    }
    header('Location: .');
    exit();
} //________________________end of confirm/delete
if (isset($_POST['confirm']) and $_POST['confirm'] == 'No') { //swap
    include $db;
    $extent = 0;
    $id = doSanitize($link, $_POST['id']);
    $result = mysqli_query($link, getColleagues($id, $domain));
    $doError = partialDefer('errorHandler', 'Database error fetching colleagues.', $terror);
    
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
    $colleagues = array();
    $all_users = array();
    include $db;
    $id = doSanitize($link, $_POST['id']);
    $answer = $_POST['swap'];
    $email = "{$_SESSION['email']}";
    if (true /*$priv == 'Admin'*/
    ) {
        $sql = "SELECT upload.id, filename, description, upload.userid, user.name FROM upload INNER JOIN user ON upload.userid=user.id  WHERE upload.id=$id";
        $result = mysqli_query($link, $sql);
        $doError = partialDefer('errorHandler', 'Database error fetching stored files', $terror);
        doWhen($always(!$result), $doError) (null); //doWhen expects an argument to pass to predicate and action functions
        $extent = 0;
        $row = mysqli_fetch_array($result);
        $id = $row['id'];
        $filename = $row['filename'];
        $diz = $row['description'];
        $userid = $row['userid'];
        $aname = $row['name'];
        $button = "Update";
        $result = mysqli_query($link, getColleagues($row['id'], $domain));
        $doError = partialDefer('errorHandler', 'Database error fetching colleagues.', $terror);
        doWhen($always(!$result), $doError) (null);
        
        while ($row = mysqli_fetch_array($result)) {
            $colleagues[$row['id']] = $row['name'];
            $extent++;
        }
        if ($priv == 'Admin' && !$extent) {
            $sql = "SELECT user.name, user.id FROM user LEFT JOIN client ON user.client_id=client.id  WHERE client.domain IS NULL UNION SELECT user.name, user.id FROM user INNER JOIN client ON user.client_id = client.id ORDER BY name";
            $result = mysqli_query($link, $sql);
            $doError = partialDefer('errorHandler', 'Database error fetching users.', $terror);
            doWhen($always(!$result), $doError) (null);
            
            while ($row = mysqli_fetch_array($result)) {
                $colleagues[$row['id']] = $row['name'];
            }
        }
        //$answer used as conditional to load update.html.php
    } //if Admin
    else {
        header('Location: . ');
        exit();
    }
} ///
if (isset($_POST['update'])) { // 'update' is common to both options of file amend block
    include $db;
    $fid = doSanitize($link, $_POST['fileid']);
    $orig = doSanitize($link, $_POST['update']);
    $user = isset($_POST['user']) ? doSanitize($link, $_POST['user']) : null;
    $user = isset($_POST['colleagues']) ? doSanitize($link, $_POST['colleagues']) : $user;
    $diz = isset($_POST['description']) ? doSanitize($link, $_POST['description']) : null;
    $fname = isset($_POST['filename']) ? doSanitize($link, $_POST['filename']) : null;
    $user = !(isset($user)) ? $orig : $user;
    $single = "UPDATE upload SET userid='$user', description='$diz', filename='$fname' WHERE id ='$fid'";
    $extent = isset($_POST['blanket']) ? assignColleague($fid, $user) : "UPDATE upload SET userid='$user' WHERE userid='$orig'";
    $sql = $_POST['answer'] === "Yes" ? $extent : $single;
    $doError = partialDefer('errorHandler', 'error updating details', $terror);
    doWhen($always(!mysqli_query($link, $sql)), $doError) (null);
    header('Location: . ');
    exit();
}
///end of UPDATE FILE BLOCK___________________________________________________________________
//a default block___________________________________________________________________
$display = 10;
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

$meswitch = array('f' => 'filename ASC', 'ff' => 'filename DESC', 'u' => 'user ASC', 'uu' => 'user DESC', 'uf' => 'user ASC, filename ASC', 'uuf' => 'user DESC, filename ASC', 'uff' => 'user ASC, filename DESC', 'uuff' => 'user DESC, filename DESC', 'ut' => 'user ASC, time ASC', 'utt' => 'user ASC, time DESC', 'uut' => 'user DESC, time ASC', 'uutt' => 'user DESC, time DESC', 't' => 'time ASC', 'tt' => 'time DESC');

foreach ($meswitch as $ix => $u) {
    if ($ix == $sort) break;
}
switch ($sort) {
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
if (!$result) {
    $error = 'Database error fetching users.';
    include $terror;
    exit();
}
while ($row = mysqli_fetch_array($result)) {
    $users[$row['id']] = $row['name'];
}

$sqlc = "SELECT name, domain, tel FROM client ORDER BY name";
$result = mysqli_query($link, $sqlc);
if (!$result) {
    $error = 'Database error fetching clients.';
    include $terror;
    exit();
}
while ($row = mysqli_fetch_array($result)) {
    $client[$row['domain']] = $row['name'];
}
//end of default_______________________________________________________________________
if (isset($_GET['find'])) {
    if ($priv != "Admin"): //CUSTOMISES SELECT MENU
        $email = "{$_SESSION['email']}";
        include $db;
        $sql = "SELECT $domain FROM user WHERE user.email='$email'";
        $result = mysqli_query($sql);
        $row = mysqli_fetch_array($result);
        $dom = $row[0];
        $sql = "SELECT COUNT(*) AS dom FROM user INNER JOIN client ON $domain = client.domain WHERE $domain = '$dom' AND client.domain =' $dom'";
        $result = mysqli_query($sql);
        $row = mysqli_fetch_array($result);
    
        $count = $row['dom'];
        if (count($count) > 0) {
            $mywhere = " WHERE user.email='$email'"; //client
            
        } else {
            $mywhere = " WHERE user.id=$key"; //user
        }
        $sql = "SELECT employer.id, employer.name  FROM user INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $domain = client.domain) AS employer ON $domain = employer.domain $mywhere";
        $result = mysqli_query($link, $sql);
        if (!$result) {
            $error = 'Database error fetching clients.';
            include $terror;
            exit();
        }
        $users = array(); //resets user array to display users of current client
        while ($row = mysqli_fetch_array($result)) {
            $users[$row['id']] = $row['name'];
        }
        if ($count <= 1) { //SELECT MENU in SEARCH for only more than one "employee"
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

if (isset($_GET['action']) and $_GET['action'] == 'search') {
    function invoke($f, $arg){
        return $f($arg);
}
    function myAlways($arg){
        return $arg;
    }
    include $db;
    $tel = '';
    $from = getBaseFrom();
    $from .= " INNER JOIN userrole ON user.id=userrole.userid";
    $user_id = doSanitize($link, $_GET['user']);
    $check = null;
    if ($priv == 'Admin') {
        //will either return empty set(no error) or produce count. Test to see if a client has been selected.
        $sql = "SELECT domain FROM client WHERE domain = '" . $user_id . "'"; 
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $where = ' WHERE TRUE';
        if (isset($row['domain']) && !is_numeric($user_id)) { //user_id is text(domain) for Clients
            $from .= " INNER JOIN client ON $domain = client.domain ";
            $where = " WHERE domain = '" . $user_id . "'";
            $check = count($row);
        }
        $select = getBaseSelect();
        $select .= ", user.name as user";
    } //admin
    else {
        $email = $_SESSION['email'];
        $where = " WHERE user.email='$email' ";
    }
    
    $haveUser = partial(negate('isEmpty'), $user_id);
    $notClient = partial('isEmpty', $check);
    $res = array_reduce([$haveUser, $notClient], 'every', true);
    $andUser = partial('concatString2', $where, " AND user.id = $user_id");
    $text = doSanitize($link, $_GET['text']);       
    $chooseUser = getBestPred($always($res))($andUser, $always);
    $where = $chooseUser($where);
    $tmp = getBestPred(partial(negate('isEmpty'), $text));
    $likeText = partial('concatString2', $where, " AND upload.filename LIKE '%$text%'");
    $chooseText = $tmp($likeText, partial('myAlways'));    
    //$cb = $compose(partial('myAdd', 5, 3), partial('myMult', 10), partial('myMult', 2));
    //$cb = $compose(partial('myAdd', 5, 3), partial('myMult', 10), partial('myMult', 2));
    
    exit($chooseText($where));
    $suffix = doSanitize($link, $_GET['suffix']);
    if (isset($suffix)) {
        if ($suffix == 'owt') {
            $where.= " AND (upload.filename NOT LIKE '%pdf' AND upload.filename NOT LIKE '%zip')";
        } elseif ($suffix == 'pdf' or $suffix == 'zip') {
            $where.= " AND upload.filename LIKE '%$suffix'";
            //$where .= sprintf(" AND upload.filename LIKE %s", GetSQLValueString('%'.$suffix, "text"));//Tricky percent symbol
        }
    }
    $order = getBaseOrder($order_by, $start, $display);
    $sql = $select . $from . $where . $order;
    $result = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Error fetching file details.' . $sql, $terror);
    doWhen($always(!$result), $doError) (null);

    $sqlcount = $select . ', COUNT(upload.id) as total ' . $from . $where . ' GROUP BY upload.id ' . $order;
    $r = mysqli_query($link, $sqlcount);
    $doError = partialDefer('errorHandler', 'Error getting file count.', $terror);
    doWhen($always(!$r), $doError) (null);

    $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
    $records = $row['total'];
    $pages = ($records > $display) ? ceil($records / $display) : 1;
    $files = array();
    while ($row = mysqli_fetch_array($result)) {
        $files[] = array('id' => $row['id'], 'user' => $row['user'], 'email' => $row['email'], 'filename' => $row['filename'], 'mimetype' => $row['mimetype'], 'description' => $row['description'], 'filepath' => $row['filepath'], 'file' => $row['file'], 'origin' => $row['origin'], 'time' => $row['time'], 'size' => $row['size']);
    }
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/files.html.php';
    exit();
}
//ENDEND S E A R C H//ENDEND S E A R C H//ENDEND S E A R C H//ENDEND S E A R C H
if ($priv == 'Admin') {
    $select = getBaseSelect();
    $select .= ", user.name as user"; //append to line 465(ish)
    $from = getBaseFrom();
    $from.= " INNER JOIN userrole ON user.id=userrole.userid";
    $where = ' WHERE TRUE';    
    
    if (isset($_GET['ext']) && $ext = doSanitize($link, $_GET['ext'])) {
        if ($ext == 'owt') {
            $where.= " AND (upload.filename NOT LIKE '%pdf' AND upload.filename NOT LIKE '%zip')";
        } else $where.= " AND upload.filename LIKE '%$ext'";
    }
    
    if (isset($useroo) && is_numeric($useroo)) { //CLIENTS USE EMAIL DOMAIN AS ID THERFORE NOT A NUMBER
        if ($useroo = doSanitize($_GET['u'])) $where.= " AND user.id=$useroo";
    } else {
        if (isset($_GET['u']) && $useroo = doSanitize($link, $_GET['u'])) $where.= " AND $domain='$useroo'";
    }
    
    if (isset($_GET['t']) && $textme = doSanitize($link, $_GET['t'])) $where.= " AND upload.filename LIKE '%$textme%'";
} //admin
else {
    $email = $_SESSION['email'];
    $from.= " INNER JOIN userrole ON user.id=userrole.userid";
    $where = " WHERE user.email='$email' ";
}
//$sql= $select . $from . $where . $order; //DEFAULT; TELEPHONE BLOCK REQUIRED TO OBTAIN CLIENT PHONE NUMBER
$sql = $select;
$select_tel = ", client.tel";
$from.= " LEFT JOIN client ON user.client_id=client.id"; //note LEFT join to include just 'users' also
$order = getBaseOrder($order_by, $start, $display);
$sql.= $select_tel . $from . $where . $order;
//____________________________________________________________________________________________END OF TELEPHONE
$result = doFetch($link, $sql, 'Database error fetching files. ' . $sql);
$files = array();
while ($row = mysqli_fetch_array($result)) {
    $files[] = array('id' => $row['id'], 'user' => (isset($row['user'])) ? $row['user'] : '', 'email' => $row['email'], 'filename' => $row['filename'], 'mimetype' => $row['mimetype'], 'description' => $row['description'], 'filepath' => $row['filepath'], 'file' => $row['file'], 'origin' => $row['origin'], 'time' => $row['time'], 'tel' => $row['tel'], // ONLY REQUIRED FOR TELEPHONE BLOCK
    'size' => $row['size']);
}
$base = 'North Wolds Printers | The File Uploads';
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/files.html.php';
?>