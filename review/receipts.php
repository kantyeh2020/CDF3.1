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
    <title>憑證資訊</title>
</head>

<body>
    <div id="header">
        <h2>憑證資訊：(最多顯示30筆資料)</h2>
    </div>
    <fieldset>
        <legend>詳細資料</legend>
        <?php
        session_start();
        // 憑證資訊
        echo "<table border=\"1\">
            <tr>
            <th align=\"center\">&nbsp;項次&nbsp;</th>
            <th align=\"center\">&nbsp;費用公司&nbsp;</th>
            <th align=\"center\">&nbsp;申請人&nbsp;</th>
            <th align=\"center\">&nbsp;登錄日期&nbsp;</th>
            <th align=\"center\">&nbsp;費用歸屬&nbsp;<br/>&nbsp;母專案科目&nbsp;</th>
            <th align=\"center\">&nbsp;費用歸屬&nbsp;<br/>&nbsp;子專案科目&nbsp;</th>
            <th align=\"center\">&nbsp;費用歸屬&nbsp;<br/>&nbsp;費用別&nbsp;</th>
            <th align=\"center\">&nbsp;憑證類型&nbsp;</th>
            <th align=\"center\">&nbsp;憑證編號&nbsp;</th>
            <th align=\"center\">&nbsp;付款日期&nbsp;</th>
            <th align=\"center\">&nbsp;廠商名稱&nbsp;</th>
            <th align=\"center\">&nbsp;支付金額&nbsp;</th>
            <th align=\"center\">&nbsp;支付方法&nbsp;</th>
            <th align=\"center\">&nbsp;費用明細&nbsp;</th>
            <th align=\"center\">&nbsp;照片&nbsp;</th>
            <th align=\"center\">&nbsp;備註&nbsp;</th>
            </tr>";

        // 連線MySQL
        include "connect_mysql.php";

        // 讀取並輸出資料
        $data = [$_SESSION["restaurant_id"], "correct"];
        $sql = "SELECT * FROM receipt JOIN restaurant ON receipt.restaurant_id=restaurant.restaurant_id JOIN employee ON receipt.emp_id=employee.emp_id WHERE receipt.restaurant_id=? AND process_status=? ORDER BY input_date DESC LIMIT 30";
        $sth = $link->prepare($sql);
        $sth->execute($data);
        $i = 0;
        try {
            while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $detail = str_replace("\n", "&nbsp;<br/>&nbsp;", $result["detail"]);
                $comments = str_replace("\n", "&nbsp;<br/>&nbsp;", $result["comments"]);
                echo "<tr>
                    <td align=\"center\">{$i}</td>
                    <td align=\"center\">&nbsp;{$result["restaurant_name"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["emp_name"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["input_date"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["main_project"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["sub_project"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["subject_id"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["receipt_type"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["receipt_id_number"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["buying_date"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["selling_company"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;" . number_format($result["total_price"], 0, ".", ",") . "元&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["payment_method"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$detail}&nbsp;</td>
                    <td align=\"center\"><img src=\"data:image/jpeg;base64," . base64_encode($result["receipt_pic"]) . "\" width=\"200\"></td>
                    <td align=\"center\">&nbsp;{$comments}&nbsp;</td>
                </tr>";
            }
            echo "<tr>
            <td colspan=\"16\" align=\"center\">以上共有{$i}項資料。</td>
        </tr>
        </table><br/>";
        } catch (PDOException $e) {
            echo "<script>alert(\"無法獲取完整的憑證資訊：{$e->getMessage()}\");</script>";
            echo "</table><div class\"description\"><p>無法獲取完整的憑證資訊：{$e->getMessage()}</p></div><br/>";
            $link = NULL;
            exit;
        }
        ?>
    </fieldset>
    <br />
    <a href="receipts_reroute.php"><button>上一頁</button></a>
</body>

</html>