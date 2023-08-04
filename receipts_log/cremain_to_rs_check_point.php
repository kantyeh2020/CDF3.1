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

// // 確認當日是否是星期日
// if (date("w", strtotime(date("Y/m/d"))) != 0) {
//     echo "<script>alert(\"此項目僅可在星期日登錄。\");</script>";
//     echo "<script>document.location.href = \"receipt_reroute.php\";</script>";
//     exit;
// }

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

// 儲存找零金的資訊於SESSION
if (isset($_POST["ones"]) && $_POST["ones"] != NULL) {
    $_SESSION["cash_remain_ones"] = $_POST["ones"];
}
if (isset($_POST["five"]) && $_POST["five"] != NULL) {
    $_SESSION["cash_remain_five"] = $_POST["five"];
}
if (isset($_POST["ten"]) && $_POST["ten"] != NULL) {
    $_SESSION["cash_remain_ten"] = $_POST["ten"];
}
if (isset($_POST["fifty"]) && $_POST["fifty"] != NULL) {
    $_SESSION["cash_remain_fifty"] = $_POST["fifty"];
}
if (isset($_POST["hundred"]) && $_POST["hundred"] != NULL) {
    $_SESSION["cash_remain_hundred"] = $_POST["hundred"];
}
if (isset($_POST["five_hundred"]) && $_POST["five_hundred"] != NULL) {
    $_SESSION["cash_remain_five_hundred"] = $_POST["five_hundred"];
}
if (isset($_POST["thousand"]) && $_POST["thousand"] != NULL) {
    $_SESSION["cash_remain_thousand"] = $_POST["thousand"];
}
if (isset($_FILES["pic"]["error"]) && $_FILES["pic"]["error"] == 0) {
    $tmpname = $_FILES["pic"]["tmp_name"];
    $file = NULL;
    $instr = fopen($tmpname, "rb");
    $file = fread($instr, filesize($tmpname));
    $_SESSION["cash_remain_pic"] = base64_encode($file);
}
$_SESSION["cash_remain_comments"] = $_POST["comments"];

