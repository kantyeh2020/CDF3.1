<!DOCTYPE html>

<?php
session_start();

// 確認是否殘留前日的資料，若有則消除
if ($_SESSION["login_date"] != date("Y/m/d")) {
    $_SESSION = array();
    $_SESSION["login_date"] = date("Y/m/d");
}

// 確認是否已登入
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
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
    <title>登入入口</title>
</head>

<body>
    <div id="welcome">歡迎來到餐廳帳務登錄系統！</div>
    <div id="intro"></div>
    <br />
    <form id="form" method="post" action="login_to_index.php">
        <fieldset>
            <legend>開始操作前請先登入</legend>
            <div id="restaurant">
                分店代號：
                <select name="restaurant_id" required>
                    <option value="0">---請選擇---</option>
                    <?php
                    include "connect_mysql.php";

                    //讀取餐廳資料
                    $sql = "SELECT * FROM restaurant";
                    $stmt = $link->prepare($sql);
                    $stmt->execute();
                    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value=\"{$result["restaurant_id"]}\">{$result["restaurant_name"]}</option>";
                    }
                    ?>
                </select>
            </div>
            <div id="emp">
                員工名稱：
                <select name="emp_id" id="emp_id" required>
                    <option value="0">---請選擇---</option>
                    <?php
                    //讀取員工資料
                    $sql = "SELECT * FROM employee";
                    $stmt = $link->prepare($sql);
                    $stmt->execute();
                    $manager = array();
                    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if ($result["authority"] == "manager") {
                            array_push($manager, $result["emp_id"]);
                        }
                        echo "<option value=\"{$result["emp_id"]}\">{$result["emp_name"]}</option>";
                    }
                    $link = NULL;
                    ?>
                </select>
            </div>
            <div id="emp_password">
                員工密碼：請選擇員工身份
            </div>
        </fieldset>
        <br />
        <input type="submit" value="登入"><br />
    </form>

    <script>
        $(function() {
            $('#emp_id').blur(function() {
                if (
                    <?php
                    for ($i = 0; $i < count($manager); $i++) {
                        if ($i == 0) {
                            echo "$('#emp_id').val() == \"{$manager[$i]}\"";
                        } else {
                            echo " || $('#emp_id').val() == \"{$manager[$i]}\"";
                        }
                    }
                    ?>
                ) {
                    $('#emp_password').html('');
                    $('#emp_password').html('員工密碼：<input type="password" name="emp_password" placeholder="請輸入密碼" required>');
                } else if ($('#emp_id').val() == 0) {
                    $('#emp_password').html('');
                    $('#emp_password').html('員工密碼：請選擇員工身份');
                } else {
                    $('#emp_password').html('');
                    $('#emp_password').html('員工密碼：此員工帳號不需要填密碼');
                }
            });
        });
    </script>

</body>

</html>