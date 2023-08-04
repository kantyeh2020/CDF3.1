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

// 連線MySQL
include "jquery_connect_mysql.php";

// 若被刪除者是普通員工，則刪除員工身份
$data = [$_POST['emp_id']];
$sql = "SELECT * FROM employee WHERE emp_id=? AND emp_status='employed'";
$sth = $link->prepare($sql);
try {
    $sth->execute($data);
    if (!($result = $sth->fetch(PDO::FETCH_ASSOC))) {
        echo json_encode('無此員工或此員工已經被刪除', JSON_UNESCAPED_UNICODE);
    } else if ($result["authority"] != "recorder") {
        echo json_encode('權限不足，無法刪除此員工', JSON_UNESCAPED_UNICODE);
    } else {
        $dataDelete = [$_POST['emp_id']];
        $sqlDelete = "UPDATE employee SET emp_status='deleted' WHERE emp_id=?";
        $sthDelete = $link->prepare($sqlDelete);
        $sthDelete->execute($dataDelete);

        echo json_encode('刪除成功', JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    echo json_encode('無法刪除：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
}
$link = NULL;
