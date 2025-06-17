<?php
session_start();

if (!isset($_SESSION["loggedin"])) {
    header("location: login.php");
    exit;
}

$_SESSION = array();

session_destroy();
header('Location: login.php');
exit;