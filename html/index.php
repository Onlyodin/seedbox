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
        }
    </style>
    <?php
        $text = gethostname();
        $figletOutput = shell_exec("figlet '$text'");
        echo "<pre>$figletOutput</pre>";
    ?>
    <p>Welcome, <?php echo $user; ?></p><br />
    <p>Here is your slot information:</p>
    <p>Total Slot Size: <?php echo formatBytes(shell_exec("lsblk -b --output SIZE -n $homeLvm")); ?></p>
    <p>Available Space: <?php echo formatBytes(disk_free_space($homeDirectory)); ?></p><br />
    <p>Quick Links:</p>
    <p><a href="/rutorrent">ruTorrent Interface</a></p>
    <p>Not much else to see here yet. Come back soon.</p>
</body>
</html>