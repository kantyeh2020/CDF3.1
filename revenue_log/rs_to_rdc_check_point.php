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
if ($_SESSION["crevenue_to_rs_check_point"] == false) {
    header("location: cash_revenue.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["rs_to_rdc_check_point"] == true) {
    header("location: revenue_double_check.php");
    exit;
}

// 儲存填入的資料
if (isset($_POST["total"]) && $_POST["total"] != NULL) {
    $_SESSION["crevenue_total"] = $_POST["total"];
}
if (isset($_FILES["pic"]["error"]) && $_FILES["pic"]["error"] == 0) {
    $tmpname = $_FILES["pic"]["tmp_name"];
    $file = NULL;
    $instr = fopen($tmpname, "rb");
    $file = fread($instr, filesize($tmpname));
    $_SESSION["crevenue_pic"] = base64_encode($file);
}
if (isset($_POST["comments"]) && $_POST["comments"] != NULL) {
    $_SESSION["crevenue_comments"] = $_POST["comments"];
}

// 檢查是否確認總和
$total = $_SESSION["cash_revenue"] + $_SESSION["crevenue_change_filled"] + $_SESSION["crevenue_fee"];
if ($_SESSION["crevenue_total"] != $total) {
    echo "<script>alert(\"請確認手上現金總和是否為{$total}，並勾選選項。\");</script>";
    echo "<script>document.location.href = \"revenue_submit.php\";</script>";
    exit;
}

// 檢查是否完成裝袋動作
$_SESSION["check"] = $_POST["check"];
if (!isset($_SESSION["check"]) || $_SESSION["check"] != "on") {
    echo "<script>alert(\"請完成裝袋動作後勾選選項。\");</script>";
    echo "<script>document.location.href = \"revenue_submit.php\";</script>";
    exit;
}

// 檢查是否已經確認過密封袋上的標註
$_SESSION["check_inform"] = $_POST["check_inform"];
if (!isset($_SESSION["check_inform"]) || $_SESSION["check_inform"] != "on") {
    echo "<script>alert(\"請確認標註是否正確後，勾選選項。\");</script>";
    echo "<script>document.location.href = \"revenue_submit.php\";</script>";
    exit;
}

// 確認照片已經上傳
if ($_SESSION["crevenue_pic"] == NULL) {
    echo "<script>alert(\"檔案未上傳或是檔案格式錯誤。\\n如果確認上傳的過程正確，請聯繫系統管理人。\");</script>";
    echo "<script>document.location.href = \"revenue_submit.php\";</script>";
    exit;
}

// 確認備註內的字數限制在100字以內
if (strlen($_SESSION["cashier_comments"]) > 200) {
    echo "<script>alert(\"字數過多，請減少文字數量\");</script>";
    echo "<script>document.location.href = \"cashier.php\";</script>";
    exit;
}

// 連線MySQL
include "connect_mysql.php";

// 檢查當日是否已經被別人上傳，若無則上傳資料
// 1. 上傳當日總營收
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM total_revenue JOIN employee ON total_revenue.emp_id=employee.emp_id WHERE input_date=(CURDATE()) AND restaurant_id=?";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result["emp_name"] == NULL) {
    $dataInput = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["total_revenue"], $_SESSION["taiwan_pay"], $_SESSION["uber_eat"], $_SESSION["credit_card"], $_SESSION["cash_revenue"]];
    $sqlInput = "INSERT INTO total_revenue VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?)";
    $stmtInput = $link->prepare($sqlInput);
    try {
        if ($stmtInput->execute($dataInput)) {
            // echo "<script>alert(\"項目1-輸入當日營收的資訊提交成功。\");</script>";
        } else {
            echo "<script>alert(\"項目1-輸入當日營收上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_submit.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳項目1-輸入當日營收：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_submit.php\";</script>";
        exit;
    }
} else if ($result["emp_name"] == $_SESSION["emp_name"]) {
    $dataDelete = [$_SESSION["restaurant_id"]];
    $sqlDelete = "DELETE FROM total_revenue WHERE input_date=(CURDATE()) AND restaurant_id=?";
    $stmtDelete = $link->prepare($sqlDelete);
    $stmtDelete->execute($dataDelete);

    $dataInput = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["total_revenue"], $_SESSION["taiwan_pay"], $_SESSION["uber_eat"], $_SESSION["credit_card"], $_SESSION["cash_revenue"]];
    $sqlInput = "INSERT INTO total_revenue VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?)";
    $stmtInput = $link->prepare($sqlInput);
    try {
        if ($stmtInput->execute($dataInput)) {
            // echo "<script>alert(\"項目1-輸入當日營收的資訊提交成功。\");</script>";
        } else {
            echo "<script>alert(\"項目1-輸入當日營收上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_submit.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳項目1-輸入當日營收：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_submit.php\";</script>";
        exit;
    }
} else {
    echo "<script>alert(\"本日結帳已經被<b>{$result["emp_name"]}</b>登錄完成，無須再次登錄。\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
}

