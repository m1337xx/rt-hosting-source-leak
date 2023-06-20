<?php
    $sessionLifetime = 15 * 24 * 60 * 60; // 15 Tage
    session_set_cookie_params($sessionLifetime);
    ini_set('session.gc_maxlifetime', $sessionLifetime);
    session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RT - Hosting</title>
    <link rel="stylesheet" href="/add/main.css">
    <meta property="og:title" content="RT-Hosting">
    <meta property="og:site_name" content="RT-Hosting">
    <meta property="og:description" content="RT-Hosting is a german hosting service which provides 1€ servers">
    <meta property="og:image" content="https://rt-hosting.eu/PB.png">
    <meta property="og:url" content="https://rt-hosting.eu">
    <meta property="og:color" content="#00ADB5">
    <meta property="og:type" content="website">
</head>
<body>
    <div class="header">
        <div class="hl">
            <h1>RT - Hosting</h1>
        </div>
        <div class="hr">
            <h3><a href="panel">Panel</a></h3>
            <h3><a href="https://discord.com/invite/cctf8VS3QZ" target="_blank">Discord</a></h3>
        </div>
    </div>
    <div class="main">
        <?php
            $servername = "localhost";
            $username = "rth";
            $password = "ThLDxjoPs%Kq03fVNr4lESc4KS60^KKh";
            $dbname = "rth";

            $conn = new mysqli($servername, $username, $password, $dbname);
            
            if ($conn->connect_error) {
                die("<p><span style='color: #ff0000;'>Ein Fehler ist aufgretreten beim Versuch eine Verbindung zur Datenbank herzustellen.</span></p>");
            }

            $sql = "SELECT * FROM `prices` WHERE `root`=1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo "<h2>Root-Server</h2>";
                echo "<div class=\"packs\">";
                    $counter = 1;
                        foreach ($result as $row) {
                            echo "<div class='pack'>";
                            echo "<h3>".$row['pack']."</h3>";
                            if ($row['cores'] == 1) {
                                echo "<p>Kern: 1</p>";
                            } else {
                                echo "<p>Kerne: ".$row['cores']."</p>";
                            }
                            echo "<p>RAM: ".$row['ram']." GB</p>";
                            echo "<p>Space: ".$row['storage']." GB</p>";
                            echo "<p>Traffic: ".$row['traffic']."</p>";
                            echo "<p>Price: <span class=\"bold\">".$row['price']."€</span></p>";
                            echo "</div>";

                            if ($counter % 3 == 0) {
                                echo "</div>";
                                echo "<div class='packs'>";
                            }
                            
                            $counter++;
                        }
                echo "</div>";
            }
            $sql = "SELECT * FROM `prices` WHERE `root`=0";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo "<h2>V-Server</h2>";
                echo "<div class=\"packs\">";
                    $counter = 1;
                        foreach ($result as $row) {
                            echo "<div class='pack'>";
                            echo "<h3>".$row['pack']."</h3>";
                            if ($row['cores'] == 1) {
                                echo "<p>Kern: 1</p>";
                            } else {
                                echo "<p>Kerne: ".$row['cores']."</p>";
                            }
                            echo "<p>RAM: ".$row['ram']." GB</p>";
                            echo "<p>Space: ".$row['storage']." GB</p>";
                            echo "<p>Traffic: ".$row['traffic']."</p>";
                            echo "<p>Price: <span class=\"bold\">".$row['price']."€</span></p>";
                            echo "</div>";

                            if ($counter % 3 == 0) {
                                echo "</div>";
                                echo "<div class='packs'>";
                            }
                            
                            $counter++;
                        }
                echo "</div>";
            }
        ?>
    </div>
</body>
</html>