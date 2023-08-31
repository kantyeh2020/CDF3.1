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
if (!(isset($_POST["ones"])) || $_POST["ones"] == NULL) {
    $one = 0;
} else {
    $one = $_POST["ones"];
}
if (!(isset($_POST["five"])) || $_POST["five"] == NULL) {
    $five = 0;
} else {
    $five = $_POST["five"];
}
if (!(isset($_POST["ten"])) || $_POST["ten"] == NULL) {
    $ten = 0;
} else {
    $ten = $_POST["ten"];
}
if (!(isset($_POST["fifty"])) || $_POST["fifty"] == NULL) {
    $fifty = 0;
} else {
    $fifty = $_POST["fifty"];
}
if (!(isset($_POST["hundred"])) || $_POST["hundred"] == NULL) {
    $hundred = 0;
} else {
    $hundred = $_POST["hundred"];
}
if (!(isset($_POST["five_hundred"])) || $_POST["five_hundred"] == NULL) {
    $fiveHundred = 0;
} else {
    $fiveHundred = $_POST["five_hundred"];
}
if (!(isset($_POST["thousand"])) || $_POST["thousand"] == NULL) {
    $thousand = 0;
} else {
    $thousand = $_POST["thousand"];
}
$total = $one + $five * 5 + $ten * 10 + $fifty * 50 + $hundred * 100 + $fiveHundred * 500 + $thousand * 1000;

// 確認資料都有正確填入
if ($one < 0 || $five < 0 || $ten < 0 || $fifty < 0 || $hundred < 0 || $fiveHundred < 0 || $thousand < 0) {
    echo json_encode('硬幣個數或紙鈔張數必須是非負整數。', JSON_UNESCAPED_UNICODE);
    exit;
}

// 連線MySQL
include "jquery_connect_mysql.php";

// 更新儲備金狀態
$data = [$one, $five, $ten, $fifty, $hundred, $fiveHundred, $thousand, $total, $_SESSION["restaurant"]];
$sql = "INSERT INTO cash_reserve_management(start_date, ones, five, ten, fifty, hundred, five_hundred, thousand, total, restaurant_id) VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
} catch (PDOException $e) {
    $link = NULL;
    echo json_encode('無法更新儲備金狀態：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    exit;
}

// 更新SESSION內的儲備金資訊
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM cash_reserve_management WHERE restaurant_id=? ORDER BY id DESC LIMIT 1";
$stmt = $link->prepare($sql);
try {
    $stmt->execute($data);
    $dCRMResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION["dCRM_id"] = $dCRMResult["id"];
    $_SESSION["dCRM_ones"] = $dCRMResult["ones"];
    $_SESSION["dCRM_five"] = $dCRMResult["five"];
    $_SESSION["dCRM_ten"] = $dCRMResult["ten"];
    $_SESSION["dCRM_fifty"] = $dCRMResult["fifty"];
    $_SESSION["dCRM_hundred"] = $dCRMResult["hundred"];
    $_SESSION["dCRM_five_hundred"] = $dCRMResult["five_hundred"];
    $_SESSION["dCRM_thousand"] = $dCRMResult["thousand"];
    $_SESSION["dCRM_total"] = $dCRMResult["total"];
} catch (PDOException $e) {
    $link = NULL;
    echo json_encode('無法更新儲備金資訊：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    exit;
}

// 告知成功，並返回員工與餐廳管理頁
$link = NULL;
echo json_encode('儲備金資訊更新成功。', JSON_UNESCAPED_UNICODE);
exit;
