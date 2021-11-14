<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';

function seek($flag = false)
{
    $arr = array(
        'suffix',
        'user',
        'text',
        'size'
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

function resetQuery($query_str, $str = '')
{
    $query_str = explode('sort=', $query_str) [0];
    ///$query_str = preg_split('/&?sort=/', $query_str)[0];
    $s = !empty($str) ? "sort=$str" : 'sort=';
    //note not including & as in '&sort=', test elsewhere
    return array(
        'query_string' => $query_str,
        'sort' => $s
    );
}

function isDouble($q)
{
    return substr($q, -3, 1) === '=';
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
    $gang = array(); //may be empty
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
    return " FROM upload INNER JOIN user ON upload.userid = user.id";
}
function getBaseOrder($o, $s, $d)
{
    return " ORDER BY $o LIMIT $s, $d";
    //return " ORDER BY $o ";
    
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

function fileCountByUser($user, $domain)
{
    //assumes getBaseFrom is invoked first
    if (is_numeric($user))
    {
        return " WHERE user.id = $user ";
    }
    return " INNER JOIN client ON $domain = client.domain WHERE client.domain = '$user'";
}

function getColleagues($id, $dom)
{
    return "SELECT employer.id, employer.name FROM upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $dom = client.domain) AS employer ON $dom = employer.domain WHERE upload.id = $id ORDER BY name";
}
/*
function getColleaguesExtent($id, $dom)
{
    return "SELECT employer.id, employer.name, COUNT(employer.id) AS extent FROM upload INNER JOIN user ON upload.userid = user.id INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $dom = client.domain) AS employer ON $dom = employer.domain WHERE upload.id = $id GROUP BY employer.id ORDER BY name";
}
function getColleagues3($id)
{
    return "SELECT user.id, user.name FROM user INNER JOIN (SELECT tgt.client_id FROM user 
INNER JOIN upload ON user.id = upload.userid 
INNER JOIN (SELECT user.client_id FROM user INNER JOIN upload ON user.id = upload.userid  WHERE upload.id = $id)  AS tgt 
ON user.client_id  =  tgt.client_id LIMIT 1)  AS client ON client.client_id = user.client_id ORDER BY name";
}
*/
function getColleaguesFromName($domain, $name)
{
    return "SELECT user.id, user.name FROM user INNER JOIN client ON $domain = client.domain WHERE client.name = '$name' ORDER BY name";
}

function doGetColleagues($link, $id, $domain)
{
    $res = doQuery($link, getColleagues($id, $domain) , 'Database error fetching list of users.');
    return doProcess($res, 'id', 'name'); //for assigning to client
    
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
        return "SELECT employer.name, employer.id FROM (SELECT user.name, user.id, client.domain FROM user INNER JOIN client ON $dom = client.domain) AS employer WHERE employer.domain = '$id' LIMIT 1";
    }

    if ( /*$privilege == 'Admin' and*/
    !empty($user))
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

function calculatePages($db, $display, $sql)
{
    include $db;
    $result = doQuery($link, $sql, 'Error generating page count.');
    $row = goFetch($result);
    $records = $row[0]; //
    $pages = ($records > $display) ? ceil($records / $display) : 1;
    return array(
        'pages' => $pages,
        'searched' => preg_split('/(?=\sFROM)/', $sql) [1]
    );
}

function massSanitize($db, $src)
{
    include $db;
    $export = array();
    $vars = array_map(partial('doSanitize', $link) , array_filter($src, 'iSet'));
    foreach ($vars as $k => $v)
    {
        $export[$k] = $v;
    }
    return $export;
}

function doSearch($db, $user_int, $dom, $domain, $compose, $order_by, $start, $display)
{
    include $db;

    $msgs = validateSearch();
    if (!empty($msgs))
    {
        $location = reLoad($msgs, '&find');
        $helper = preserveValidFormValues($location, '&', 'x', '=');
        $location = $helper("&text={$_GET['text']}", "&size={$_GET['size']}");
        doExit($location);
        
        $doLocation = partial('concatString', $location);
        $notInString = curry2(negate('inString'))($location);
        $mapped = array_map($notInString, array('xtext', 'xsize'));
        $decorators = array("&text={$_GET['text']}", "&size={$_GET['size']}");
        
        foreach($mapped as $k => $v){
            if($mapped[$k]){
              $location = $doLocation($decorators[$k]);
            }
        }
        
        
    }
    $email = $_SESSION['email'];
    $select = "SELECT COUNT(upload.id) as total ";
    $from = getBaseFrom();
    $order = getBaseOrder($order_by, $start, $display);
    $isAdmin = partial('equals', $user_int, 0);
    //we need fallbacks for FROM and WHERE clauses in case a user isn't selected in our search box
    $fallback_where = [' WHERE TRUE', ' ', " WHERE user.email = '$email'"];
    $fallback_from = [partial('doAlways', '') , partial('fileCountByUser', $dom, $domain) , partial('doAlways', '') ];
    $vars = massSanitize($db, $_GET);

    foreach ($vars as $k => $v)
    {
        ${$k} = $v;
    }
   
    if(!empty($size)){
    $m = explode('m', $size);
    $size = $m[0];
    $mod = preg_split('/</', $size);
    $size = isset($mod[1]) ? $mod[1] : $mod[0];
    if(isset($m[1])){
        $size *= 1000;
    }
    $size = isset($mod[1]) ? "< $size" : "> $size";
    }

    $emptyText = partial('isEmpty', $text);
    $emptySize = partial('isEmpty', $size);
    $active_where = [' ', " WHERE user.id = $user", " WHERE user.email = '$email'"];
    $haveUser = partial(negate('isEmpty') , $user);
    $andUser = curry2('concatString')(" AND user.id = $user");
    $queryUser = getBestPred($isAdmin) ($andUser, partial('doAlways'));
    $where = $active_where[$user_int];
    $active_from = [partial('fileCountByUser', $user, $domain) , partial('doAlways', '') , partial('doAlways', '') ];   

    if (empty($user))
    {
        //dump($from);
        $where = $fallback_where[$user_int];
        $from .= $fallback_from[$user_int]();
        if ($isAdmin())
        { //only admin can have no constraints on user
            $queryUser = partial('doAlways', $where);
        }
        //$queryUser = partial('doAlways', '');
        
    }
    else
    {
        $from .= $active_from[$user_int]();
        $queryUser = getBestPred($isAdmin) (partial('doAlways') , $andUser);
    }
    $likeText = curry2('concatString')(" AND upload.filename LIKE '%$text%' ");
    $andSize = curry2('concatString')(" AND size $size");
    $queryText = getBestPred($emptyText)(partial('doAlways'), $likeText);
    $querySize = getBestPred($emptySize)(partial('doAlways'), $andSize);

    $decorate = $compose($queryUser, curry2('getFileTypeQuery') ($suffix) , $queryText, $querySize);
    $where = $decorate($where);
    $sql = $select . $from . $where . $order;
    //dump($sql);
    return calculatePages($db, $display, $sql);
}

function validateFileDetails($d = "description")
{

    $msgs = array();
    $messages = array(
        VALIDATE_DESCRIPTION,
        REQUIRED_FILENAME,
        VALIDATE_FILENAME
    );

    $negate = curryLeft2('preg_match', 'negate');
    $compose = curry2(compose('reduce'));
    $dopush = $compose(populateArray($msgs, ';'));
    $isDesc = getBestArgs('isEmpty') (partial('doAlways', false) , $negate('/^[\w]+[\w\s]{2,29}/'));
    $isFileName = $negate('/^[\w][\w-]{0,49}\.[\w]{2,4}/');
    $checks = array_map('ALWAYS', $messages);
    $beBadDiz = array(
        $isDesc,
        $dopush($checks[0])
    );
    $beEmpty = array(
        'isEmpty',
        $dopush($checks[1])
    );
    $beBadFileName = array(
        $isFileName,
        $dopush($checks[2])
    );
    $cbs = array(
        $d => array(
            $beBadDiz
        )
    );
    if (!isset($_POST['action']))
    { //upload
        $cbs = array(
            $d => array(
                $beBadDiz
            ) ,
            'filename' => array(
                $beBadFileName,
                $beEmpty
            )
        );
    }
    doWhenLoop($cbs);
    return $msgs;
}

function validateSearch()
{
    $msgs = array();
    $messages = array(
        VALIDATE_SEARCH,
        VALIDATE_FILESIZE
    );
    $match = curryLeft2('preg_match');
    $negate = curryLeft2('preg_match', 'negate');
    $compose = curry2(compose('reduce'));
    $dopush = $compose(populateArray($msgs, ';'));
    $isFileSize = getBestArgs('isEmpty') (partial('doAlways', false) , $negate('/^[<>]?\d{1,4}m{0,1}/'));
    $isSearchTerm = getBestArgs('isEmpty') (partial('doAlways', false) , $negate('/^[\w]+[\w\s-]{1,4}/'));
    $checks = array_map('ALWAYS', $messages);
    $beBadSearch = array(
        $isSearchTerm,
        $dopush($checks[0])
    );
    $beBadFileSize = array(
        $isFileSize,
        $dopush($checks[1])
    );
    $cbs = array(
        'text' => array(
            $beBadSearch
        ) ,
        'size' => array(
            $beBadFileSize
        )
    );
    doWhenLoop($cbs);
    return $msgs;
}

function reLoad($msgs, $q = '')
{
    $error = array_values($msgs) [0];
    $warning = implode(' ', array_keys($msgs));
    $warning .= " warning";
    $warning .= " upload";
    return "?error=$error&warning=$warning$q";
}

function doUpload($db, $priv, $key, $domain)
{
    //Bail out if the file isn't really an upload
    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
    $doError = partialDefer('errorHandler', 'There was no file uploaded!', $terror);
    $uploaddesc = isset($_POST['description']) ? $_POST['description'] : '';
    $msgs = validateFileDetails();

    if (!empty($msgs))
    {
        doExit(reLoad($msgs, '&onupload=true'));
    }
    doWhen(partial('doAlways', !is_uploaded_file($_FILES['upload']['tmp_name'])) , $doError) (null);
    $realname = uploadedfile('name');
    $ext = strchr($realname, '.');
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
    $doInsert = doInsert($link); //older versions of php may need to capture closure in a variable as opposed to func()();
    $sql = $doInsert($realname, uploadedfile('type') , $uploaddesc, $path, $uploadname, uploadedfile('size') , $mykey);
    doFetch($link, $sql, 'Database error storing file information!' . $sql);

    //doEmail($link, mysqli_insert_id($link));
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

function prepUpdate($db, $id)
{
    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
    $sql = "SELECT upload.id, filename, description, upload.userid, user.name FROM upload INNER JOIN user ON upload.userid=user.id  WHERE upload.id = $id";
    include $db;
    $res = doQuery($link, $sql, 'Database error fetching list of users.');
    return goFetch($res, MYSQLI_ASSOC);
}

function prepUpdateUser($db)
{
    include $db;
    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
    $sql = "SELECT user.name, user.id FROM user LEFT JOIN client ON user.client_id = client.id  WHERE client.domain IS NULL UNION SELECT user.name, user.id FROM user INNER JOIN client ON user.client_id = client.id ORDER BY name";
    $res = doQuery($link, $sql, 'Database error fetching list of users.');
    return doProcess($res, 'id', 'name');
}

function doSelected($pred)
{
    if (equals($a, $b))
    {
        return " selected='selected' ";
    }
    return '';
}

function populateList($db, $domain, $user_int, $clientname)
{
    include $db;
    $sql = "SELECT user.id, user.name FROM user LEFT JOIN client ON user.client_id = client.id";
    $users = array();
    $client = array();
    if (!$user_int)
    {
        $result = doQuery($link, $sql . " WHERE client.domain IS NULL ORDER BY name", 'Database error fetching users.');
        $users = doProcess($result, 'id', 'name');

        $sql = "SELECT name, domain, tel FROM client ORDER BY name";
        $result = doQuery($link, $sql, 'Database error fetching clients.');
        $client = doProcess($result, 'domain', 'name');
    }
    elseif ($user_int === 1)
    { /////Present list of users for specific client
        $sql = getColleaguesFromName($domain, $clientname);
        $result = doQuery($link, $sql, 'Database error fetching clients.');
        $client = doProcess($result, 'id', 'name');
    }
    return array(
        'users' => $users,
        'client' => $client
    );
}

function doUpdate($db)
{
    include $db;
    $terror = $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php';
    if (isset($_COOKIE['filename']))
    {
        $ext = strchr($_COOKIE['filename'], '.');
    }
    $replacement = "$1$ext";
    $filename = preg_replace('/^([\w]+)(\.[\w]+)/', $replacement, $_POST['filename']);
    $msgs = validateFileDetails('desc');
    if (!empty($msgs))
    {
        $location = reLoad($msgs);
        $id = $_POST['fileid'];
        $location = reLoad($msgs, "&id=$id&swap=No");
        if (!inString('xname', $_GET['warning']))
        {
            $n = $_POST['filename'];
            $location .= "&filename=$n";
        }
        doExit($location);
    }
    $fid = doSanitize($link, $_POST['fileid']);
    $orig = doSanitize($link, $_POST['update']);
    $user = isset($_POST['user']) ? doSanitize($link, $_POST['user']) : null;
    $user = isset($_POST['colleagues']) ? doSanitize($link, $_POST['colleagues']) : $user;
    $diz = isset($_POST['desc']) ? doSanitize($link, $_POST['desc']) : null;
    $fname = isset($_POST['filename']) ? doSanitize($link, $filename) : null;
    $user = !(isset($user)) ? $orig : $user;
    $single = "UPDATE upload SET userid ='$user', description ='$diz', filename ='$fname' WHERE id ='$fid'";
    $extent = isset($_POST['blanket']) ? assignColleague($fid, $user) : "UPDATE upload SET userid='$user' WHERE userid='$orig'";
    $sql = $_POST['answer'] === "Yes" ? $extent : $single;
    doQuery($link, $sql, 'error updating details');
    //navigates back to selected page, remove query string from swap onwards to prevent loading update.html.php
    $qs = explode('swap', $_SERVER['QUERY_STRING']) [0];
    $qs = "?$qs";
    header("Location: $qs");
    exit();
}
function getClientNameFromEmail($db, $domain, $email)
{
    //bit confusing as $domain can either be a mysql formula to extract a portion of an email OR that actual portion
    include $db;
    if (isset($email))
    {
        $key = doSanitize($link, $email);
        $res = doQuery($link, "SELECT client.name, client.id, client.domain FROM client INNER JOIN user ON $domain = client.domain WHERE user.email='$email' ", 'Db error retrieving client name');
    }
    else
    {
        $key = doSanitize($link, $domain);
        $res = doQuery($link, "SELECT client.name, client.id, client.domain FROM client WHERE domain = '$key'", 'Db error retrieving client name');
    }
    return goFetch($res, MYSQLI_ASSOC);
}

function getNameFromEmail($db, $email)
{
    include $db;
    $key = doSanitize($link, $email);
    $res = doQuery($link, "SELECT user.name from user WHERE user.email='$email'", 'Db error retrieving user name');
    return goFetch($res) [0];
}

function userDetailsFromDomain($db, $key, $domain)
{
    include $db;
    $sql = "SELECT employer.user_name, employer.user_id FROM (SELECT user.name AS user_name, user.id AS user_id, client.domain FROM user INNER JOIN client ON $domain = client.domain) AS employer WHERE employer.domain='$key'";
    $res = doQuery($link, $sql, 'Database error fetching users.');
    return doProcess($res, 'user_id', 'user_name');
}

function testDomain($db, $key)
{
    include $db;
    $key = doSanitize($link, $key);
    $res = doQuery($link, "SELECT domain, name FROM client WHERE domain = '$key' ", 'Database error fetching clients.');
    return goFetch($res);
}

function getUserNameFromID($db, $key)
{
    include $db;
    $res = doQuery($link, "SELECT id, name FROM user where id ='$key' ORDER BY name", "Error retrieving users from the database!");
    return doProcess($res, 'id', 'name');
}

function chooseAdmin($db, $key, $user, $domain)
{
    $row = testDomain($db, $user);
    $client = null;
    include $db;
    if (isset($row))
    {
        $c = getClientNameFromEmail($db, $user, null) ['name'];
        $manage = "Manage users of $c";
        $users = userDetailsFromDomain($db, $user, $domain);
    }
    else
    {
        $manage = "Edit details";
        $users = getUserNameFromID($db, $user);
    }
    return array(
        'users' => $users,
        'manage' => $manage,
        'ret' => '.',
        'page' => 'list',
        'client' => $client
    );
}

function chooseClient($db, $key, $user, $domain)
{
    include $db;
    $client = null;
    $user = domainFromUserID($link, $key); //list of members
    if ($user)
    {
        $users = userDetailsFromDomain($db, $user, $domain);
        $client = true;
    }
    else
    {
        $users = getUserNameFromID($db, $key);
    }
    return array(
        'users' => $users,
        'manage' => 'Edit Details',
        'ret' => '..',
        'page' => 'uploads',
        'client' => $client
    );
}

function asAdmin($p, $clientname)
{
    return $p === ('Admin') || $clientname;
}
function getPages($db, $display, $getCountQuery, $pages)
{
    if (isset($_GET['page']) && is_numeric($_GET['page']))
    {
        $pages = $_GET['page'];
    }
    elseif (!isset($pages))
    { // counts all files
        include $db;
        $sql = "SELECT COUNT(upload.id) ";
        $sql .= getBaseFrom();
        $sql .= $getCountQuery();
        $res = doQuery($link, $sql, 'Database error fetching requesting the list of files');
        $row = goFetch($res, MYSQLI_NUM);
        $records = intval($row[0]);
        $pages = ($records > $display) ? ceil($records / $display) : 1;
    } //end of IF NOT PAGES SET
    return $pages;
}
$doFile = curry3('concatCB') ('f');