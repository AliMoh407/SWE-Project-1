<?php
require_once 'config.php';
session_destroy();
header('Location: ' . getBaseUrl() . 'login.php');
exit();
?>
