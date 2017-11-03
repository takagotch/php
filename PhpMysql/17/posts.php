<?php
session_start(  );
require_once('config.php');
require_once('db_login.php');
require_once('DB.php');
// ページヘッダを出力する
$smarty->assign('blog_title',$blog_title);
$smarty->display('header.tpl');
// ログイン済みか確認する
if (!isset($_SESSION['username'])) {
    echo 'Please <a href="login.php">login</a>.';
}
else {
    // データベースに接続する
    $connection = DB::connect("mysql://$db_username:$db_password@$db_host/$db_database");

    if (DB::isError($connection)){
        die ("Could not connect to the database: <br />". DB::errorMessage($connection));
    }
    // カテゴリとユーザ情報でクエリを作成する
    $query = "SELECT * FROM users NATURAL JOIN posts NATURAL JOIN categories ORDER BY posted DESC";
    // クエリを実行する
    $result = $connection->query($query);
    if (DB::isError($result)){
        die("Could not query the database: <br />".$query." ".DB::errorMessage($result));
    }
    // 実行結果を配列に格納する
    while ($result_row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
        $test[] = $result_row;
    }
    // データをテンプレートに渡す
    $smarty->assign('posts', $test);
    // 渡したデータでテンプレートを表示する
    $smarty->display('posts.tpl');
    // データベース接続を閉じる
    $connection->disconnect(  );
    // ページフッタを表示する
    $smarty->display('footer.tpl');
}
?>
