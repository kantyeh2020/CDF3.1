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
if ($_SESSION["cashier_to_cr_check_point"] == false) {
    header("location: cashier.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["cr_to_mat_check_point"] == true) {
    header("location: merge_and_take.php");
    exit;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>項目3-補充錢櫃儲備金</title>
</head>

<body>
    <div id="header">
        <h2>項目(3/7) 補充錢櫃儲備金</h2>
    </div>
    <div id="intro">
        <p>(1) 請拿出盛裝<b>換錢金</b>的盒子(簡稱<b>換零盒</b>)<br />
            (2) 將收銀台內的<b>硬幣與鈔票數量</b>補充至規定的數額，並在<b>硬幣與鈔票的操作參考</b>表格中勾選已經完成的項目。<br />
            (3) 將多餘的硬幣與鈔票放至換零盒內。<br />
            (4) 完成後請按下<b>下一步</b><br /><br />
        </p>
    </div>
    <form method="post" action="cr_to_mat_check_point.php" enctype="multipart/form-data">
        <fieldset>
            <legend>硬幣與鈔票的操作參考</legend>
            <?php
            session_start();
            $defaultCR = [$_SESSION["dCRM_ones"], $_SESSION["dCRM_five"], $_SESSION["dCRM_ten"], $_SESSION["dCRM_fifty"], $_SESSION["dCRM_hundred"], $_SESSION["dCRM_five_hundred"], $_SESSION["dCRM_thousand"]];
            $cashier = [$_SESSION["cashier_ones"], $_SESSION["cashier_five"], $_SESSION["cashier_ten"], $_SESSION["cashier_fifty"], $_SESSION["cashier_hundred"], $_SESSION["cashier_five_hundred"], $_SESSION["cashier_thousand"]];
            for ($i = 0; $i < 7; $i++) {
                switch ($i) {
                    case 0:
                        $name = "cashier_reserve_one";
                        $unit = "個";
                        $dollar = "1元硬幣";
                        break;
                    case 1:
                        $name = "cashier_reserve_five";
                        $unit = "個";
                        $dollar = "5元硬幣";
                        break;
                    case 2:
                        $name = "cashier_reserve_ten";
                        $unit = "個";
                        $dollar = "10元硬幣";
                        break;
                    case 3:
                        $name = "cashier_reserve_fifty";
                        $unit = "個";
                        $dollar = "50元硬幣";
                        break;
                    case 4:
                        $name = "cashier_reserve_hundred";
                        $unit = "張";
                        $dollar = "100元鈔票";
                        break;
                    case 5:
                        $name = "cashier_reserve_five_hundred";
                        $unit = "張";
                        $dollar = "500元鈔票";
                        break;
                    case 6:
                        $name = "cashier_reserve_thousand";
                        $unit = "張";
                        $dollar = "1,000元鈔票";
                        break;
                }
                echo "<div id=\"{$name}\" class=\"checkbox\"><label><input type=\"checkbox\" name=\"check[]\" value=\"{$name}\" required ";
                if ($_SESSION["{$name}"] == true) {
                    echo "checked";
                }
                if ($defaultCR[$i] - $cashier[$i] < 0) {
                    $j = $i + 1;
                    $diff = abs($defaultCR[$i] - $cashier[$i]);
                    echo ">{$j}. 從<b>收銀台</b>取出{$diff}{$unit}{$dollar}，並放入<b>換零盒</b>中。此時應有{$defaultCR[$i]}{$unit}{$dollar}在收銀台內。</label></div>";
                } else if ($defaultCR[$i] - $cashier[$i] > 0) {
                    $j = $i + 1;
                    $diff = abs($defaultCR[$i] - $cashier[$i]);
                    echo ">{$j}. 從<b>換零盒</b>取出{$diff}{$unit}{$dollar}，並放入<b>收銀台</b>中。此時應有{$defaultCR[$i]}{$unit}{$dollar}在收銀台內。</label></div>";
                } else {
                    $j = $i + 1;
                    echo ">{$j}. {$dollar}數量正好，無須動作。此時應有{$defaultCR[$i]}{$unit}{$dollar}在收銀台內。</label></div>";
                }
            }
            ?>
        </fieldset>
        <br />
        <br />
        <a href="./cancel_cashier_to_cr_check_point.php"><input type="button" value="上一步"></a>
        <input type="submit" value="下一步">
    </form>
</body>

</html>