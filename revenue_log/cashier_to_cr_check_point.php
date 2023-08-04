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
if ($_SESSION["tr_to_cashier_check_point"] == false) {
    header("location: total_revenue.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["cashier_to_cr_check_point"] == true) {
    header("location: cashier_reserve.php");
    exit;
}

// 儲存找零金的資訊於SESSION
if (isset($_POST["ones"]) && $_POST["ones"] != NULL) {
    $_SESSION["cashier_ones"] = $_POST["ones"];
}
if (isset($_POST["five"]) && $_POST["five"] != NULL) {
    $_SESSION["cashier_five"] = $_POST["five"];
}
if (isset($_POST["ten"]) && $_POST["ten"] != NULL) {
    $_SESSION["cashier_ten"] = $_POST["ten"];
}
if (isset($_POST["fifty"]) && $_POST["fifty"] != NULL) {
    $_SESSION["cashier_fifty"] = $_POST["fifty"];
}
if (isset($_POST["hundred"]) && $_POST["hundred"] != NULL) {
    $_SESSION["cashier_hundred"] = $_POST["hundred"];
}
if (isset($_POST["five_hundred"]) && $_POST["five_hundred"] != NULL) {
    $_SESSION["cashier_five_hundred"] = $_POST["five_hundred"];
}
if (isset($_POST["thousand"]) && $_POST["thousand"] != NULL) {
    $_SESSION["cashier_thousand"] = $_POST["thousand"];
}
if (isset($_FILES["pic"]["error"]) && $_FILES["pic"]["error"] == 0) {
    $tmpname = $_FILES["pic"]["tmp_name"];
    $file = NULL;
    $instr = fopen($tmpname, "rb");
    $file = fread($instr, filesize($tmpname));
    $_SESSION["cashier_pic"] = base64_encode($file);
}
$_SESSION["cashier_comments"] = $_POST["comments"];

// 確認所有填入的數字皆為非負整數
if ($_SESSION["cashier_ones"] < 0 || $_SESSION["cashier_ones"] == NULL) {
    echo "<script>alert(\"1元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}
if ($_SESSION["cashier_five"] < 0 || $_SESSION["cashier_five"] == NULL) {
    echo "<script>alert(\"5元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}
if ($_SESSION["cashier_ten"] < 0 || $_SESSION["cashier_ten"] == NULL) {
    echo "<script>alert(\"10元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}
if ($_SESSION["cashier_fifty"] < 0 || $_SESSION["cashier_fifty"] == NULL) {
    echo "<script>alert(\"50元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}
if ($_SESSION["cashier_hundred"] < 0 || $_SESSION["cashier_hundred"] == NULL) {
    echo "<script>alert(\"100元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}
if ($_SESSION["cashier_five_hundred"] < 0 || $_SESSION["cashier_five_hundred"] == NULL) {
    echo "<script>alert(\"500元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}
if ($_SESSION["cashier_thousand"] < 0 || $_SESSION["cashier_thousand"] == NULL) {
    echo "<script>alert(\"1000元數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}

// 確認照片已經上傳
if ($_SESSION["cashier_pic"] == NULL) {
    echo "<script>alert(\"檔案未上傳或是檔案格式錯誤。\\n如果確認上傳的過程正確，請聯繫系統管理人。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}

// 確認備註內的字數限制在100字以內
if (strlen($_SESSION["cashier_comments"]) > 200) {
    echo "<script>alert(\"字數過多，請減少文字數量\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}

// 確認零錢數額是否正確，若不正確則需要填寫備註
$cashRevenue = $_SESSION["cash_revenue"]; // POS機上的數字
$one = $_SESSION["cashier_ones"];
$five = $_SESSION["cashier_five"];
$ten = $_SESSION["cashier_ten"];
$fifty = $_SESSION["cashier_fifty"];
$hundred = $_SESSION["cashier_hundred"];
$fiveHundred = $_SESSION["cashier_five_hundred"];
$thousand = $_SESSION["cashier_thousand"];
$total = $one + 5 * $five + 10 * $ten + 50 * $fifty + 100 * $hundred + 500 * $fiveHundred + 1000 * $thousand;
$_SESSION["cashier_total"] = $total;
$dTotal = $cashRevenue + $_SESSION["dCRM_total"];
if ($total == $dTotal) {
    $_SESSION["cashier_to_cr_check_point"] = true;
    header("location: cashier_reserve.php");
    exit;
} else if (!isset($_SESSION["cashier_comments"]) || $_SESSION["cashier_comments"] == NULL) {
    echo "<script>alert(\"總額錯誤：\\n現金總額應為{$dTotal}，而當前實際總額為{$total}元整，請再算一次。\\n\\n若仍然有誤，則請思考原因並填寫備註。\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
} else {
    $_SESSION["cashier_to_cr_check_point"] = true;
    header("location: cashier_reserve.php");
    exit;
}
