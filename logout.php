<?php
// logout.php
require_once 'database/db.php';
start_secure_session();
$_SESSION = array();
session_destroy();
header("Location: index.php");
exit;