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

// 確認當日是否是星期六、日或一
include "day_check.php";

// 檢查當日結帳系統是否已經填寫完成
if (isset($_SESSION["cremainUploadDoneBy"]) && $_SESSION["cremainUploadDoneBy"] != NULL) {
    if ($_SESSION["cremainUploadDoneBy"] == $_SESSION["emp_name"] && $_SESSION["cremainAllDone"]) {
        echo "<script>alert(\"本期剩餘廠商金登錄已經填寫完成。\\n即將跳轉至憑證與剩餘廠商金登錄系統的明細頁。\");</script>";
        echo "<script>document.location.href = \"receipts_detail.php\";</script>";
        exit;
    } else if ($_SESSION["cremainUploadDoneBy"] != $_SESSION["emp_name"]) {
        if (!$_SESSION["cremainAllDone"]) {
            echo "<script>alert(\"本期剩餘廠商金登錄正在由{$_SESSION["cremainUploadDoneBy"]}填寫。\\n即將跳轉至憑證與剩餘廠商金登錄系統的明細頁。\");</script>";
            echo "<script>document.location.href = \"receipts_detail.php\";</script>";
            exit;
        } else {
            echo "<script>alert(\"本期剩餘廠商金登錄已經由{$_SESSION["cremainUploadDoneBy"]}填寫完成。\\n即將跳轉至憑證與剩餘廠商金登錄系統的明細頁。\");</script>";
            echo "<script>document.location.href = \"receipts_detail.php\";</script>";
            exit;
        }
    }
}

// 取消Check Point
$_SESSION["cremain_to_rs_check_point"] = false;
header("location: cash_remain.php");
exit;
