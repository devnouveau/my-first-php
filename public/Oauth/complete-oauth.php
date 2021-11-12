<?php

// 인가받은 후 리다이렉트되는 페이지
// 액세스 토큰을 요청함.

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
// require_once __DIR__ . '/../vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php';

$settings = include_once 'settings.php';

if(!isset($_GET['code'])) {
    header("Location: index.php");
    exit;
}

$client = new Client();

try { // 액세스토큰요청
    $response = $client->post('https://api.instagram.com/oauth/access_token', [
        'form_params' => [
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $settings['redirect_uri'],
            'code' => $_GET['code']
        ]
    ]);
} catch(ClientException $e) {
    if($e->getCode() == 400) {
        $errorResponse = json_decode($e->getResponse()->getBody(), true);
        die("Authentication Error: {$errorResponse['error_message']}");
        echo $errorResponse['error_message'];
        echo $response->getBody();
    }
    throw $e;
}

$result = json_decode($response->getBody(), true);
echo print_r($result);

$_SESSION['access_token'] = $result; // 토큰저장

header("Location: feed.php");
exit;