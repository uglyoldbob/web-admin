<?php
$config = parse_ini_file("config.ini", true);

echo $config[$argv[1]][$argv[2]];
?>
