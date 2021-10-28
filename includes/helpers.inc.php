<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';

function seek($flag =  false)
{
    $arr = array(
        'suffix',
        'user',
        'text'
    );
    $i = count($arr);
    while ($i--)
    {
        //https://stackoverflow.com/questions/29636880/compile-error-cannot-use-isset-on-the-result-of-an-expression

        if (goGet($arr[$i], $flag))
        {
            return '.';
        }
    }
    return '?find';
}

function uploadedfile($arg)
{
    return $_FILES['upload'][$arg];
}
function isTreble($q)
{
    return substr($q, -4, 1) === '=';
}

function notToggled($q){
    return substr($q,-2, 1) != substr($q,-1, 1);
}

function resetQuery1($str = ''){
    $s = isset($str) ?  "sort=$str" : 'sort=';
    return array('q' => '?', 'sort' =>  $s );
}

function resetQuery($query_str, $str = ''){
    $query_str = explode('sort=', $query_str)[0];
    $s = isset($str) ?  "sort=$str" : 'sort=';
    return array('q' => $query_str, 'sort' =>  $s );
}


function isDouble($q)
{
    return substr($q, -3, 1) === '=';
}

function isSingle($q)
{
    return substr($q, -2, 1) === '=';
}

function isSubList($q)
{
    return substr($q, -2, 1) !== substr($q, -1, 1);
}

function getToggle($arr)
{
    return function ($i) use ($arr)
    {
        return isset($i) ? $arr[$i] : $arr;
    };
}

if (!function_exists("GetSQLValueString"))
{
    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
    {
        if (PHP_VERSION < 6)
        {
            $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
        }

        $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

        switch ($theType)
        {
            case "text":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
            case "long":
            case "int":
                $theValue = ($theValue != "") ? intval($theValue) : "NULL";
            break;
            case "double":
                $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
            break;
            case "date":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
            case "defined":
                $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
            break;
        }
        return $theValue;
    }
}

function bbcode2html($text)
{
    $text = html($text); // [B]old
    $text = preg_replace('/\[B](.+?)\[\/B]/i', '<strong>$1</strong>', $text);
    // [I]talic
    $text = preg_replace('/\[I](.+?)\[\/I]/i', '<em>$1</em>', $text);
    // Convert Windows (\r\n) to Unix (\n)
    $text = str_replace("\r\n", "\n", $text);
    // Convert Macintosh (\r) to Unix (\n)
    $text = str_replace("\r", "\n", $text);
    // Paragraphs
    $text = '<p>' . str_replace("\n\n", '</p><p>', $text) . '</p>';
    // Line breaks
    $text = str_replace("\n", '<br/>', $text);
    // [URL]link[/URL]
    $text = preg_replace('/\[URL]([-a-z0-9._~:\/?#@!$&\'()*+,;=%]+)\[\/URL]/i', '<a href="$1">$1</a>', $text);
    // [URL=url]link[/URL]
    $text = preg_replace('/\[URL=([-a-z0-9._~:\/?#@!$&\'()*+,;=%]+)](.+?)\[\/URL]/i', '<a href="$1">$2</a>', $text);
    return $text;
}
function bbcodeout($text)
{
    echo bbcode2html($text);
}
/*
function html($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function htmlout($text)
{
    echo html($text);
}
*/
function add_querystring_var($url, $key, $value)
{
    $url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
    $url = substr($url, 0, -1);
    if (strpos($url, '?') === false)
    {
        return ($url . '?' . $key . '=' . $value);
    }
    else
    {
        return ($url . '&' . $key . '=' . $value);
    }
}

function remove_querystring_var($url, $key)
{
    $url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
    $url = substr($url, 0, -1);
    return ($url);
}
function doSanitize($lnk, $arg)
{
    return mysqli_real_escape_string($lnk, $arg);
}
function doSafeFetch($lnk, $sql, $mode = MYSQLI_BOTH)
{
    //assumes query works!!
    return mysqli_fetch_array(mysqli_query($lnk, $sql) , $mode);
}

function goFetch($result, $mode = MYSQLI_BOTH)
{
    return mysqli_fetch_array($result, $mode);
}

function doProcess($r, $k, $v, $mode = MYSQLI_BOTH)
{
    while ($row = mysqli_fetch_array($r, $mode))
    {
        //$key = isset($k) ?
        $gang[$row[$k]] = $row[$v];
    }
    return $gang;
}

function doBuild($r, $v, $mode = MYSQLI_BOTH)
{
    while ($row = mysqli_fetch_array($r, $mode))
    {
        $gang[] = $row[$v];
    }
    return $gang;
}

