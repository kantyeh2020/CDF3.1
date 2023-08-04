<!DOCTYPE html>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script>
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

// 確認當日是否是星期六、日或一
include "day_check.php";

// 檢查當日結帳系統是否已經填寫完成
if (isset($_SESSION["cremainUploadDoneBy"]) && $_SESSION["cremainUploadDoneBy"] != NULL) {
    if ($_SESSION["cremainUploadDoneBy"] == $_SESSION["emp_name"] && $_SESSION["cremainAllDone"]) {
        echo "<script>alert(\"本期剩餘廠商金登錄已經填寫完成。\\n即將跳轉至憑證與剩餘廠商金登錄系統的明細頁。\");</script>";
        echo "<script>document.location.href = \"receipts_detail.php\";</script>";
        exit;
    } else if ($_SESSION["cremainUploadDoneBy"] != $_SESSION["emp_name"]) {
        if (!$_SESSION["cremainAllDone"]) {
            echo "<script>alert(\"本期剩餘廠商金登錄正在由{$_SESSION["cremainUploadDoneBy"]}填寫。\\n即將跳轉至憑證與剩餘廠商金登錄系統的明細頁。\");</script>";
            echo "<script>document.location.href = \"receipts_detail.php\";</script>";
            exit;
        } else {
            echo "<script>alert(\"本期剩餘廠商金登錄已經由{$_SESSION["cremainUploadDoneBy"]}填寫完成。\\n即將跳轉至憑證與剩餘廠商金登錄系統的明細頁。\");</script>";
            echo "<script>document.location.href = \"receipts_detail.php\";</script>";
            exit;
        }
    }
}

if ($_SESSION["cremain_to_rs_check_point"] == true) {
    header("location: receipts_submit.php");
    exit;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>項目1-登錄剩餘廠商金</title>
</head>

<body>
    <div id="header">
        <h2>項目(1/1) 登錄剩餘廠商金</h2>
    </div>
    <div id="intro">
        <p>(1) 請拿出裝本期廠商金的袋子。<br />
            (2) 將收袋子內的<b>硬幣與鈔票數量</b>填入表格中。<br />
            (3) 完成後請按下<b>送出</b>。<br />
            <font size="2">*註：送出後請使用新的廠商金袋子內的現金來支付貨款。舊的袋子內的現金請盡速拿去銀行存款。</font><br />
            <font size="2">*註：每一次結算剩餘廠商金計為一期。每一期的剩餘廠商金加上當期內使用廠商金付款的發票總和應為<b><?php session_start();
                                                                            echo number_format($_SESSION["dCCM_total"], 0, ".", ","); ?>元</b>整。</font>
        </p>
    </div>
    <form method="post" action="cremain_to_rs_check_point.php" enctype="multipart/form-data">
        <fieldset>
            <legend>填入硬幣與鈔票數量</legend>
            <?php
            session_start();
            echo "<div id=\"one\" class=\"number\">*1元(個數)：<input type=\"number\" name=\"ones\" value=\"{$_SESSION["cash_remain_ones"]}\" required></div>";
            echo "<div id=\"five\" class=\"number\">*5元(個數)：<input type=\"number\" name=\"five\" value=\"{$_SESSION["cash_remain_five"]}\" required></div>";
            echo "<div id=\"ten\" class=\"number\">*10元(個數)：<input type=\"number\" name=\"ten\" value=\"{$_SESSION["cash_remain_ten"]}\" required></div>";
            echo "<div id=\"fifty\" class=\"number\">*50元(個數)：<input type=\"number\" name=\"fifty\" value=\"{$_SESSION["cash_remain_fifty"]}\" required></div>";
            echo "<div id=\"hundred\" class=\"number\">*100元(張數)：<input type=\"number\" name=\"hundred\" value=\"{$_SESSION["cash_remain_hundred"]}\" required></div>";
            echo "<div id=\"five_hundred\" class=\"number\">*500元(張數)：<input type=\"number\" name=\"five_hundred\" value=\"{$_SESSION["cash_remain_five_hundred"]}\" required></div>";
            echo "<div id=\"thousand\" class=\"number\">*1,000元(張數)：<input type=\"number\" name=\"thousand\" value=\"{$_SESSION["cash_remain_thousand"]}\" required></div>";
            ?>
        </fieldset><br />
        <fieldset>
            <legend>照片與備註</legend>
            <?php
            if (isset($_SESSION["cash_remain_pic"]) && $_SESSION["cash_remain_pic"] != NULL) {
                echo "<div id=\"pic\" class=\"pic\">*照片(除非想要更換照片，否則不需要再次上傳)：<br/>";
                echo "<img src=\"data:image/jpeg;base64,{$_SESSION["cash_remain_pic"]}\" width=\"200\"/><br/>";
                echo "<input type=\"file\" name=\"pic\" accept=\"image/*\"></div>";
            } else {
                echo "<div id=\"pic\" class=\"pic\">*照片(上傳乙張)：<input type=\"file\" name=\"pic\" accept=\"image/*\" required></div>";
            }
            echo "<br/>";
            echo "<div id=\"comment\" class=\"textarea\">備註：(至多100中文字或200英文字母)<br/>
            <textarea name=\"comments\" style=\"font-family:sans-serif;font-size:1.2em;\">{$_SESSION["cash_remain_comments"]}</textarea>
            </div>";
            ?>
        </fieldset>
        <font size="2">*註：備註在總額正確的情況下為非必填項。</font>
        <br />
        <br />
        <input type="submit" value="提交">
        <br />
        <br />
        <a href="./receipt_reroute.php"><input type="button" value="返回分流頁面"></a>
        <a href="./receipt.php"><input type="button" value="返回憑證登錄"></a>
    </form>
</body>

</html>