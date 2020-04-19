<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
//ob_start('ob_postprocess');
//ob_start('ob_gzhandler');
?>
		<h1><a href="<?php $_SERVER['PHP_SELF']?>">North Wolds | File Uploads</a></h1>
		<h2><?php echo date('l F j, Y'); ?></h2>
		<form action="<?php $_SERVER['PHP_SELF']?>" method="post" name="uploadform" enctype="multipart/form-data">
		<table class="up"><tr><td><label for="uploadfiles">Upload File:</label></td><td><input id="uploadfiles" type="file" name="upload"/></td></tr>
		<tr><td><label for="desc">File Description: </label></td><td><input id="desc" type="text" name="desc" maxlength="255"/></td></tr>
			<?php if ($priv =='Admin') : ?>
<tr><td><label for="user">User:</label></td><td><select id="user" name="user"><option value="">Select one</option>
<optgroup label="clients"><?php  foreach ($client as $x => $c): ?>
<option value="<?php htmlout($x); ?>"><?php htmlout($c); ?>
</option><?php endforeach; ?></optgroup>
<optgroup label="users">
<?php  foreach ($users as $ix => $u): ?>
<option value="<?php htmlout($ix); ?>"><?php htmlout($u); ?>
</option><?php endforeach; ?></optgroup></select>
</td></tr>
<?php endif; ?>
<input type="hidden" name="action" value="upload"/>
<tr><td><input type="submit" value="Upload"/></td><td>&nbsp;</td></tr></table>
</form>
	<?php if (count($files) > 0): ?>
		<p>The following files are stored in the database:</p>
		<table>
			<thead>
				<tr>
<?php 
$tel = '';

// TABLE ORDERING...
$q =$_SERVER['QUERY_STRING'];
$q = preg_replace('/(\?[a-z0-9=&]*)(&sort|&flag)(=?[a-z]*)/','$1','?'.$q);
if($q=='?') $sort='sort='; 
elseif(substr($q,1,4)=='sort') $q='?sort='; 
else $sort='&sort='; 

//echo('AFTER ' . $q .' *!* ' . $sort . ' *!* ' . $so);


if ((substr($so,0,2)=='uu' and strlen($so)<=3)) $toggle=array($so .'f',  'u', $so . 't' );
elseif ((substr($so,0,1)=='u' and strlen($so)<=2)) $toggle=array($so .'f', $so . 'u', $so . 't' );
elseif (!$so or  strlen($so)>1 )$toggle=array('f','u','t');
else $toggle= array($so .'f', $so . 'u', $so . 't' );//append to existing sort

?>

<th><a href="<?php echo $q . $sort . $toggle[0]; ?>">File name</a></th>
<?php $choice = ($priv =='Admin'  ? 'User' : 'Description')  ?>
<th><a href="<?php echo $q . $sort . $toggle[1]; ?>"><?php echo $choice; ?></a></th>
<th><a href="<?php echo $q . $sort . $toggle[2]; ?>">Time</a></th>

<?php $num = ($priv !='Browser'  ? '2' : '1')  ?>
<th colspan="<?php echo($num) ?>" class="control">Control<?php ?></th>
</tr>
</thead>
<tbody>
			
<?php foreach($files as $f): ?>
<tr valign="top" class="<?php if($f['origin'] == '86.133.121.115
.') echo 'admin';?>">
<?php if ($f['size'] > 1024){
$fsize = number_format($f['size']/1024, 2, '.', ''). 'mb';
}
else {
$fsize= ceil($f['size']).'kb';
}
?>
<td><a title="<?php htmlout($fsize); ?>"  >
<!-- href="'../../filestore/'<?php htmlout($f['file']);?>"-->
<?php htmlout($f['filename']); ?></a></td>
<?php if ($priv =='Client') : ?>
<td><?php htmlout($f['description']); ?></td>
<?php endif;
if ($priv =='Admin') : 
$des = (empty($f['description'])  ? 'No description provided' : html($f['description'])); ?>
<td title="<?php echo $des; ?>" >
<?php htmlout($f['user']); ?></td>
<?php endif; 
?>

<td title="<?php echo $tel ?>">
    <?php 
$stamp = html($f["time"]);
echo date("g:i a F j ", strtotime($stamp)) ;?></td>


