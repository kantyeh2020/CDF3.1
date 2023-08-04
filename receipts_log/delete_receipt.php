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

// 連線MySQL
$dsn = "mysql:dbname=check_flow_database_v3;host=localhost;port=3306";
$username = "root";
$password = "password";
try {
    $link = new PDO($dsn, $username, $password);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $link->query('SET NAMES utf8');
    //echo "成功建立MySQL伺服器連接和開啟check_flow_database資料庫</br>";
} catch (PDOException $e) {
    $link = NULL;
    echo json_encode('連接伺服器失敗：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
}

// 刪除憑證
$data = [$_SESSION['emp_id'], $_POST['receipt_id']];
$sql = 'UPDATE receipt SET process_status="deleted", edit_emp_id=?, edit_date=(NOW()) WHERE receipt_id=? AND process_status="correct"';
$sth = $link->prepare($sql);
try {
    if ($sth->execute($data)) {
        echo json_encode('刪除完成', JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode('刪除失敗', JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    echo json_encode('無法刪除：' . $e->getMessage(), JSON_UNESCAPED_UNICODE);
}
$link = NULL;
