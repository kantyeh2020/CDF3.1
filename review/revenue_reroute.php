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

// 確認權限為reviewer
if (!(isset($_SESSION["authority"]) && $_SESSION["authority"] == "reviewer")) {
    header("location: login_to_index.php");
    exit;
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script>
    <title>每日結帳系統資訊檢視</title>
</head>

<body>
    <div id="header">
        <h2>每日結帳系統資訊檢視</h2>
    </div>
    <div class="intro">
        <p>(1) <b>每日總營收</b>紀錄當日POS機上的資訊，包含：當日總營收、Taiwan Pay營收、Uber Eat營收、信用卡營收和現金營收。<br />
            (2) <b>錢櫃金</b>紀錄當日錢櫃內所含的所有現金數量(含錢櫃儲備金(<?php session_start();
                                                    echo number_format($_SESSION["dCRM_total"], 0, ".", ","); ?>))。<br />
            (3) <b>換錢金</b>紀錄當日換零盒內所含的所有現金數量(換零盒內應常駐有<?php session_start();
                                                    echo number_format($_SESSION["dECM_total"], 0, ".", ","); ?>元)。<br />
            (4) <b>每日現金組成</b>紀錄當日實際上包裝於透明密封袋內的現金和小白單數量，及各個條目的組成成分，包含：實際現金營收、補足換零盒的現金數量、小費、小白單張數和包裝的照片。
            <?php
            if (($_SESSION["trUploadDone"] || $_SESSION["xcUploadDone"] || $_SESSION["ccUploadDone"] || $_SESSION["crUploadDone"]) && !$_SESSION["revenueAllDone"]) {
                echo "<blockquote>|<br/>";
                echo "|---><b>今日結帳系統</b>：今日登錄內容正在由 <b>{$_SESSION["trUploadDoneBy"]}</b> 進行登錄。</blockquote>";
            } else if ($_SESSION["revenueAllDone"]) {
                echo "<blockquote>|<br/>";
                echo "|---><b>今日結帳系統</b>：今日登錄內容已由 <b>{$_SESSION["trUploadDoneBy"]}</b> 登錄完成。</blockquote>";
            } else {
                echo "<blockquote>|<br/>";
                echo "|---><b>今日結帳系統</b>：今日結帳系統尚無人登錄。</blockquote>";
            }
            ?>
        </p>
    </div>
    <fieldset>
        <legend>請選擇要檢視的內容</legend>
        &nbsp;<a href="total_revenue.php"><button>每日總營收</button></a>
        &nbsp;<a href="cashier_change.php"><button>錢櫃金(現金營收+<?php session_start();
                                                            echo number_format($_SESSION["dCRM_total"], 0, ".", ","); ?>)</button></a>
        &nbsp;<a href="exchange_change.php"><button>換錢金(<?php session_start();
                                                        echo number_format($_SESSION["dECM_total"], 0, ".", ","); ?>)</button></a>
        &nbsp;<a href="cash_revenue.php"><button>每日現金組成</button></a><br />
    </fieldset>
</body>

</html>