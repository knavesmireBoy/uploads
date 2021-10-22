<?php
function seek()
{
    $arr = array(
        'suffix',
        'user_id',
        'text',
        'ext',
        'useroo',
        'textme'
    );
    $i = count($arr);
    while ($i--)
    {
        if (isset($GLOBALS[$arr[$i]]))
        {
            return '.';
        }
    }
    return '?find';
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
    return mysqli_fetch_array(mysqli_query($lnk, $sql), $mode);
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

function formatFileSize($size)
{
    if ($size > 1024)
    {
        return number_format($size / 1024, 2, '.', '') . 'mb';
    }
    return ceil($size) . 'kb';
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

function concatString($str, $opt = ''){
        return $str .= $opt;
}

function getFileTypeQuery($where, $ext){
    if(isset($ext) && $ext === 'owt'){
       return $where .= " AND (upload.filename NOT LIKE '%pdf' AND upload.filename NOT LIKE '%zip')";
    }
    elseif(isset($ext)){
        return $where .= " AND upload.filename LIKE '%$ext'";
    }
    return $where;
}

function doFind($db, $key, $domain){
    $email = "{$_SESSION['email']}";
    include $db;
        $sql = "SELECT $domain FROM user WHERE user.email='$email'";
        $result = mysqli_query($sql);
        $row = mysqli_fetch_array($result);
        $dom = $row[0];
        $sql = "SELECT COUNT(*) AS dom FROM user INNER JOIN client ON $domain = client.domain WHERE $domain = '$dom' AND client.domain =' $dom'";
        $result = mysqli_query($sql);
        $row = mysqli_fetch_array($result);
    $where = count($count) > 0 ? " WHERE user.email='$email'" : " WHERE user.id=$key"; //user
        $sql = "SELECT employer.id, employer.name  FROM user INNER JOIN (SELECT user.id, user.name, client.domain FROM user INNER JOIN client ON $domain = client.domain) AS employer ON $domain = employer.domain $where";
        $result = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Database error fetching clients.', $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php');
    doWhen($always(!$result), $doError) (null);
    
        $users = array(); //resets user array to display users of current client
        while ($row = mysqli_fetch_array($result)) {
            $users[$row['id']] = $row['name'];
        }
        if ($count <= 1) { //SELECT MENU in SEARCH for only more than one "employee"
            $users = array();
            $zero = true;
        }
        $client = array();
}

function prepFind($users, $client){
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/search.html.php';
}

function doSearch($db, $priv, $domain, $compose, $order_by, $start, $display, $client, $users, $myip){
    
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
    $text = doSanitize($link, $_GET['text']);
    $suffix = isset($_GET['suffix']) ? doSanitize($link, $_GET['suffix']) : null;
    
    $haveUser = partial(negate('isEmpty'), $user_id);
    $notClient = partial('isEmpty', $check);
    $res = array_reduce([$haveUser, $notClient], 'every', true);
    
    $andUser = curry2('concatString')(" AND user.id = $user_id");
    $queryUser = getBestPred(partial('doAlways', $res))($andUser, partial('doAlways'));
    $likeText = curry2('concatString')(" AND upload.filename LIKE '%$text%'");
    $tmp = getBestPred(partial(negate('isEmpty'), $text));
    $queryText = $tmp($likeText, partial('doAlways')); 
    $cb = $compose($queryUser, $queryText, partial('getFileTypeQuery', $where, $suffix));
    $where = $cb($where);
    $order = getBaseOrder($order_by, $start, $display);
    $sql = $select . $from . $where . $order;
    $result = mysqli_query($link, $sql);
    $doError = partialDefer('errorHandler', 'Error fetching file details.' . $sql, $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php');
    doWhen(partial('doAlways',!$result), $doError) (null);
    $sqlcount = $select . ', COUNT(upload.id) as total ' . $from . $where . ' GROUP BY upload.id ' . $order;   
    $result = mysqli_query($link, $sqlcount);
    $doError = partialDefer('errorHandler', 'Error getting file count.', $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/error.html.php');
    doWhen(partial('doAlways', !$result), $doError) (null);

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

?>
