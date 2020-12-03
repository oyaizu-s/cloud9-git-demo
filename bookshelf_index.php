<?php
    // htmlspecialchars関数を実行するだけの関数 h() を定義
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
    
    // MySQLサーバ接続に必要な値を変数に代入
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $db_name = 'bookshelf';

    // 変数を設定して、MySQLサーバに接続
    $database = mysqli_connect($host, $username, $password, $db_name);

    // 接続を確認し、接続できていない場合にはエラーを出力して終了する
    if ($database == false) {
        die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
    }

    // MySQL に utf8 で接続するための設定をする
    $charset = 'utf8';
    mysqli_set_charset($database, $charset);

    // ここにMySQLを使ったなんらかの処理を書く
    
    // bookshelf_form.phpから送られてくる書籍データの登録
    if (array_key_exists('submit_add_book', $_POST)) {
        // まずは送られてきた画像をuploadsフォルダに移動させる
        $file_name = $_FILES['add_book_image']['name'];
        $image_path = './uploads/' . $file_name;
        move_uploaded_file($_FILES['add_book_image']['tmp_name'], $image_path);

        // データベースに書籍を新規登録する
        $sql = 'INSERT INTO books (title, image_url, status, memo) VALUES(?, ?, "unread", ?)';
        $statement = mysqli_prepare($database, $sql);
        mysqli_stmt_bind_param($statement, 'sss', $_POST['add_book_title'], $image_path, $_POST['add_book_memo']);
        mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
    }

    // ステータス変更の処理
    if (array_key_exists('submit_book_unread', $_POST)) {
        // 未読へ変更
        $sql = 'UPDATE books SET status="unread" WHERE id=?';        // 実行するSQLを作成
        $statement = mysqli_prepare($database, $sql);                // セキュリティ対策をする
        mysqli_stmt_bind_param($statement, 'i', $_POST['book_id']);  // id=?の?の部分に代入する
        mysqli_stmt_execute($statement);                             // SQL文を実行する
        mysqli_stmt_close($statement);                               // SQL文を破棄する
    }
    elseif (array_key_exists('submit_book_reading', $_POST)) {
        // 読中へ変更
        $sql = 'UPDATE books SET status="reading" WHERE id=?';
        $statement = mysqli_prepare($database, $sql);
        mysqli_stmt_bind_param($statement, 'i', $_POST['book_id']);
        mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
    }
    elseif (array_key_exists('submit_book_finished', $_POST)) {
        // 読了へ変更
        $sql = 'UPDATE books SET status="finished" WHERE id=?';
        $statement = mysqli_prepare($database, $sql);
        mysqli_stmt_bind_param($statement, 'i', $_POST['book_id']);
        mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
    }
    
    // メモ更新の処理
    if (array_key_exists('submit_book_memo', $_POST)) {
        // 読了へ変更
        $sql = 'UPDATE books SET memo=? WHERE id=?';
        $statement = mysqli_prepare($database, $sql);
        mysqli_stmt_bind_param($statement, 'si', $_POST['add_book_memo'], $_POST['book_id']);
        mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
    }
    
    // データ削除の処理
    if (array_key_exists('submit_book_delete', $_POST)) {
        $sql = 'DELETE FROM books WHERE id=?';                            // 実行するSQLを作成
        $statement = mysqli_prepare($database, $sql);                // セキュリティ対策をする
        mysqli_stmt_bind_param($statement, 'i', $_POST['book_id']);  // id=?の?の部分に代入する
        mysqli_stmt_execute($statement);                             // SQL文を実行する
        mysqli_stmt_close($statement);                               // SQL文を破棄する
    }
    
    // 未読数のカウント
    $sql = 'SELECT COUNT(*) as count FROM books where status = "unread"';
    $result = mysqli_query($database, $sql);
    $record = mysqli_fetch_assoc($result);
    $count_unread = $record['count'];
    
    // 読中数のカウント
    $sql = 'SELECT COUNT(*) as count FROM books where status = "reading"';
    $result = mysqli_query($database, $sql);
    $record = mysqli_fetch_assoc($result);
    $count_reading = $record['count'];
    
    // 読了数のカウント
    $sql = 'SELECT COUNT(*) as count FROM books where status = "finished"';
    $result = mysqli_query($database, $sql);
    $record = mysqli_fetch_assoc($result);
    $count_finished = $record['count'];
    
    // どれも$resultにレコードが代入されるので、どの場合でも$resultを使う
    if (array_key_exists('submit_only_unread', $_POST)) {
        // 未読ステータスの書籍だけを取得する
        $sql = 'SELECT * FROM books WHERE status="unread" ORDER BY created_at DESC';
    }
    elseif (array_key_exists('submit_only_reading', $_POST)) {
        // 読中ステータスの書籍だけを取得する
        $sql = 'SELECT * FROM books WHERE status="reading" ORDER BY created_at DESC';
    }
    elseif (array_key_exists('submit_only_finished', $_POST)) {
        // 読了ステータスの書籍だけを取得する
        $sql = 'SELECT * FROM books WHERE status="finished" ORDER BY created_at DESC';
    }
    else {
        // 登録されている書籍を全て取得する
        $sql = 'SELECT * FROM books ORDER BY created_at DESC';
    }

    // セクション6で後述しますが、この部分に if-elseif-else 文を後ほど挿入します
    // いずれかの $sql を実行して $result に代入する
    $result = mysqli_query($database, $sql);

    // MySQLを使った処理が終わると、接続は不要なので切断する
    mysqli_close($database);
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>Bookshelf | カンタン！あなたのオンライン本棚</title>
        <link rel="stylesheet" href="bookshelf.css">
    </head>
    <body>
        <header>
            <div id="header">
                <div id="logo">
                    <a href="./bookshelf_index.php"><img src="./images/logo.png" alt="Bookshelf"></a>
                </div>
                <nav>
                    <a href="./bookshelf_form.php"><img src="./images/icon_plus.png" alt=""> 書籍登録</a>
                </nav>
            </div>
        </header>
        <div id="cover">
            <h1 id="cover_title">カンタン！あなたのオンライン本棚</h1>
            <form action="bookshelf_index.php" method="post">
                <div class="book_status unread active">
                    <input type="submit" name="submit_only_unread" value="未読"><br>
                    <div class="book_count"><?php print h($count_unread); ?></div>
                </div>
                <div class="book_status reading active">
                    <input type="submit" name="submit_only_reading" value="読中"><br>
                    <div class="book_count"><?php print h($count_reading); ?></div>
                </div>
                <div class="book_status finished active">
                    <input type="submit" name="submit_only_finished" value="読了"><br>
                    <div class="book_count"><?php print h($count_finished); ?></div>
                </div>
            </form>
        </div>
        <div class="wrapper">
            <div id="main">
                <div id="book_list">
