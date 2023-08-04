<?php
include "connect_mysql.php";

$sql = "SET FOREIGN_KEY_CHECKS=0";
$sth = $link->prepare($sql);
$sth->execute();

$sql = "TRUNCATE TABLE exchange_change";
$sth = $link->prepare($sql);
$sth->execute();

$sql = "TRUNCATE TABLE cashier_change";
$sth = $link->prepare($sql);
$sth->execute();

$sql = "TRUNCATE TABLE cash_revenue";
$sth = $link->prepare($sql);
$sth->execute();

$sql = "TRUNCATE TABLE total_revenue";
$sth = $link->prepare($sql);
$sth->execute();

$sql = "TRUNCATE TABLE receipt";
$sth = $link->prepare($sql);
$sth->execute();

$sql = "TRUNCATE TABLE cash_remain";
$sth = $link->prepare($sql);
$sth->execute();

$sql = "SET FOREIGN_KEY_CHECKS=1";
$sth = $link->prepare($sql);
$sth->execute();

$link = NULL;
header("location: login.php");
exit;
