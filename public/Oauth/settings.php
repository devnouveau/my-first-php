<?php

return [
    'client_id' => '',
    'client_secret' => '',
    'redirect_uri' => 'https://' . $_SERVER['HTTP_HOST'] . '/Oauth/complete-oauth.php',
    'scopes' => [
        'user_profile','user_media'
    ]
];