function doFetch($lnk, $sql, $msg)
{
    $result = mysqli_query($lnk, $sql);
    if (!$result)
    {
        $error = $msg;
        include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
        exit();
    }
    return $result;
}
function doQuery($lnk, $sql, $msg)
{
    $result = mysqli_query($lnk, $sql);
    if (!$result)
    {
        $error = $msg;
        include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
        exit();
    }
    return $result;
}

function domainFromUserID($link, $id)
{
    $sql = "SELECT domain from client INNER JOIN user ON user.client_id = client.id WHERE user.id = $id";
    $res = doQuery($link, $sql, 'Database getting client domain.');
    return goFetch($res) [0];
}

function formatFileSize($size)
{
    if ($size > 1024)
    {
        return number_format($size / 1024, 2, '.', '') . 'mb';
    }
    return ceil($size) . 'kb';
}

function getBaseSelect()
{
    return "SELECT upload.id, filename, mimetype, description, filepath, file, size, time,  MID(file, 11, 14) AS origin, user.email";
}

function getBaseFrom()
{
    return " FROM upload INNER JOIN user ON upload.userid=user.id";
}
function getBaseOrder($o, $s, $d)
{
    return " ORDER BY $o LIMIT $s, $d";
    //return " ORDER BY $o ";
    
}

function concatString($str, $opt = '')
{
    return $str .= $opt;
}

function getFileTypeQuery($where, $ext)
{
    if (isset($ext) && !empty($ext) && $ext === 'owt')
    {
        return $where .= " AND (upload.filename NOT LIKE '%pdf' AND upload.filename NOT LIKE '%zip')";
    }
    elseif (isset($ext) && !empty($ext))
    {
        return $where .= " AND upload.filename LIKE '%$ext'";
    }
    return $where;
}

function fileCountByUser($user, $domain) {
    if(is_numeric($user)){
        return  " INNER JOIN user on upload.userid = user.id WHERE user.id = '$user' ";
    }
    return " INNER JOIN user ON user.id = upload.userid INNER JOIN client ON $domain = client.domain AND client.domain='$user'";
}

function getIdTypeQuery($where, $user, $domain){
    if (isset($user) && !empty($user) && !is_numeric($user)) {
        return $where .= " AND $domain = '$user'";
    }
    elseif (isset($user) && !empty($user) && is_numeric($user)) { 
        return $where .= " AND user.id = $user";
    }
    return $where;
}

function getColleagues($id, $dom)
{
    return "SELECT employer.id, employer.name FROM upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $dom = client.domain) AS employer ON $dom = employer.domain WHERE upload.id= $id ORDER BY name";
}
function getColleagues2($id)
{
    return "SELECT user.id, user.name FROM user INNER JOIN (SELECT tgt.client_id FROM user 
INNER JOIN upload ON user.id = upload.userid 
INNER JOIN (SELECT user.client_id FROM user INNER JOIN upload ON user.id = upload.userid  WHERE upload.id = $id)  AS tgt 
ON user.client_id  =  tgt.client_id LIMIT 1)  AS client ON client.client_id = user.client_id ORDER BY name";
}

function assignColleague($upload_id, $user_id)
{
    return "UPDATE upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT user.client_id FROM upload INNER JOIN user 
ON upload.userid = user.id WHERE upload.id = $upload_id) AS tgt ON user.client_id = tgt.client_id SET upload.userid = $user_id WHERE user.client_id = tgt.client_id";
}

function doInsert($link)
{
    return function () use ($link)
    {
        $doSanitize = partial('doSanitize', $link);
        $args = array_map($doSanitize, func_get_args());
        return "INSERT INTO upload SET filename = '$args[0]', mimetype = '$args[1]', description = '$args[2]', filepath = '$args[3]', file = '$args[4]', size ='$args[5]'/1024, userid='$args[6]', time=NOW()";
    };
}

function doEmail($link, $id)
{
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
}

