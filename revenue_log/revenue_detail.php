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
    <title>當日結帳系統登錄明細</title>
</head>

<body>
    <div id="header">
        <h2>當前已經被<b><?php session_start();
                    echo $_SESSION["trUploadDoneBy"]; ?></b>上傳的資料總覽</h2>
    </div>
    <fieldset>
        <legend>已上傳的內容</legend>
        <?php
        // 連線MySQL
        $dsn = "mysql:dbname=check_flow_database_v3;host=localhost;port=3306";
        $username = "root";
        $password = "password";
        try {
            $link = new PDO($dsn, $username, $password);
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $link->query('SET NAMES utf8');
            //echo "成功建立MySQL伺服器連接和開啟check_flow_database資料庫</br>";
        } catch (PDOException $e) {
            echo "<script>alert(\"MySQL連接失敗：{$e->getMessage()}\")</script>";
            $link = NULL;
            echo "<div class=\"intro\">MySQL連接失敗：{$e->getMessage()}<br/>請聯繫系統管理人。</div>";
            exit;
        }

        // 1. 當日總營收
        $data = [$_SESSION["restaurant_id"]];
        $sql = 'SELECT * FROM total_revenue JOIN employee ON total_revenue.emp_id=employee.emp_id JOIN restaurant ON total_revenue.restaurant_id=restaurant.restaurant_id WHERE input_date=CURDATE() AND total_revenue.restaurant_id=?';
        $stmt = $link->prepare($sql);
        try {
            $stmt->execute($data);
            echo "<div id=\"total_revenue\" class=\"data\"><div><h3>項目1-輸入當日營收：</h3></div>";
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<table border=\"1\">
                            <tr>
                                <th>日期</th>
                                <th>輸入者</th>
                                <th>餐廳</th>
                                <th>當日總營收</th>
                                <th>TaiwanPay營收</th>
                                <th>UberEat營收</th>
                                <th>信用卡營收</th>
                                <th>現金營收</th>
                            </tr>
                            <tr>
                                <td align='center' valign=\"middle\">{$result["input_date"]}</td>
                                <td align='center' valign=\"middle\">{$result["emp_name"]}</td>
                                <td align='center' valign=\"middle\">{$result["restaurant_name"]}</td>
                                <td align='center' valign=\"middle\">" . number_format($result["total"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">" . number_format($result["taiwan_pay"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">" . number_format($result["uber_eat"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">" . number_format($result["credit_card"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">" . number_format($result["cash"], 0, ".", ",") . "元</td>
                            </tr>
                        </table><br/></div>";
            } else {
                echo "<div class=\"intro\"><p>發生錯誤，無法讀取項目1-當日營收資料。</p></div>";
            }
        } catch (PDOException $e) {
            echo "<script>alert(\"無法讀取項目1-輸入當日營收：{$e->getMessage()}\");</script>";
            $link = NULL;
            echo "<div class\"description\"><p>無法讀取項目1-輸入當日營收：{$e->getMessage()}\")</p></div>";
        }

        // 2. 錢櫃金資訊
        $data = [$_SESSION["restaurant_id"]];
        $sql = 'SELECT * FROM cashier_change JOIN employee ON cashier_change.emp_id=employee.emp_id JOIN restaurant ON cashier_change.restaurant_id=restaurant.restaurant_id WHERE input_date=CURDATE() AND cashier_change.restaurant_id=?';
        $stmt = $link->prepare($sql);
        try {
            $stmt->execute($data);
            echo "<div id=\"cashier_change\" class=\"data\"><div><h3>項目2-登錄錢櫃金(含錢櫃儲備金與現金營收)：</h3></div>";
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comment = str_replace("\n", "<br/>", $result["comments"]);
                echo "<table border=\"1\">
                            <tr>
                                <th>日期</th>
                                <th>輸入者</th>
                                <th>餐廳</th>
                                <th>1元硬幣</th>
                                <th>5元硬幣</th>
                                <th>10元硬幣</th>
                                <th>50元硬幣</th>
                                <th>100元鈔票</th>
                                <th>500元鈔票</th>
                                <th>1,000元鈔票</th>
                                <th>照片</th>
                                <th>總計</th>
                                <th>備註</th>
                            </tr>
                            <tr>
                                <td align='center' valign=\"middle\">{$result["input_date"]}</td>
                                <td align='center' valign=\"middle\">{$result["emp_name"]}</td>
                                <td align='center' valign=\"middle\">{$result["restaurant_name"]}</td>
                                <td align='center' valign=\"middle\">" . number_format($result["ones"], 0, ".", ",") . "枚</td>
                                <td align='center' valign=\"middle\">" . number_format($result["five"], 0, ".", ",") . "枚</td>
                                <td align='center' valign=\"middle\">" . number_format($result["ten"], 0, ".", ",") . "枚</td>
                                <td align='center' valign=\"middle\">" . number_format($result["fifty"], 0, ".", ",") . "枚</td>
                                <td align='center' valign=\"middle\">" . number_format($result["hundred"], 0, ".", ",") . "張</td>
                                <td align='center' valign=\"middle\">" . number_format($result["five_hundred"], 0, ".", ",") . "張</td>
                                <td align='center' valign=\"middle\">" . number_format($result["thousand"], 0, ".", ",") . "張</td>
                                <td align='center' valign=\"middle\"><img src=\"data:image/jpeg;base64," . base64_encode($result["pic"]) . "\" width=\"100\"></td>
                                <td align='center' valign=\"middle\">" . number_format($result["total"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">{$comment}</td>
                            </tr>
                        </table><br/></div>";
            } else {
                echo "<div class=\"intro\"><p>發生錯誤，無法讀取項目2-登錄錢櫃金資料。</p></div>";
            }
        } catch (PDOException $e) {
            echo "<script>alert(\"無法讀取項目2-登錄錢櫃金：{$e->getMessage()}\");</script>";
            $link = NULL;
            echo "<div class\"description\"><p>無法讀取項目2-登錄錢櫃金：{$e->getMessage()}\")</p></div>";
        }

        // 3. 換錢金資訊
        $data = [$_SESSION["restaurant_id"]];
        $sql = 'SELECT * FROM exchange_change JOIN employee ON exchange_change.emp_id=employee.emp_id JOIN restaurant ON exchange_change.restaurant_id=restaurant.restaurant_id WHERE input_date=CURDATE() AND exchange_change.restaurant_id=?';
        $stmt = $link->prepare($sql);
        try {
            $stmt->execute($data);
            echo "<div id=\"exchange_change\" class=\"data\"><div><h3>項目5-計算換錢金總額：</h3></div>";
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comment = str_replace("\n", "<br/>", $result["comments"]);
                echo "<table border=\"1\">
                            <tr>
                                <th>日期</th>
                                <th>輸入者</th>
                                <th>餐廳</th>
                                <th>1元硬幣</th>
                                <th>5元硬幣</th>
                                <th>10元硬幣</th>
                                <th>50元硬幣</th>
                                <th>100元鈔票</th>
                                <th>500元鈔票</th>
                                <th>1000元鈔票</th>
                                <th>袋裝100元</th>
                                <th>袋裝500元</th>
                                <th>袋裝1,000元</th>
                                <th>照片</th>
                                <th>總計</th>
                                <th>備註</th>
                            </tr>
                            <tr>
                                <td align='center' valign=\"middle\">{$result["input_date"]}</td>
                                <td align='center' valign=\"middle\">{$result["emp_name"]}</td>
                                <td align='center' valign=\"middle\">{$result["restaurant_name"]}</td>
                                <td align='center' valign=\"middle\">" . number_format($result["ones"], 0, ".", ",") . "枚</td>
                                <td align='center' valign=\"middle\">" . number_format($result["five"], 0, ".", ",") . "枚</td>
                                <td align='center' valign=\"middle\">" . number_format($result["ten"], 0, ".", ",") . "枚</td>
                                <td align='center' valign=\"middle\">" . number_format($result["fifty"], 0, ".", ",") . "枚</td>
                                <td align='center' valign=\"middle\">" . number_format($result["hundred"], 0, ".", ",") . "張</td>
                                <td align='center' valign=\"middle\">" . number_format($result["five_hundred"], 0, ".", ",") . "張</td>
                                <td align='center' valign=\"middle\">" . number_format($result["thousand"], 0, ".", ",") . "張</td>
                                <td align='center' valign=\"middle\">" . number_format($result["hundred_bag"], 0, ".", ",") . "包</td>
                                <td align='center' valign=\"middle\">" . number_format($result["five_hundred_bag"], 0, ".", ",") . "包</td>
                                <td align='center' valign=\"middle\">" . number_format($result["thousand_bag"], 0, ".", ",") . "包</td>
                                <td align='center' valign=\"middle\"><img src=\"data:image/jpeg;base64," . base64_encode($result["pic"]) . "\" width=\"100\"></td>
                                <td align='center' valign=\"middle\">" . number_format($result["total"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">{$comment}</td>
                            </tr>
                        </table><br/></div>";
            } else {
                echo "<div class=\"intro\"><p>發生錯誤，無法讀取項目5-計算換錢金總額資料。</p></div>";
            }
        } catch (PDOException $e) {
            echo "<script>alert(\"無法讀取項目5-計算換錢金總額：{$e->getMessage()}\");</script>";
            $link = NULL;
            echo "<div class\"description\"><p>無法讀取項目5-計算換錢金總額：{$e->getMessage()}\")</p></div>";
        }

        // 4. 當日現金營收與小白單資訊
        $data = [$_SESSION["restaurant_id"]];
        $sql = 'SELECT * FROM cash_revenue JOIN employee ON cash_revenue.emp_id=employee.emp_id JOIN restaurant ON cash_revenue.restaurant_id=restaurant.restaurant_id WHERE input_date=CURDATE() AND cash_revenue.restaurant_id=?';
        $stmt = $link->prepare($sql);
        try {
            $stmt->execute($data);
            echo "<div id=\"cash_revenue\" class=\"data\"><div><h3>項目6-換錢金總額確認與小費、小白單登錄的資訊：</h3></div>";
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comment = str_replace("\n", "<br/>", $result["comments"]);
                echo "<table border=\"1\">
                            <tr>
                                <th>日期</th>
                                <th>輸入者</th>
                                <th>餐廳</th>
                                <th>POS機顯示的現金營收</th>
                                <th>從換零盒拿到現金營收的錢</th>
                                <th>小費</th>
                                <th>本日實際裝袋的現金</th>
                                <th>小白單張數</th>
                                <th>照片</th>
                                <th>備註</th>
                            </tr>
                            <tr>
                                <td align='center' valign=\"middle\">{$result["input_date"]}</td>
                                <td align='center' valign=\"middle\">{$result["emp_name"]}</td>
                                <td align='center' valign=\"middle\">{$result["restaurant_name"]}</td>
                                <td align='center' valign=\"middle\">" . number_format($result["cash_revenue"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">" . number_format($result["change_filled"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">" . number_format($result["fee"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">" . number_format($result["total"], 0, ".", ",") . "元</td>
                                <td align='center' valign=\"middle\">" . number_format($result["white_list"], 0, ".", ",") . "張</td>
                                <td align='center' valign=\"middle\"><img src=\"data:image/jpeg;base64," . base64_encode($result["pic"]) . "\" width=\"100\"></td>
                                <td align='center' valign=\"middle\">{$comment}</td>
                            </tr>
                        </table><br/></div>
                        <font size=\"2\">*註：在<b>\"從換零盒拿到現金營收的錢\"</b>欄位中，負數表示從現金營收拿到換零和的數量；正數表示從換零盒拿到現金營收的數量。</font>";
            } else {
                echo "<div class=\"intro\"><p>發生錯誤，無法讀取項目6-換錢金總額確認與小費、小白單登錄的資訊資料。</p></div>";
            }
        } catch (PDOException $e) {
            echo "<script>alert(\"無法讀取項目6-換錢金總額確認與小費、小白單登錄的資訊：{$e->getMessage()}\");</script>";
            $link = NULL;
            echo "<div class\"description\"><p>無法讀取項目6-換錢金總額確認與小費、小白單登錄的資訊：{$e->getMessage()}\")</p></div>";
        }
        ?>
    </fieldset>
</body>

</html>