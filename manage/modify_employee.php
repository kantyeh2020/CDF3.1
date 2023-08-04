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

// 確認權限為manager
if (!(isset($_SESSION["authority"]) && $_SESSION["authority"] == "manager")) {
    header("location: login_to_index.php");
    exit;
}

// 確認資訊都正確填入
if (!(isset($_POST["emp_name"])) || $_POST["emp_name"] == NULL) {
    echo json_encode('請填入員工的姓名。', JSON_UNESCAPED_UNICODE);
    exit;
}
if ($_POST["emp_authority"] == "recorder") {
    $_POST["emp_password"] = NULL;
}
if ($_POST["emp_authority"] == "manager" && (!(isset($_POST["emp_password"])) || $_POST["emp_password"] == NULL)) {
    echo json_encode('管理者身份必須要填入密碼。', JSON_UNESCAPED_UNICODE);
    exit;
}
if (mb_strlen($_POST["emp_password"], "UTF-8") != strlen($_POST["emp_password"])) {
    echo json_encode('管理者密碼只能使用英文或數字。', JSON_UNESCAPED_UNICODE);
    exit;
}

// 連線MySQL
include "jquery_connect_mysql.php";

// 更新員工狀態。
$newEmpId = strtoupper($_POST["emp_id"]);
$data = [$newEmpId];
$sql = "SELECT * FROM employee WHERE emp_id=?";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
    $result = $sth->fetch(PDO::FETCH_ASSOC);
    if ($result["authority"] == "recorder" || $_POST["emp_authority"] != "recorder") {
        $dataEmp = [$_POST["emp_name"], $_POST["emp_authority"], $_POST["emp_password"], $newEmpId];
        $sqlEmp = "UPDATE employee SET emp_name=?, authority=?, emp_password=? WHERE emp_id=?";
        $sthEmp = $link->prepare($sqlEmp);
        $sthEmp->execute($dataEmp);

        echo json_encode('員工資訊修改成功', JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode('權限無法由非普通員工轉至普通員工', JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    echo json_encode('無法修改員工資訊：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
}

// 若是自己，則更新SESSION狀態
if ($newEmpId == $_SESSION["emp_id"]) {
    $_SESSION["emp_name"] = $_POST["emp_name"];
    $_SESSION["authority"] = $_POST["emp_authority"];
    $_SESSION["emp_password"] = $_POST["emp_password"];
}

// 返回員工與餐廳管理頁
$link = NULL;
exit;
