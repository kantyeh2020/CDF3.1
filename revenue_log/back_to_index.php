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
if ($_SESSION["rs_to_rdc_check_point"] == false) {
    header("location: revenue_submit.php");
    exit;
}

// 確認已經勾選選項
if (!(isset($_POST["double_check"]) && $_POST["double_check"] == "on")) {
    echo "<script>alert(\"請勾選\\\"我已經確認過內容無誤\\\"選項。\");</script>";
    echo "<script>document.location.href = \"revenue_double_check.php\";</script>";
    exit;
}

// 連線MySQL
include "connect_mysql.php";

// 更新當日結帳系統的登錄狀況為已經完成登錄(cash_revenue::done==true)
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM cash_revenue JOIN employee ON cash_revenue.emp_id=employee.emp_id WHERE input_date=(CURDATE()) AND restaurant_id=?";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result["emp_name"] == $_SESSION["emp_name"]) {
    $dataUpdate = [1, $_SESSION["restaurant_id"]];
    $sqlUpdate = "UPDATE cash_revenue SET done=? WHERE input_date=(CURDATE()) AND restaurant_id=?";
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
unset($_SESSION["total_revenue"], $_SESSION["taiwan_pay"], $_SESSION["uber_eat"], $_SESSION["credit_card"], $_SESSION["cash_revenue"]);
unset($_SESSION["cashier_ones"], $_SESSION["cashier_five"], $_SESSION["cashier_ten"], $_SESSION["cashier_fifty"], $_SESSION["cashier_hundred"], $_SESSION["cashier_five_hundred"], $_SESSION["cashier_thousand"], $pic, $_SESSION["cashier_total"], $_SESSION["dCRM_id"], $_SESSION["cashier_comments"]);
unset($_SESSION["exchange_ones"], $_SESSION["exchange_five"], $_SESSION["exchange_ten"], $_SESSION["exchange_fifty"], $_SESSION["exchange_hundred"], $_SESSION["exchange_five_hundred"], $_SESSION["exchange_thousand"], $_SESSION["exchange_hundred_bag"], $_SESSION["exchange_five_hundred_bag"], $_SESSION["exchange_thousand_bag"], $pic, $_SESSION["exchange_total"], $_SESSION["dECM_id"], $_SESSION["exchange_comments"]);
unset($_SESSION["cash_revenue"], $_SESSION["crevenue_change_filled"], $_SESSION["crevenue_fee"], $_SESSION["crevenue_total"], $_SESSION["crevenue_white_list"], $pic, $_SESSION["crevenue_comments"]);
unset($_SESSION["tr_to_cashier_check_point"], $_SESSION["cashier_to_cr_check_point"], $_SESSION["cr_to_mat_check_point"], $_SESSION["mat_to_exchange_check_point"], $_SESSION["exchange_to_crevenue_check_point"], $_SESSION["crevenue_to_rs_check_point"], $_SESSION["rs_to_rdc_check_point"], $_SESSION["_check_point"]);

// 頒發Check Point
echo "<script>alert(\"提交完成，即將返回首頁\");</script>";
echo "<script>document.location.href = \"../login_to_index.php\";</script>";
exit;
