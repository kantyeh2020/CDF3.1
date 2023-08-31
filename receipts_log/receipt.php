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
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script>
    <title>項目0-憑證登錄</title>
</head>

<body>
    <div id="header">
        <h2>項目(0/1) 憑證登錄</h2>
    </div>
    <div id="intro">
        <p>(1) 請拿出要登錄的憑證。<br />
            (2) 參考下方的科目代碼，填寫憑證的<b>母科目、子科目</b>和<b>費用別</b>資訊到表格中。<br />
            (3) 選擇<b>憑證類型</b>。若憑證類型是<b>發票</b>，則請填入發票編號。<br />
            (4) 填入<b>付款日期</b>。付款日期不是憑證上寫的日期，而是實際將錢交付給廠商的日期。<br />
            (5) 依序填入<b>廠商名稱、支付金額</b>及<b>費用明細</b>。(費用明細應填入憑證上商品的條目和數量)<br />
            (6) 以上皆完成後請拍一張憑證的照片並上傳。<br />
            (7) 完成後請按下<b>送出</b>。<br />
            (8) 此時下方<b>今日已上繳的憑證簡介</b>的表格中應該要出現剛才送出的憑證。<br />
            <font size="2">註1：在<b>費用明細</b>中，至多填入25個中文字或50個英文字；在<b>備註</b>中，至多填入100個中文字或200個英文字；</font><br />
            <font size="2">註2：在<b>今日已上繳的憑證簡介</b>的表格中，僅自己提交的憑證可以被刪除。</font><br />
            <font size="2">註3：注意不要重複提交憑證。</font>
        </p>
    </div>
    <form action="receipt_to_mysql.php" enctype="multipart/form-data" method="post" id="form">
        <fieldset>
            <legend>輸入憑證內容</legend>
            <table border="1">
                <tr>
                    <td align="center">*母科目</td>
                    <td align="center">
                        <?php
                        // 費用歸屬母專案科目
                        if (!isset($_SESSION["main_project"]) || $_SESSION["main_project"] == NULL) {
                            echo "
                            <select name=\"main_project\" id=\"main_project\" required>
                                <option value=\"0\">---請選擇---</option>
                                <option value=\"AM01_營業費用\">AM01_營業費用</option>
                                <option value=\"AM02_行銷費用\">AM02_行銷費用</option>
                                <option value=\"AM03_人事成本\">AM03_人事成本</option>
                                <option value=\"AM04_福利費用\">AM04_福利費用</option>
                            </select><br />
                        ";
                        } else {
                            echo "
                            <select name=\"main_project\" id=\"main_project\" required>
                            <option value=\"0\">---請選擇---</option>";
                            $data = array("AM01_營業費用", "AM02_行銷費用", "AM03_人事成本", "AM04_福利費用");
                            for ($i = 0; $i < count($data); $i++) {
                                if ($data[$i] == $_SESSION["main_project"]) {
                                    echo "<option value=\"{$data[$i]}\" selected>{$data[$i]}</option>";
                                } else {
                                    echo "<option value=\"{$data[$i]}\">{$data[$i]}</option>";
                                }
                            }
                            echo "</select><br />
                        ";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*子科目</td>
                    <td align="center">
                        <?php
                        // 費用歸屬子專案科目
                        if (!isset($_SESSION["sub_project"]) || $_SESSION["sub_project"] == NULL) {
                            echo "
                            <select name=\"sub_project\" id=\"sub_project\" required>
                                <option value=\"0\">---請選擇---</option>
                            </select><br />
                            ";
                        } else {
                            echo "
                            <select name=\"sub_project\" id=\"sub_project\" required>
                                <script>
                                    $(function() {
                                        $.post('sub_project_id.php', {
                                            main_project: \"{$_SESSION['main_project']}\"
                                        }, function(data) {
                                            $('#sub_project').html(\"\");
                                            $('#subject_id').html(\"\");
                                            var len = data.length;
                                            var sub_project_html = '<option value=\"0\">---請選擇---</option>';
                                            for (var i = 0; i < len; i++) {
                                                if (data[i] == \"{$_SESSION['sub_project']}\") {
                                                    sub_project_html += '<option value=\"' + data[i] + '\" selected>' + data[i] + '</option>';
                                                } else {
                                                    sub_project_html += '<option value=\"' + data[i] + '\">' + data[i] + '</option>';
                                                }
                                            }
                                            $('#sub_project').html(sub_project_html);
                                        }, 'json')
                                    })
                                </script>
                            </select><br />
                            ";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*費用別</td>
                    <td align="center">
                        <?php
                        // 費用歸屬費用別：
                        if (!isset($_SESSION["subject_id"]) || $_SESSION["subject_id"] == NULL) {
                            echo "
                            <select name=\"subject_id\" id=\"subject_id\" required>
                                <option value=\"0\">---請選擇---</option>
                            </select><br />
                            ";
                        } else {
                            echo "
                            <select name=\"subject_id\" id=\"subject_id\" required>
                                <script>
                                    $(function() {
                                        $.post('subject_id.php', {
                                            sub_project: \"{$_SESSION['sub_project']}\"
                                        }, function(data) {
                                            $('#subject_id').html(\"\");
                                            var len = data.length;
                                            var subject_id_html = '<option value=\"0\">---請選擇---</option>';
                                            for (var i = 0; i < len; i++) {
                                                if (data[i] == \"{$_SESSION['subject_id']}\") {
                                                    subject_id_html += '<option value=\"' + data[i] + '\" selected>' + data[i] + '</option>';
                                                } else {
                                                    subject_id_html += '<option value=\"' + data[i] + '\">' + data[i] + '</option>';
                                                }
                                            }
                                            $('#subject_id').html(subject_id_html);
                                        }, 'json');
                                    })
                                </script>
                            </select><br />
                            ";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*憑證類型</td>
                    <td align="center">
                        <?php
                        // 憑證類型
                        for ($i = 1; $i <= $_SESSION["receipt_type_number"]; $i++) {
                            if (isset($_SESSION["receipt_type"]) && $_SESSION["receipt_type"] = $_SESSION["receipt_type_{$i}"]) {
                                echo "<label><input type=\"radio\" id=\"receipt_type_{$i}\" onclick=isReceipt($i) name=\"receipt_type\" value=\"{$_SESSION["receipt_type_{$i}"]}\" checked required>{$_SESSION["receipt_type_{$i}"]}</label>";
                            } else {
                                echo "<label><input type=\"radio\" id=\"receipt_type_{$i}\" onclick=isReceipt($i) name=\"receipt_type\" value=\"{$_SESSION["receipt_type_{$i}"]}\" required>{$_SESSION["receipt_type_{$i}"]}</label>";
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*發票編號</td>
                    <td align="center">
                        <?php
                        // 發票編號
                        if (isset($_SESSION["receipt_type"]) && $_SESSION["receipt_type"] == "發票") {
                            echo "<div id=\"receipt_id_number\"><input type=\"text\" name=\"receipt_id_number\" value=\"{$_SESSION["receipt_id_number"]}\" placeholder=\"請輸入發票編號\" required></div>";
                        } else {
                            echo "<div id=\"receipt_id_number\">僅須在登錄發票的時候填寫。</div>";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*付款日期</td>
                    <td align="center">
                        <?php
                        // 付款日期
                        $today = date("Y-m-d");
                        if (!isset($_SESSION["buying_date"]) || $_SESSION["buying_date"] == NULL) {
                            echo "<input type=\"date\" name=\"buying_date\" value=\"{$today}\"><br />";
                        } else {
                            echo "<input type=\"date\" name=\"buying_date\" value=\"{$_SESSION["buying_date"]}\"><br />";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*廠商名稱</td>
                    <td align="center">
                        <?php
                        // 廠商名稱
                        echo "<input type=\"text\" name=\"selling_company\" value=\"{$_SESSION['selling_company']}\" placeholder=\"請輸入廠商名稱\" required><br />";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*支付金額(元)</td>
                    <td align="center">
                        <?php
                        // 支付金額
                        echo "<input type=\"number\" name=\"total_price\" value=\"{$_SESSION['total_price']}\" placeholder=\"請輸入支付金額\" required><br />";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*支付方式</td>
                    <td align="center">
                        <?php
                        // 支付方式
                        if (isset($_SESSION["payment_method"]) && $_SESSION["payment_method"] != NULL) {
                            switch ($_SESSION["payment_method"]) {
                                case "廠商金支付":
                                    echo "
                                    <label><input type=\"radio\" name=\"payment_method\" value=\"廠商金支付\" checked required>廠商金支付</label>
                                    <label><input type=\"radio\" name=\"payment_method\" value=\"換錢金支付\" required>換錢金支付</label><br />
                                    ";
                                    break;
                                case "換錢金支付":
                                    echo "
                                    <label><input type=\"radio\" name=\"payment_method\" value=\"廠商金支付\" required>廠商金支付</label>
                                    <label><input type=\"radio\" name=\"payment_method\" value=\"換錢金支付\" checked required>換錢金支付</label><br />
                                    ";
                                    break;
                                default:
                                    echo "
                                    <label><input type=\"radio\" name=\"payment_method\" value=\"廠商金支付\" checked required>廠商金支付</label>
                                    <label><input type=\"radio\" name=\"payment_method\" value=\"換錢金支付\" required>換錢金支付</label><br />
                                    ";
                                    break;
                            }
                        } else {
                            echo "
                            <label><input type=\"radio\" name=\"payment_method\" value=\"廠商金支付\" checked required>廠商金支付</label>
                            <label><input type=\"radio\" name=\"payment_method\" value=\"換錢金支付\" required>換錢金支付</label><br />
                            ";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*費用明細<br />(至多25個中文字)</td>
                    <td align="center">
                        <?php
                        // 費用明細
                        echo "<div>
                        <textarea name=\"detail\" style=\"font-family:sans-serif;font-size:1.2em;\" placeholder=\"請輸入費用明細\" required>{$_SESSION['detail']}</textarea>
                        </div>";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">*憑證照片</td>
                    <td align="center">
                        <?php
                        // 憑證照片
                        if (isset($_SESSION["receipt_pic"]) && $_SESSION["receipt_pic"] != NULL) {
                            echo "(除非想要更換照片，否則不需要再次上傳)<br/>";
                            echo "<img src=\"data:image/jpeg;base64,{$_SESSION["receipt_pic"]}\" width=\"200\"/><br/>";
                            echo "<input type=\"file\" name=\"receipt_pic\" accept=\"image/*\"><br />";
                        } else {
                            echo "<input type=\"file\" name=\"receipt_pic\" accept=\"image/*\" required><br />";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="center">備註<br />(至多100個中文字)</td>
                    <td align="center">
                        <?php
                        // 備註
                        echo "<div>
                        <textarea name=\"comments\" style=\"font-family:sans-serif;font-size:1.2em;\">{$_SESSION['comments']}</textarea>
                        </div>";
                        ?>
                    </td>
                </tr>
            </table>
            <br />
            <input type="submit" id="input" value="送出">
        </fieldset>
        <br>
        <a href="./receipt_reroute.php"><input type="button" value="返回分流頁面"></a>
        <a href="./cash_remain.php"><input type="button" value="前往剩餘廠商金登錄"></a>
    </form><br />

    <script>
        $(function() {
            $('#main_project').blur(function() {
                $.post('sub_project_id.php', {
                    main_project: $('#main_project').val()
                }, function(data) {
                    $('#sub_project').html("");
                    $('#subject_id').html("");
                    var len = data.length;
                    var sub_project_html = '<option value="0">---請選擇---</option>';
                    for (var i = 0; i < len; i++) {
                        sub_project_html += '<option value="' + data[i] + '">' + data[i] + '</option>';
                    }
                    $('#sub_project').html(sub_project_html);

                    $('#sub_project').blur(function() {
                        $.post('subject_id.php', {
                            sub_project: $('#sub_project').val()
                        }, function(data) {
                            $('#subject_id').html("");
                            var len = data.length;
                            var subject_id_html = '<option value="0">---請選擇---</option>';
                            for (var i = 0; i < len; i++) {
                                subject_id_html += '<option value="' + data[i] + '">' + data[i] + '</option>';
                            }
                            $('#subject_id').html(subject_id_html);
                        }, 'json');
                    });


                }, 'json');

            });
        });

        $(function() {
            $('#sub_project').blur(function() {
                $.post('subject_id.php', {
                    sub_project: $('#sub_project').val()
                }, function(data) {
                    $('#subject_id').html("");
                    var len = data.length;
                    var subject_id_html = '<option value="0">---請選擇---</option>';
                    for (var i = 0; i < len; i++) {
                        subject_id_html += '<option value="' + data[i] + '">' + data[i] + '</option>';
                    }
                    $('#subject_id').html(subject_id_html);
                }, 'json');
            });
        });

        function isReceipt($i) {
            if ($i == 1) {
                $('#receipt_id_number').html("");
                $('#receipt_id_number').html("<input type=\"text\" name=\"receipt_id_number\" value=\"<?php session_start();
                                                                                                        echo $_SESSION["receipt_id_number"]; ?>\" placeholder=\"請輸入發票編號\" required>");
            } else {
                $('#receipt_id_number').html("");
                $('#receipt_id_number').html("僅須在登錄發票的時候填寫。");
            }
        }

        function deleteReceipt($i, $receipt_id) {
            if (window.confirm("確認要刪除項次為 " + $i + " 的憑證嗎？")) {
                $.post("delete_receipt.php", {
                    receipt_id: $receipt_id
                }, function(result) {
                    alert(result);
                    document.location.href = "./receipt.php";
                });
            }
        }
    </script>

    <div id="uploaded-receipts">
        <h3>今日已上繳的憑證簡介：</h3>
        <?php
        echo "<table border=\"1\">
        <tr>
            <th align=\"center\">&nbsp;項次&nbsp;</th>
            <th align=\"center\">&nbsp;憑證類型&nbsp;</th>
            <th align=\"center\">&nbsp;廠商名稱&nbsp;</th>
            <th align=\"center\">&nbsp;支付金額&nbsp;<br/>&nbsp;(元)&nbsp;</th>
            <th align=\"center\">&nbsp;支付方式&nbsp;</th>
            <th align=\"center\">&nbsp;費用明細&nbsp;</th>
            <th align=\"center\">&nbsp;刪除&nbsp;</th>
        </tr>";

        // 連線MySQL
        include "connect_mysql.php";

        // 查詢當日、當店上傳的資料
        $data = [$_SESSION["restaurant_id"]];
        $sql = 'SELECT * FROM receipt WHERE process_status="correct" AND input_date>=(CURDATE()) AND restaurant_id=? ORDER BY receipt_id ASC';
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        $i = 0;
        $total_price = 0;
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $i++;
            $total_price += $result['total_price'];
            $detail = str_replace("\n", "&nbsp;<br/>&nbsp;", $result['detail']);
            echo "<tr>
            <td align=\"center\">{$i}</td>
            <td align=\"center\">&nbsp;{$result['receipt_type']}&nbsp;</td>
            <td align=\"center\">&nbsp;{$result['selling_company']}&nbsp;</td>
            <td align=\"center\">&nbsp;" . number_format($result["total_price"], 0, ".", ",") . "&nbsp;</td>
            <td align=\"center\">&nbsp;{$result['payment_method']}&nbsp;</td>
            <td>&nbsp;{$detail}&nbsp;</td>";
            if ($result["emp_id"] == $_SESSION["emp_id"]) {
                echo "<td align=\"center\">&nbsp;<button onclick=\"deleteReceipt({$i}, {$result["receipt_id"]})\" id=\"{$i}\">刪除</button>&nbsp;</td>
            </tr>";
            } else {
                echo "<td align=\"center\">&nbsp;僅登錄人可刪除&nbsp;</td>
            </tr>";
            }
        }
        echo "<tr>
        <td colspan=\"7\" align=\"center\">以上{$i}項憑證，共計" . number_format($total_price, 0, ".", ",") . "元。</td>
        </tr>
        </table><br/>";
        $link = NULL;
        ?>
    </div>
</body>

</html>