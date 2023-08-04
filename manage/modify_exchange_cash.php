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

// 更換資料格式
if (!(isset($_POST["total"])) || $_POST["total"] == NULL) {
    $total = 0;
} else {
    $total = $_POST["total"];
}

// 確認資料都有正確填入
if ($total < 0) {
    echo json_encode('換錢金的總額必須是非負整數。', JSON_UNESCAPED_UNICODE);
    exit;
}

// 連線MySQL
include "jquery_connect_mysql.php";

// 更新換錢金狀態
$data = [$total];
$sql = "INSERT INTO exchange_cash_management(start_date, total) VALUES ((CURDATE()), ?)";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
} catch (PDOException $e) {
    $link = NULL;
    echo json_encode('無法更新換錢金狀態：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    exit;
}

// 更新SESSION內的換錢金資訊
$sql = "SELECT * FROM exchange_cash_management ORDER BY id DESC LIMIT 1";
$stmt = $link->prepare($sql);
try {
    $stmt->execute();
    $dECMResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION["dECM_id"] = $dECMResult["id"];
    $_SESSION["dECM_total"] = $dECMResult["total"];
} catch (PDOException $e) {
    $link = NULL;
    echo json_encode('無法更新換錢金資訊：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    exit;
}

// 告知成功，並返回員工與餐廳管理頁
$link = NULL;
echo json_encode('換錢金資訊更新成功。', JSON_UNESCAPED_UNICODE);
exit;
