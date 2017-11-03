<?php

session_start(  );

require_once('config.php');
require_once('db_login.php');
require_once('DB.php');

// ヘッダを表示する
$smarty->assign('blog_title',$blog_title);
$smarty->display('header.tpl');

// ログイン済みか確認する
if (!isset($_SESSION["username"])) {
    echo 'Please <a href="login.php">login</a>.';
    exit;
}

// データベースに接続する
$connection = DB::connect("mysql://$db_username:$db_password@$db_host/$db_database");

if (DB::isError($connection)){
    die ("Could not connect to the database: <br />". DB::errorMessage($connection));
}

$stop = FALSE;


$post_id=$_POST[post_id];
$title= $_POST['title'];
$body= $_POST['body'];
$action= $_POST['action'];
$category_id= $_POST['category_id'];
$user_id=$_SESSION["user_id"];
    $comment_id = $_POST['comment_id'];

if ($_GET['action'] == "delete" and !$stop) {
        $comment_id = $_GET["comment_id"];
        $comment_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($comment_id) : $comment_id);
        $query = "DELETE FROM comments WHERE comment_id='".$comment_id."'AND user_id='".$user_id."'";
    $result = $connection->query($query);
    if (DB::isError($result)){
       die("Could not query the database: <br />".$query." ".DB::errorMessage($result));
    }
    echo "Deleted successfully.<br />";
    $stop = TRUE;
}

// URLからIDを取得して、データを変更する
    if ($_GET["comment_id"] and !$stop) {
        $comment_id = $_GET["comment_id"];
        $query = "SELECT * FROM comments NATURAL JOIN users WHERE comment_id=".$_GET["comment_id"];
    $result = $connection->query($query);
    if (DB::isError($result)){
        die("Could not query the database: <br />".$query." ".DB::errorMessage($result));
    }
    while ($result_row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
        $comments[] = array('title'=>htmlentities($result_row['title']),
                            'body'=>htmlentities($result_row['body']),
                            'comment_id'=>$result_row['comment_id']);
    }
    $post_id = $_GET["post_id"];
    $smarty->assign('action','edit');
    $smarty->assign('comments',$comments);
    $smarty->assign('post_id',htmlentities($post_id));
    $smarty->display('comment_form.tpl');
    // フッタを表示する
    $smarty->display('footer.tpl');
    exit;
}

// フォームが送信された場合の処理（追加と変更で処理を分岐）
if ($_POST['submit'] and !$stop) {
    // フィールドを検証する
    if ($title == ""){
    echo 'Title must not be null.<br />';
    $found_error = TRUE;
    $stop = TRUE;
}
if ($body == ""){
    echo "Body must not be null.<br />";
    $found_error = TRUE;
    $stop = TRUE;
}
// 検証結果に問題がないのでデータベースへ追加
if ($_POST['action'] == "add" AND !$stop) {
    $title=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($title) : $title);
    $body=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($body) : $body);
    $post_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($post_id) : $post_id);
    $user_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($user_id) : $user_id);
        $query = "INSERT INTO comments VALUES (NULL,'".$user_id."','".$post_id."','".$title."','".$body."', NULL)";
    $result = $connection->query($query);
    if (DB::isError($result)){
        die("Could not query the database: <br />".$query." ".DB::errorMessage($result));
    }
    echo "Posted successfully.<br />";
    $stop = TRUE;
}
if ($_POST['action']=="edit" and !$stop){
    $title=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($title) : $title);
    $body=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($body) : $body);
    $comment_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($comment_id) : $comment_id);
    $user_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($user_id) : $user_id);
        $query = "UPDATE comments SET title='".$title."',body='".$body."' WHERE comment_id='".$comment_id."' AND user_id='".$user_id."'";
    $result = $connection->query($query);
    if (DB::isError($result)){
        die("Could not query the database: <br />".$query." ".DB::errorMessage($result));
    }
    echo 'Updated successfully.<br />';
    $stop = TRUE;
    }
}

if (!$stop){
    // 空のフォームを表示する
    // 入力内容は空にする
    $post_id = $_GET["post_id"];
    $result_row = array('title'=>NULL,'body'=>NULL,'comment_id'=>NULL);
    $comments[] = $result_row;
    // カテゴリを取得する
    $smarty->assign('post_id',htmlentities($post_id));
    $smarty->assign('comments',$comments);
    $smarty->assign('action','add');
    $smarty->display('comment_form.tpl');
}

if ($found_error) {
    // 入力された内容を変数に格納して
    // フォームを再表示する
    $post_id = $_POST["post_id"];
    $result_row = array('title'=>htmlentities($title),'body'=>htmlentities($body),'comment_id'=>htmlentities($comment_id));
    $comments[] = $result_row;
    $smarty->assign('action',htmlentities($action));
    $smarty->assign('post_id',htmlentities($post_id));
    $smarty->assign('comments',$comments);
    $smarty->display('comment_form.tpl');
}

// フッタを表示する
$smarty->display('footer.tpl');

?>
