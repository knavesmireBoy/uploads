<h1>Search Files</h1>
<form action="?" method="get" name="searchFiles">
    <p>View files satisfying the following criteria:</p>
    <?php if(!isset($zero)) :?>
    <div><label for="user">By user: </label>
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
        <label for="text">Containing text:</label> <input id="text" type="search" name="text"/></div>
    <div>
        <div>
            <label for="suffix">Suffix: </label>
            <select id="suffix" name="suffix">
                <option value="">Search files</option>
                <option value="pdf">pdf</option>
                <option value="zip">zip</option>
                <option value="owt">other</option>
            </select></div>
        <input type="hidden" name="action" value="search"/>
        <input type="submit" value="Search"/></div>
	
</form>
<p><a href=".">Return</a></p>
<?php echo '</body></html>';