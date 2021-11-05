<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/helpers.inc.php';
//ob_start('ob_postprocess');
//ob_start('ob_gzhandler');
$span = 2;
?>
<h1 class="<?php echo strtolower($priv); ?>"><a href="?"><?php echo "$base | $name"; ?></a></h1>
<h2><?php echo date('l F j, Y'); ?></h2>
<?php if($nonBrowser()) : ?>
<form action="<?php $_SERVER['PHP_SELF']?>" method="post" name="uploadform" enctype="multipart/form-data">
    <table class="up">
        <tr><td><label for="uploadfiles">Upload File:</label></td><td><input id="uploadfiles" type="file" name="upload"/></td></tr>
        <tr><td><label for="desc">File Description: </label></td><td><input id="desc" type="text" name="desc" maxlength="255"/></td></tr>
<?php
    if($isPriv() || $isTrueClient()) : ?>
<tr><td><label for="user">User:</label></td><td>
<select id="user" name="user">
<option value="">Select one</option>
    <?php 
    echo $doOpt('clients');
    foreach ($client as $k => $v){
    echo $doSelected($k, $v);
    }
    echo $doOptEnd();
    echo $doOpt('users');
    foreach ($users as $k => $v) {
    echo $doSelected($k, $v);
    }
    echo $doOptEnd(); ?>
    </select>
</td></tr>
<?php endif; ?>
<input type="hidden" name="action" value="upload"/>
<tr><td><input type="submit" value="Upload"/></td><td>&nbsp;</td></tr></table>
</form>
	<?php endif; //Browser
if (count($files) > 0): ?>
<p>The following files are stored in the database:</p>
<table>
    <thead>
        <tr>
            <th><a href="<?php echo $query_string . $sort . 'f'; ?>">File name</a></th>
            <?php $choice = isset($admin_status) && count($client) > 1 ? 'User' : 'Description'  ?>
            <th><a href="<?php echo $query_string . $sort . 'u'; ?>"><?php echo $choice; ?></a></th>
            <th><a href="<?php echo $query_string . $sort . 't'; ?>">Time</a></th>
            <?php $span = ($nonBrowser() ? '2' : '1')  ?>
            <th colspan="<?php echo($span) ?>" class="control">Control<?php ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($files as $f): ?>
        <tr valign="top" class="<?php if($f['origin'] == $myip) echo 'admin';?>">
            <?php $fsize = formatFileSize($f['size']); ?>
            <td><a title="<?php htmlout($fsize); ?>" href="?action=view&amp;id=<?php htmlout($f['id']); ?>">
                <?php htmlout($f['filename']); ?></a></td>
            <?php if ($isSingleUser()) : ?>
            <td><?php htmlout($f['description']); ?></td>
            <?php endif;
            if (isset($admin_status)) : //add description as title attribute on user field
            $des = (empty($f['description'])  ? 'No description provided' : html($f['description'])); 
            if($isPriv() || $isTrueClient()): ?>
            <td title="<?php echo $des; ?>" >
                <?php htmlout($f['user']); ?></td>
            <?php elseif(!$isSingleUser()): ?>
            <td><?php echo $des; ?></td>
            <?php
            endif;
            endif;
            ?>
            <td title="<?php echo $tel ?>">
                <?php $stamp = html($f["time"]);
                echo date("g:i a F j ", strtotime($stamp)); ?></td>
            <td><form action="<?php $_SERVER['PHP_SELF']?>" method="get" name="downloads">
                <div><input type="hidden" name="action" value="download"/>
                    <input type="hidden" name="id" value="<?php htmlout($f['id']); ?>"/>
                    <input type="submit" value="download"/></div></form></td>
            <?php if ($nonBrowser()) : ?>
            <td><form action="<?php $_SERVER['PHP_SELF']?>" method="post" name="<?php htmlout($f['id']); ?>">
                <div><input type="hidden" name="action" value="delete"/>
                    <input type="hidden" name="id" value="<?php htmlout($f['id']); ?>"/>
                    <input type="submit" value="edit"/></div>
                </form>
            </td>
            <?php endif; ?></tr><?php endforeach; ?>
    </tbody>
</table>
<?php else :
$greeting = ($_SERVER['QUERY_STRING']) ? 'There were no files that matched your criteria' : 'There are currently no files in the
database' ?>
<h2><a href="<?php $_SERVER['PHP_SELF']?>" title="Click to return"><?php echo $greeting; ?>
</a></h2>
<?php endif; 
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/logout.inc.html.php'; ?>
<p><a href="admin/">Admin Pages</a></p>
<?php
$wither = seek(true);
$link = $wither == '.'  ? 'Clear search results' : 'Search files';
?>
<p><a href="<?php echo $wither; ?>"><?php echo $link; ?></a></p>
<p class="footer">
<?php
    $check = explode('sort=', $_SERVER['QUERY_STRING']);
    if(isset($check[1])){
        $sort = $check[1];
    }
    else {
        $sort = '';
    }
    
    $q = "&page=$pages&user=$user&text=$text&suffix=$suffix&sort=$sort";
    
    if ($pages > 1) {
        $current_page = ($start/$display) + 1;
        if ($current_page != 1) { ?>
    <a href="?start=<?php echo $start-$display; echo $q; ?>">Previous</a>
    <?php 
                                }
        for ($i=1; $i<=$pages; $i++){
            if ($i != $current_page) { ?>
    <a href="?start=<?php echo ($display * ($i-1)); echo $q; ?>"><?php echo $i ?></a>
    <?php
                                     }
            else {  ?>
    <span class="current"><?php echo($i); ?></span>
    <?php
                 }
        }
        if ($current_page <> $pages) { ?>
    <a href="?start=<?php echo $start+$display; echo $q; ?>">Next</a></p>
<?php
                                     }
    }//If Pages > 1
    
    if (isset($call)) {
        include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/prompt.html.php';
        if (!isset($filename)) { 
            echo '</div></body></html>';
        }
    }//prompt
    
    elseif (isset($answer)) {
        include $_SERVER['DOCUMENT_ROOT'] . '/uploads/templates/update.html.php';
        echo '</div></body></html>';
    }