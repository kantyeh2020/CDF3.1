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

// 儲存發票的資訊於SESSION
if (isset($_POST["main_project"]) && $_POST["main_project"] != NULL) {
    $_SESSION["main_project"] = $_POST["main_project"];
}
if (isset($_POST["sub_project"]) && $_POST["sub_project"] != NULL) {
    $_SESSION["sub_project"] = $_POST["sub_project"];
}
if (isset($_POST["subject_id"]) && $_POST["subject_id"] != NULL) {
    $_SESSION["subject_id"] = $_POST["subject_id"];
}
if (isset($_POST["receipt_type"]) && $_POST["receipt_type"] != NULL) {
    $_SESSION["receipt_type"] = $_POST["receipt_type"];
}
if (isset($_POST["receipt_id_number"]) && $_POST["receipt_id_number"] != NULL) {
    $_SESSION["receipt_id_number"] = strtoupper($_POST["receipt_id_number"]);
}
if (isset($_POST["buying_date"]) && $_POST["buying_date"] != NULL) {
    $_SESSION["buying_date"] = $_POST["buying_date"];
}
if (isset($_POST["selling_company"]) && $_POST["selling_company"] != NULL) {
    $_SESSION["selling_company"] = $_POST["selling_company"];
}
if (isset($_POST["total_price"]) && $_POST["total_price"] != NULL) {
    $_SESSION["total_price"] = $_POST["total_price"];
}
if (isset($_POST["payment_method"]) && $_POST["payment_method"] != NULL) {
    $_SESSION["payment_method"] = $_POST["payment_method"];
}
if (isset($_POST["detail"]) && $_POST["detail"] != NULL) {
    $_SESSION["detail"] = $_POST["detail"];
}
if (isset($_FILES["receipt_pic"]["error"]) && $_FILES["receipt_pic"]["error"] == 0) {
    $tmpname = $_FILES["receipt_pic"]["tmp_name"];
    $file = NULL;
    $instr = fopen($tmpname, "rb");
    $file = fread($instr, filesize($tmpname));
    $_SESSION["receipt_pic"] = base64_encode($file);
}
$_SESSION["comments"] = $_POST["comments"];