function getInitialKey($conn, $privilege, $user, $dom)
{

    function assignInitialUser($id, $dom)
    {
        return "SELECT employer.name, employer.id FROM (SELECT user.name, user.id, client.domain FROM user INNER JOIN client ON $dom = client.domain) AS employer WHERE employer.domain='$id' LIMIT 1";
    }

    if ($privilege == 'Admin' and !empty($user))
    { //ie Admin selects user
        $key = $user;
        include $conn;
        //will either return empty set(no error) or produce count. Test to see if a client has been selected.
        $row = doSafeFetch($link, "SELECT domain FROM client WHERE domain='$key'");
        if (isset($row[0]))
        {
            //RETURNS one user, as relationship between file and user is one to one.
            $row = doSafeFetch($link, assignInitialUser($key, $dom));
            $key = $row['id'];
            if (!$key)
            {
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

function doFind($db, $key, $domain)
{
    $email = "{$_SESSION['email']}";
    include $db;
    $sql = "SELECT $domain FROM user WHERE user.email='$email'";
    $result = doQuery($link, $sql, 'Error selecting client domain');
    $row = goFetch($result);
    $dom = $row[0];
    $sql = "SELECT COUNT(*) AS dom FROM user INNER JOIN client ON $domain = client.domain WHERE $domain = '$dom' AND client.domain =' $dom'";
    $result = doQuery($link, $sql, 'Error getting file count');
    $row = goFetch($result);
    $where = $row['dom'] > 0 ? " WHERE user.email='$email'" : " WHERE user.id=$key"; //user
    $sql = "SELECT employer.id, employer.name  FROM user INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $domain = client.domain) AS employer ON $domain = employer.domain $where";
    $result = doQuery($link, $sql, 'Database error fetching clients.');
    $users = array(); //resets user array to display users of current client
    $users = doProcess($result, 'id', 'name');
    if ($row['dom'] <= 1)
    { //SELECT MENU in SEARCH for only more than one "employee"
        $users = array();
        $zero = true;
    }
    $client = array();
}

function prepFind($users, $client)
{
    /*
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
    */
}

function doSearch($db, $priv, $domain, $compose, $order_by, $start, $display, $client, $users, $myip)
{

    include $db;
    $tel = '';
    $from = getBaseFrom();
    $from .= " INNER JOIN userrole ON user.id=userrole.userid";
    $user_id = doSanitize($link, $_GET['user']);
    $check = null;
    $select = getBaseSelect();
    if ($priv == 'Admin')
    {
        //will either return empty set(no error) or produce count. Test to see if a client has been selected.
        $sql = "SELECT domain FROM client WHERE domain = '" . $user_id . "'";

        $result = doQuery($link, $sql, 'Error retrieving records for user');
        $row = goFetch($result, MYSQLI_ASSOC);

        $where = ' WHERE TRUE';
        if (isset($row['domain']) && !is_numeric($user_id))
        { //user_id is text(domain) for Clients
            $from .= " INNER JOIN client ON $domain = client.domain ";
            $where = " WHERE domain = '" . $user_id . "'";
            $check = count($row);
        }
        
        $select .= ", user.name as user";
    } //admin
    else
    {
        $email = $_SESSION['email'];
        $where = " WHERE user.email='$email' ";
    }
    $text = doSanitize($link, $_GET['text']);
    $suffix = isset($_GET['suffix']) ? doSanitize($link, $_GET['suffix']) : null;

    $haveUser = partial(negate('isEmpty') , $user_id);
    $notClient = partial('isEmpty', $check);
    $res = array_reduce([$haveUser, $notClient], 'every', true);

    $andUser = curry2('concatString') (" AND user.id = $user_id");
    $queryUser = getBestPred(partial('doAlways', $res)) ($andUser, partial('doAlways'));
    $likeText = curry2('concatString') (" AND upload.filename LIKE '%$text%' ");
    $tmp = getBestPred(partial(negate('isEmpty'), $text));
    $queryText = $tmp($likeText, partial('doAlways'));
    $cb = $compose($queryUser, partial('getFileTypeQuery', $where, $suffix), $likeText);
    $where = $cb($where);
    $order = getBaseOrder($order_by, $start, $display);
    $sql = $select . $from . $where . $order;
    
    $result = doQuery($link, $sql, 'Error fetching file details.');
    $sqlcount = $select . ', COUNT(upload.id) as total ' . $from . $where . ' GROUP BY upload.id ' . $order;
    $result = doQuery($link, $sqlcount, 'Error getting file count.');

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
    $records = $row['total'];
    $pages = ($records > $display) ? ceil($records / $display) : 1;
/*
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/files.html.php';
    exit();
    */
}

function doUpload($db, $priv, $key, $domain)
{
    //Bail out if the file isn't really an upload
    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
    $doError = partialDefer('errorHandler', 'There was no file uploaded!', $terror);
    doWhen(partial('doAlways', !is_uploaded_file($_FILES['upload']['tmp_name'])) , $doError) (null);

    $realname = uploadedfile('name');
    $ext = preg_replace('/(.*)(\.[^0-9.]+$)/i', '$2', $realname);
    $uploadname = time() . getRemoteAddr() . $ext;
    $path = '../../filestore/';
    $filedname = $path . $uploadname;
    // Copy the file (if it is deemed safe)
    $doError = partialDefer('errorHandler', "Could not  save file as $filedname!", $terror);
    doWhen(partial('doAlways', !copy(uploadedfile('tmp_name') , $filedname)) , $doError) (null);

    $theKey = getInitialKey($db, $priv, $_POST['user'], $domain);
    $mykey = $theKey ? $theKey : $key;
    // Prepare user-submitted values for safe database insert
    include $db;
    $uploaddesc = isset($_POST['desc']) ? $_POST['desc'] : '';
    $doInsert = doInsert($link); //older versions of php may need to capture closure in a variable as opposed to func()();
    $sql = $doInsert($realname, uploadedfile('type') , $uploaddesc, $path, $uploadname, uploadedfile('size') , $mykey);
    doFetch($link, $sql, 'Database error storing file information!' . $sql);

    doEmail($link, mysqli_insert_id($link));
    header('Location: .');
    exit();
}

function doView($db)
{
    include $db;
    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
    $id = doSanitize($link, $_GET['id']);
    $result = mysqli_query($link, "SELECT filename, mimetype, filepath, file, size FROM upload WHERE id = '$id'");
    $doError = partialDefer('errorHandler', 'Database error fetching requested file.', $terror);
    doWhen(partial('doAlways', !$result) , $doError) (null);

    $file = mysqli_fetch_array($result);
    $doError = partialDefer('errorHandler', 'File with specified ID not found in the database!', $terror);
    doWhen(partial('doAlways', !$file) , $doError) (null);

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
}

function doDelete($db, $compose)
{
    include $db;
    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
    $findIndex = curry2('array_search') (array(
        "c",
        "u",
        "f"
    ));
    $id = doSanitize($link, $_POST['id']);
    $routes = array(
        "SELECT c.file FROM user INNER JOIN client ON user.client_id = client.id INNER JOIN upload AS c ON user.id = c.userid  INNER JOIN upload AS d ON d.userid=user.id WHERE d.id=$id",
        "SELECT upload.file FROM upload INNER JOIN user ON upload.userid=user.id INNER JOIN upload AS d ON upload.userid=d.userid WHERE d.id=$id",
        "SELECT file FROM upload WHERE id=$id"
    );
    $getRoute = partial('getProperty', $routes);
    $sql = $compose($findIndex, $getRoute) ($_POST['extent']);
    if (!$sql)
    {
        header('Location: .');
    }
    $result = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Database error fetching stored files.', $terror);
    doWhen(partial('doAlways', !$result) , $doError) (null);

    while ($row = mysqli_fetch_array($result))
    {
        $file = $row['file'];
        $sql = "DELETE FROM upload WHERE file = '$file'";
        $doError = partialDefer('errorHandler', 'Error deleting file.', $terror);
        doWhen(partial('doAlways', !mysqli_query($link, $sql)) , $doError) (null);

        unlink('../../filestore/' . $file);
    }
    header('Location: .');
    exit();
}

function prepUpdate($db, $priv, $id, $domain)
{

    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
    $sql = "SELECT upload.id, filename, description, upload.userid, user.name FROM upload INNER JOIN user ON upload.userid=user.id  WHERE upload.id=$id";
    include $db;
    $result = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Database error fetching stored files', $terror);
    doWhen(partial('doAlways', !$result) , $doError) (null); //doWhen expects an argument to pass to predicate and action functions
    return mysqli_fetch_array($result);
}

function prepUpdateUser($db, $priv)
{
    if ($priv == 'Admin')
    {
        include $db;
        $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
        $sql = "SELECT user.name, user.id FROM user LEFT JOIN client ON user.client_id=client.id  WHERE client.domain IS NULL UNION SELECT user.name, user.id FROM user INNER JOIN client ON user.client_id = client.id ORDER BY name";
        $result = mysqli_query($link, $sql);

        $doError = partialDefer('errorHandler', 'Database error fetching users.', $terror);
        doWhen(partial('doAlways', !$result) , $doError) (null);

        while ($row = mysqli_fetch_array($result))
        {
            $colleagues[$row['id']] = $row['name'];
        }
        return $colleagues;
    }
}

function doUpdate($db)
{
    include $db;
    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
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
    doWhen(partial('doAlways', !mysqli_query($link, $sql)) , $doError) (null);

    header('Location: . ');
    exit();
}