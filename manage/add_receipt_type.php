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

// 新增憑證類型
$data = [$_POST['class']];
$sql = "SELECT * FROM receipt_type_management WHERE class=?";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
    if (!($result = $sth->fetch(PDO::FETCH_ASSOC))) {
        $dataAdd = [$_POST['class']];
        $sqlAdd = "INSERT INTO receipt_type_management(class) VALUES (?)";
        $sthAdd = $link->prepare($sqlAdd);
        $sthAdd->execute($dataAdd);
    } else {
        echo json_encode('新增失敗，改憑證類型名稱已經存在。', JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode('無法新增憑證類型：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    exit;
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
echo json_encode('新增成功', JSON_UNESCAPED_UNICODE);
$link = NULL;
exit;
