<!DOCTYPE html>
<html>
<head>
    <title>Welcome to <?php echo gethostname(); ?></title>
</head>
<body>
    <h1><?php echo gethostname(); ?></h1>
    <p>Not much to see here yet. Come back soon.</p>
    <p><a href="/rutorrent">ruTorrent Interface</p>
    <p>Authenticated User: <?php echo $_SERVER['PHP_AUTH_USER']; ?></p>
</body>
</html>