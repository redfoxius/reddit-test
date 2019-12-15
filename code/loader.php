<?php
require 'RedditClient.php';
require 'DbClient.php';
require 'settings.php';

$page  = isset($_GET['page']) ? intval($_GET['page']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;

$db     = new DbClient([
    'host' => $dbHost,
    'port' => $dbPort,
    'name' => $dbName,
    'user' => $dbUser,
    'pass' => $dbPass,
]);

// load additional from reddit if not enough at database
$count = $db->getPostsCount();
if ($count < ($limit * ($page + 1))) {
    $client = new RedditClient($clientId, $clientSecret);
    if ($client->login($login, $password)) {
        $hot = $client->getPopularPosts($limit, $page);
        $db->insertData($hot);
    }
}

header('Content-Type: application/json');
echo json_encode($db->getPosts($limit, $page));
