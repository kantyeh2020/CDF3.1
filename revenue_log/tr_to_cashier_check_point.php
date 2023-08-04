<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script>

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

// 若為reviewer，則重新導向目錄
if ($_SESSION["authority"] == "reviewer") {
    header("location: login_to_index.php");
    exit;
}

// 檢查當日結帳系統是否已經填寫完成
if (isset($_SESSION["trUploadDoneBy"]) && $_SESSION["trUploadDoneBy"] != NULL) {
    if ($_SESSION["trUploadDoneBy"] == $_SESSION["emp_name"] && $_SESSION["revenueAllDone"]) {
        echo "<script>alert(\"本日結帳系統已經填寫完成。\\\n即將跳轉至每日結帳系統的明細頁。\");</script>";
        echo "<script>document.location.href = \"revenue_detail.php\";</script>";
        exit;
    } else if ($_SESSION["trUploadDoneBy"] != $_SESSION["emp_name"]) {
        if (!$_SESSION["revenueAllDone"]) {
            echo "<script>alert(\"本日結帳系統正在由{$_SESSION["trUploadDoneBy"]}填寫。\\\n即將跳轉至每日結帳系統的明細頁。\");</script>";
            echo "<script>document.location.href = \"revenue_detail.php\";</script>";
            exit;
        } else {
            echo "<script>alert(\"本日結帳系統已經由{$_SESSION["trUploadDoneBy"]}填寫完成。\\\n即將跳轉至每日結帳系統的明細頁。\");</script>";
            echo "<script>document.location.href = \"revenue_detail.php\";</script>";
            exit;
        }
    }
}

// Check Point狀態檢查
if ($_SESSION["tr_to_cashier_check_point"] == true) {
    header("location: cashier.php");
    exit;
}

// 儲存當日營收的資訊於SESSION
if (isset($_POST["total"]) && $_POST["total"] != NULL) {
    $_SESSION["total_revenue"] = $_POST["total"];
}
if (isset($_POST["taiwan_pay"]) && $_POST["taiwan_pay"] != NULL) {
    $_SESSION["taiwan_pay"] = $_POST["taiwan_pay"];
}
if (isset($_POST["uber_eat"]) && $_POST["uber_eat"] != NULL) {
    $_SESSION["uber_eat"] = $_POST["uber_eat"];
}
if (isset($_POST["credit_card"]) && $_POST["credit_card"] != NULL) {
    $_SESSION["credit_card"] = $_POST["credit_card"];
}
if (isset($_POST["cash"]) && $_POST["cash"] != NULL) {
    $_SESSION["cash_revenue"] = $_POST["cash"];
}

// 確認填入的數字皆為正整數
if ($_SESSION["total_revenue"] < 0 || $_SESSION["total_revenue"] == NULL) {
    echo "<script>alert(\"總營收必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"total_revenue.php\";</script>";
    exit;
}
if ($_SESSION["taiwan_pay"] < 0 || $_SESSION["taiwan_pay"] == NULL) {
    echo "<script>alert(\"TaiwanPay營收必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"total_revenue.php\";</script>";
    exit;
}
if ($_SESSION["uber_eat"] < 0 || $_SESSION["uber_eat"] == NULL) {
    echo "<script>alert(\"UberEat營收必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"total_revenue.php\";</script>";
    exit;
}
if ($_SESSION["credit_card"] < 0 || $_SESSION["credit_card"] == NULL) {
    echo "<script>alert(\"信用卡營收必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"total_revenue.php\";</script>";
    exit;
}
if ($_SESSION["cash_revenue"] < 0 || $_SESSION["cash_revenue"] == NULL) {
    echo "<script>alert(\"現金營收必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"total_revenue.php\";</script>";
    exit;
}

// 確認儲存的資料無矛盾
$totalRevenue = $_SESSION["total_revenue"];
$taiwanPay = $_SESSION["taiwan_pay"];
$uberEat = $_SESSION["uber_eat"];
$creditCard = $_SESSION["credit_card"];
$cashRevenue = $_SESSION["cash_revenue"];

if ($totalRevenue == $taiwanPay + $uberEat + $creditCard + $cashRevenue) {
    $_SESSION["tr_to_cashier_check_point"] = true;
    header("location: cashier.php");
    exit;
} else {
    echo "<script>alert(\"營收總和錯誤：\\n    總營收應為 {$totalRevenue} 元整。\\n    TaiwanPay+UberEat+信用卡+現金實為 " . ($taiwanPay + $uberEat + $creditCard + $cashRevenue) . " 元整。\\n總金額不合。請重新檢查一次。\")</script>";
    echo "<script>document.location.href = \"total_revenue.php\";</script>";
    exit;
}
