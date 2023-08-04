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
if ($_SESSION["mat_to_exchange_check_point"] == false) {
    header("location: merge_and_take.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["exchange_to_crevenue_check_point"] == true) {
    header("location: cash_revenue.php");
    exit;
}

// 儲存找零金的資訊於SESSION
if (isset($_POST["hundred_bag"]) && $_POST["hundred_bag"] != NULL) {
    $_SESSION["exchange_hundred_bag"] = $_POST["hundred_bag"];
}
if (isset($_POST["five_hundred_bag"]) && $_POST["five_hundred_bag"] != NULL) {
    $_SESSION["exchange_five_hundred_bag"] = $_POST["five_hundred_bag"];
}
if (isset($_POST["thousand_bag"]) && $_POST["thousand_bag"] != NULL) {
    $_SESSION["exchange_thousand_bag"] = $_POST["thousand_bag"];
}
if (isset($_POST["ones"]) && $_POST["ones"] != NULL) {
    $_SESSION["exchange_ones"] = $_POST["ones"];
}
if (isset($_POST["five"]) && $_POST["five"] != NULL) {
    $_SESSION["exchange_five"] = $_POST["five"];
}
if (isset($_POST["ten"]) && $_POST["ten"] != NULL) {
    $_SESSION["exchange_ten"] = $_POST["ten"];
}
if (isset($_POST["fifty"]) && $_POST["fifty"] != NULL) {
    $_SESSION["exchange_fifty"] = $_POST["fifty"];
}
if (isset($_POST["hundred"]) && $_POST["hundred"] != NULL) {
    $_SESSION["exchange_hundred"] = $_POST["hundred"];
}
if (isset($_POST["five_hundred"]) && $_POST["five_hundred"] != NULL) {
    $_SESSION["exchange_five_hundred"] = $_POST["five_hundred"];
}
if (isset($_POST["thousand"]) && $_POST["thousand"] != NULL) {
    $_SESSION["exchange_thousand"] = $_POST["thousand"];
}
if (isset($_FILES["pic"]["error"]) && $_FILES["pic"]["error"] == 0) {
    $tmpname = $_FILES["pic"]["tmp_name"];
    $file = NULL;
    $instr = fopen($tmpname, "rb");
    $file = fread($instr, filesize($tmpname));
    $_SESSION["exchange_pic"] = base64_encode($file);
}
$_SESSION["exchange_comments"] = $_POST["comments"];

// 確認所有填入的數字皆為非負整數
if ($_SESSION["exchange_hundred_bag"] < 0 || $_SESSION["exchange_hundred_bag"] == NULL) {
    echo "<script>alert(\"袋裝100元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_five_hundred_bag"] < 0 || $_SESSION["exchange_five_hundred_bag"] == NULL) {
    echo "<script>alert(\"袋裝500元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_thousand"] < 0 || $_SESSION["exchange_thousand"] == NULL) {
    echo "<script>alert(\"袋裝1000元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_ones"] < 0 || $_SESSION["exchange_ones"] == NULL) {
    echo "<script>alert(\"1元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_five"] < 0 || $_SESSION["exchange_five"] == NULL) {
    echo "<script>alert(\"5元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_ten"] < 0 || $_SESSION["exchange_ten"] == NULL) {
    echo "<script>alert(\"10元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_fifty"] < 0 || $_SESSION["exchange_fifty"] == NULL) {
    echo "<script>alert(\"50元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_hundred"] < 0 || $_SESSION["exchange_hundred"] == NULL) {
    echo "<script>alert(\"100元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_five_hundred"] < 0 || $_SESSION["exchange_five_hundred"] == NULL) {
    echo "<script>alert(\"500元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}
if ($_SESSION["exchange_thousand"] < 0 || $_SESSION["exchange_thousand"] == NULL) {
    echo "<script>alert(\"1000元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}

// 確認照片已經上傳
if ($_SESSION["exchange_pic"] == NULL) {
    echo "<script>alert(\"檔案未上傳或是檔案格式錯誤。\\n如果確認上傳的過程正確，請聯繫系統管理人。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}

// 確認備註內的字數限制在100字以內
if (strlen($_SESSION["exchange_comments"]) > 200) {
    echo "<script>alert(\"字數過多，請減少文字數量\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
}

// 確認零錢數額是否正確，若不正確則需要填寫備註
$dTotal = $_SESSION["dECM_total"]; // POS機上的數字
$hundredBag = $_SESSION["exchange_hundred_bag"];
$fiveHundredBag = $_SESSION["exchange_five_hundred_bag"];
$thousandBag = $_SESSION["exchange_thousand_bag"];
$one = $_SESSION["exchange_ones"];
$five = $_SESSION["exchange_five"];
$ten = $_SESSION["exchange_ten"];
$fifty = $_SESSION["exchange_fifty"];
$hundred = $_SESSION["exchange_hundred"];
$fiveHundred = $_SESSION["exchange_five_hundred"];
$thousand = $_SESSION["exchange_thousand"];
$total = $hundredBag * 100 + $fiveHundredBag * 500 + $thousandBag * 1000 + $one + 5 * $five + 10 * $ten + 50 * $fifty + 100 * $hundred + 500 * $fiveHundred + 1000 * $thousand;
$_SESSION["exchange_total"] = $total;
if ($total == $dTotal) {
    $_SESSION["exchange_to_crevenue_check_point"] = true;
    header("location: cash_revenue.php");
    exit;
} else if (!isset($_SESSION["exchange_comments"]) || $_SESSION["exchange_comments"] == NULL) {
    echo "<script>alert(\"總額錯誤：\\n換錢金總額應為{$dTotal}，而當前實際總額為{$total}元整，請再算一次。\\n\\n若仍然有誤，則請思考原因並填寫備註。\");</script>";
    echo "<script>document.location.href = \"exchange.php\";</script>";
    exit;
} else {
    $_SESSION["exchange_to_crevenue_check_point"] = true;
    header("location: cash_revenue.php");
    exit;
}
