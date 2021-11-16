<?php
    $error = isset($_GET['error']) ? $_GET['error'] : "View files satisfying the following criteria:";
    $warning = isset($_GET['warning'])  ? $_GET['warning'] : "searcher";
$text = isset($_GET['text'])  ? $_GET['text']  : '';
$size = isset($_GET['size'])  ? $_GET['size']  : '';
?>

<h1></h1>
<form action="?" method="get" name="searchFiles" class="<?php echo $warning; ?>">
    <fieldset><legend><?php echo $error; ?></legend>
    <?php if(!isset($zero)) :?>
    <div><label for="user">By user:&nbsp;</label>
        <select id="user" name="user">
            <option value="">Any User</option>
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
    </div>
    <?php endif; ?>
    <div>
        <label for="text">Containing text:&nbsp;</label><input value="<?php htmlout($text); ?>" maxlength="300" type="search" id="text" name="text"/></div>
    <div>
        <div>
        <label for="size">File size:&nbsp;</label><input value="<?php htmlout($size); ?>"title="defaults to kb and >, override with < or m" id="size" name="size" maxlength="20" placeholder="<2m"/></div>
        <div>
            <label for="suffix">Suffix:&nbsp;</label>
            <select id="suffix" name="suffix">
                <option value="">Search files</option>
                <option value="pdf">pdf</option>
                <option value="zip">zip</option>
                <option value="owt">other</option>
            </select></div>
        <input type="hidden" name="action" value="search"/>
        <input type="submit" value="Search"/></div>
	
    </fieldset></form>
<p><a href=".">Return</a></p>
<?php echo '</body></html>';