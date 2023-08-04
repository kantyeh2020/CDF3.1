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
    <title>換錢金(<?php session_start();
                echo number_format($_SESSION["dECM_total"], 0, ".", ","); ?>)</title>
</head>

<body>
    <div id="header">
        <h2>換錢金(<?php session_start();
                echo number_format($_SESSION["dECM_total"], 0, ".", ","); ?>)：(最多顯示30筆資料)</h2>
    </div>
    <fieldset>
        <legend>詳細資料</legend>
        <?php
        session_start();
        // 零找金(15000)
        echo "<table border=\"1\">
            <tr>
                <th>項次</th>
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
                <th>袋裝1000元</th>
                <th>照片</th>
                <th>總計</th>
                <th>備註</th>
            </tr>";

        // 連線MySQL
        include "connect_mysql.php";

        // 讀取並輸出資料
        $data = [$_SESSION["restaurant_id"]];
        $sql = "SELECT * FROM exchange_change JOIN restaurant ON exchange_change.restaurant_id=restaurant.restaurant_id JOIN employee ON exchange_change.emp_id=employee.emp_id WHERE exchange_change.restaurant_id=? ORDER BY input_date DESC LIMIT 30";
        $sth = $link->prepare($sql);
        $sth->execute($data);
        $i = 0;
        try {
            while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                $comment = str_replace("\n", "<br/>", $result["comments"]);
                $i++;
                $total = $result["ones"] + 5 * $result["five"] + 10 * $result["ten"] + 50 * $result["fifty"] + 100 * $result["hundred"] + 500 * $result["five_hundred"] + 1000 * $result["thousand"] + $result["change_filled"];
                echo "<tr>
                    <td align=\"center\">{$i}</td>
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
                </tr>";
            }
            echo "<tr>
            <td colspan=\"17\" align=\"center\">以上共有{$i}項資料。</td>
        </tr>
        </table><br/>";
        } catch (PDOException $e) {
            echo "<script>alert(\"無法獲取完整的換錢金紀錄：{$e->getMessage()}\");</script>";
            echo "</table><div class\"description\"><p>無法獲取完整的換錢金紀錄：{$e->getMessage()}</p></div><br/>";
            $link = NULL;
            exit;
        }
        ?>
    </fieldset>
    <br />
    <a href="revenue_reroute.php"><button>上一頁</button></a>
</body>

</html>