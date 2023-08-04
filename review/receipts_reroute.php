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
    <title>憑證與剩餘廠商金資訊檢視</title>
</head>

<body>
    <div id="header">
        <h2>憑證登錄相關分流頁面資訊檢視</h2>
    </div>
    <div class="intro">
        <p>(1) <b>憑證資訊</b>紀錄每一張憑證的所有資訊。<br />
            (2) <b>每期剩餘廠商金</b>以一周為單位，紀錄每期剩餘的廠商金數量。<br />
            <?php
            if (date("w", strtotime(date("Y-m-d"))) == 0) {
                if ($_SESSION["cremainUploadDone"] && !$_SESSION["cremainAllDone"]) {
                    echo "<blockquote>|---><b>憑證登錄</b>：憑證在任何時間皆可登錄。<br/>";
                    echo "|---><b>剩餘廠商金登錄</b>：本期剩餘廠商金登錄正在由 <b>{$_SESSION["cremainUploadDoneBy"]}</b> 進行登錄。</blockquote>";
                } else if ($_SESSION["cremainAllDone"]) {
                    echo "<blockquote>|---><b>憑證登錄</b>：憑證在任何時間皆可登錄。<br/>";
                    echo "|---><b>剩餘廠商金登錄</b>：本期剩餘廠商金登錄已由 <b>{$_SESSION["cremainUploadDoneBy"]}</b> 登錄完成。</blockquote>";
                } else {
                    echo "<blockquote>|---><b>憑證登錄</b>：憑證在任何時間皆可登錄。<br/>";
                    echo "|---><b>剩餘廠商金登錄</b>：本期剩餘廠商金登錄尚未完成。</blockquote>";
                }
            } else {
                echo "<blockquote>|---><b>憑證登錄</b>：憑證在任何時間皆可登錄。<br/>";
                echo "|---><b>剩餘廠商金登錄</b>：星期日才需要登錄剩餘廠商金。</blockquote>";
            }
            ?>
        </p>
    </div>

    <fieldset>
        <legend>請選擇要檢視的內容</legend>
        &nbsp;<a href="receipts.php"><button>憑證資訊</button></a>
        &nbsp;<a href="cash_remain.php"><button>每期剩餘廠商金資訊</button></a><br />
    </fieldset>
</body>

</html>