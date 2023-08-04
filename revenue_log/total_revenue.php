<!DOCTYPE html>
<a href="../login_to_index.php"><input type="button" value="返回目錄"></a><br />
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
} else {
    echo "現在登錄者：{$_SESSION['emp_name']}<br/>";
    echo "現在登錄位置：{$_SESSION['restaurant_name']}<br/>";
    echo "今日日期：" . date("Y/m/d") . "<br/><br/>";
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

// Check Point狀態檢查
if ($_SESSION["tr_to_cashier_check_point"] == true) {
    header("location: cashier.php");
    exit;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>項目1-輸入當日營收</title>
</head>

<body>
    <div id="header">
        <h2>項目(1/7) 輸入當日營收</h2>
    </div>
    <div id="intro">
        <p>(1) 請打開POS機的結帳系統<br />
            (2) 將系統內的<b>當日總營收</b>、<b>TaiwanPay營收</b>、<b>UberEat營收</b>、<b>信用卡營收</b>和<b>現金營收</b>填入表格中。<br />
            (3) 完成後請按下<b>下一步</b></p>
    </div>
    <form method="post" action="tr_to_cashier_check_point.php">
        <fieldset>
            <legend>輸入當日總營收</legend>
            <?php
            session_start();
            echo "<div id=\"total_revenue\" class=\"number\">當日總營收：<input type=\"number\" name=\"total\" value=\"{$_SESSION["total_revenue"]}\" required></div>";
            ?>
        </fieldset><br />
        <fieldset>
            <legend>輸入當日總營收組成</legend>
            <?php
            echo "<div id=\"taiwan_pay\" class=\"number\">TaiwanPay營收：<input type=\"number\" name=\"taiwan_pay\" value=\"{$_SESSION["taiwan_pay"]}\" required></div>";
            echo "<div id=\"uber_eat\" class=\"number\">UberEat營收：<input type=\"number\" name=\"uber_eat\" value=\"{$_SESSION["uber_eat"]}\" required></div>";
            echo "<div id=\"credit_card\" class=\"number\">信用卡營收：<input type=\"number\" name=\"credit_card\" value=\"{$_SESSION["credit_card"]}\" required></div>";
            echo "<div id=\"cash_revenue\" class=\"number\">現金營收：<input type=\"number\" name=\"cash\" value=\"{$_SESSION["cash_revenue"]}\" required></div>";
            ?>
        </fieldset>

        <br />
        <input type="submit" value="下一步">
    </form>
</body>

</html>