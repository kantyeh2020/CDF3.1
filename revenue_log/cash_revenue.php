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
if ($_SESSION["exchange_to_crevenue_check_point"] == false) {
    header("location: exchange.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["crevenue_to_rs_check_point"] == true) {
    header("location: revenue_submit.php");
    exit;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>項目6-換錢金總額確認與小費、小白單登錄</title>
</head>

<body>
    <div id="header">
        <h2>項目(6/7) 換錢金總額確認與小費、小白單登錄</h2>
    </div>
    <div id="intro">
        <p>(1) 取出剛才放在旁邊的現金營收堆。<br />
            (2) 將換零盒內的現金數量補至<?php session_start();
                            echo number_format($_SESSION["dECM_total"], 0, ".", ","); ?>元整。<br />
            (2) 檢查是否有小費，並將小費的數量填入表格中。(沒有則填0，計算小費時要先將原本就在小費盒內的100元扣除)<br />
            (3) 計算當日的小白單張數，並填入表格中。(沒有則填0)<br />
            (4) 取出裝現金營收的透明袋，並將<b>現金營收、小費和小白單</b>裝入透明袋中<br />
            (5) 完成後請按下<b>下一步</b><br /><br />
        </p>
    </div>
    <form method="post" action="crevenue_to_rs_check_point.php" enctype="multipart/form-data">
        <fieldset>
            <legend>將換零盒內的現金數量補至<?php session_start();
                                echo number_format($_SESSION["dECM_total"], 0, ".", ","); ?>元</legend>
            <?php
            session_start();
            $total = $_SESSION["exchange_total"];
            $dTotal = $_SESSION["dECM_total"];
            $diff = $total - $dTotal;
            $absDiff = abs($dTotal - $total);
            if ($total < $dTotal) {
                echo "<div class=\"checkbox\" id=\"change_filled\"><label><input type=\"checkbox\" name=\"change_filled\" value=\"{$diff}\" required";
                if ($_SESSION["crevenue_change_filled"] == $diff) {
                    echo " checked";
                }
                echo ">將{$absDiff}元從現金營收拿出，並放入換零盒中。完成後請打勾。</label></div>";
            } else if ($total > $dTotal) {
                echo "<div class=\"checkbox\" id=\"change_filled\"><label><input type=\"checkbox\" name=\"change_filled\" value=\"{$diff}\" required";
                if ($_SESSION["crevenue_change_filled"] == $diff) {
                    echo " checked";
                }
                echo ">將{$absDiff}元從換零盒拿出，並放入現金營收中。完成後請打勾。</label></div>";
            } else {
                echo "<div class=\"checkbox\" id=\"change_filled\"><label><input type=\"checkbox\" name=\"change_filled\" value=\"{$diff}\" required checked>換錢金的數量正確，不需要再移動換零盒內的現金。</label></div>";
            }
            ?>
        </fieldset><br />
        <fieldset>
            <legend>檢查是否有小費及小白單</legend>
            <?php
            echo "<div id=\"fee\" class=\"number\">*小費(元)：<input type=\"number\" name=\"fee\" value=\"{$_SESSION["crevenue_fee"]}\" required></div>";
            echo "<div id=\"white_list\" class=\"number\">*小白單(張)：<input type=\"number\" name=\"white_list\" value=\"{$_SESSION["crevenue_white_list"]}\" required></div>";
            ?>
        </fieldset>
        <font size="2">*註：以上兩項沒有就填0。</font>
        <br />
        <br />
        <a href="./cancel_exchange_to_crevenue_check_point.php"><input type="button" value="上一步"></a>
        <input type="submit" value="下一步">
    </form>
</body>

</html>