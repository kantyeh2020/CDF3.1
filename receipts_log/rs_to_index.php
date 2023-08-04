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

// 確認是否已經完成上個表單
if ($_SESSION["cremain_to_rs_check_point"] == false) {
    header("location: cash_remain.php");
    exit;
}

// 確認已經勾選選項
if (!(isset($_POST["double_check"]) && $_POST["double_check"] == "on")) {
    echo "<script>alert(\"請勾選\\\"我已經確認過內容無誤\\\"選項。\");</script>";
    echo "<script>document.location.href = \"receipts_submit.php\";</script>";
    exit;
}

// 連線MySQL
include "connect_mysql.php";

// 更新當日結帳系統的登錄狀況為已經完成登錄(cash_remain::done==true)
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM cash_remain JOIN employee ON cash_remain.emp_id=employee.emp_id WHERE input_date>=(CURDATE()) AND restaurant_id=?";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result["emp_name"] == $_SESSION["emp_name"]) {
    $dataUpdate = [1, $_SESSION["restaurant_id"]];
    $sqlUpdate = "UPDATE cash_remain SET done=? WHERE input_date>=(CURDATE()) AND restaurant_id=?";
    $stmtUpdate = $link->prepare($sqlUpdate);
    try {
        if ($stmtUpdate->execute($dataUpdate)) {
            //
        } else {
            echo "<script>alert(\"最終確認資料上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_double_check.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳最終確認資料：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_double_check.php\";</script>";
        exit;
    }
} else {
    echo "<script>alert(\"本日結帳系統已經由{$result["emp_name"]}上傳，無須重複填寫。\\n即將返回首頁。\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"../login_to_index.php\";</script>";
    exit;
}

// 清空cookie儲存的表格資料
unset($_SESSION["cash_remain_ones"], $_SESSION["cash_remain_five"], $_SESSION["cash_remain_ten"], $_SESSION["cash_remain_fifty"], $_SESSION["cash_remain_hundred"], $_SESSION["cash_remain_five_hundred"], $_SESSION["cash_remain_thousand"], $_SESSION["cash_remain_pic"], $_SESSION["cash_remain_total"], $_SESSION["cash_remain_comments"]);
unset($_SESSION["cremain_to_rs_check_point"]);

// 返回首頁
echo "<script>alert(\"本期的廠商金登錄已經完成，即將返回首頁。\");</script>";
echo "<script>document.location.href = \"../login_to_index.php\";</script>";
exit;
