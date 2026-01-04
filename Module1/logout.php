<?php

//start session
session_start();

//clear data
$_SESSION = [];

//remove all session variables registered
session_unset();

//destroy session on the server
session_destroy();

//no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//redirect to login
header("Location: ../Module1/login.php");
exit();