// 確認資訊有正確填寫(備註為非必填項目)
if ($_SESSION["main_project"] == 0 || $_SESSION["main_project"] == NULL) {
    echo "<script>alert(\"請選擇憑證母科目。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
if ($_SESSION["sub_project"] == 0 || $_SESSION["sub_project"] == NULL) {
    echo "<script>alert(\"請選擇憑證子科目。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
if ($_SESSION["subject_id"] == 0 || $_SESSION["subject_id"] == NULL) {
    echo "<script>alert(\"請選擇憑證費用別。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
$receiptTypeExist = false;
for ($i = 1; $i <= $_SESSION["receipt_type_number"]; $i++) {
    if ($_SESSION["receipt_type"] == $_SESSION["receipt_type_{$i}"]) {
        $receiptTypeExist = true;
    }
}
if ($receiptTypeExist == false) {
    echo "<script>alert(\"請選擇憑證類型。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
if ($_SESSION["receipt_type"] == "發票" && !(isset($_SESSION["receipt_id_number"]) && $_SESSION["receipt_id_number"] != NULL)) {
    echo "<script>alert(\"憑證類型為發票時，發票編號為必填項目。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
if (strtotime($_SESSION["buying_date"]) > strtotime($_SESSION["login_date"]) || $_SESSION["buying_date"] == NULL) {
    echo "<script>alert(\"請正確地填入支付日期。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
if ($_SESSION["selling_company"] == NULL) {
    echo "<script>alert(\"請填入廠商名稱。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
if ($_SESSION["total_price"] <= 0 || $_SESSION["total_price"] == NULL) {
    echo "<script>alert(\"支付金額必須為非負整數。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
if ($_SESSION["payment_method"] != "廠商金支付" && $_SESSION["payment_method"] != "換零金支付") {
    echo "<script>alert(\"支付方式必須要二選一。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
if ($_SESSION["detail"] == NULL) {
    echo "<script>alert(\"費用明細為必填項目。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}

// 確認照片已經上傳
if ($_SESSION["receipt_pic"] == NULL) {
    echo "<script>alert(\"檔案未上傳或是檔案格式錯誤。\\n如果確認上傳的過程正確，請聯繫系統管理人。\");</script>";
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}

// 連線MySQL
include "connect_mysql.php";

// 上傳資料
$receiptPic = base64_decode($_SESSION["receipt_pic"]);
if ($_SESSION["receipt_type"] == "發票") { // 僅發票要上傳憑證編號
    $data = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["main_project"], $_SESSION["sub_project"], $_SESSION["subject_id"], $_SESSION["selling_company"], $_SESSION["receipt_type"], $_SESSION["receipt_id_number"], $_SESSION["buying_date"], $_SESSION["total_price"], $_SESSION["payment_method"], $_SESSION["detail"], $receiptPic, $_SESSION["comments"]];
    $sql = "INSERT INTO receipt(restaurant_id, emp_id, input_date, main_project, sub_project, subject_id, selling_company, receipt_type, receipt_id_number, buying_date, total_price, payment_method, detail, receipt_pic, comments, process_status) VALUE (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \"correct\")";
} else { // 收據、合約、請款單不用上傳憑證編號
    $data = [$_SESSION["restaurant_id"], $_SESSION["emp_id"], $_SESSION["main_project"], $_SESSION["sub_project"], $_SESSION["subject_id"], $_SESSION["selling_company"], $_SESSION["receipt_type"], $_SESSION["buying_date"], $_SESSION["total_price"], $_SESSION["payment_method"], $_SESSION["detail"], $receiptPic, $_SESSION["comments"]];
    $sql = "INSERT INTO receipt(restaurant_id, emp_id, input_date, main_project, sub_project, subject_id, selling_company, receipt_type, buying_date, total_price, payment_method, detail, receipt_pic, comments, process_status) VALUE (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \"correct\")";
}

$sth = $link->prepare($sql);
try {
    if ($sth->execute($data)) {
        if ($_SESSION["receipt_type"] == "發票") { // 發票時
            echo "<script>alert(\"憑證的資訊提交成功：\\n\\n提交者：{$_SESSION["emp_name"]}\\n餐廳：{$_SESSION["restaurant_name"]}\\n提交日期：{$_SESSION["login_date"]}\\n憑證母科目：{$_SESSION["main_project"]}\\n憑證子科目：{$_SESSION["sub_project"]}\\n憑證費用別：{$_SESSION["subject_id"]}\\n憑證類型：{$_SESSION["receipt_type"]}\\n發票編號：{$_SESSION["receipt_id_number"]}\\n付款日期：{$_SESSION["buying_date"]}\\n廠商名稱：{$_SESSION["selling_company"]}\\n支付金額：{$_SESSION["total_price"]}元\\n支付方式：{$_SESSION["payment_method"]}\\n費用明細：{$_SESSION["detail"]}\\n備註：{$_SESSION["comments"]}\\n\\n即將跳回憑證提交頁面。\");</script>";
        } else { // 收據、合約、請款單時
            echo "<script>alert(\"憑證的資訊提交成功：\\n\\n提交者：{$_SESSION["emp_name"]}\\n餐廳：{$_SESSION["restaurant_name"]}\\n提交日期：{$_SESSION["login_date"]}\\n憑證母科目：{$_SESSION["main_project"]}\\n憑證子科目：{$_SESSION["sub_project"]}\\n憑證費用別：{$_SESSION["subject_id"]}\\n付款日期：{$_SESSION["buying_date"]}\\n廠商名稱：{$_SESSION["selling_company"]}\\n支付金額：{$_SESSION["total_price"]}元\\n支付方式：{$_SESSION["payment_method"]}\\n費用明細：{$_SESSION["detail"]}\\n備註：{$_SESSION["comments"]}\\n\\n即將跳回憑證提交頁面。\");</script>";
        }
        $link = NULL;
        // 清空憑證資訊
        unset($_SESSION["main_project"], $_SESSION["sub_project"], $_SESSION["subject_id"], $_SESSION["selling_company"], $_SESSION["receipt_type"], $_SESSION["receipt_id_number"], $_SESSION["buying_date"], $_SESSION["total_price"], $_SESSION["payment_method"], $_SESSION["detail"], $_SESSION["receipt_pic"], $_SESSION["comments"]);
        echo "<script>document.location.href = \"receipt.php\";</script>";
        exit;
    } else {
        echo "<script>alert(\"憑證上傳失敗，請聯繫系統管理人。\");</script>";
        $link = NULL;
        echo "<script>document.location.href = \"receipt.php\";</script>";
        exit;
    }
} catch (PDOException $e) {
    echo "<script>alert(\"憑證無法上傳：{$e->getMessage()}\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"receipt.php\";</script>";
    exit;
}
