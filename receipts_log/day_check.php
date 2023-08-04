<?php
// 確認當日是否是星期六、日、一
$day = date("w", strtotime(date("Y/m/d")));
switch ($day) {
    case 0:
        break;
    case 1:
        break;
    case 6:
        break;
    default:
        echo "<script>alert(\"此項目僅可在星期六、日或下周一登錄。\");</script>";
        echo "<script>document.location.href = \"receipt_reroute.php\";</script>";
        exit;
}
