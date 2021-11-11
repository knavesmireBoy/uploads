<?php
////mysql -u root -p mypoloafrica < ../../../../../Users/user/mypol.sql
ini_set("display_errors", true);
//https://www.airpair.com/php/fatal-error-allowed-memory-size
ini_set('memory_limit', '1024M'); // or you could use 1G
date_default_timezone_set("Europe/London");  // http://www.php.net/manual/en/timezones.php
//require_once '../../../semolinapilchard/poloafricaDB.txt';

define("MAGIC", $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php');
define("HELPERS", $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php');
define("DB",  '../includes/db.inc.php');
define("ACCESS", '../includes/access.inc.php');

define("TEMPLATE_PATH", "../templates/");

define("REQUIRED_NAME", "xname;NAME is a required field");
define("REQUIRED_EMAIL", "xemail;EMAIL is a required field");
define("REQUIRED_PWD", "xpassword;a PASSWORD is required to login");
define("VALIDATE_NAME", "xname;Please supply NAME in expected format");
define("VALIDATE_EMAIL", "xemail;Please supply a valid EMAIL address");
define("VALIDATE_PWD", "xpassword;PASSWORD should be at least three characters in length");

define("REQUIRED_DOMAIN", "xdomain;DOMAIN is a required field");
define("VALIDATE_DOMAIN", "xdomain;Please supply a valid DOMAIN");
define("VALIDATE_UNIQUE_DOMAIN", "xdomain;DOMAIN already exists");

define("VALIDATE_PHONE", "xphone;Please supply a valid PHONE NUMBER");
define("VALIDATE_DESCRIPTION", "xdesc;Description is optional but should be between 3 and 30 word characters");
define("VALIDATE_EXTENSION", "xdesc;Please preserve existing extension");
define("SANITIZE_DESCRIPTION", "xdesc;Description contains invalid characters");

// Or, using an anonymous function as of PHP 5.3.0
spl_autoload_register(function ($class) {
    require_once CLASS_PATH . $class . '.php';
});

?>