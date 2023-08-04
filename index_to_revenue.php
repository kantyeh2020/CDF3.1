<?php
session_start();
// 確認是否殘留前日的資料，若有則消除
if ($_SESSION["login_date"] != date("Y/m/d")) {
    $_SESSION = array();
    $_SESSION["login_date"] = date("Y/m/d");
}

// 確認是否是未登入的狀態，若未登入則跳回登入入口
if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true)) {
    header("location: login.php");
    exit;
}

// 若為reviewer，則跳至reviewer的目錄
if ($_SESSION["authority"] == "reviewer") {
    header("location: index_reviewer.php");
    exit;
}

// 分流
if (!isset($_SESSION["trUploadDoneBy"]) || $_SESSION["trUploadDoneBy"] == NULL) {
    header("location: ./revenue_log/total_revenue.php");
    exit;
} else if ($_SESSION["trUploadDoneBy"] == $_SESSION["emp_name"]) {
    if (!$_SESSION["revenueAllDone"]) {
        header("location: ./revenue_log/total_revenue.php");
        exit;
    } else {
        echo "<script>alert(\"本日結帳系統已經填寫完成。\\n即將跳轉至每日結帳系統的明細頁。\");</script>";
        echo "<script>document.location.href = \"./revenue_log/revenue_detail.php\";</script>";
        exit;
    }
} else if ($_SESSION["trUploadDoneBy"] != $_SESSION["emp_name"]) {
    if (!$_SESSION["revenueAllDone"]) {
        echo "<script>alert(\"本日結帳系統正在由{$_SESSION["trUploadDoneBy"]}填寫。\\n即將跳轉至每日結帳系統的明細頁。\");</script>";
        echo "<script>document.location.href = \"./revenue_log/revenue_detail.php\";</script>";
        exit;
    } else {
        echo "<script>alert(\"本日結帳系統已經由{$_SESSION["trUploadDoneBy"]}填寫完成。\\n即將跳轉至每日結帳系統的明細頁。\");</script>";
        echo "<script>document.location.href = \"./revenue_log/revenue_detail.php\";</script>";
        exit;
    }
}
