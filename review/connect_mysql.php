<?php
$dsn = "mysql:dbname=check_flow_database_v3;host=localhost;port=3306";
$username = "root";
$password = "password";
try {
    $link = new PDO($dsn, $username, $password);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $link->query('SET NAMES utf8');
    //echo "成功建立MySQL伺服器連接和開啟check_flow_database資料庫</br>";
} catch (PDOException $e) {
    echo "連線MySQL失敗，無法顯示憑證內容：{$e->getMessage()}<br/>請聯繫系統管理人\"";
    $link = NULL;
    exit;
}