<td><form action="<?php $_SERVER['PHP_SELF']?>" method="get" name="downloads">
<div><input type="hidden" name="action" value="download"/>
<input type="hidden" name="id" value="<?php htmlout($f['id']); ?>"/>
<input type="submit" value="Download"/></div></form></td>
<?php if ($priv !='Browser') : ?>
<td><form action="<?php $_SERVER['PHP_SELF']?>" method="post" name="<?php htmlout($f['id']); ?>">
<div><input type="hidden" name="action" value="delete"/>
<input type="hidden" name="id" value="<?php htmlout($f['id']); ?>"/>
<input type="submit" value="Delete"/></div>
</form>
</td>
<?php endif; ?></tr><?php endforeach; ?>
</tbody>
</table>


<?php else :
 $greeting=($_SERVER['QUERY_STRING']) ? 'There were no files that matched your criteria' : 'There are currently no files in the database' ?>

<h2><a href="<?php $_SERVER['PHP_SELF']?>" title="Click to return"><?php echo $greeting; ?>

</a></h2>
<?php 
endif; 

include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/logout.inc.html.php'; 
if ($priv =='Admin' or $priv =='Client') : ?>
<p><a href="admin/">Admin Pages</a></p>



<?php 
endif;
//$wither = ($suffix || $user_id || $text || $ext || $useroo || $textme ? '.' : '?find'); 
$wither = seek();
$link = ($wither=='.'  ? 'Clear search results' : 'Search files');
?>
<p><a href="<?php echo $wither;?>"><?php echo $link; ?></a></p>

<p class="footer">

<?php
if(isset($_GET['ext'])) $suffix=$ext;
if(isset($_GET['u'])) $user_id=$useroo;
if(isset($_GET['u'])) $text= $textme;
if ($pages > 1) {
$current_page = ($start/$display) + 1;
if ($current_page != 1) { ?>
<a href="?s=<?php echo $start-$display; ?>&p=<?php echo ($pages); ?>&u=<?php echo $user_id; ?>&t=<?php echo $text; ?>&ext=<?php echo $suffix; ?>&sort=<?php echo $so; ?>">Previous</a>
<?php 
}
for ($i=1; $i<=$pages; $i++){
if ($i != $current_page) { ?>
<a href="?s=<?php echo ($display * ($i-1)); ?>&p=<?php echo ($pages);?>&u=<?php echo $user_id; ?>&t=<?php echo $text; ?>&ext=<?php echo $suffix; ?>&sort=<?php echo $so; ?>"><?php echo $i ?></a>
<?php
}
else { ?>
<span class="current"><?php echo($i); ?></span>
<?php
}
}
if ($current_page <> $pages) { ?>
<a href="?s=<?php echo $start+$display; ?>&p=<?php echo ($pages); ?>&u=<?php echo $user_id; ?>&t=<?php echo $text; ?>&ext=<?php echo $suffix;?>&sort=<?php echo $so; ?>">Next</a></p>
<?php
}
}//If Pages > 1

if (isset($prompt)) {
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/prompt.html.php';
if (!isset($filename)) { ?>
</div></body></html>
<?php exit();}
}//prompt
if (isset($filename)) {
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/update.html.php';
?>
</div></body></html>
<?php exit(); } ?>
</div></body></html>


<?php 
/*
$stamp = date("g:i a F j, Y ", strtotime($row["date"]));     

echo date("F j, Y g:i a", strtotime($row["date"]));                  // October 5, 2008 9:34 pm
echo date("m.d.y", strtotime($row["date"]));                         // 10.05.08
echo date("j, n, Y", strtotime($row["date"]));                       // 5, 10, 2008
echo date("Ymd", strtotime($row["date"]));                           // 20081005
echo date('\i\t \i\s \t\h\e jS \d\a\y.', strtotime($row["date"]));   // It is the 5th day.
echo date("D M j G:i:s T Y", strtotime($row["date"])); 

end output buffering and send our HTML to the browser as a whole
ob_end_flush();
ob_postprocess is our custom post processing function
function ob_postprocess($buffer)
{
$buffer = str_replace('database', 'heaven', $buffer);
// "return $buffer;" will send what is in $buffer to the browser, which includes our changes
return $buffer;
$position  = array_search(substr($so,0,-1), $toggle);
if ($position !== false) {
echo "the element in position " . $position . " has " . $so . " as its value in array  " .$array;
}
/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/ password
"/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/  email

<?php  foreach ($users as $ix => $u): 
$keywords[$ix] = preg_split("/[\s]+/", $u);
print($keywords[$ix][1] .' ' . $keywords[$ix][0] . '<br />');
endforeach; ?>
$str = 'string';

			
}
<!--
<th><a href="<?php echo $q . $sort . $toggle[0]; 
if(isset($_GET['flag']) and  !isset($_GET['check']))	{ ?>
&s=<?php echo $start ?>&p=<?php echo $pages; ?>&check=tick<?php
}
">File name</a></th>-->
*/
?>