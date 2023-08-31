<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script>

<?php
session_start();
// 確認是否殘留前日的資料，若有則消除
if ($_SESSION["login_date"] != date("Y/m/d")) {
    $_SESSION = array();
    $_SESSION["login_date"] = date("Y/m/d");
}

// 將登入的資訊儲存於SESSION
if (isset($_POST["restaurant_id"]) && $_POST["restaurant_id"] != NULL) {
    $_SESSION["restaurant_id"] = $_POST["restaurant_id"];
}
if (isset($_POST["emp_id"]) && $_POST["emp_id"] != NULL) {
    $_SESSION["emp_id"] = $_POST["emp_id"];
}
if (isset($_POST["emp_password"]) && $_POST["emp_password"] != NULL) {
    $_SESSION["emp_password"] = $_POST["emp_password"];
}

// 確認表格都有填入東西
if ($_SESSION["restaurant_id"] == "0") {
    echo "<script>alert(\"請選擇分店代號。\");</script>";
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
}
if ($_SESSION["emp_id"] == "0") {
    echo "<script>alert(\"請選擇員工名稱。\");</script>";;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
}

// 連線MySQL
include "connect_mysql.php";

// 確認MySQL內的員工及分店資訊
$data = [$_SESSION["emp_id"]];
$sql = 'SELECT * FROM employee WHERE emp_id=?';
$stmt = $link->prepare($sql);
$stmt->execute($data);
$empResult = $stmt->fetch(PDO::FETCH_ASSOC);

$data = [$_SESSION["restaurant_id"]];
$sql = 'SELECT * FROM restaurant WHERE restaurant_id=?';
$stmt = $link->prepare($sql);
$stmt->execute($data);
$restaurantResult = $stmt->fetch(PDO::FETCH_ASSOC);

if ($empResult && $restaurantResult && $empResult["emp_password"] == $_SESSION["emp_password"]) {
    // 登錄正在使用此帳號的裝置
    $ip = $_SERVER["REMOTE_ADDR"];
    $_SESSION["machine_name"] = gethostbyaddr($ip);
    try {
        $data = [$_SESSION["machine_name"], $_SESSION["emp_id"]];
        $sql = "UPDATE employee SET login_machine=? WHERE emp_id=?";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
    } catch (PDOException $e) {
        echo "<script>alert(\"無法登錄使用者的裝置資訊：{$e->getMessage()}\")</script>";
        $link = NULL;
        echo "<script>document.location.href = \"login.php\";</script>";
        exit;
    }

    // 資訊正確即可登入
    $_SESSION["loggedin"] = true;
    $_SESSION["restaurant_name"] = $restaurantResult["restaurant_name"];
    $_SESSION["emp_name"] = $empResult["emp_name"];
    $_SESSION["authority"] = $empResult["authority"];
} else if (!$empResult) {
    $_SESSION["loggedin"] = false;
    echo "<script>alert(\"查無此員工身份。\");</script>";;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
} else if (!$restaurantResult) {
    $_SESSION["loggedin"] = false;
    echo "<script>alert(\"查無此分店代號。\");</script>";;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
} else if ($empResult["emp_password"] != $_SESSION["emp_password"]) {
    $_SESSION["loggedin"] = false;
    unset($_SESSION["emp_password"]);
    echo "<script>alert(\"密碼錯誤，請重新嘗試。\");</script>";;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
} else {
    $_SESSION["loggedin"] = false;
    echo "<script>alert(\"發生意外狀況，請聯絡系統管理人。\");</script>";;
    echo "<script>document.location.href = \"login.php\";</script>";
    exit;
}

// 獲得預設的錢櫃儲備金資訊
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM cash_reserve_management WHERE restaurant_id=? ORDER BY id DESC LIMIT 1";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$dCRMResult = $stmt->fetch(PDO::FETCH_ASSOC);
$_SESSION["dCRM_id"] = $dCRMResult["id"];
$_SESSION["dCRM_ones"] = $dCRMResult["ones"];
$_SESSION["dCRM_five"] = $dCRMResult["five"];
$_SESSION["dCRM_ten"] = $dCRMResult["ten"];
$_SESSION["dCRM_fifty"] = $dCRMResult["fifty"];
$_SESSION["dCRM_hundred"] = $dCRMResult["hundred"];
$_SESSION["dCRM_five_hundred"] = $dCRMResult["five_hundred"];
$_SESSION["dCRM_thousand"] = $dCRMResult["thousand"];
$_SESSION["dCRM_total"] = $dCRMResult["total"];

// 獲得預設的換錢金資訊
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM exchange_cash_management WHERE restaurant_id=? ORDER BY id DESC LIMIT 1";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$dECMResult = $stmt->fetch(PDO::FETCH_ASSOC);
$_SESSION["dECM_id"] = $dECMResult["id"];
$_SESSION["dECM_total"] = $dECMResult["total"];

