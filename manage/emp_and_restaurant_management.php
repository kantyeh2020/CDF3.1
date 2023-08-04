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
    <title>員工與餐廳管理</title>
</head>

<body>
    <div id="header">
        <h2>員工與餐廳管理</h2>
    </div>
    <div class="intro">
        <p>

        </p>
    </div>
    <form id="employee" method="post" action="add_employee.php">
        <fieldset>
            <legend>員工狀態</legend>
            <?php
            session_start();

            // 連線MySQL
            include "../connect_mysql.php";

            // 員工資料
            echo "<table border=\"1\" id=\"emp_table\">
            <tr>
                <th>項次</th>
                <th>員工編號</th>
                <th>姓名</th>
                <th>權限</th>
                <th>是否需要密碼</th>
                <th>修改/刪除</th>
            </tr>";

            // 讀取並輸出員工資料
            $sql = "SELECT * FROM employee WHERE authority != 'reviewer' AND emp_status = 'employed' ORDER BY authority";
            $sth = $link->prepare($sql);
            $sth->execute();
            $i = 0;
            try {
                while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $i++;
                    echo "<tr>
                    <td align=\"center\">{$i}</td>
                    <td align='center' valign=\"middle\">&emsp;{$result["emp_id"]}&emsp;</td>
                    <td id=\"emp_name{$result["emp_id"]}\" align='center' valign=\"middle\">&emsp;{$result["emp_name"]}&emsp;</td>";
                    switch ($result["authority"]) {
                        case "manager":
                            echo "<td id=\"emp_authority{$result["emp_id"]}\" align='center' valign=\"middle\">&emsp;管理者&emsp;</td>";
                            break;
                        case "recorder":
                            echo "<td id=\"emp_authority{$result["emp_id"]}\" align='center' valign=\"middle\">&emsp;普通員工&emsp;</td>";
                            break;
                    }
                    if ($result["emp_password"] != NULL) {
                        echo "<td id=\"emp_password{$result["emp_id"]}\" align='center' valign=\"middle\">是</td>";
                    } else {
                        echo "<td id=\"emp_password{$result["emp_id"]}\" align='center' valign=\"middle\">否</td>";
                    }
                    if ($result["authority"] != "manager") {
                        echo "<td align='center' valign=\"middle\"><div id=\"emp_change_button{$result["emp_id"]}\">&nbsp;<input type=\"button\" onclick=\"modifyEmployee('{$result["emp_id"]}')\" value=\"修改\">&nbsp;<input type=\"button\" onclick=\"deleteEmployee('{$result["emp_id"]}', '{$result["emp_name"]}')\" value=\"刪除\">&nbsp;</div></td>";
                    } else if ($result["emp_id"] == $_SESSION["emp_id"]) {
                        echo "<td align='center' valign=\"middle\"><div id=\"emp_change_button{$result["emp_id"]}\">&nbsp;<input type=\"button\" onclick=\"modifyEmployee('{$result["emp_id"]}')\" value=\"修改\">&nbsp;</div></td>";
                    } else {
                        echo "<td align='center' valign=\"middle\"></td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } catch (PDOException $e) {
                echo "<script>alert(\"無法獲取完整的員工資訊：{$e->getMessage()}\");</script>";
            }
            ?>
            <input id="emp_button" type="button" onclick="addEmployee()" value="新增員工">
        </fieldset>
    </form>
    <br />
    <form id="restaurant" method="post" action="add_restaurant.php">
        <fieldset>
            <legend>餐廳狀態</legend>
            <?php
            // 餐廳資料
            echo "<table border=\"1\" id=\"restaurant_table\">
            <tr>
                <th>項次</th>
                <th>餐廳代號</th>
                <th>餐廳名稱</th>
                <th>修改/刪除</th>
            </tr>";

            // 讀取並輸出餐廳資料
            $sql = "SELECT * FROM restaurant WHERE restaurant_status = 'open' ORDER BY restaurant_name";
            $sth = $link->prepare($sql);
            $sth->execute();
            $i = 0;
            try {
                while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $i++;
                    $stringArr = explode("_", $result["restaurant_name"]);
                    echo "<tr>
                    <td align=\"center\">{$i}</td>
                    <td id=\"restaurant_brand_name{$stringArr[0]}\" align='center' valign=\"middle\">&emsp;{$stringArr[0]}&emsp;</td>
                    <td id=\"restaurant_name{$stringArr[0]}\" align='center' valign=\"middle\">&emsp;{$stringArr[1]}&emsp;</td>";
                    if ($result["restaurant_name"] == $_SESSION["restaurant_name"]) {
                        echo "<td align='center' valign=\"middle\"><div id=\"restaurant_change_button{$stringArr[0]}\">&nbsp;<input type=\"button\" onclick=\"modifyRestaurant('{$stringArr[0]}')\" value=\"修改\">&nbsp;</div></td>
                    </tr>";
                    } else {
                        echo "<td align='center' valign=\"middle\"><div id=\"restaurant_change_button{$stringArr[0]}\">&nbsp;<input type=\"button\" onclick=\"modifyRestaurant('{$stringArr[0]}')\" value=\"修改\">&nbsp;<input type=\"button\" onclick=\"deleteRestaurant('{$result["restaurant_name"]}', '{$stringArr[1]}')\" value=\"刪除\">&nbsp;</div></td>
                    </tr>";
                    }
                }
                echo "</table>";
            } catch (PDOException $e) {
                echo "<script>alert(\"無法獲取完整的餐廳資訊：{$e->getMessage()}\");</script>";
            }

            $link = NULL;
            ?>
            <input id="restaurant_button" type="button" onclick="addRestaurant()" value="新增餐廳">
        </fieldset>
    </form>
    <script>
        function addEmployee() {
            $('#emp_button').remove();
            $('#emp_table').children('tbody').append("<tr id=\"new_employee_info\"> <td align=\"center\">新</td><td align = 'center' valign = \"middle\"><input type=\"text\" name=\"new_emp_id\" placeholder=\"請填入員工編號\" required></td> <td align = 'center' valign = \"middle\"><input type=\"text\" name=\"new_emp_name\" placeholder=\"請填入員工姓名\" required></td> <td align = 'center' valign = \"middle\"><select name=\"new_emp_authority\" id=\"new_emp_authority\" required> <option value = \"recorder\">普通員工</option> <option value = \"manager\">管理者</option> < /select > < /td ><td align = 'center' valign = \"middle\"><div id=\"new_emp_password\">此身份不需要密碼</div></td>< /tr >");
            $('#emp_table').parent().append("<div id=\"new_employee_button\">&nbsp;<input type=\"submit\" value=\"新增\">&nbsp;<input type=\"button\" onclick=\"cancelAddEmployee()\" value=\"取消\">&nbsp;</div>");

            $(function() {
                $("#new_emp_authority").blur(function() {
                    if ($("#new_emp_authority").val() == "manager") {
                        $("#new_emp_password").html("");
                        $("#new_emp_password").html("<input type=\"text\" name=\"new_emp_password\" placeholder=\"請填入管理者密碼\" required>");
                    } else {
                        $("#new_emp_password").html("");
                        $("#new_emp_password").html("此身份不需要密碼");
                    }
                });
            });
        }

        function addRestaurant() {
            $('#restaurant_button').remove();
            $('#restaurant_table').children('tbody').append("<tr id=\"new_restaurant_info\"> <td align=\"center\">新</td><td align = 'center' valign = \"middle\"><input type=\"text\" name=\"new_restaurant_brand_name\" placeholder=\"請填入餐廳代號\" required></td> <td align = 'center' valign = \"middle\"><input type=\"text\" name=\"new_restaurant_name\" placeholder=\"請填入餐廳名稱\" required></td>< /tr >")
            $('#restaurant_table').parent().append("<div id=\"new_restaurant_button\">&nbsp;<input type=\"submit\" value=\"新增\">&nbsp;<input type=\"button\" onclick=\"cancelAddRestaurant()\" value=\"取消\">&nbsp;</div>");
        }

        function cancelAddEmployee() {
            if ($("input[name='new_emp_id']").val() || $("input[name='new_emp_name']").val() || $("input[name='new_emp_password']").val()) {
                if (window.confirm("資料尚未儲存，是否確定要取消？")) {
                    $('#new_employee_info').remove();
                    $('#new_employee_button').remove();
                    $('#emp_table').after("<input id=\"emp_button\" type=\"button\" onclick=\"addEmployee()\" value=\"新增員工\">");
                }
            } else {
                $('#new_employee_info').remove();
                $('#new_employee_button').remove();
                $('#emp_table').after("<input id=\"emp_button\" type=\"button\" onclick=\"addEmployee()\" value=\"新增員工\">");
            }
        }

        function cancelAddRestaurant() {
            if ($("input[name='new_restaurant_brand_name']").val() || $("input[name='new_restaurant_name']").val()) {
                if (window.confirm("資料尚未儲存，是否確定要取消？")) {
                    $('#new_restaurant_info').remove();
                    $('#new_restaurant_button').remove();
                    $('#restaurant_table').after("<input id=\"restaurant_button\" type=\"button\" onclick=\"addRestaurant()\" value=\"新增餐廳\">");
                }
            } else {
                $('#new_restaurant_info').remove();
                $('#new_restaurant_button').remove();
                $('#restaurant_table').after("<input id=\"restaurant_button\" type=\"button\" onclick=\"addRestaurant()\" value=\"新增餐廳\">");
            }
        }

        function deleteEmployee(empId, name) {
            if (window.confirm("是否確認要刪除 " + name + " 的員工身份？")) {
                $.post("delete_employee.php", {
                    emp_id: empId
                }, function(result) {
                    alert(result);
                    document.location.href = "./emp_and_restaurant_management.php";
                });
            }
        }

        function deleteRestaurant(restaurantFullName, restaurantName) {
            if (window.confirm("是否確認要刪除 " + restaurantName + " ？")) {
                $.post("delete_restaurant.php", {
                    restaurant_name: restaurantFullName
                }, function(result) {
                    alert(result);
                    document.location.href = "./emp_and_restaurant_management.php";
                });
            }
        }

        function modifyEmployee(empId) {
            var a = $("#emp_name" + empId).html().trim();
            var b = $("#emp_authority" + empId).html().trim();
            var c = $("#emp_password" + empId).html().trim();
            $("#emp_name" + empId).html("<input type=\"text\" name=\"modify_emp_name" + empId + "\" value=\"" + a + "\" placeholder=\"請填入姓名\" required>");
            if (empId == "<?php echo $_SESSION["emp_id"]; ?>") {
                $("#emp_password" + empId).html("<input type=\"text\" name=\"modify_emp_password" + empId + "\" value=\"<?php echo $_SESSION["emp_password"]; ?>\" placeholder=\"請填入新的管理者密碼\" required>");
            } else {
                $("#emp_authority" + empId).html("<select name=\"modify_emp_authority" + empId + "\" id=\"modify_emp_authority" + empId + "\" required> <option value = \"recorder\">普通員工</option> <option value = \"manager\">管理者</option> < /select >");
                $("#emp_password" + empId).html("此身份不需要密碼");

                $(function() {
                    $("#modify_emp_authority" + empId).blur(function() {
                        if ($("#modify_emp_authority" + empId).val() == "manager") {
                            $("#emp_password" + empId).html("");
                            $("#emp_password" + empId).html("<input type=\"text\" name=\"modify_emp_password" + empId + "\" placeholder=\"請填入管理者密碼\" required>");
                        } else {
                            $("#emp_password" + empId).html("");
                            $("#emp_password" + empId).html("此身份不需要密碼");
                        }
                    });
                });
            }

            $("#emp_change_button" + empId).html("&nbsp;<input type=\"button\" onclick=\"confirmModifyEmployee('" + empId + "')\" value=\"儲存\">&nbsp;<input type=\"button\" onclick=\"cancelModifyEmployee('" + empId + "', '" + a + "', '" + b + "', '" + c + "')\" value=\"取消\">&nbsp;");
        }

        function modifyRestaurant(restaurantBrandName) {
            var a = $("#restaurant_name" + restaurantBrandName).html().trim();
            $("#restaurant_brand_name" + restaurantBrandName).html("<input type=\"text\" name=\"modify_restaurant_brand_name" + restaurantBrandName + "\" value=\"" + restaurantBrandName + "\" placeholder=\"請填入餐廳代號\" required>");
            $("#restaurant_name" + restaurantBrandName).html("<input type=\"text\" name=\"modify_restaurant_name" + restaurantBrandName + "\" value=\"" + a + "\" placeholder=\"請填入餐廳名稱\" required>");

            $("#restaurant_change_button" + restaurantBrandName).html("&nbsp;<input type=\"button\" onclick=\"confirmModifyRestaurant('" + restaurantBrandName + "', '" + restaurantBrandName + "_" + a + "')\" value=\"儲存\">&nbsp;<input type=\"button\" onclick=\"cancelModifyRestaurant('" + restaurantBrandName + "', '" + a + "')\" value=\"取消\">&nbsp;");
        }

        function cancelModifyEmployee(empId, empName, empAuthority, empPassword) {
            $("#emp_name" + empId).html("" + empName);
            $("#emp_authority" + empId).html("" + empAuthority);
            $("#emp_password" + empId).html("" + empPassword);

            if (empId == "<?php echo $_SESSION["emp_id"] ?>") {
                $("#emp_change_button" + empId).html("&nbsp;<input type=\"button\" onclick=\"modifyEmployee('" + empId + "')\" value=\"修改\">&nbsp;");
            } else {
                $("#emp_change_button" + empId).html("&nbsp;<input type=\"button\" onclick=\"modifyEmployee('" + empId + "')\" value=\"修改\">&nbsp;<input type=\"button\" onclick=\"deleteEmployee('" + empId + "', '" + empName + "')\" value=\"刪除\">&nbsp;");
            }
        }

        function cancelModifyRestaurant(restaurantBrandName, restaurantName) {
            $("#restaurant_brand_name" + restaurantBrandName).html(restaurantBrandName);
            $("#restaurant_name" + restaurantBrandName).html(restaurantName);

            $("#restaurant_change_button" + restaurantBrandName).html("&nbsp;<input type=\"button\" onclick=\"modifyRestaurant('" + restaurantBrandName + "')\" value=\"修改\">&nbsp;<input type=\"button\" onclick=\"deleteRestaurant('" + restaurantBrandName + "_" + restaurantName + "', '" + restaurantName + "')\" value=\"刪除\">&nbsp;");
        }

        function confirmModifyEmployee(empId) {
            var a = $("input[name='modify_emp_name" + empId + "']").val();
            var b = $("#modify_emp_authority" + empId).val();
            if (b == "manager" || empId == "<?php echo $_SESSION["emp_id"]; ?>") {
                var authority = "管理者";
                b = "manager";
                var c = $("input[name='modify_emp_password" + empId + "']").val();
            } else {
                var authority = "普通員工";
                var c = '';
            }
            if (window.confirm("以下是即將更新的內容：\n員工編號：" + empId + "\n員工姓名：" + a + "\n權限：" + authority + "\n密碼：" + c + "\n\n是否確認要更新員工資訊？")) {
                $.post("modify_employee.php", {
                    emp_id: empId,
                    emp_name: a,
                    emp_authority: b,
                    emp_password: c
                }, function(result) {
                    alert(result);
                    document.location.href = "./emp_and_restaurant_management.php";
                });
            }
        }

        function confirmModifyRestaurant(restaurantBrandName, restaurantFullName) {
            var a = $("input[name='modify_restaurant_brand_name" + restaurantBrandName + "']").val();
            var b = $("input[name='modify_restaurant_name" + restaurantBrandName + "']").val();
            if (a == '' || b == '') {
                alert("餐廳代號和餐廳名稱皆不可以是空值。");
            } else {
                if (window.confirm("以下是即將更新的內容：\n餐廳代號：" + a + "\n餐廳名稱：" + b + "\n\n是否確認要更新餐廳資訊？")) {
                    $.post("modify_restaurant.php", {
                        original_restaurant_name: restaurantFullName,
                        restaurant_brand_name: a,
                        restaurant_name: b
                    }, function(result) {
                        alert(result);
                        document.location.href = "./emp_and_restaurant_management.php";
                    });
                }
            }
        }
    </script>
</body>

</html>