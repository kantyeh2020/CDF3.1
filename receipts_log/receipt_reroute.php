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
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script>
    <title>憑證與剩餘廠商金登錄</title>
</head>

<body>
    <div class="header">
        <h2>憑證登錄相關分流頁面</h2>
    </div>
    <div class="intro">
        <p>(1) 要登錄<b>發票、收據、合約及請款單</b>，請選擇<b>憑證登錄</b>。<br />
            (2) 要登錄當期的<b>剩餘廠商金</b>，請選擇<b>剩餘廠商金登錄</b>。<br />
            <font size="2">*註1：憑證登錄為常態開放，只要當日有拿到新的憑證就必須當日登錄。</font><br />
            <font size="2">*註2：剩餘廠商金登錄只有在每周日開放，每周日須由當日值班外場的員工登錄。(測試環境中沒有限制僅星期日可登錄)</font><br />
        </p>
    </div>
    <fieldset>
        <legend>可執行的動作</legend>
        請選擇要執行的動作：<br />
        &nbsp;<a href="receipt.php"><button><b>憑證</b>登錄</button></a>
        &nbsp;<a href="cash_remain.php"><button onclick=alertCheck()><b>剩餘廠商金</b>登錄</button></a><br />
        <?php
        if (date("w", strtotime(date("Y-m-d"))) == 0 || date("w", strtotime(date("Y-m-d"))) == 1 || date("w", strtotime(date("Y-m-d"))) == 6) {
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
            echo "|---><b>剩餘廠商金登錄</b>：每期剩餘廠商金只需在星期六、日或下周一選擇一日登錄。</blockquote>";
        }
        ?>
    </fieldset>

    <script>
        // 警示登錄完此項目後，之後的發票將會被記錄在下周
        function alertCheck() {
            alert("登錄前請再次確認本期發票都已經登錄完成。");
        }
    </script>
</body>

</html>