// 獲得預設的廠商金資訊
$data = [$_SESSION["restaurant_id"]];
$sql = "SELECT * FROM company_cash_management WHERE restaurant_id=? ORDER BY id DESC LIMIT 1";
$stmt = $link->prepare($sql);
$stmt->execute($data);
$dCCMResult = $stmt->fetch(PDO::FETCH_ASSOC);
$_SESSION["dCCM_id"] = $dCCMResult["id"];
$_SESSION["dCCM_total"] = $dCCMResult["total"];

// 獲得憑證類型資訊
$sql = "SELECT * FROM receipt_type_management";
$stmt = $link->prepare($sql);
$stmt->execute();
while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["receipt_type_{$result["id"]}"] = $result["class"];
    $_SESSION["receipt_type_number"] = $result["id"];
}

// 檢查當日結帳系統是否已經完成
$dataTR = [$_SESSION["restaurant_id"]]; // 項目1-輸入當日營收
$checkTR = 'SELECT * FROM total_revenue JOIN employee ON total_revenue.emp_id=employee.emp_id WHERE input_date=CURDATE() AND restaurant_id=?';
$prepareTR = $link->prepare($checkTR);
$prepareTR->execute($dataTR);
if ($resultTR = $prepareTR->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["trUploadDone"] = true;
    $_SESSION["trUploadDoneBy"] = $resultTR["emp_name"];
}

$dataXC = [$_SESSION["restaurant_id"]]; // 項目2-確認找零金(15000)
$checkXC = 'SELECT * FROM exchange_change JOIN employee ON exchange_change.emp_id=employee.emp_id WHERE input_date=CURDATE() AND restaurant_id=?';
$prepareXC = $link->prepare($checkXC);
$prepareXC->execute($dataXC);
if ($resultXC = $prepareXC->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["xcUploadDone"] = true;
}

$dataCC = [$_SESSION["restaurant_id"]]; // 項目3-確認錢櫃零用金(5000)
$checkCC = 'SELECT * FROM cashier_change JOIN employee ON cashier_change.emp_id=employee.emp_id WHERE input_date=CURDATE() AND restaurant_id=?';
$prepareCC = $link->prepare($checkCC);
$prepareCC->execute($dataCC);
if ($resultCC = $prepareCC->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["ccUploadDone"] = true;
}

$dataCR = [$_SESSION["restaurant_id"]]; // 項目4-登錄現金頁面
$checkCR = 'SELECT * FROM cash_revenue JOIN employee ON cash_revenue.emp_id=employee.emp_id WHERE input_date=CURDATE() AND restaurant_id=?';
$prepareCR = $link->prepare($checkCR);
$prepareCR->execute($dataCR);
if ($resultCR = $prepareCR->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["crUploadDone"] = true;
    $_SESSION["revenueAllDone"] = $resultCR["done"];
}

// 檢查當期廠商金登錄是否已經完成
$dataCRemain = [$_SESSION["restaurant_id"]];
$day = date("w", strtotime(date("Y/m/d")));
switch ($day) {
    case 6:
        $checkCRemain = 'SELECT * FROM cash_remain JOIN employee ON cash_remain.emp_id=employee.emp_id WHERE input_date>=(CURDATE()) AND restaurant_id=?';
        break;
    case 0:
        $checkCRemain = 'SELECT * FROM cash_remain JOIN employee ON cash_remain.emp_id=employee.emp_id WHERE input_date>=DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND restaurant_id=?';
        break;
    case 1:
        $checkCRemain = 'SELECT * FROM cash_remain JOIN employee ON cash_remain.emp_id=employee.emp_id WHERE input_date>=DATE_SUB(CURDATE(), INTERVAL 2 DAY) AND restaurant_id=?';
        break;
    default:
        $checkCRemain = 'SELECT * FROM cash_remain JOIN employee ON cash_remain.emp_id=employee.emp_id WHERE input_date>=(CURDATE()) AND restaurant_id=?';
        break;
}
$prepareCRemain = $link->prepare($checkCRemain);
$prepareCRemain->execute($dataCRemain);
if ($resultCRemain = $prepareCRemain->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["cremainUploadDone"] = true;
    $_SESSION["cremainUploadDoneBy"] = $resultCRemain["emp_name"];
    $_SESSION["cremainAllDone"] = $resultCRemain["done"];
}

// 根據權限分流到各個目錄頁
$link = NULL;
if ($_SESSION["authority"] == "reviewer") {
    header("location: index_reviewer.php");
    exit;
}
header("location: index.php");
exit;
?>