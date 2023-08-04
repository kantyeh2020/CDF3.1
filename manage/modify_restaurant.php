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

// 確認資訊都正確填入
if (!(isset($_POST["restaurant_brand_name"])) || $_POST["restaurant_brand_name"] == NULL) {
    echo json_encode('請填入餐廳的代號。', JSON_UNESCAPED_UNICODE);
    exit;
}
if (!(isset($_POST["restaurant_name"])) || $_POST["restaurant_name"] == NULL) {
    echo json_encode('請填入餐廳的名稱。', JSON_UNESCAPED_UNICODE);
    exit;
}
if (mb_strlen($_POST["restaurant_brand_name"], "UTF-8") != strlen($_POST["restaurant_brand_name"])) {
    echo json_encode('餐廳代號只能使用英文或數字。', JSON_UNESCAPED_UNICODE);
    exit;
}

// 連線MySQL
include "jquery_connect_mysql.php";

// 更新餐廳狀態
$restaurantBrandName = strtoupper($_POST["restaurant_brand_name"]);
$data = ["^" . $restaurantBrandName, $_POST["restaurant_name"] . "$", $_POST["original_restaurant_name"]];
$sql = "SELECT * FROM restaurant WHERE (restaurant_name REGEXP ? OR restaurant_name REGEXP ?) AND restaurant_name!=?";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
    if (!($result = $sth->fetch(PDO::FETCH_ASSOC))) {
        $dataModify = [$restaurantBrandName . "_" . $_POST["restaurant_name"], $_POST["original_restaurant_name"]];
        $sqlModify = "UPDATE restaurant SET restaurant_name=? WHERE restaurant_name=?";
        $sthModify = $link->prepare($sqlModify);
        $sthModify->execute($dataModify);

        echo json_encode('餐廳資訊更新完成。', JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode('餐廳代號或餐廳名稱和已存在的其他餐廳重複，請重新確認一次。', JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    echo json_encode('無法修改餐廳資訊：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
}

// 若是更新的是現在正在使用的餐廳，則更新餐廳資訊
if ($_SESSION["restaurant_name"] == $_POST["original_restaurant_name"]) {
    $_SESSION["restaurant_name"] = $restaurantBrandName . "_" . $_POST["restaurant_name"];
}

// 返回員工與餐廳管理頁
$link = NULL;
exit;
