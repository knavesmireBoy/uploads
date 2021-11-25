<?php

$sorted = isset($_GET['sort']) ? true : false;
$query_string = preg_replace('/(\?[a-z0-9=&]*)(&sort)([a-z]*)/', '$1$2', '?' . $_SERVER['QUERY_STRING']);
$amper = explode('&sort=', $_SERVER['QUERY_STRING']);
$question = explode('sort=', $query_string);
//https://stackoverflow.com/questions/15626955/php-regexp-negative-lookahead
$presort = preg_match('/\?(?!sort)./', $query_string);//returns false, 0, 1
$sort_string = '';
/*Mostly $sort should be an empty string as it will be present in the query string:
<th><a href="<?php echo $query_string . $sort . 'f'; ?>">File name</a></th>
it is initialy set to one of two defaults depending on the status of the current query string */
if (strlen($query_string) === 1){ //initial
    $sort = 'sort=';
}
else if($presort && !isset($amper[1])){//earlier queries are in $_SERVER['QUERY_STRING']
    $sort = '&sort=';
}
else {
    $sort = '';//because it will be part of query string
}
//query the 'sort' part of the query string as pattern may be in a field of a filename search eg: &text=vacuum&sort=uu
if(isset($question[1])){
    $sort_string = "sort=$question[1]";
}
$checksort = preg_match('/sort/', $sort_string);
if($checksort) {
    $checkReset = function($needle, $haystack) {
        $reset = explode($needle, $haystack);
        $reset = isset($reset[1]) ? $reset[1] : '';
        return strlen($reset) === 2 ? true : false;
    };
$deferCheckReset = curry2($checkReset);
$deferCheckReset = $deferCheckReset($sort_string);
/*doWhen expects one argument, even if it resolves to empty, it receives two partially applied functions and itself is also partially applied*/
$tisTwoo = $always(true);
    $resetQ = curry22('resetQuery');
    $resetQ = $resetQ('');
$checkTreble = partial(doWhen($tisTwoo, $resetQ($query_string)), '');
$checkDouble = partial(doWhen($deferCheckReset, partial('resetQuery', $query_string)), 'uu');
$checkSingle = partial(doWhen($deferCheckReset, partial('resetQuery', $query_string)), 'u');
$cbs = [$checkTreble, $checkDouble, $checkSingle];
$notUser = negate(partial('preg_match', '/u/', $sort_string));
$split = curry2('preg_split');
$checkUserToggleStatus = $compose('notEmpty', $split($sort_string));
/*IF splitting produces a a two member array for 'uuu' scenario reset sort string, for 'u' and 'uu' reset when second member has two characters ie ?sort=uut : ['sort=', 't'], ?sort=uuu : ['sort=', ''], ?sort=uutt : ['sort=', 'tt']*/
$u = $checkUserToggleStatus('/u/');
$uu = $checkUserToggleStatus('/uu/');
$uuu = $checkUserToggleStatus('/uu(u|[^u]u)/');
//order is critical as potentially more than one scenario can return true, we 
$options = array($uuu, $uu, $u);
$cb = function (&$item, $i) {
        $item = isset($item[1]) && andNotEmpty($item[1], $i);
};
$result = array_walk($options, $cb);//changes array in-place to a series of booleans
/* A case for classes here as we are either in USER MODE where we can refine the order by time or filename which is achieved by appending a t or f to the existing user: ie uf uuf uuff, reset occurs at uuff so we next get uuf*/
    
$int = array_search(true, $options);
if (is_int($int)){//get the first found boolean, if any
    $resetvars = $cbs[$int]();
}
elseif($notUser() && isDouble($sort_string)){
    $resetvars = resetQuery($query_string);
}
    if(isset($resetvars)){//resetQuery may not run
        foreach ($resetvars as $k => $v) { ${$k} = $v; }
    }//resetvars
}//checksort
if ($sorted && $_GET['sort'] == 'uuu' || $sorted && $_GET['sort'] == 'uutu' || $sorted && $_GET['sort'] == 'uufu')
{
    header("Location: .");
}
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/base.html.php';
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/files.html.php';
if ($findmode)
{
    include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/search.html.php';
}