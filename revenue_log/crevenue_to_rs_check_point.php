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

// 確認是否已經完成上個表單
if ($_SESSION["exchange_to_crevenue_check_point"] == false) {
    header("location: exchange.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["crevenue_to_rs_check_point"] == true) {
    header("location: revenue_submit.php");
    exit;
}

// 儲存填入的資料
if (isset($_POST["change_filled"]) && $_POST["change_filled"] != NULL) {
    $_SESSION["crevenue_change_filled"] = $_POST["change_filled"];
}
if (isset($_POST["fee"]) && $_POST["fee"] != NULL) {
    $_SESSION["crevenue_fee"] = $_POST["fee"];
}
if (isset($_POST["white_list"]) && $_POST["white_list"] != NULL) {
    $_SESSION["crevenue_white_list"] = $_POST["white_list"];
}

// 檢查是否有誤
if ($_SESSION["crevenue_change_filled"] != $_SESSION["exchange_total"] - $_SESSION["dECM_total"]) {
    echo "<script>alert(\"請將換零盒補足至{$_SESSION["dECM_total"]}後，勾選選項。\");</script>";
    echo "<script>document.location.href = \"cash_revenue.php\";</script>";
    exit;
}
if ($_SESSION["crevenue_fee"] < 0 || $_SESSION["crevenue_fee"] == NULL) {
    echo "<script>alert(\"小費數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_revenue.php\";</script>";
    exit;
}
if ($_SESSION["crevenue_white_list"] < 0 || $_SESSION["crevenue_white_list"] == NULL) {
    echo "<script>alert(\"小白單數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_revenue.php\";</script>";
    exit;
}

// 頒發Check Point
$_SESSION["crevenue_to_rs_check_point"] = true;
header("location: revenue_submit.php");
exit;
