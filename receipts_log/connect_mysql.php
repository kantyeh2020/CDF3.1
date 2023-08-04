<?php
session_start();
$dsn = "mysql:dbname=check_flow_database_v3;host=localhost;port=3306";
$username = "root";
$password = "password";
try {
    $link = new PDO($dsn, $username, $password);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $link->query('SET NAMES utf8');
    //echo "成功建立MySQL伺服器連接和開啟check_flow_database資料庫</br>";
} catch (PDOException $e) {
    echo "<script>alert(\"無法連線MySQL：{$e->getMessage()}\\n請聯繫系統管理人\");</script>";
    $link = NULL;
    exit;
}
