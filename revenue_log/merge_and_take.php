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

// 確認是否已經完成上個表單
if ($_SESSION["cr_to_mat_check_point"] == false) {
    header("location: cashier_reserve.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["mat_to_exchange_check_point"] == true) {
    header("location: exchange.php");
    exit;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>項目4-從換零盒內取出當日現金營收</title>
</head>

<body>
    <div id="header">
        <h2>項目(4/7) 從換零盒內取出當日現金營收</h2>
    </div>
    <div id="intro">
        <p>(1) 請上一步驟中多餘的現金和換錢金合併<br />
            (2) 從換零盒中<b>取出<?php session_start();
                            echo number_format($_SESSION["cash_revenue"], 0, ".", ","); ?>元整</b>(和POS機上顯示的現金營收數量相同)<br />
            (3) 將這筆現金放在一旁，待後續步驟使用。<br />
            (4) 勾選<b>"我確定我已經拿出
                <?php
                echo number_format($_SESSION["cash_revenue"], 0, ".", ",");
                ?>
                元了。"</b><br />
            (4) 完成後請按下<b>下一步</b><br /><br />
        </p>
    </div>
    <form method="post" action="mat_to_exchange_check_point.php" enctype="multipart/form-data">
        <fieldset>
            <legend>確認完成步驟</legend>
            <div id="check_take" class="checkbox">
                <label><input type="checkbox" name="take" required>我確定我已經拿出
                    <?php
                    echo number_format($_SESSION["cash_revenue"], 0, ".", ",");
                    ?>
                    元了。</label>
            </div>
        </fieldset>
        <br />
        <br />
        <a href="./cancel_cr_to_mat_check_point.php"><input type="button" value="上一步"></a>
        <input type="submit" value="下一步">
    </form>
</body>

</html>