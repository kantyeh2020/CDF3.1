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
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>
    <title>每日總營收</title>
</head>

<body>
    <div id="header">
        <h2>每日總營收：(最多顯示30筆資料)</h2>
    </div>
    <fieldset>
        <legend>詳細資料</legend>
        <?php
        session_start();
        // 每日總營收
        echo "<table border=\"1\">
            <tr>
                <th>項次</th>
                <th>日期</th>
                <th>餐廳</th>
                <th>輸入者</th>
                <th>當日總營收</th>
                <th>TaiwanPay營收</th>
                <th>UberEat營收</th>
                <th>信用卡營收</th>
                <th>現金營收</th>
            </tr>";

        // 連線MySQL
        include "connect_mysql.php";

        // 讀取並輸出資料
        $data = [$_SESSION["restaurant_id"]];
        $sql = "SELECT * FROM total_revenue JOIN restaurant ON total_revenue.restaurant_id=restaurant.restaurant_id JOIN employee ON total_revenue.emp_id=employee.emp_id WHERE total_revenue.restaurant_id=? ORDER BY input_date DESC LIMIT 30";
        $sth = $link->prepare($sql);
        $sth->execute($data);
        $i = 0;
        try {
            while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                echo "<tr>
                    <td align=\"center\">{$i}</td>
                    <td align='center' valign=\"middle\">{$result["input_date"]}</td>
                    <td align='center' valign=\"middle\">{$result["emp_name"]}</td>
                    <td align='center' valign=\"middle\">{$result["restaurant_name"]}</td>
                    <td align='center' valign=\"middle\">" . number_format($result["total"], 0, ".", ",") . "元</td>
                    <td align='center' valign=\"middle\">" . number_format($result["taiwan_pay"], 0, ".", ",") . "元</td>
                    <td align='center' valign=\"middle\">" . number_format($result["uber_eat"], 0, ".", ",") . "元</td>
                    <td align='center' valign=\"middle\">" . number_format($result["credit_card"], 0, ".", ",") . "元</td>
                    <td align='center' valign=\"middle\">" . number_format($result["cash"], 0, ".", ",") . "元</td>
                </tr>";
            }
            echo "<tr>
            <td colspan=\"9\" align=\"center\">以上共有{$i}項資料。</td>
        </tr>
        </table><br/>";
        } catch (PDOException $e) {
            echo "<script>alert(\"無法獲取完整的總營收紀錄：{$e->getMessage()}\");</script>";
            echo "</table><div class\"description\"><p>無法獲取完整的總營收紀錄：{$e->getMessage()}</p></div><br/>";
            $link = NULL;
            exit;
        }
        $link = NULL;
        ?>
        <button id="btnExport" onclick="exportReportToExcel(this)">匯出成Excel檔</button>
    </fieldset>
    <br />
    <a href="revenue_reroute.php"><button>上一頁</button></a>
    <script>
        function exportReportToExcel() {
            let table = document.getElementsByTagName('table'); // you can use document.getElementById('tableId') as well by providing id to the table tag
            TableToExcel.convert(table[0], { // html code may contain multiple tables so here we are refering to 1st table tag
                name: `總營收資訊.xlsx`, // fileName you could use any name
                sheet: {
                    name: '總營收資訊' // sheetName
                }
            });
        }
    </script>
</body>

</html>