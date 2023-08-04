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

// 刪除餐廳
$data = [$_POST['restaurant_name']];
$sql = "SELECT * FROM restaurant WHERE restaurant_name=? AND restaurant_status='open'";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
    if (!($result = $sth->fetch(PDO::FETCH_ASSOC))) {
        echo json_encode('無此餐廳或此餐廳已經被刪除', JSON_UNESCAPED_UNICODE);
        exit;
    } else if ($_SESSION["restaurant_name"] == $_POST["restaurant_name"]) {
        echo json_encode('不可刪除你正在使用的餐廳別', JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $dataDelete = [$_POST['restaurant_name']];
        $sqlDelete = "UPDATE restaurant SET restaurant_status='closed' WHERE restaurant_name=?";
        $sthDelete = $link->prepare($sqlDelete);
        $sthDelete->execute($dataDelete);
    }
} catch (PDOException $e) {
    echo json_encode('無法刪除：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    exit;
}

// 告知成功，並返回員工與餐廳管理頁
echo json_encode('刪除成功', JSON_UNESCAPED_UNICODE);
$link = NULL;
exit;
