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
    <title>檢視目錄</title>
</head>

<body>
    <div id="header">
        <h1>Reveiwer首頁(目錄)</h1>
    </div>
    <fieldset>
        <legend>簡介</legend>
        <?php
        echo "哈囉，<b>{$_SESSION["emp_name"]}</b>！<br/>";
        echo "今天的日期是" . date("Y/m/d") . "<br/>";
        echo "目前正在登錄的餐廳為 <b>{$_SESSION["restaurant_name"]}</b>。<br/>";
        echo "<br/>";
        echo "請選擇要前往的目的地=>";
        echo "&nbsp;<a href=\"./review/revenue_reroute.php\"><button><b>每日結帳狀況</b>檢視</button></a>&nbsp;or&nbsp;";
        echo "&nbsp;<a href=\"./review/receipts_reroute.php\"><button><b>憑證</b>與<b>廠商支付餘額</b>檢視</button></a><br/>";
        ?>
    </fieldset>
    <br />
    <button onclick="logout()">登出系統</button>
    <script>
        function logout() {
            if (window.confirm("確認要登出嗎?")) {
                document.location.href = "./logout.php";
            }
        }
    </script>
</body>

</html>