// 確認所有填入的數字皆為非負整數
if ($_SESSION["cash_remain_ones"] < 0 || $_SESSION["cash_remain_ones"] == NULL) {
    echo "<script>alert(\"壹圓數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}
if ($_SESSION["cash_remain_five"] < 0 || $_SESSION["cash_remain_five"] == NULL) {
    echo "<script>alert(\"伍圓數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}
if ($_SESSION["cash_remain_ten"] < 0 || $_SESSION["cash_remain_ten"] == NULL) {
    echo "<script>alert(\"拾圓數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}
if ($_SESSION["cash_remain_fifty"] < 0 || $_SESSION["cash_remain_fifty"] == NULL) {
    echo "<script>alert(\"伍拾圓數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}
if ($_SESSION["cash_remain_hundred"] < 0 || $_SESSION["cash_remain_hundred"] == NULL) {
    echo "<script>alert(\"壹佰圓數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}
if ($_SESSION["cash_remain_five_hundred"] < 0 || $_SESSION["cash_remain_five_hundred"] == NULL) {
    echo "<script>alert(\"伍佰圓數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}
if ($_SESSION["cash_remain_thousand"] < 0 || $_SESSION["cash_remain_thousand"] == NULL) {
    echo "<script>alert(\"壹仟圓數量必須填入非負整數。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}

// 確認照片已經上傳
if ($_SESSION["cash_remain_pic"] == NULL) {
    echo "<script>alert(\"檔案未上傳或是檔案格式錯誤。\\n如果確認上傳的過程正確，請聯繫系統管理人。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}

// 確認備註內的字數限制在100字以內
if (strlen($_SESSION["cash_remain_comments"]) > 200) {
    $_SESSION["go_to_comment_cash_remain"] = true;
    echo "<script>alert(\"字數過多，請減少備註的文字數量\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}

// 連線MySQL
include "connect_mysql.php";

// 從MySQL中獲取上次登入剩餘廠商金的時間，並檢查當日是否已經上傳過。
$dataGetLastTime = [$_SESSION["restaurant_id"]];
$sqlGetLastTime = "SELECT * FROM cash_remain JOIN employee ON cash_remain.emp_id=employee.emp_id WHERE restaurant_id=? LIMIT 2";
$sthGetLastTime = $link->prepare($sqlGetLastTime);
try {
    $sthGetLastTime->execute($dataGetLastTime);
    $_SESSION["lastTime"] = "0001-01-01 00:00:00";
    while ($resultGetLastTime = $sthGetLastTime->fetch(PDO::FETCH_ASSOC)) {
        if ($resultGetLastTime["input_date"] != NULL && strtotime($resultGetLastTime["input_date"]) < strtotime(date("Y-m-d"))) {
            $_SESSION["lastTime"] = $resultGetLastTime["input_date"];
            break;
        } else if ($resultGetLastTime["emp_name"] != NULL && $resultGetLastTime["emp_name"] != $_SESSION["emp_name"]) { // 如果當日的上傳者是目前的使用者，則可以繼續
            echo "<script>alert(\"本期的剩餘廠商金已經由{$resultGetLastTime["emp_name"]}登錄完成，無須重複登錄。\\n即將返回首頁。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"../login_to_index.php\";</script>";
            exit;
            break;
        }
    }
} catch (PDOException $e) {
    echo "<script>alert(\"無法獲取上次剩餘廠商金的資料：{$e->getMessage()}\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}

// 從MySQL下載憑證的總支出
$receiptTotalPrice = 0;
$data = ["廠商金支付", $lastTime, "correct"];
$sql = "SELECT total_price FROM receipt WHERE payment_method=? AND input_date<=(NOW()) AND input_date>=? AND process_status=?";
$sth = $link->prepare($sql);
$sth->execute($data);
try {
    while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
        $receiptTotalPrice += $result["total_price"];
    }
} catch (PDOException $e) {
    echo "<script>alert(\"無法獲取本期的憑證紀錄：{$e->getMessage()}\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}

// 確認剩餘廠商金和憑證總支出的總額為預設的廠商金總額
$one = $_SESSION["cash_remain_ones"];
$five = $_SESSION["cash_remain_five"];
$ten = $_SESSION["cash_remain_ten"];
$fifty = $_SESSION["cash_remain_fifty"];
$hundred = $_SESSION["cash_remain_hundred"];
$fiveHundred = $_SESSION["cash_remain_five_hundred"];
$thousand = $_SESSION["cash_remain_thousand"];
$_SESSION["cash_remain_total"] = $one + 5 * $five + 10 * $ten + 50 * $fifty + 100 * $hundred + 500 * $fiveHundred + 1000 * $thousand;
$total = $_SESSION["cash_remain_total"] + $receiptTotalPrice;
$_SESSION["receipts_total"] = $total;
if ($total != $_SESSION["dCCM_total"] && (!isset($_SESSION["cash_remain_comments"]) || $_SESSION["cash_remain_comments"] == NULL)) { // 確認在總和不相等的環境下，備註已填
    echo "<script>alert(\"總額錯誤：\\n本期的廠商金和憑證支出總和為{$_SESSION["receipts_total"]}元，和應有的廠商金額度{$_SESSION["dCCM_total"]}不符，請再算一次。\\n\\n若仍然有誤，則請思考原因並填寫備註。\");</script>";
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}

// 將資料上傳至MySQL
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM cash_remain WHERE restaurant_id=? AND input_date>=(CURDATE())";
$stmt = $link->prepare($sql);
$stmt->execute($data);
if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dataDelete = [$_SESSION["restaurant_id"]];
    $sqlDelete = "DELETE FROM cash_remain WHERE restaurant_id=? AND input_date>=(CURDATE())";
    $stmtDelete = $link->prepare($sqlDelete);
    $stmtDelete->execute($dataDelete);
}
$pic = base64_decode($_SESSION["cash_remain_pic"]);
$dataUpload = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["cash_remain_ones"], $_SESSION["cash_remain_five"], $_SESSION["cash_remain_ten"], $_SESSION["cash_remain_fifty"], $_SESSION["cash_remain_hundred"], $_SESSION["cash_remain_five_hundred"], $_SESSION["cash_remain_thousand"], $pic, $_SESSION["cash_remain_total"], $_SESSION["dCRM_id"], $_SESSION["cash_remain_comments"], 0];
$sqlUpload = "INSERT INTO cash_remain VALUES ((NOW()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $link->prepare($sqlUpload);
try {
    if ($stmt->execute($dataUpload)) {
        // echo "<script>alert(\"項目1-登錄剩餘廠商金的資訊提交成功。\");</script>";
    } else {
        echo "<script>alert(\"項目1-登錄剩餘廠商金上傳失敗，請聯繫系統管理人。\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"cash_remain.php\";</script>";
        exit;
    }
} catch (PDOException $e) {
    echo "<script>alert(\"無法上傳項目1-登錄剩餘廠商金：{$e->getMessage()}\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"cash_remain.php\";</script>";
    exit;
}

// 所有確認都通過後，頒發Check Point
$_SESSION["cremain_to_rs_check_point"] = true;
header("location: receipts_submit.php");
exit;
