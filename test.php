<?php
$ip = $_SERVER["REMOTE_ADDR"];
$machine_name = gethostbyaddr($ip);

echo "HELLO!";
echo "<br/>";
echo "IP of this device is {$ip}.";
echo "<br/>";
echo "Machine name of this device is {$machine_name}.";