<?php
require 'DbClient.php';
require 'settings.php';

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$db     = new DbClient([
    'host' => $dbHost,
    'port' => $dbPort,
    'name' => $dbName,
    'user' => $dbUser,
    'pass' => $dbPass,
]);
$db->removePost($postId);