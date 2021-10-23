<?php
function dump($arg){
 exit(var_dump($arg));
}

function doConfirm($db, $action){
    if($action === 'Yes'){
        include $db;
        $id = mysqli_real_escape_string($link, $_POST['id']);
        $result = doQuery($link, "DELETE FROM user WHERE id = $id", 'Error deleting user.');
    }
	header('Location: . ');
	exit();
}