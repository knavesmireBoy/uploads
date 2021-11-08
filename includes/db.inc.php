<?php

$link = mysqli_connect('localhost', 'root', 'covid19@krauq');
if (!$link)
{
$error = 'Unable to connect to the database server.'. mysqli_error($link);
include 'error.html.php';
exit();
}

if (!mysqli_select_db($link, 'uploads'))
{
$error = 'Unable to locate the uploads database.' . mysqli_error($link);
include 'error.html.php';
exit();
}