// 2. 上傳錢櫃金資訊
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM cashier_change JOIN employee ON cashier_change.emp_id=employee.emp_id WHERE input_date=(CURDATE()) AND restaurant_id=?";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result["emp_name"] == NULL) {
    $pic = base64_decode($_SESSION["cashier_pic"]);
    $dataInput = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["cashier_ones"], $_SESSION["cashier_five"], $_SESSION["cashier_ten"], $_SESSION["cashier_fifty"], $_SESSION["cashier_hundred"], $_SESSION["cashier_five_hundred"], $_SESSION["cashier_thousand"], $pic, $_SESSION["cashier_total"], $_SESSION["dCRM_id"], $_SESSION["cashier_comments"]];
    $sqlInput = "INSERT INTO cashier_change VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInput = $link->prepare($sqlInput);
    try {
        if ($stmtInput->execute($dataInput)) {
            // echo "<script>alert(\"項目2-登錄錢櫃金的資訊提交成功。\");</script>";
        } else {
            echo "<script>alert(\"項目2-登錄錢櫃金上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_submit.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳項目2-登錄錢櫃金：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_submit.php\";</script>";
        exit;
    }
} else if ($result["emp_name"] == $_SESSION["emp_name"]) {
    $dataDelete = [$_SESSION["restaurant_id"]];
    $sqlDelete = "DELETE FROM cashier_change WHERE input_date=(CURDATE()) AND restaurant_id=?";
    $stmtDelete = $link->prepare($sqlDelete);
    $stmtDelete->execute($dataDelete);

    $pic = base64_decode($_SESSION["cashier_pic"]);
    $dataInput = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["cashier_ones"], $_SESSION["cashier_five"], $_SESSION["cashier_ten"], $_SESSION["cashier_fifty"], $_SESSION["cashier_hundred"], $_SESSION["cashier_five_hundred"], $_SESSION["cashier_thousand"], $pic, $_SESSION["cashier_total"], $_SESSION["dCRM_id"], $_SESSION["cashier_comments"]];
    $sqlInput = "INSERT INTO cashier_change VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInput = $link->prepare($sqlInput);
    try {
        if ($stmtInput->execute($dataInput)) {
            // echo "<script>alert(\"項目2-登錄錢櫃金的資訊提交成功。\");</script>";
        } else {
            echo "<script>alert(\"項目2-登錄錢櫃金上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_submit.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳項目2-登錄錢櫃金：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_submit.php\";</script>";
        exit;
    }
} else {
    echo "<script>alert(\"本日結帳已經被<b>{$result["emp_name"]}</b>登錄完成，無須再次登錄。\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
}

