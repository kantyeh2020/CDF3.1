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
    <title>每日現金組成</title>
</head>

<body>
    <div id="header">
        <h2>每日現金組成：(最多顯示30筆資料)</h2>
    </div>
    <fieldset>
        <legend>詳細資料</legend>
        <?php
        session_start();
        // 現金營收組成
        echo "<table border=\"1\">
            <tr>
                <th>項次</th>
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
            </tr>";

        // 連線MySQL
        include "connect_mysql.php";

        // 讀取並輸出資料
        $data = [$_SESSION["restaurant_id"]];
        $sql = "SELECT * FROM cash_revenue JOIN restaurant ON cash_revenue.restaurant_id=restaurant.restaurant_id JOIN employee ON cash_revenue.emp_id=employee.emp_id WHERE cash_revenue.restaurant_id=? ORDER BY input_date DESC LIMIT 30";
        $sth = $link->prepare($sql);
        $sth->execute($data);
        $i = 0;
        try {
            while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                $comment = str_replace("\n", "<br/>", $result["comments"]);
                $i++;
                $comments = str_replace("\n", "&nbsp;<br/>&nbsp;", $result["comments"]);
                echo "<tr>
                    <td align=\"center\">{$i}</td>
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
                </tr>";
            }
            echo "<tr>
            <td colspan=\"11\" align=\"center\">以上共有{$i}項資料。</td>
        </tr>
        </table><br/>";
        } catch (PDOException $e) {
            echo "<script>alert(\"無法獲取完整的每日現金組成紀錄：{$e->getMessage()}\");</script>";
            echo "</table><div class\"description\"><p>無法獲取完整的每日現金組成紀錄：{$e->getMessage()}</p></div><br/>";
            $link = NULL;
            exit;
        }
        ?>
        <button id="btnExport" onclick="exportReportToExcel(this)">匯出成Excel檔</button>
    </fieldset>
    <br />
    <a href="revenue_reroute.php"><button>上一頁</button></a>
    <script>
        function exportReportToExcel() {
            let table = document.getElementsByTagName('table'); // you can use document.getElementById('tableId') as well by providing id to the table tag
            TableToExcel.convert(table[0], { // html code may contain multiple tables so here we are refering to 1st table tag
                name: `每日現金組成資訊.xlsx`, // fileName you could use any name
                sheet: {
                    name: '每日現金組成資訊' // sheetName
                }
            });
        }
    </script>
</body>

</html>