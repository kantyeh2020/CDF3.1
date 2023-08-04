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
if ($_SESSION["cashier_to_cr_check_point"] == false) {
    header("location: cashier.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["cr_to_mat_check_point"] == true) {
    header("location: merge_and_take.php");
    exit;
}

// 將勾選的資訊存入SESSION
if (isset($_POST["check"]) && $_POST["check"] != NULL) {
    $check = $_POST["check"];
    $checkedNum = count($_POST["check"]);
}
$_SESSION["cashier_reserve_one"] = false;
$_SESSION["cashier_reserve_five"] = false;
$_SESSION["cashier_reserve_ten"] = false;
$_SESSION["cashier_reserve_fifty"] = false;
$_SESSION["cashier_reserve_hundred"] = false;
$_SESSION["cashier_reserve_five_hundred"] = false;
$_SESSION["cashier_reserve_thousand"] = false;

for ($i = 0; $i < 7; $i++) {
    switch ($check[$i]) {
        case "cashier_reserve_one":
            $_SESSION["cashier_reserve_one"] = true;
            break;
        case "cashier_reserve_five":
            $_SESSION["cashier_reserve_five"] = true;
            break;
        case "cashier_reserve_ten":
            $_SESSION["cashier_reserve_ten"] = true;
            break;
        case "cashier_reserve_fifty":
            $_SESSION["cashier_reserve_fifty"] = true;
            break;
        case "cashier_reserve_hundred":
            $_SESSION["cashier_reserve_hundred"] = true;
            break;
        case "cashier_reserve_five_hundred":
            $_SESSION["cashier_reserve_five_hundred"] = true;
            break;
        case "cashier_reserve_thousand":
            $_SESSION["cashier_reserve_thousand"] = true;
            break;
    }
}

// 檢查是否所有選項皆被勾選
if (!$_SESSION["cashier_reserve_one"]) {
    echo "<script>alert(\"第1項未完成。\");</script>";
    echo "<script>document.location.href = \"cashier_reserve.php\";</script>";
    exit;
}
if (!$_SESSION["cashier_reserve_five"]) {
    echo "<script>alert(\"第2項未完成。\");</script>";
    echo "<script>document.location.href = \"cashier_reserve.php\";</script>";
    exit;
}
if (!$_SESSION["cashier_reserve_ten"]) {
    echo "<script>alert(\"第3項未完成。\");</script>";
    echo "<script>document.location.href = \"cashier_reserve.php\";</script>";
    exit;
}
if (!$_SESSION["cashier_reserve_fifty"]) {
    echo "<script>alert(\"第4項未完成。\");</script>";
    echo "<script>document.location.href = \"cashier_reserve.php\";</script>";
    exit;
}
if (!$_SESSION["cashier_reserve_hundred"]) {
    echo "<script>alert(\"第5項未完成。\");</script>";
    echo "<script>document.location.href = \"cashier_reserve.php\";</script>";
    exit;
}
if (!$_SESSION["cashier_reserve_five_hundred"]) {
    echo "<script>alert(\"第6項未完成。\");</script>";
    echo "<script>document.location.href = \"cashier_reserve.php\";</script>";
    exit;
}
if (!$_SESSION["cashier_reserve_thousand"]) {
    echo "<script>alert(\"第7項未完成。\");</script>";
    echo "<script>document.location.href = \"cashier_reserve.php\";</script>";
    exit;
}

// 頒發Check Point
$_SESSION["cr_to_mat_check_point"] = true;
header("location: merge_and_take.php");
exit;
