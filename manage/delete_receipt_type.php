<?php
session_start();

// 確認是否殘留前日的資料，若有則消除
if ($_SESSION["login_date"] != date("Y/m/d")) {
    $_SESSION = array();
    $_SESSION["login_date"] = date("Y/m/d");
}

// 確認是否是未登入的狀態，若未登入則跳回登入入口
if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true)) {
    header("location: ../login.php");
    exit;
}

// 確認權限為manager
if (!(isset($_SESSION["authority"]) && $_SESSION["authority"] == "manager")) {
    header("location: login_to_index.php");
    exit;
}

// 連線MySQL
include "jquery_connect_mysql.php";

// 刪除憑證類型
$data = [$_POST['id']];
$sql = "SELECT * FROM receipt_type_management WHERE id=?";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
    if (!($result = $sth->fetch(PDO::FETCH_ASSOC))) {
        echo json_encode('無此憑證類型或此憑證類型已經被刪除', JSON_UNESCAPED_UNICODE);
    } else {
        $dataDelete = [$_POST['id']];
        $sqlDelete = "DELETE FROM receipt_type_management WHERE id=?";
        $sthDelete = $link->prepare($sqlDelete);
        $sthDelete->execute($dataDelete);
    }
} catch (PDOException $e) {
    echo json_encode('無法刪除：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
}

// 更新憑證類型資訊
$sql = "SELECT * FROM receipt_type_management";
$stmt = $link->prepare($sql);
try {
    $stmt->execute();
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION["receipt_type_{$result["id"]}"] = $result["class"];
        $_SESSION["receipt_type_number"] = $result["id"];
    }
} catch (PDOException $e) {
    $link = NULL;
    echo json_encode('無法更新憑證類型名稱：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    exit;
}

// 告知更新成功，並返回雜項管理頁
echo json_encode('刪除成功', JSON_UNESCAPED_UNICODE);
$link = NULL;
exit;
