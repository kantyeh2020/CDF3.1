<!DOCTYPE html>

<?php
session_start();

// 確認是否殘留前日的資料，若有則消除
if ($_SESSION["login_date"] != date("Y/m/d")) {
    $_SESSION = array();
    $_SESSION["login_date"] = date("Y/m/d");
}

// 確認是否是未登入的狀態，若未登入則跳回登入入口
if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true)) {
    header("location: login.php");
    exit;
}

// 若為reviewer，則重新導向目錄
if ($_SESSION["authority"] == "reviewer") {
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
    <title>首頁目錄</title>
</head>

<body>
    <div id="header">
        <h1>首頁(目錄)</h1>
    </div>

    <fieldset>
        <legend>簡介</legend>
        <?php
        echo "哈囉，<b>{$_SESSION["emp_name"]}</b>！<br/>";
        echo "今天的日期是" . date("Y/m/d") . "<br/>";
        echo "目前正在登錄的餐廳為 <b>{$_SESSION["restaurant_name"]}</b>。<br/>";
        echo "<br/>";
        echo "請選擇要前往的目的地=>";
        echo "&nbsp;<a href=\"index_to_revenue.php\"><button><b>每日結帳系統</b></button></a>&nbsp;or&nbsp;";
        echo "&nbsp;<a href=\"./receipts_log/receipt_reroute.php\"><button><b>憑證</b>與<b>廠商支付餘額</b>登錄</button></a>";
        echo "<br/>";
        if (($_SESSION["trUploadDone"] || $_SESSION["xcUploadDone"] || $_SESSION["ccUploadDone"] || $_SESSION["crUploadDone"]) && !$_SESSION["revenueAllDone"]) {
            echo "&emsp;&emsp;|<br/>";
            echo "&emsp;&emsp;|---><b>每日結帳系統</b>：今日登錄內容正在由 <b>{$_SESSION["trUploadDoneBy"]}</b> 進行登錄。";
        } else if ($_SESSION["revenueAllDone"]) {
            echo "&emsp;&emsp;|<br/>";
            echo "&emsp;&emsp;|---><b>每日結帳系統</b>：今日登錄內容已由 <b>{$_SESSION["trUploadDoneBy"]}</b> 登錄完成。";
        } else {
            echo "&emsp;&emsp;|<br/>";
            echo "&emsp;&emsp;|---><b>每日結帳系統</b>：今日登錄尚未完成。";
        }
        if (date("w", strtotime(date("Y-m-d"))) == 0 || date("w", strtotime(date("Y-m-d"))) == 1 || date("w", strtotime(date("Y-m-d"))) == 6) {
            if ($_SESSION["cremainUploadDone"] && !$_SESSION["cremainAllDone"]) {
                echo "<br/>";
                echo "&emsp;&emsp;|---><b>憑證與剩餘廠商金登錄</b>：憑證在任何時間皆可登錄。本期剩餘廠商金登錄正在由 <b>{$_SESSION["cremainUploadDoneBy"]}</b> 進行登錄。<br/>>";
            } else if ($_SESSION["cremainAllDone"]) {
                echo "<br/>";
                echo "&emsp;&emsp;|---><b>憑證與剩餘廠商金登錄</b>：憑證在任何時間皆可登錄。本期剩餘廠商金登錄已由 <b>{$_SESSION["cremainUploadDoneBy"]}</b> 登錄完成。<br/>";
            } else {
                echo "<br/>";
                echo "&emsp;&emsp;|---><b>憑證與剩餘廠商金登錄</b>：憑證在任何時間皆可登錄。本日剩餘廠商金登錄尚未完成。<br/>";
            }
        } else {
            echo "<br/>";
            echo "&emsp;&emsp;|---><b>憑證與剩餘廠商金登錄</b>：憑證在任何時間皆可登錄。每期的剩餘廠商金只需要在星期六、日或下周一選擇一日登錄。<br/>";
        }
        ?>
        <br />
    </fieldset>
    <br />
    <?php
    if ($_SESSION["authority"] == "manager") {
        echo "<fieldset>
            <legend>管理者功能</legend>";
        echo "以下內容僅管理者可以操作：<br/><br/>";
        echo "<b>員工與餐廳管理</b>=>";
        echo "&nbsp;<a href=\"./manage/emp_and_restaurant_management.php\"><button><b>員工與餐廳管理</b></button></a>&nbsp;<br/>";
        echo "&emsp;&emsp;|<br/>";
        echo "&emsp;&emsp;|--->(1) 新增、修改、刪除員工資訊<br/>";
        echo "&emsp;&emsp;|--->(2) 新增、修改、刪除餐廳資訊<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<b>雜項管理</b>=>";
        echo "&nbsp;<a href=\"./manage/sundries_management.php\"><button><b>雜項管理</b></button></a>&nbsp;<br/>";
        echo "&emsp;&emsp;|<br/>";
        echo "&emsp;&emsp;|--->(1) 修改儲備金總額<br/>";
        echo "&emsp;&emsp;|--->(2) 修改換錢金總額<br/>";
        echo "&emsp;&emsp;|--->(3) 修改廠商金總額<br/>";
        echo "&emsp;&emsp;|--->(4) 新增、刪除憑證類型<br/>";
        echo "</fieldset>";
    }
    ?>

    <?php
    echo "<br/>";
    echo "<button onclick=\"logout()\">登出系統</button>&nbsp;<font size=\"2\">*註：尚未按下\"送出\"前<b>千萬不要</b>登出系統，否則所有未送出的資料都將要重新填寫。</font>\n\n";

    // 登出按鈕的js
    if (isset($_SESSION["total_revenue"]) && $_SESSION["total_revenue"] != NULL) {
        echo "<script>
            function logout() {
                if (window.confirm(\"每日結帳系統中有尚未送出的資料，確認要登出嗎?\\n(還沒填完就不要登出。)\")) {
                        document.location.href = \"./logout.php\";
                }
            }
        </script>";
    } else {
        echo "<script>
            function logout() {
                if (window.confirm(\"確認要登出嗎?\")) {
                        document.location.href = \"./logout.php\";
                }
            }
        </script>";
    }
    ?>
</body>

</html>