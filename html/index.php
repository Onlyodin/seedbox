<!DOCTYPE html>
<html>
<head>
    <title>Welcome to <?php echo gethostname(); ?></title>
    <?php
        $user = $_SERVER['PHP_AUTH_USER'];
        $homeDirectory = '/home/' . $user;
        $homeLvm = '/dev/mapper/data-' . $user;
        function formatBytes($bytes) {
            if ($bytes > 0) {
                $i = floor(log($bytes) / log(1024));
                $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
                return sprintf('%.02F', round($bytes / pow(1024, $i),1)) * 1 . ' ' . @$sizes[$i];
            } else {
                return 0;
            }
        }
    ?>
</head>
<body>
    <style>
        body {
            background-image: linear-gradient(to bottom, #c0c0c0, #ffffff);
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
            font-family: "Inter", Helvetica, sans-serif;
        }
        h4 {
            margin: 0 0;
        }
    </style>
    <div style="width: 90%; margin: 0 auto; display: flex;">
    <div>
        <div>
            <?php
                $text = gethostname();
                $figletOutput = shell_exec("figlet '$text'");
                echo "\n<pre>\n" . $figletOutput . "</pre>\n";
            ?>
        </div>
        <div>
            <h2>Welcome, <?php echo ucfirst($user); ?>!</h2><br />
        </div>
        <div style="display: flex;">
            <div style="width: 50%;">
                    <!-- Content for the first div -->
                    <h4>Slot information:</h4>
                    <div style="display: flex;">
                        <div style="width: 50%;">
                            <p>Total Slot Size:</p>
                            <p>Available Space:</p>
                        </div>
                        <div style="width: 50%;">
                            <?php
                            echo "<p>" . formatBytes(shell_exec("lsblk -b --output SIZE -n $homeLvm")) . "</p>";
                            echo "<p>" . formatBytes(disk_free_space($homeDirectory)) . "</p>";
                            ?>
                        </div>
                    </div>
                    <br />
            </div>
            <div style="width: 50%;">
                <!-- Content for the second div -->
                <h4>Quick Links:</h4>
                <div style="display: flex;">
                    <div>
                        <p><a href="/rutorrent">ruTorrent Interface</a></p>
                        <p><a href="/<?php echo $user; ?>">Downloads directory</a></p>
                        <br /><br />
                    </div>
                </div>
            </div>
        </div>
        <div>
        <h3>rTorrent Statistics</h3>
            <?php
            $fqdn = $_SERVER['SERVER_NAME'];
            $domain = str_replace(gethostname() . '.', '', $_SERVER['SERVER_NAME']);
            $imagePath = "/munin/" . $domain . "/" . $fqdn . "/rtom_";
            $rtomPlugins = array("spdd", "vol", "peers", "mem");
            foreach ($rtomPlugins as $plugin) {
                echo "\n    <p>\n";
                echo "        <img src=\"" . $imagePath . $user . "_" . $plugin . "-day.png\" alt=\"rTorrent " . $plugin . " - by day\">\n";
                echo "        <img src=\"" . $imagePath . $user . "_" . $plugin . "-week.png\" alt=\"rTorrent " . $plugin . " - by week\">\n";
                echo "    </p>";
            }
            echo "\n";
            ?>    
        </div>
        </div>
    </div>
    </div>
</body>
</html>