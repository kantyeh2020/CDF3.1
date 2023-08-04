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
if ($_SESSION["crevenue_to_rs_check_point"] == false) {
    header("location: cash_revenue.php");
    exit;
}

// Check Point狀態檢查
if ($_SESSION["rs_to_rdc_check_point"] == true) {
    header("location: revenue_double_check.php");
    exit;
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>項目7-當日全部現金裝袋與提交資料</title>
</head>

<body>
    <div id="header">
        <h2>項目(7/7) 當日全部現金裝袋與提交資料</h2>
    </div>
    <div id="intro">
        <p>(1) 拿出一個透明密封袋、剩餘的現金營收、小費和小白單。<br />
            (2) 確認當前手上現金營收和小費的總額為<b>
                <?php session_start();
                $crevenueTotal = $_SESSION["cash_revenue"] + $_SESSION["crevenue_change_filled"] + $_SESSION["crevenue_fee"];
                echo number_format($crevenueTotal, 0, ".", ","); ?>元整</b>。
            (若不是，則回到前方依序重新檢查錢櫃儲備金(<?php echo number_format($_SESSION["dCRM_total"], 0, ".", ","); ?>元)和換錢金(<?php echo number_format($_SESSION["dECM_total"], 0, ".", ","); ?>元)是否正確)<br />
            (3) 將剩餘的現金營收、小費和小白單放入透明密封袋中。<br />
            (4) 拿出奇異筆，並在透明密封袋上依序寫下：<b><?php echo date("Y/m/d");
                                        echo "、" . number_format($crevenueTotal, 0, ".", ",") . "元";
                                        if (isset($_SESSION["crevenue_white_list"]) && $_SESSION["crevenue_white_list"] > 0) {
                                            echo "、小白單{$_SESSION["crevenue_white_list"]}張";
                                        }
                                        echo "。" ?></b><br />
            (5) 將透明密封袋拍照上傳，並收納至每日營收收納盒中。<br />
            (6) 完成後請按下<b>提交</b>。(後面還會有一個總覽頁面以供檢查提交內容。)<br /><br />
        </p>
    </div>
    <form method="post" action="rs_to_rdc_check_point.php" enctype="multipart/form-data">
        <fieldset>
            <legend>確認剩餘現金營收及小費總和</legend>
            <div class="checkbox" id="total">
                <label>
                    <input type="checkbox" name="total" value="<?php echo $crevenueTotal; ?>" required <?php if ($_SESSION["crevenue_total"] == $crevenueTotal) {
                                                                                                            echo " checked";
                                                                                                        }
                                                                                                        ?>>我已經確認手上的現金總合為<b><?php echo number_format($crevenueTotal, 0, ".", ","); ?>元</b>。
                </label>
            </div>
        </fieldset><br />
        <fieldset>
            <legend>確認已經完成裝袋動作</legend>
            <div class="checkbox" id="check"><label><input type="checkbox" name="check" required>我已經將<b>現金營收、小費和小白單</b>裝入透明袋了。</label></div>
        </fieldset><br />
        <fieldset>
            <legend>確認密封袋上的標註正確</legend>
            <div class="checkbox" id="crevenue_total">
                <label><input type="checkbox" name="check_inform" required>我確認我已經將本日結帳的相關資訊寫在透明密封袋上：<b><?php echo date("Y/m/d");
                                                                                                        echo "、" . number_format($crevenueTotal, 0, ".", ",") . "元";
                                                                                                        if (isset($_SESSION["crevenue_white_list"]) && $_SESSION["crevenue_white_list"] > 0) {
                                                                                                            echo "、小白單{$_SESSION["crevenue_white_list"]}張";
                                                                                                        }
                                                                                                        echo "。" ?></b>。</label>
            </div>
        </fieldset><br />
        <fieldset>
            <legend>照片與備註</legend>
            <?php
            if (isset($_SESSION["crevenue_pic"]) && $_SESSION["crevenue_pic"] != NULL) {
                echo "<div id=\"pic\" class=\"pic\">*照片(除非想要更換照片，否則不需要再次上傳)：<br/>";
                echo "<img src=\"data:image/jpeg;base64,{$_SESSION["crevenue_pic"]}\" width=\"200\"/><br/>";
                echo "<input type=\"file\" name=\"pic\" accept=\"image/*\"></div>";
            } else {
                echo "<div id=\"pic\" class=\"pic\">*照片(上傳乙張)：<input type=\"file\" name=\"pic\" accept=\"image/*\" required></div>";
            }
            echo "<br/>";
            echo "<div id=\"comment\" class=\"textarea\">備註：(至多100中文字或200英文字母)<br/>
            <textarea name=\"comments\" style=\"font-family:sans-serif;font-size:1.2em;\">{$_SESSION["crevenue_comments"]}</textarea>
            </div>";
            ?>
        </fieldset>
        <font size="2">*註：備註為非必填項。</font>
        <br />
        <br />
        <br />
        <a href="./cancel_crevenue_to_rs_check_point.php"><input type="button" value="上一步"></a>
        <input type="submit" value="提交">
    </form>
</body>

</html>