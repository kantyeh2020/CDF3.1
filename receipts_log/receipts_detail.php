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
    <title>本期憑證與剩餘廠商金登錄明細</title>
</head>

<body>
    <div id="header">
        <h2>當前已經被<b><?php session_start();
                    echo $_SESSION["cremainUploadDoneBy"]; ?></b>上傳的資料總覽</h2>
    </div>
    <fieldset>
        <legend>已上傳的內容</legend>
        <?php
        session_start();
        // 連線MySQL
        include "connect_mysql.php";

        // 剩餘廠商金資料
        echo "<div><h3>剩餘廠商金之登錄內容：</h3></div>";
        $data = [$_SESSION["restaurant_id"]];
        $sql = "SELECT * FROM cash_remain JOIN employee ON cash_remain.emp_id=employee.emp_id JOIN restaurant ON cash_remain.restaurant_id=restaurant.restaurant_id WHERE input_date>=(CURDATE()) AND cash_remain.restaurant_id=?";
        $stmt = $link->prepare($sql);
        try {
            $stmt->execute($data);
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
            </table><br/>";
            } else {
                echo "<div class=\"intro\"><p>發生錯誤，無法讀取剩餘廠商金之登錄內容。</p></div>";
                $link = NULL;
                exit;
            }
        } catch (PDOException $e) {
            echo "<script>alert(\"無法讀取剩餘廠商金之登錄內容：{$e->getMessage()}\");</script>";
            echo "<div class\"description\"><p>無法讀取剩餘廠商金之登錄內容：{$e->getMessage()}</p></div><br/>";
            $link = NULL;
            exit;
        }

        // 廠商金支付之憑證內容
        echo "<div><h3>使用廠商金支付之憑證內容：</h3></div>";
        echo "<table border=\"1\">
            <tr>
                <th align=\"center\">&nbsp;項次&nbsp;</th>
                <th align=\"center\">&nbsp;費用公司&nbsp;</th>
                <th align=\"center\">&nbsp;申請人&nbsp;</th>
                <th align=\"center\">&nbsp;母科目&nbsp;</th>
                <th align=\"center\">&nbsp;子科目&nbsp;</th>
                <th align=\"center\">&nbsp;費用別&nbsp;</th>
                <th align=\"center\">&nbsp;憑證類型&nbsp;</th>
                <th align=\"center\">&nbsp;憑證編號&nbsp;</th>
                <th align=\"center\">&nbsp;付款日期&nbsp;</th>
                <th align=\"center\">&nbsp;廠商名稱&nbsp;</th>
                <th align=\"center\">&nbsp;支付金額&nbsp;</th>
                <th align=\"center\">&nbsp;費用明細&nbsp;</th>
                <th align=\"center\">&nbsp;備註&nbsp;</th>
            </tr>";

        $data = ["廠商金支付", $_SESSION["last_time"], "correct"];
        $sql = "SELECT * FROM receipt JOIN restaurant ON receipt.restaurant_id=restaurant.restaurant_id JOIN employee ON receipt.emp_id=employee.emp_id WHERE payment_method=? AND input_date<=(NOW()) AND input_date>=? AND process_status=?";
        $sth = $link->prepare($sql);
        $i = 0;
        $receiptTotalPrice = 0;
        try {
            $sth->execute($data);
            while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $receiptTotalPrice += $result["total_price"];
                $detail = str_replace("\n", "&nbsp;<br/>&nbsp;", $result["detail"]);
                $comments = str_replace("\n", "&nbsp;<br/>&nbsp;", $result["comments"]);
                echo "<tr>
                    <td align=\"center\">{$i}</td>
                    <td align=\"center\">&nbsp;{$result["restaurant_name"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["emp_name"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["main_project"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["sub_project"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["subject_id"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["receipt_type"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["receipt_id_number"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["buying_date"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["selling_company"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;" . number_format($result["total_price"], 0, ".", ",") . "&nbsp;</td>
                    <td>&nbsp;{$detail}&nbsp;</td>
                    <td>&nbsp;{$comments}&nbsp;</td>
                </tr>";
            }
            echo "<tr>
            <td colspan=\"13\" align=\"center\">本期共有{$i}項憑證。所有憑證支出總額為" . number_format($receiptTotalPrice, 0, ".", ",") . "元。</td>
        </tr>
        </table>";
        } catch (PDOException $e) {
            echo "<script>alert(\"無法獲取本期完整的憑證紀錄：{$e->getMessage()}\");</script>";
            echo "</table><div class\"description\"><p>無法獲取本期完整的憑證紀錄：{$e->getMessage()}</p></div><br/>";
            $link = NULL;
            exit;
        }

        // 對於實際總和的備註
        if ($_SESSION["receipts_total"] == $_SESSION["dCCM_total"]) {
            echo "<font size=\"2\">*註1：上述<b>憑證支出總額</b>與<b>剩餘廠商金總額</b>相加後應為 <b>" . number_format($_SESSION["dCCM_total"], 0, ".", ",") . "</b> 元整，實為 <b>" . number_format($_SESSION["receipts_total"], 0, ".", ",") . "</b> 元整，總額無誤。</font><br/>";
        } else {
            echo "<font size=\"2\">*註1：上述<b>憑證支出總額</b>與<b>剩餘廠商金總額</b>相加後應為 <b>" . number_format($_SESSION["dCCM_total"], 0, ".", ",") . "</b> 元整，然而實際相加為 <b>" . number_format($_SESSION["receipts_total"], 0, ".", ",") . "</b> 元整。<br/>&emsp;&emsp;&emsp;送出表單前請確認所有備註都有妥當填寫。</font><br/>";
        }

        // 營收代支付之憑證內容
        echo "<br/><br/>";
        echo "<div><h3>使用營收代支付之憑證內容：(不計入廠商金之" . number_format($_SESSION["dCCM_total"], 0, ".", ",") . "元內)</h3></div>";
        echo "<table border=\"1\">
            <tr>
                <th align=\"center\">&nbsp;項次&nbsp;</th>
                <th align=\"center\">&nbsp;費用公司&nbsp;</th>
                <th align=\"center\">&nbsp;申請人&nbsp;</th>
                <th align=\"center\">&nbsp;母科目&nbsp;</th>
                <th align=\"center\">&nbsp;子科目&nbsp;</th>
                <th align=\"center\">&nbsp;費用別&nbsp;</th>
                <th align=\"center\">&nbsp;憑證類型&nbsp;</th>
                <th align=\"center\">&nbsp;憑證編號&nbsp;</th>
                <th align=\"center\">&nbsp;付款日期&nbsp;</th>
                <th align=\"center\">&nbsp;廠商名稱&nbsp;</th>
                <th align=\"center\">&nbsp;支付金額&nbsp;</th>
                <th align=\"center\">&nbsp;費用明細&nbsp;</th>
                <th align=\"center\">&nbsp;備註&nbsp;</th>
            </tr>";

        $data = ["營收代支付",  $_SESSION["last_time"], "correct"];
        $sql = "SELECT * FROM receipt JOIN restaurant ON receipt.restaurant_id=restaurant.restaurant_id JOIN employee ON receipt.emp_id=employee.emp_id WHERE payment_method=? AND input_date<=(NOW()) AND input_date>=? AND process_status=?";
        $sth = $link->prepare($sql);
        $i = 0;
        $receiptTotalPrice = 0;
        try {
            $sth->execute($data);
            while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $receiptTotalPrice += $result["total_price"];
                $detail = str_replace("\n", "&nbsp;<br/>&nbsp;", $result["detail"]);
                $comments = str_replace("\n", "&nbsp;<br/>&nbsp;", $result["comments"]);
                echo "<tr>
                    <td align=\"center\">{$i}</td>
                    <td align=\"center\">&nbsp;{$result["restaurant_name"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["emp_name"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["main_project"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["sub_project"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["subject_id"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["receipt_type"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["receipt_id_number"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["buying_date"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;{$result["selling_company"]}&nbsp;</td>
                    <td align=\"center\">&nbsp;" . number_format($result["total_price"], 0, ".", ",") . "&nbsp;</td>
                    <td>&nbsp;{$detail}&nbsp;</td>
                    <td>&nbsp;{$comments}&nbsp;</td>
                </tr>";
            }
            echo "<tr>
            <td colspan=\"13\" align=\"center\">本期共有{$i}項此類憑證。此類憑證支出總額為" . number_format($receiptTotalPrice, 0, ".", ",") . "元。</td>
        </tr>
        </table><br/>";
        } catch (PDOException $e) {
            echo "<script>alert(\"無法獲取本期完整的憑證紀錄：{$e->getMessage()}\");</script>";
            echo "</table><div class\"description\"><p>無法獲取本期完整的憑證紀錄：{$e->getMessage()}</p></div><br/>";
            $link = NULL;
            exit;
        }
        $link = NULL;
        ?>
    </fieldset>
</body>

</html>