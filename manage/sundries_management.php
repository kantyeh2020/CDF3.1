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

// 確認權限為manager
if (!(isset($_SESSION["authority"]) && $_SESSION["authority"] == "manager")) {
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
    <title>雜項管理</title>
</head>

<body>
    <div id="header">
        <h2>雜項管理</h2>
    </div>
    <div class="intro">
        <p>

        </p>
    </div>
    <fieldset>
        <legend>儲備金和換錢金</legend>
        <div id="reserve_cash">
            儲備金資訊：
            <?php
            session_start();

            // 連線MySQL
            include "connect_mysql.php";

            // 儲備金資料
            echo "<table border=\"1\" id=\"cash_reserve_table\">
            <tr>
                <th>開始日期</th>
                <th>1元</th>
                <th>5元</th>
                <th>10元</th>
                <th>50元</th>
                <th>100元</th>
                <th>500元</th>
                <th>1000元</th>
                <th>總計(元)</th>
                <th>修改</th>
            </tr>";

            // 輸出儲備金資料
            $data = [$_SESSION["restaurant_id"]];
            $sql = "SELECT * FROM cash_reserve_management WHERE restaurant_id=? ORDER BY id DESC LIMIT 1";
            $sth = $link->prepare($sql);
            try {
                $sth->execute($data);
                if ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                    <td align='center' id=\"cash_reserve_start_date\" valign=\"middle\">&emsp;{$result["start_date"]}&emsp;</td>
                    <td align='center' id=\"cash_reserve_ones\" valign=\"middle\">&emsp;{$result["ones"]}&emsp;</td>
                    <td align='center' id=\"cash_reserve_five\" valign=\"middle\">&emsp;{$result["five"]}&emsp;</td>
                    <td align='center' id=\"cash_reserve_ten\" valign=\"middle\">&emsp;{$result["ten"]}&emsp;</td>
                    <td align='center' id=\"cash_reserve_fifty\" valign=\"middle\">&emsp;{$result["fifty"]}&emsp;</td>
                    <td align='center' id=\"cash_reserve_hundred\" valign=\"middle\">&emsp;{$result["hundred"]}&emsp;</td>
                    <td align='center' id=\"cash_reserve_five_hundred\" valign=\"middle\">&emsp;{$result["five_hundred"]}&emsp;</td>
                    <td align='center' id=\"cash_reserve_thousand\" valign=\"middle\">&emsp;{$result["thousand"]}&emsp;</td>
                    <td align='center' id=\"cash_reserve_total\" valign=\"middle\">&emsp;{$result["total"]}&emsp;</td>
                    <td align='center' valign=\"middle\"><div id=\"cash_reserve_change_button\">&nbsp;<input type=\"button\" onclick=\"modifyCashReserve()\" value=\"修改\">&nbsp;</div></td>";

                    echo "</tr>";
                } else {
                    echo "<script>alert(\"無法獲取儲備金資訊\");</script>";
                }
            } catch (PDOException $e) {
                echo "<script>alert(\"無法獲取完整的儲備金資訊：{$e->getMessage()}\");</script>";
            }
            echo "</table><br/>";
            ?>
        </div>
        <br />
        <div id="exchange_cash">
            換錢金資訊：
            <?php
            // 換錢金資料
            echo "<table border=\"1\" id=\"exchange_cash_table\">
            <tr>
                <th>開始日期</th>
                <th>總計(元)</th>
                <th>修改</th>
            </tr>";

            // 輸出換錢金資料
            $data = [$_SESSION["restaurant_id"]];
            $sql = "SELECT * FROM exchange_cash_management WHERE restaurant_id=? ORDER BY id DESC LIMIT 1";
            $sth = $link->prepare($sql);
            try {
                $sth->execute($data);
                if ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                    <td align='center' id=\"exchange_cash_start_date\" valign=\"middle\">&emsp;{$result["start_date"]}&emsp;</td>
                    <td align='center' id=\"exchange_cash_total\" valign=\"middle\">&emsp;{$result["total"]}&emsp;</td>
                    <td align='center' valign=\"middle\"><div id=\"exchange_cash_change_button\">&nbsp;<input type=\"button\" onclick=\"modifyExchangeCash()\" value=\"修改\">&nbsp;</div></td>";

                    echo "</tr>";
                } else {
                    echo "<script>alert(\"無法獲取換錢金資訊\");</script>";
                }
            } catch (PDOException $e) {
                echo "<script>alert(\"無法獲取完整的換錢金資訊：{$e->getMessage()}\");</script>";
            }
            echo "</table>";
            ?>
        </div>
    </fieldset>
    <br />
    <fieldset>
        <legend>廠商金和憑證類型</legend>
        <div id="company_cash">
            廠商金資訊：
            <?php
            // 廠商金資料
            echo "<table border=\"1\" id=\"company_cash_table\">
            <tr>
                <th>開始日期</th>
                <th>總計(元)</th>
                <th>修改</th>
            </tr>";

            // 輸出廠商金資料
            $data = [$_SESSION["restaurant_id"]];
            $sql = "SELECT * FROM company_cash_management WHERE restaurant_id=? ORDER BY id DESC LIMIT 1";
            $sth = $link->prepare($sql);
            try {
                $sth->execute($data);
                if ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                    <td align='center' id=\"company_cash_start_date\" valign=\"middle\">&emsp;{$result["start_date"]}&emsp;</td>
                    <td align='center' id=\"company_cash_total\" valign=\"middle\">&emsp;{$result["total"]}&emsp;</td>
                    <td align='center' valign=\"middle\"><div id=\"company_cash_change_button\">&nbsp;<input type=\"button\" onclick=\"modifyCompanyCash()\" value=\"修改\">&nbsp;</div></td>";

                    echo "</tr>";
                } else {
                    echo "<script>alert(\"無法獲取廠商金資訊\");</script>";
                }
            } catch (PDOException $e) {
                echo "<script>alert(\"無法獲取完整的廠商金資訊：{$e->getMessage()}\");</script>";
            }
            echo "</table>";
            ?>
        </div>
        <br />
        <div id="receipt_type">
            所有憑證類型：
            <?php
            // 憑證類型資料
            echo "<table border=\"1\" id=\"receipt_type_table\">
            <tr>
                <th>憑證類型編號</th>
                <th>憑證類型名稱</th>
                <th>修改/刪除</th>
            </tr>";

            // 輸出憑證類型資料
            $sql = "SELECT * FROM receipt_type_management ORDER BY id ASC";
            $sth = $link->prepare($sql);
            try {
                $sth->execute();
                $i = 0;
                while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $i++;
                    echo "<tr>
                    <td align='center' id=\"receipt_type_id{$result["id"]}\" valign=\"middle\">&emsp;{$i}&emsp;</td>
                    <td align='center' id=\"receipt_type_class{$result["id"]}\" valign=\"middle\">&emsp;{$result["class"]}&emsp;</td>
                    <td align='center' valign=\"middle\"><div id=\"receipt_type_change_button{$result["id"]}\">&nbsp;<input type=\"button\" onclick=\"modifyReceiptType('{$result["id"]}')\" value=\"修改\">&nbsp;<input type=\"button\" onclick=\"deleteReceiptType('{$result["id"]}')\" value=\"刪除\">&nbsp;</div></td>";

                    echo "</tr>";
                }
            } catch (PDOException $e) {
                echo "<script>alert(\"無法獲取完整的憑證類型資訊：{$e->getMessage()}\");</script>";
            }
            echo "</table>";
            $link = NULL;
            ?>
            <input id="receipt_type_add_button" type="button" onclick="addReceiptType()" value="新增憑證類型">
        </div>
    </fieldset>
    <script>
        function modifyCashReserve() {
            var oriStartDate = $("#cash_reserve_start_date").html().trim();
            var oriOne = $("#cash_reserve_ones").html().trim();
            var oriFive = $("#cash_reserve_five").html().trim();
            var oriTen = $("#cash_reserve_ten").html().trim();
            var oriFifty = $("#cash_reserve_fifty").html().trim();
            var oriHundred = $("#cash_reserve_hundred").html().trim();
            var oriFiveHundred = $("#cash_reserve_five_hundred").html().trim();
            var oriThousand = $("#cash_reserve_thousand").html().trim();
            var oriTotal = $("#cash_reserve_total").html().trim();

            var width = 40;
            $("#cash_reserve_start_date").html("&emsp;<?php echo date("Y-m-d"); ?>&emsp;");
            $("#cash_reserve_ones").html("<input type=\"number\" name=\"cash_reserve_ones\" value=\"" + oriOne + "\" placeholder=\"請填入1元個數\" style=\"width:" + width + "px\" required>");
            $("#cash_reserve_five").html("<input type=\"number\" name=\"cash_reserve_five\" value=\"" + oriFive + "\" placeholder=\"請填入5元個數\" style=\"width:" + width + "px\" required>");
            $("#cash_reserve_ten").html("<input type=\"number\" name=\"cash_reserve_ten\" value=\"" + oriTen + "\" placeholder=\"請填入10元個數\" style=\"width:" + width + "px\" required>");
            $("#cash_reserve_fifty").html("<input type=\"number\" name=\"cash_reserve_fifty\" value=\"" + oriFifty + "\" placeholder=\"請填入50元個數\" style=\"width:" + width + "px\" required>");
            $("#cash_reserve_hundred").html("<input type=\"number\" name=\"cash_reserve_hundred\" value=\"" + oriHundred + "\" placeholder=\"請填入100元張數\" style=\"width:" + width + "px\" required>");
            $("#cash_reserve_five_hundred").html("<input type=\"number\" name=\"cash_reserve_five_hundred\" value=\"" + oriFiveHundred + "\" placeholder=\"請填入500元張數\" style=\"width:" + width + "px\" required>");
            $("#cash_reserve_thousand").html("<input type=\"number\" name=\"cash_reserve_thousand\" value=\"" + oriThousand + "\" placeholder=\"請填入1000元張數\" style=\"width:" + width + "px\" required>");

            $("#cash_reserve_change_button").html("&nbsp;<input type=\"button\" onclick=\"confirmModifyCashReserve()\" value=\"儲存\">&nbsp;<input type=\"button\" onclick=\"cancelModifyCashReserve('" + oriStartDate + "', '" + oriOne + "', '" + oriFive + "', '" + oriTen + "', '" + oriFifty + "', '" + oriHundred + "', '" + oriFiveHundred + "', '" + oriThousand + "', '" + oriTotal + "')\" value=\"取消\">&nbsp;");

            $(function() {
                <?php
                for ($i = 0; $i < 7; $i++) {
                    switch ($i) {
                        case 0:
                            $num = "ones";
                            break;
                        case 1:
                            $num = "five";
                            break;
                        case 2:
                            $num = "ten";
                            break;
                        case 3:
                            $num = "fifty";
                            break;
                        case 4:
                            $num = "hundred";
                            break;
                        case 5:
                            $num = "five_hundred";
                            break;
                        case 6:
                            $num = "thousand";
                            break;
                    }
                    echo "$(\"input[name='cash_reserve_{$num}']\").blur(function() {
                        var one = $(\"input[name='cash_reserve_ones']\").val();
                        var five = $(\"input[name='cash_reserve_five']\").val();
                        var ten = $(\"input[name='cash_reserve_ten']\").val();
                        var fifty = $(\"input[name='cash_reserve_fifty']\").val();
                        var hundred = $(\"input[name='cash_reserve_hundred']\").val();
                        var fiveHundred = $(\"input[name='cash_reserve_five_hundred']\").val();
                        var thousand = $(\"input[name='cash_reserve_thousand']\").val();

                        var total = parseInt(one) + parseInt(five*5) + parseInt(ten*10) + parseInt(fifty*50) + parseInt(hundred*100) + parseInt(fiveHundred*500) + parseInt(thousand*1000);
                        $(\"#cash_reserve_total\").html(\"&emsp;\" + total + \"&emsp;\");
                    });
                    
                    ";
                }
                ?>
            });
        }

        function confirmModifyCashReserve() {
            var one = $("input[name='cash_reserve_ones']").val();
            var five = $("input[name='cash_reserve_five']").val();
            var ten = $("input[name='cash_reserve_ten']").val();
            var fifty = $("input[name='cash_reserve_fifty']").val();
            var hundred = $("input[name='cash_reserve_hundred']").val();
            var fiveHundred = $("input[name='cash_reserve_five_hundred']").val();
            var thousand = $("input[name='cash_reserve_thousand']").val();
            var total = parseInt(one) + parseInt(five * 5) + parseInt(ten * 10) + parseInt(fifty * 50) + parseInt(hundred * 100) + parseInt(fiveHundred * 500) + parseInt(thousand * 1000);

            if (window.confirm("以下是即將更新的內容：\n1元：" + one + "個\n5元：" + five + "個\n10元：" + ten + "個\n50元：" + fifty + "個\n100元：" + hundred + "張\n500元：" + fiveHundred + "張\n1000元：" + thousand + "張\n總計：" + total + "元\n\n是否確認要更新儲備金資訊？")) {
                $.post("modify_cash_reserve.php", {
                    ones: one,
                    five: five,
                    ten: ten,
                    fifty: fifty,
                    hundred: hundred,
                    five_hundred: fiveHundred,
                    thousand: thousand,
                    total: total
                }, function(result) {
                    alert(result);
                    document.location.href = "./sundries_management.php";
                });
            }
        }

        function cancelModifyCashReserve(oriStartDate, oriOne, oriFive, oriTen, oriFifty, oriHundred, oriFiveHundred, oriThousand, oriTotal) {
            $("#cash_reserve_start_date").html("&emsp;" + oriStartDate + "&emsp;");
            $("#cash_reserve_ones").html("&emsp;" + oriOne + "&emsp;");
            $("#cash_reserve_five").html("&emsp;" + oriFive + "&emsp;");
            $("#cash_reserve_ten").html("&emsp;" + oriTen + "&emsp;");
            $("#cash_reserve_fifty").html("&emsp;" + oriFifty + "&emsp;");
            $("#cash_reserve_hundred").html("&emsp;" + oriHundred + "&emsp;");
            $("#cash_reserve_five_hundred").html("&emsp;" + oriFiveHundred + "&emsp;");
            $("#cash_reserve_thousand").html("&emsp;" + oriThousand + "&emsp;");
            $("#cash_reserve_total").html("&emsp;" + oriTotal + "&emsp;");
            $("#cash_reserve_change_button").html("&nbsp;<input type=\"button\" onclick=\"modifyCashReserve()\" value=\"修改\">&nbsp;");
        }

        function modifyExchangeCash() {
            var oriStartDate = $("#exchange_cash_start_date").html().trim();
            var oriTotal = $("#exchange_cash_total").html().trim();

            var width = 70;
            $("#exchange_cash_start_date").html("&emsp;<?php echo date("Y-m-d"); ?>&emsp;");
            $("#exchange_cash_total").html("<input type=\"number\" name=\"exchange_cash_total\" value=\"" + oriTotal + "\" placeholder=\"請填入換錢金總額\" style=\"width:" + width + "px\" required>");

            $("#exchange_cash_change_button").html("&nbsp;<input type=\"button\" onclick=\"confirmModifyExchangeCash()\" value=\"儲存\">&nbsp;<input type=\"button\" onclick=\"cancelModifyExchangeCash('" + oriStartDate + "', '" + oriTotal + "')\" value=\"取消\">&nbsp;");
        }

        function confirmModifyExchangeCash() {
            var total = $("input[name='exchange_cash_total']").val();

            if (window.confirm("以下是即將更新的內容：\n總計：" + total + "元\n\n是否確認要更新換零金資訊？")) {
                $.post("modify_exchange_cash.php", {
                    total: total
                }, function(result) {
                    alert(result);
                    document.location.href = "./sundries_management.php";
                });
            }
        }

        function cancelModifyExchangeCash(oriStartDate, oriTotal) {
            $("#exchange_cash_start_date").html("&emsp;" + oriStartDate + "&emsp;");
            $("#exchange_cash_total").html("&emsp;" + oriTotal + "&emsp;");
            $("#exchange_cash_change_button").html("&nbsp;<input type=\"button\" onclick=\"modifyExchangeCash()\" value=\"修改\">&nbsp;");
        }

        function modifyCompanyCash() {
            var oriStartDate = $("#company_cash_start_date").html().trim();
            var oriTotal = $("#company_cash_total").html().trim();

            var width = 70;
            $("#company_cash_start_date").html("&emsp;<?php echo date("Y-m-d"); ?>&emsp;");
            $("#company_cash_total").html("<input type=\"number\" name=\"company_cash_total\" value=\"" + oriTotal + "\" placeholder=\"請填入換錢金總額\" style=\"width:" + width + "px\" required>");

            $("#company_cash_change_button").html("&nbsp;<input type=\"button\" onclick=\"confirmModifyCompanyCash()\" value=\"儲存\">&nbsp;<input type=\"button\" onclick=\"cancelModifyCompanyCash('" + oriStartDate + "', '" + oriTotal + "')\" value=\"取消\">&nbsp;");
        }

        function confirmModifyCompanyCash() {
            var total = $("input[name='company_cash_total']").val();

            if (window.confirm("以下是即將更新的內容：\n總計：" + total + "元\n\n是否確認要更新廠商金資訊？")) {
                $.post("modify_company_cash.php", {
                    total: total
                }, function(result) {
                    alert(result);
                    document.location.href = "./sundries_management.php";
                });
            }
        }

        function cancelModifyCompanyCash(oriStartDate, oriTotal) {
            $("#company_cash_start_date").html("&emsp;" + oriStartDate + "&emsp;");
            $("#company_cash_total").html("&emsp;" + oriTotal + "&emsp;");
            $("#company_cash_change_button").html("&nbsp;<input type=\"button\" onclick=\"modifyCompanyCash()\" value=\"修改\">&nbsp;");
        }

        function modifyReceiptType(id) {
            var oriId = $("#receipt_type_id" + id).html().trim();
            var oriClass = $("#receipt_type_class" + id).html().trim();

            var width = 70;
            $("#receipt_type_class" + id).html("<input type=\"text\" name=\"receipt_type_class" + id + "\" value=\"" + oriClass + "\" placeholder=\"請填入憑證類型名稱\" style=\"width:" + width + "px\" required>");

            $("#receipt_type_change_button" + id).html("&nbsp;<input type=\"button\" onclick=\"confirmModifyReceiptType('" + id + "')\" value=\"儲存\">&nbsp;<input type=\"button\" onclick=\"cancelModifyReceiptType('" + id + "', '" + oriClass + "')\" value=\"取消\">&nbsp;");
        }

        function confirmModifyReceiptType(id) {
            var item = $('#receipt_type_id' + id).html().trim();
            var name = $("input[name='receipt_type_class" + id + "']").val();

            if (window.confirm("以下是即將更新的內容：\n憑證類型編號：" + item + "\n憑證類型名稱：" + name + "\n\n是否確定要更新此憑證類型的資訊？")) {
                $.post("modify_receipt_type.php", {
                    id: id,
                    class: name
                }, function(result) {
                    alert(result);
                    document.location.href = "./sundries_management.php";
                });
            }
        }

        function cancelModifyReceiptType(id, oriClass) {
            $("#receipt_type_class" + id).html("&emsp;" + oriClass + "&emsp;");
            $("#receipt_type_change_button" + id).html("&nbsp;<input type=\"button\" onclick=\"modifyReceiptType('" + id + "')\" value=\"修改\">&nbsp;<input type=\"button\" onclick=\"deleteReceiptType('" + id + "')\" value=\"刪除\">&nbsp;")
        }

        function addReceiptType() {
            $('#receipt_type_add_button').remove();
            var width = 100;
            $('#receipt_type_table').children('tbody').append("<tr id=\"new_receipt_type_info\"> <td align=\"center\">新</td><td align='center' valign=\"middle\"><input type=\"text\" name=\"new_receipt_type_class\" placeholder=\"請填入新的憑證類型名稱\" style=\"width:" + width + "px\" required></td></tr>")
            $('#receipt_type_table').parent().append("<div id=\"new_receipt_type_button\">&nbsp;<input type=\"button\" onclick=\"confirmAddReceiptType()\" value=\"新增\">&nbsp;<input type=\"button\" onclick=\"cancelAddReceiptType()\" value=\"取消\">&nbsp;</div>");
        }

        function cancelAddReceiptType() {
            if ($("input[name='new_receipt_type_class']").val()) {
                if (window.confirm("資料尚未儲存，是否確定要取消？")) {
                    $('#new_receipt_type_info').remove();
                    $('#new_receipt_type_button').remove();
                    $('#receipt_type_table').after("<input id=\"receipt_type_add_button\" type=\"button\" onclick=\"addReceiptType()\" value=\"新增憑證類型\">");
                }
            } else {
                $('#new_receipt_type_info').remove();
                $('#new_receipt_type_button').remove();
                $('#receipt_type_table').after("<input id=\"receipt_type_add_button\" type=\"button\" onclick=\"addReceiptType()\" value=\"新增憑證類型\">");
            }
        }

        function confirmAddReceiptType() {
            var name = $("input[name='new_receipt_type_class']").val();

            if (window.confirm("即將新增：\n憑證類型名稱：" + name + "\n\n是否確定要新增此憑證類型？")) {
                $.post("add_receipt_type.php", {
                    class: name
                }, function(result) {
                    alert(result);
                    document.location.href = "./sundries_management.php";
                });
            }
        }

        function deleteReceiptType(id) {
            var item = $('#receipt_type_id' + id).html().trim();
            var name = $("#receipt_type_class" + id).html().trim();

            if (window.confirm("即將刪除：\n憑證類型編號：" + item + "\n憑證類型名稱：" + name + "\n\n是否確定要刪除此憑證類型？")) {
                $.post("delete_receipt_type.php", {
                    id: id
                }, function(result) {
                    alert(result);
                    document.location.href = "./sundries_management.php";
                });
            }
        }
    </script>
</body>

</html>