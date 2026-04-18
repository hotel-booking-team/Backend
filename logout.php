<?php
session_start();
session_destroy();

$redirect = isset($_GET['redirect']) ? trim((string)$_GET['redirect']) : '../frontend/login.html';
if ($redirect === '' || preg_match('/^https?:\/\//i', $redirect)) {
	$redirect = '../frontend/login.html';
}

header("Location: " . $redirect);
exit();
?>