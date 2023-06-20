<?php
$sessionLifetime = 15 * 24 * 60 * 60; // 15 Tage
session_set_cookie_params($sessionLifetime);
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_start();

$servername = "localhost";
$username = "rth";
$password = "ThLDxjoPs%Kq03fVNr4lESc4KS60^KKh";
$dbname = "rth";

$conn = new mysqli($servername, $username, $password, $dbname);

if (session_status() == PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION['discord_access_token'])) {
        header('Location: https://discord.com/api/oauth2/authorize?client_id=1113522506352050196&redirect_uri=https%3A%2F%2Frt-hosting.eu%2Fcallback.php&response_type=code&scope=identify%20guilds%20guilds.join');
        exit;
    }

    $accessToken = $_SESSION['discord_access_token'];

    $userURL = "https://discord.com/api/v10/users/@me";
    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'GET'
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($userURL, false, $context);

    if ($response === false) {
        die('Ein Fehler ist aufgetreten beim Abrufen der erforderlichen Informationen.');
        unset($_SESSION['discord_access_token']);
    }

    $responseData = json_decode($response, true);

    if (isset($responseData["username"])) {
        $dcid = $responseData['id'];
        $username = $responseData['username'];
    } else {
        die('Fehler beim Abrufen der Benutzerdaten.');
        unset($_SESSION['discord_access_token']);
    }

} else {
    header('Location: https://discord.com/api/oauth2/authorize?client_id=1113522506352050196&redirect_uri=https%3A%2F%2Frt-hosting.eu%2Fcallback.php&response_type=code&scope=identify%20guilds%20guilds.join');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['_id'])) {
        if (isset($_POST['_method'])) {
            $stmt = $conn->prepare("SELECT * FROM `servers` WHERE `dcid`=? AND `vmid`=?");
            $stmt->bind_param("ss", $dcid, $_POST['_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows !== 1) { $feedback = "verification for user (".$dcid.") and vm (".$_POST['_id'].") failed!"; }
            else {
                if ($_POST['_method'] === "start") {
                    $feedback = startVm($_POST['_id']);
                }
                if ($_POST['_method'] === "stop") {
                    $feedback = stopVm($_POST['_id']);
                }
                if ($_POST['_method'] === "restart") {
                    $feedback = restartVm($_POST['_id']);
                }
    
                if (isset($feedback)) {
                    $feedback .= "\n\nPlease note that the current VM status may differ, <a href='/panel'>click here</a> to update your website.";
                }
            }
        }
    }
}

function getVmStatus($vmid)
{
    $apiUrl = "https://45.142.107.202:8006/api2/json";
    $apiToken = "root@pam!roottoken=827f9a66-9bd9-4d2c-ac2c-afcf0e5ea1ad";
    $nodeName = "node01";

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/status/current";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response | ".$response;
    }

    $data = json_decode($response, true);
    if ($data === null || !isset($data["data"]["status"])) {
        return "No data";
    }

    return $data["data"]["status"];
}

function stopVm($vmid)
{
    $apiUrl = "https://45.142.107.202:8006/api2/json";
    $apiToken = "root@pam!roottoken=827f9a66-9bd9-4d2c-ac2c-afcf0e5ea1ad";
    $nodeName = "node01";

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/status/stop";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response";
    }

    return "Success";
}

function startVm($vmid)
{
    $apiUrl = "https://45.142.107.202:8006/api2/json";
    $apiToken = "root@pam!roottoken=827f9a66-9bd9-4d2c-ac2c-afcf0e5ea1ad";
    $nodeName = "node01";

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/status/start";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response";
    }

    return "Success";
}

function restartVm($vmid)
{
    $apiUrl = "https://45.142.107.202:8006/api2/json";
    $apiToken = "root@pam!roottoken=827f9a66-9bd9-4d2c-ac2c-afcf0e5ea1ad";
    $nodeName = "node01";

    $cstatusUrl = "{$apiUrl}/nodes/{$nodeName}/qemu/{$vmid}/status/reboot";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cstatusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: PVEAPIToken={$apiToken}"));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return "No response";
    }

    return "Success";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <title>RTH - Panel</title>
    <link rel="stylesheet" href="/add/main.css">
</head>
<body>
    <div class="header">
        <div class="hl">
            <h1>RT - Panel</h1>
        </div>
        <div class="hr">
            <h3><a href="/">Hauptseite</a></h3>
            <h3><a href="https://discord.com/invite/cctf8VS3QZ" target="_blank">Discord</a></h3>
        </div>
    </div>
    <div class="main">
        <?php
            if (isset($username) && isset($dcid)) {
                echo 'Welcome back '.$username;
                
                if ($conn->connect_error) {
                    die("<p><span style='color: #ff0000;'>Ein Fehler ist aufgretreten beim Versuch eine Verbindung zur Datenbank herzustellen.</span></p>");
                }

                $sql = "SELECT * FROM `coinsys` WHERE `dcid`=$dcid";
                $result = $conn->query($sql);

                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    $coinsHolding = $row["coins"];

                    echo "<p>Aktuell bist du im besitz von <span class='bold'>$coinsHolding Coins</span>!</p>";
                } else {
                    echo "<p><span style='color: #a70000;' class='bold'>Du bist nicht in der Datenbank eingetragen. Schreibe eine Nachricht im Discord, um Coins zu sammeln!</span></p>";
                }

                if (isset($feedback)) {
                    echo "<div class='packs'>";
                    echo "<div class='feedback'>";
                    echo "<p><span class='bold'>Feedback:</span> ".$feedback."</p>";
                    echo "</div>";
                    echo "</div>";
                }
                
                $sql = "SELECT * FROM `servers` WHERE `dcid`=$dcid";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<div class=\"packs\">";
                    $counter = 1;
                        foreach ($result as $row) {
                            if ($row['done']) {
                                echo "<div class='pack'>";
                                echo "<h3>".$row['vmid']."</h3>";
                                echo "<p>Status: <span class='bold'>".getVmStatus($row['vmid'])."</span></p>";
                                echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST'><input type='hidden' name='_id' value='".$row["vmid"]."'><input type='hidden' name='_method' value='start'><input type='submit' value='START'></form>";
                                echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST'><input type='hidden' name='_id' value='".$row["vmid"]."'><input type='hidden' name='_method' value='restart'><input type='submit' value='RESTART'></form>";
                                echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST'><input type='hidden' name='_id' value='".$row["vmid"]."'><input type='hidden' name='_method' value='stop'><input type='submit' value='STOP'></form>";
                                echo "</div>";
                            }

                            if ($counter % 3 == 0) {
                                echo "</div>";
                                echo "<div class='packs'>";
                            }
                            
                            $counter++;
                        }
                    echo "</div>";
                } else {
                    echo "<p><span style='color: #a70000;' class='bold'>Du besitzt aktuell noch keinen Server! Schreibe unserem Support um einen Server zu erwerben.</span></p>";
                }
            }
        ?>
    </div>
</body>
</html>