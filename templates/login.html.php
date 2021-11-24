<h1>Log In</h1>
<p>Please log in to upload or download files</p>
<?php if (isset($loginError)): ?>
<p><?php htmlout($loginError); ?></p>
<?php endif;
$email = isset($_GET['logme'] ? $_GET['logme'] : '';
?>
<form action="." method="post" name="loginform" class="log">
    <div><label for="email">Email: <input id="email" type="email" name="email" value="<?php htmlout($email); ?>"/></label></div>
    <div><label for="password">Password: <input id="password" type="password" name="password"/></label></div>
    <div><input type="hidden" name="action" value="login"/><input type="submit" value="login"/></div>
</form><?php echo '</div></body></html>';