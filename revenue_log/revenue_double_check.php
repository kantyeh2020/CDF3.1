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
if ($_SESSION["rs_to_rdc_check_point"] == false) {
    header("location: revenue_submit.php");
    exit;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>項目X-已上傳的資料總覽</title>
</head>

<body>
    <div id="header">
        <h2>項目(X/7) 已上傳的資料總覽</h2>
    </div>
    <div id="intro">
        <p>(1) 請仔細核對上傳的項目是否與之前輸入的內容不同。<br />
            (2) 有空白的地方就表示那個格子沒有填，或是上傳失敗。(如果確定自己有填那個欄位，則請聯繫系統管理人除錯。)<br />
            (3) 若是發現錯誤，請按<b>上一頁</b>來回到錯誤的頁面並進行修正。(此處的錯誤是指"剛才放在旁邊，忘記數到的部分"，若是"換錢金和錢櫃儲備金交換時換錯"或"找客人零錢找錯"則不算在此列。)<br />
            (3) 檢查完後請滑至最下面，並勾選<b>我已經確認過內容無誤</b>。<br />
            (4) 完成後請按下<b>確定提交</b>。<br />
        </p>
    </div>
    <form method="post" action="back_to_index.php" enctype="multipart/form-data">
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
                                <th>1,000元鈔票</th>
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
        <fieldset>
            <legend>檢查證明</legend>
            <div class="checkbox" id="double_check">
                <label>
                    <input type="checkbox" name="double_check" required><b>我已經確認過內容無誤</b>。
                </label>
            </div>
        </fieldset>
        <font size="2">*註：本次提交後今日結帳資料將無法再更改。</font>
        <br />
        <br />
        <a href="./cancel_rs_to_rdc_check_point.php"><input type="button" value="上一步"></a>
        <input type="submit" value="確定提交">
    </form>
</body>

</html>