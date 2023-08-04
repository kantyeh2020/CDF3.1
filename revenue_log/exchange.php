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
if ($_SESSION["mat_to_exchange_check_point"] == false) {
    header("location: merge_and_take.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["exchange_to_crevenue_check_point"] == true) {
    header("location: cash_revenue.php");
    exit;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>項目5-計算換錢金總額是否正確</title>
</head>

<body>
    <div id="header">
        <h2>項目(5/7) 計算換錢金總額是否正確</h2>
    </div>
    <div id="intro">
        <p>(1) 請清空換零盒的周圍<br />
            (2) 將換零盒內的<b>硬幣袋裝數量</b>填入表格中對應欄位(如5元硬幣和10元硬幣都各有一袋500元袋裝的硬幣，則在袋裝500元的欄位中填入2)。<br />
            (3) 將換零盒內的<b>硬幣與鈔票數量</b>填入表格中。(不用算步驟4中拿出的現金營收)<br />
            (4) 完成後請按下<b>下一步</b><br /><br />
        </p>
    </div>
    <form method="post" action="exchange_to_crevenue_check_point.php" enctype="multipart/form-data">
        <fieldset>
            <legend>袋裝硬幣數量</legend>
            <?php
            session_start();
            echo "<div id=\"hundred_bag\" class=\"number\">*袋裝100元(包)：<input type=\"number\" name=\"hundred_bag\" value=\"{$_SESSION["exchange_hundred_bag"]}\" required></div>";
            echo "<div id=\"five_hundred_bag\" class=\"number\">*袋裝500元(包)：<input type=\"number\" name=\"five_hundred_bag\" value=\"{$_SESSION["exchange_five_hundred_bag"]}\" required></div>";
            echo "<div id=\"thousand_bag\" class=\"number\">*袋裝1,000元(包)：<input type=\"number\" name=\"thousand_bag\" value=\"{$_SESSION["exchange_thousand_bag"]}\" required></div>";
            ?>
        </fieldset><br />
        <fieldset>
            <legend>非袋裝硬幣與鈔票數量</legend>
            <?php
            echo "<div id=\"one\" class=\"number\">*1元(個數)：<input type=\"number\" name=\"ones\" value=\"{$_SESSION["exchange_ones"]}\" required></div>";
            echo "<div id=\"five\" class=\"number\">*5元(個數)：<input type=\"number\" name=\"five\" value=\"{$_SESSION["exchange_five"]}\" required></div>";
            echo "<div id=\"ten\" class=\"number\">*10元(個數)：<input type=\"number\" name=\"ten\" value=\"{$_SESSION["exchange_ten"]}\" required></div>";
            echo "<div id=\"fifty\" class=\"number\">*50元(個數)：<input type=\"number\" name=\"fifty\" value=\"{$_SESSION["exchange_fifty"]}\" required></div>";
            echo "<div id=\"hundred\" class=\"number\">*100元(張數)：<input type=\"number\" name=\"hundred\" value=\"{$_SESSION["exchange_hundred"]}\" required></div>";
            echo "<div id=\"five_hundred\" class=\"number\">*500元(張數)：<input type=\"number\" name=\"five_hundred\" value=\"{$_SESSION["exchange_five_hundred"]}\" required></div>";
            echo "<div id=\"thousand\" class=\"number\">*1,000元(張數)：<input type=\"number\" name=\"thousand\" value=\"{$_SESSION["exchange_thousand"]}\" required></div>";
            ?>
        </fieldset><br />
        <fieldset>
            <legend>照片與備註</legend>
            <?php
            if (isset($_SESSION["exchange_pic"]) && $_SESSION["exchange_pic"] != NULL) {
                echo "<div id=\"pic\" class=\"pic\">*照片(除非想要更換照片，否則不需要再次上傳)：<br/>";
                echo "<img src=\"data:image/jpeg;base64,{$_SESSION["exchange_pic"]}\" width=\"200\"/><br/>";
                echo "<input type=\"file\" name=\"pic\" accept=\"image/*\"></div>";
            } else {
                echo "<div id=\"pic\" class=\"pic\">*照片(上傳乙張)：<input type=\"file\" name=\"pic\" accept=\"image/*\" required></div>";
            }
            echo "<br/>";
            echo "<div id=\"comment\" class=\"textarea\">備註：(至多100中文字或200英文字母)<br/>
        <textarea name=\"comments\" style=\"font-family:sans-serif;font-size:1.2em;\">{$_SESSION["exchange_comments"]}</textarea>
        </div>";
            ?>
        </fieldset>
        <font size="2">*註：備註在總額正確的情況下為非必填項。</font>
        <br />
        <br />
        <a href="./cancel_mat_to_exchange_check_point.php"><input type="button" value="上一步"></a>
        <input type="submit" value="下一步">
    </form>
</body>

</html>