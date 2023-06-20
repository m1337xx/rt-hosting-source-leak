<?php

$clientID = '1113522506352050196';
$clientSecret = 'CNgAHKvUegHadJEWXoQq8_Rnn3XDAu0j';
$redirectURI = 'https://rt-hosting.eu/callback.php';

if (isset($_GET['code'])) {
    // Der Benutzer hat den OAuth2-Autorisierungsprozess abgeschlossen

    // Austauschen des Autorisierungscodes gegen ein Zugriffstoken
    $tokenURL = 'https://discord.com/api/oauth2/token';
    $postData = [
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $redirectURI,
        'scope' => 'identify guilds guilds.join'
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($postData)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($tokenURL, false, $context);

    if ($response === false) {
        die('Ein Fehler ist aufgetreten beim Abrufen der erforderlichen Informationen.');
    }

    $responseData = json_decode($response, true);

    if (isset($responseData['access_token'])) {
        $sessionLifetime = 15 * 24 * 60 * 60; // 15 Tage
        session_set_cookie_params($sessionLifetime);
        ini_set('session.gc_maxlifetime', $sessionLifetime);
        session_start();

        $_SESSION['discord_access_token'] = $responseData['access_token'];

        // Weiterleiten zur geschützten Seite oder Benachrichtigung über erfolgreiche Anmeldung
        header('Location: https://rt-hosting.eu/panel');
        exit();
    } else {
        die('Ein Fehler ist aufgetreten beim Abrufen der erforderlichen Informationen.');
    }
} else {
    header('Location: https://discord.com/api/oauth2/authorize?client_id=1113522506352050196&redirect_uri=https%3A%2F%2Frt-hosting.eu%2Fcallback.php&response_type=code&scope=identify%20guilds%20guilds.join');
    exit;
}

?>
