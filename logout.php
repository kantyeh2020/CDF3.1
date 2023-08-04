<?php
session_start();
//連線MySQL
include "connect_mysql.php";

// 移除裝置資訊
try {
    $data = [$_SESSION["emp_id"]];
    $sql = "UPDATE employee SET login_machine='' WHERE emp_id=?";
    $stmt = $link->prepare($sql);
    $stmt->execute($data);
    $link = NULL;
} catch (PDOException $e) {
    echo "<script>alert(\"清除裝置資料失敗：{$e->getMessage()}\")</script>";
    $link = NULL;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
}

// 清除SESSION
$_SESSION = array();
session_destroy();

// 返回首頁
header("location: login.php");
exit;
