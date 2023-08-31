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
if (!(isset($_POST["new_restaurant_brand_name"])) || $_POST["new_restaurant_brand_name"] == NULL) {
    echo "<script>alert(\"請填入新餐廳的餐廳代號。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}
if (mb_strlen($_POST["new_restaurant_brand_name"], "UTF-8") != strlen($_POST["new_restaurant_brand_name"])) {
    echo "<script>alert(\"餐廳代號只能填入英文或數字。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}
if (!(isset($_POST["new_restaurant_name"])) || $_POST["new_restaurant_name"] == NULL) {
    echo "<script>alert(\"請填入新餐廳的名稱。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}

// 確認字符不會太長
if (strlen($_POST["new_restaurant_brand_name"]) + strlen($_POST["new_restaurant_name"]) > 28) {
    echo "<script>alert(\"餐廳代號和餐廳名稱的總長度太長，請縮短餐廳代號或餐廳名稱的長度。\");</script>";
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}

// 連線MySQL
include "connect_mysql.php";

// 若餐廳代號未被使用，則新增餐廳。若已被使用，且名稱相同，但餐廳資料已經被刪除，則更新資料。其餘狀況拒絕上傳。
$newRestaurantBrandName = strtoupper($_POST["new_restaurant_brand_name"]);
$data = ["^" . $newRestaurantBrandName];
$sql = "SELECT * FROM restaurant WHERE restaurant_name REGEXP ?";
$sth = $link->prepare($sql);
$sth->execute($data);
try {
    if (!($result = $sth->fetch(PDO::FETCH_ASSOC))) {
        // 新增餐廳
        $dataRes = [$newRestaurantBrandName . "_" . $_POST["new_restaurant_name"]];
        $sqlRes = "INSERT INTO restaurant(restaurant_name) VALUES (?)";
        $sthRes = $link->prepare($sqlRes);
        $sthRes->execute($dataRes);

        // 獲取新餐廳的id
        $sqlRes = "SELECT * FROM restaurant WHERE restaurant_name=?";
        $sthRes = $link->prepare($sqlRes);
        $sthRes->execute($dataRes);
        $resultRes = $sthRes->fetch(PDO::FETCH_ASSOC);
        $newRestaurantId = $resultRes["restaurant_id"];

        // 新增儲備金資訊
        $dataRes = [$newRestaurantId];
        $sqlRes = "INSERT INTO cash_reserve_management(start_date, restaurant_id) VALUES ((CURRENT()), ?)";
        $sthRes = $link->prepare($sqlRes);
        $sthRes->execute($dataRes);

        // 新增換錢金資訊
        $dataRes = [$newRestaurantId];
        $sqlRes = "INSERT INTO exchange_cash_management(start_date, restaurant_id) VALUES ((CURRENT()), ?)";
        $sthRes = $link->prepare($sqlRes);
        $sthRes->execute($dataRes);

        // 新增廠商金資訊
        $dataRes = [$newRestaurantId];
        $sqlRes = "INSERT INTO company_cash_management(start_date, restaurant_id) VALUES ((CURRENT()), ?)";
        $sthRes = $link->prepare($sqlRes);
        $sthRes->execute($dataRes);
    } else if ($result["restaurant_status"] == "closed" && $result["restaurant_name"] == $newRestaurantBrandName . "_" . $_POST["new_restaurant_name"]) {
        $dataRes = ["open", $result["restaurant_id"]];
        $sqlRes = "UPDATE restaurant SET restaurant_status=? WHERE restaurant_id=?";
        $sthRes = $link->prepare($sqlRes);
        $sthRes->execute($dataRes);
    } else {
        echo "<script>alert(\"此餐廳代號已被使用，請使用其他餐廳代號。\");</script>";
        echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
        $link = NULL;
        exit;
    }
} catch (PDOException $e) {
    echo "<script>alert(\"新餐廳資料無法上傳：{$e->getMessage()}\");</script>";
    $link = NULL;
    echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
    exit;
}

// 告示成功，並返回員工與餐廳管理頁
$link = NULL;
echo "<script>alert(\"新增成功。\");</script>";
echo "<script>document.location.href = \"emp_and_restaurant_management.php\";</script>";
exit;