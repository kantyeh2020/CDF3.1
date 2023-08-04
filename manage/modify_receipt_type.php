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

// 確認資料都有正確填入
if (!(isset($_POST["id"])) || $_POST["id"] == NULL) {
    echo json_encode('憑證類型編號不可以為空值。', JSON_UNESCAPED_UNICODE);
    exit;
}
if (!(isset($_POST["class"])) || $_POST["class"] == NULL) {
    echo json_encode('憑證類型名稱不可以為空值。', JSON_UNESCAPED_UNICODE);
    exit;
}

// 確認憑證類型名稱小於六個字元
if (mb_strlen($_POST["class"]) > 3 && strlen($_POST["class"]) > 6) {
    echo json_encode('憑證類型名稱最多使用三個中文字或六個英文字母。', JSON_UNESCAPED_UNICODE);
    exit;
}

// 連線MySQL
include "jquery_connect_mysql.php";

// 更新憑證類型內容
$data = [$_POST["class"], $_POST["id"]];
$sql = "SELECT * FROM receipt_type_management WHERE class=? AND id!=?";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
    if (!($result = $sth->fetch(PDO::FETCH_ASSOC))) {
        $dataModify = [$_POST["class"], $_POST["id"]];
        $sqlModify = "UPDATE receipt_type_management SET class=? WHERE id=?";
        $sthModify = $link->prepare($sqlModify);
        $sthModify->execute($dataModify);
    } else {
        echo json_encode('更新失敗，此憑證類型名稱正在被其他憑證類型使用中。', JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (PDOException $e) {
    $link = NULL;
    echo json_encode('無法更新憑證類型名稱：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    exit;
}

// 更新SESSION內的憑證類型名稱
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

// 告知成功，並返回員工與餐廳管理頁
$link = NULL;
echo json_encode('憑證類型名稱更新成功。', JSON_UNESCAPED_UNICODE);
exit;
