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

// 確認權限為manager
if (!(isset($_SESSION["authority"]) && $_SESSION["authority"] == "manager")) {
    header("location: login_to_index.php");
    exit;
}

// 確認資訊都正確填入
if (!(isset($_POST["new_emp_id"])) || $_POST["new_emp_id"] == NULL) {
    echo "<script>alert(\"請填入新員工的員工編號。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}
if (mb_strlen($_POST["new_emp_id"], "UTF-8") != strlen($_POST["new_emp_id"])) {
    echo "<script>alert(\"員工編號只能填入英文或數字。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}
if (!(isset($_POST["new_emp_name"])) || $_POST["new_emp_name"] == NULL) {
    echo "<script>alert(\"請填入新員工的姓名。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}
if ($_POST["new_emp_authority"] == "recorder") {
    $_POST["new_emp_password"] = NULL;
}
if ($_POST["new_emp_authority"] == "manager" && (!(isset($_POST["new_emp_password"])) || $_POST["new_emp_password"] == NULL)) {
    echo "<script>alert(\"管理者身份必須要填入密碼。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}
if (mb_strlen($_POST["new_emp_password"], "UTF-8") != strlen($_POST["new_emp_password"])) {
    echo "<script>alert(\"管理者密碼只能使用英文或數字。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}

// 連線MySQL
include "connect_mysql.php";

// 若員工編號沒有被使用，則上傳資料。若有同編號、同姓名但曾經被刪除的資料，則更新狀態。其餘狀況拒絕上傳。
$newEmpId = strtoupper($_POST["new_emp_id"]);
$data = [$newEmpId];
$sql = "SELECT * FROM employee WHERE emp_id=?";
$sth = $link->prepare($sql);
$sth->execute($data);
try {
    if (!($result = $sth->fetch(PDO::FETCH_ASSOC))) {
        $dataEmp = [$newEmpId, $_POST["new_emp_name"], $_POST["new_emp_authority"], $_POST["new_emp_password"]];
        $sqlEmp = "INSERT INTO employee(emp_id, emp_name, authority, emp_password) VALUES (?, ?, ?, ?)";
        $sthEmp = $link->prepare($sqlEmp);
        $sthEmp->execute($dataEmp);
    } else if ($result["emp_status"] == "deleted" && $result["emp_name"] == $_POST["new_emp_name"]) {
        $dataEmp = [$_POST["new_emp_authority"], $_POST["new_emp_password"], "employed", $newEmpId];
        $sqlEmp = "UPDATE employee SET authority=?, emp_password=?, emp_status=? WHERE emp_id=?";
        $sthEmp = $link->prepare($sqlEmp);
        $sthEmp->execute($dataEmp);
    } else {
        echo "<script>alert(\"此員工編號已被其他員工使用，請使用其他員工編號。\");</script>";
        echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
        exit;
        $link = NULL;
    }
} catch (PDOException $e) {
    echo "<script>alert(\"新員工資料無法上傳：{$e->getMessage()}\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}

// 告示成功，並返回員工與餐廳管理頁
$link = NULL;
echo "<script>alert(\"新增成功。\");</script>";
echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
exit;
