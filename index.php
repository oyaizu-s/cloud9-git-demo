<?php
    date_default_timezone_set('Asia/Tokyo');
    $now_hour = (int)date("G");
    
    $name = "名無し";
    if (array_key_exists('target_name', $_POST)) {
        $name = $_POST['target_name'];
    }
    
    $age = "--";
    if (array_key_exists('target_age', $_POST)) {
        $age = $_POST['target_age'];
    }
    
    function greeting($hour) {
        $result = "";

        if (6 <= $hour && $hour < 12) {
            $result = "おはようございます";
        }
        elseif (12 <= $hour && $hour < 18) {
            $result = "こんにちは";
        }
        else {
            $result = "こんばんは";
        }

        return $result;
    }
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>タイトル</title>
    </head>
    <body>
        <form action="index.php" method="POST">
            <label>名前： <input type="text" name="target_name" required</label>
            <label>年齢： <input type="number" name="target_age" required</label>
            <input type="submit" value="送信">
        </form>
        <p>今は<?php print $now_hour; ?>時です。</p>
        <p><?php print greeting($now_hour); ?>、<?php print htmlspecialchars($name, ENT_QUOTES, "UTF-8"); ?>さん(<?php print htmlspecialchars($age, ENT_QUOTES, "UTF-8"); ?>歳)</p>
    </body>
</html>