<?php
                    if ($result) {
                        while ($record = mysqli_fetch_assoc($result)) {
                            // 1レコード分の値をそれぞれ変数に代入する
                            $id = $record['id'];
                            $title = $record['title'];
                            $image_url = $record['image_url'];
                            $status = $record['status'];
                            $memo = $record['memo'];
?>
                    <div class="book_item">
                        <div class="book_image">
                            <img src="<?php print h($image_url); ?>" alt="">
                        </div>
                        <div class="book_detail">
                            <div class="book_title">
                                <?php print h($title); ?>
                            </div>
                            <form action="bookshelf_index.php" method="post">
                                <input type="hidden" name="book_id" value="<?php print h($id); ?>">
                                <div class="book_status unread <?php if ($status == "unread") print "active"; ?>">
                                    <input type="submit" name="submit_book_unread" value="未読">
                                </div>
                                <div class="book_status reading <?php if ($status == "reading") print "active"; ?>">
                                    <input type="submit" name="submit_book_reading" value="読中">
                                </div>
                                <div class="book_status finished <?php if ($status == "finished") print "active"; ?>">
                                    <input type="submit" name="submit_book_finished" value="読了">
                                </div>
                            </form>
                            <form action="bookshelf_index.php" method="post">
                                <input type="hidden" name="book_id" value="<?php print h($id); ?>">
                                <div class="memo">
                                    <p>
                                        <input type="text" name="add_book_memo" value="<?php print h($memo); ?>" placeholder="メモを入力">
                                        <input type="submit" name="submit_book_memo" value="更新">
                                    </p>
                                </div>
                            </form>
                            <div class="sub">
                                <div class="memo_edit">
                                <a href="./bookshelf_form.php">編集する</a>
                                </div>
                                <form action="bookshelf_index.php" method="post">
                                    <input type="hidden" name="book_id" value="<?php print h($id); ?>">
                                    <div class="book_delete">
                                      <input type="submit" name="submit_book_delete" value="削除する"><img src="images/icon_trash.png" alt="icon trash">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
<?php
                        }
                        mysqli_free_result($result);
                    }
?>
                </div>
            </div>
        </div>
        <footer>
            <small>© 2019 Bookshelf.</small>
        </footer>
    </body>
</html>