// 3. 上傳換錢金資訊
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM exchange_change JOIN employee ON exchange_change.emp_id=employee.emp_id WHERE input_date=(CURDATE()) AND restaurant_id=?";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result["emp_name"] == NULL) {
    $pic = base64_decode($_SESSION["exchange_pic"]);
    $dataInput = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["exchange_ones"], $_SESSION["exchange_five"], $_SESSION["exchange_ten"], $_SESSION["exchange_fifty"], $_SESSION["exchange_hundred"], $_SESSION["exchange_five_hundred"], $_SESSION["exchange_thousand"], $_SESSION["exchange_hundred_bag"], $_SESSION["exchange_five_hundred_bag"], $_SESSION["exchange_thousand_bag"], $pic, $_SESSION["exchange_total"], $_SESSION["dECM_id"], $_SESSION["exchange_comments"]];
    $sqlInput = "INSERT INTO exchange_change VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInput = $link->prepare($sqlInput);
    try {
        if ($stmtInput->execute($dataInput)) {
            // echo "<script>alert(\"項目5-計算換錢金總額的資訊提交成功。\");</script>";
        } else {
            echo "<script>alert(\"項目5-計算換錢金總額上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_submit.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳項目5-計算換錢金總額：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_submit.php\";</script>";
        exit;
    }
} else if ($result["emp_name"] == $_SESSION["emp_name"]) {
    $dataDelete = [$_SESSION["restaurant_id"]];
    $sqlDelete = "DELETE FROM exchange_change WHERE input_date=(CURDATE()) AND restaurant_id=?";
    $stmtDelete = $link->prepare($sqlDelete);
    $stmtDelete->execute($dataDelete);

    $pic = base64_decode($_SESSION["exchange_pic"]);
    $dataInput = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["exchange_ones"], $_SESSION["exchange_five"], $_SESSION["exchange_ten"], $_SESSION["exchange_fifty"], $_SESSION["exchange_hundred"], $_SESSION["exchange_five_hundred"], $_SESSION["exchange_thousand"], $_SESSION["exchange_hundred_bag"], $_SESSION["exchange_five_hundred_bag"], $_SESSION["exchange_thousand_bag"], $pic, $_SESSION["exchange_total"], $_SESSION["dECM_id"], $_SESSION["exchange_comments"]];
    $sqlInput = "INSERT INTO exchange_change VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInput = $link->prepare($sqlInput);
    try {
        if ($stmtInput->execute($dataInput)) {
            // echo "<script>alert(\"項目5-計算換錢金總額的資訊提交成功。\");</script>";
        } else {
            echo "<script>alert(\"項目5-計算換錢金總額上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_submit.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳項目5-計算換錢金總額：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_submit.php\";</script>";
        exit;
    }
} else {
    echo "<script>alert(\"本日結帳已經被<b>{$result["emp_name"]}</b>登錄完成，無須再次登錄。\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
}

// 4. 上傳當日現金營收與小白單資訊
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM cash_revenue JOIN employee ON cash_revenue.emp_id=employee.emp_id WHERE input_date=(CURDATE()) AND restaurant_id=?";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result["emp_name"] == NULL) {
    $pic = base64_decode($_SESSION["crevenue_pic"]);
    $dataInput = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["cash_revenue"], $_SESSION["crevenue_change_filled"], $_SESSION["crevenue_fee"], $_SESSION["crevenue_total"], $_SESSION["crevenue_white_list"], $pic, $_SESSION["crevenue_comments"], 0];
    $sqlInput = "INSERT INTO cash_revenue VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInput = $link->prepare($sqlInput);
    try {
        if ($stmtInput->execute($dataInput)) {
            // echo "<script>alert(\"項目6-換錢金總額確認與小費、小白單登錄的資訊提交成功。\");</script>";
        } else {
            echo "<script>alert(\"項目6-換錢金總額確認與小費、小白單登錄上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_submit.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳項目6-換錢金總額確認與小費、小白單登錄：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_submit.php\";</script>";
        exit;
    }
} else if ($result["emp_name"] == $_SESSION["emp_name"]) {
    $dataDelete = [$_SESSION["restaurant_id"]];
    $sqlDelete = "DELETE FROM cash_revenue WHERE input_date=(CURDATE()) AND restaurant_id=?";
    $stmtDelete = $link->prepare($sqlDelete);
    $stmtDelete->execute($dataDelete);

    $pic = base64_decode($_SESSION["crevenue_pic"]);
    $dataInput = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["cash_revenue"], $_SESSION["crevenue_change_filled"], $_SESSION["crevenue_fee"], $_SESSION["crevenue_total"], $_SESSION["crevenue_white_list"], $pic, $_SESSION["crevenue_comments"], 0];
    $sqlInput = "INSERT INTO cash_revenue VALUES ((CURDATE()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInput = $link->prepare($sqlInput);
    try {
        if ($stmtInput->execute($dataInput)) {
            // echo "<script>alert(\"項目6-換錢金總額確認與小費、小白單登錄的資訊提交成功。\");</script>";
        } else {
            echo "<script>alert(\"項目6-換錢金總額確認與小費、小白單登錄上傳失敗，請聯繫系統管理人。\");</script>";
            $link = NULL;
            echo "<script>document.location.href = \"revenue_submit.php\";</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert(\"無法上傳項目6-換錢金總額確認與小費、小白單登錄：{$e->getMessage()}\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"revenue_submit.php\";</script>";
        exit;
    }
} else {
    echo "<script>alert(\"本日結帳已經被<b>{$result["emp_name"]}</b>登錄完成，無須再次登錄。\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
}

// 頒發Check Point
$_SESSION["rs_to_rdc_check_point"] = true;
header("location: revenue_double_check.php");
exit;
