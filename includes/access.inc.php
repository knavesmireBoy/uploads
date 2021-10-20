<?php
function databaseContainsUser($email, $password){
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
$email = mysqli_real_escape_string($link, $email);
$password = mysqli_real_escape_string($link, $password);
$sql = "SELECT COUNT(*) FROM user INNER JOIN userrole ON user.id=userrole.userid WHERE email='$email' AND password='$password' ";
$result = mysqli_query($link, $sql);
if (!$result)  {
$error = 'Error searching for user.';
include 'error.html.php';
exit();
}
$row = mysqli_fetch_array($result);
if ($row[0] > 0)  {
return TRUE;
}  else  {
 return FALSE;
 }
 }

function userIsLoggedIn(){
if (isset($_POST['action']) and $_POST['action'] == 'login')  {
if (!isset($_POST['email']) or $_POST['email'] == '' or !isset($_POST['password']) or $_POST['password'] == ''){
$GLOBALS['loginError'] = 'Please fill in both fields';
return FALSE;
}
$password = md5($_POST['password'] . 'uploads');

if (databaseContainsUser($_POST['email'], $password))    {
 session_start();
 $_SESSION['loggedIn'] = TRUE;
 $_SESSION['email'] = $_POST['email'];
 $_SESSION['password'] = $password;
  return TRUE;
 }
 else{
  session_start();
  unset($_SESSION['loggedIn']);
  unset($_SESSION['email']);
  unset($_SESSION['password']);
  $GLOBALS['loginError'] = 'The specified email address or password was incorrect.';
  return FALSE;
  }
  }//end of log in attempt

  if (isset($_POST['action']) and $_POST['action'] == 'logout')  {
  session_start();
  unset($_SESSION['loggedIn']);
  unset($_SESSION['email']);
  unset($_SESSION['password']);
  header("Location: ". $_POST['goto']);
      exit();
      }//end of logout

session_start();
if (isset($_SESSION['loggedIn']))  {
return databaseContainsUser($_SESSION['email'], $_SESSION['password']);  }
}// end of user check



function userHasWhatRole(){
include $_SERVER['DOCUMENT_ROOT'] . '/uploads/includes/db.inc.php';
$email = mysqli_real_escape_string($link, $_SESSION['email']);
$sql = "SELECT userrole.roleid, user.id, count(user.id) as total FROM userrole INNER JOIN user ON user.id=userrole.userid where user.email='$email' GROUP BY userrole.roleid, user.id";
 $result = mysqli_query($link, $sql);

 if (!$result)  {
 $error = 'Error searching for user roles.';
 include 'error.html.php';
 exit();
 header('Location: .');
 }
 $row = mysqli_fetch_array($result);
 if ($row['total'] > 0)
 {
//$roleplay[$row['id']] = $row['roleid'];
return $row;
//return $roleplay;
 }
else  {
return FALSE;
}
}


/*
function email($em, $id) {
$email = html($em);
$body = "Before I answer that question, lets look at the alternative method your printer is suggesting. In InDesign and all the Creative Suite applications, it s easy to create a PostScript file from the Print dialog box. Just select PostScript from the Printer menu at the top of the dialog box. Then you can choose a PPD file (I d suggest selecting your Adobe PDF printer if you have Acrobat) or Device Independent (removing any printer dependencies, useful for some postprocessing workflows like imposition). Make your choices in the Print dialog box, and then click Save instead of Print to create a PostScript file. You process that PostScript file in Distiller using the PDF settings file your printer suggests.";
$body = wordwrap($body, 70);
return mail($email, 'File upload complete', $body, "From: {$_SESSION['email']}");
}
*/

function email($em, $id) {
echo $em;
}

?>
