<?php
include('db_login.php');
require_once('DB.php');
require_once('config.php');

// ログイン済みか確認する
session_start(  );

$stop=FALSE;
$found_error=FALSE;
// ヘッダを表示する
$smarty->assign('blog_title',$blog_title);
$smarty->display('header.tpl');

if  (!isset($_SESSION['username'])) {
    echo ("Please <a href='login.php'>login</a>.");
    $stop=TRUE;
}
// フォーム入力値を取得する
$post_id=$_POST[post_id];
$title= $_POST['title'];
$body= $_POST['body'];
$action= $_POST['action'];
$category_id= $_POST['category_id'];
$user_id=$_SESSION["user_id"];

// データベースに接続する
$connection = DB::connect( "mysql://$db_username:$db_password@$db_host/$db_database" );
if (!$connection){
    die ("Could not connect to the database: <br>". DB::errorMessage(  ));
}
if ($_GET['action']=="delete" AND !$stop){
    $get_post_id=$_GET[post_id];
    $get_post_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($get_post_id) : $get_post_id);
    $user_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($user_id) : $user_id);
    $query = "DELETE FROM posts WHERE post_id='".$get_post_id."' AND
    user_id='".$user_id."'";
    $result = $connection->query($query);
    if (DB::isError($result)){
        die ("Could not query the database: <br>". $query. " ".
        DB::errorMessage($result));
    }
    echo ("Deleted successfully.<br />");
    $stop=TRUE;
}

// URLからIDを取得して、データを変更する
if ($_GET['post_id'] AND !$stop) {
    $get_post_id=$_GET[post_id];
    $get_post_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($get_post_id) : $get_post_id);
    $query = "SELECT * FROM users NATURAL JOIN posts NATURAL JOIN categories
    where post_id = $get_post_id";
    $result = $connection->query($query);
    if (DB::isError($result)){
        die ("Could not query the database: <br>". $query. " ".DB::errorMessage($result));
    }
    while ($result_row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
        $posts[]=$result_row;
    }
    $smarty->assign('action','edit');
    $smarty->assign('posts',$posts);
    // カテゴリを取得する
    $query = "SELECT category_id, category FROM categories";
    $smarty->assign('categories',$connection->getAssoc($query));
    $smarty->display('post_form.tpl');
    $stop=TRUE;
}

// フォームが送信された場合の処理（追加と変更で処理を分岐）
if ($_POST['submit'] AND !$stop)
{
    // フィールドを検証する
    if ($title == ""){
        echo ("Title must not be null.<br>");
        $found_error=TRUE;
        $stop=TRUE;
    }
    if ($body == ""){
        echo ("Body must not be null.<br>");
        $found_error=TRUE;
        $stop=TRUE;
    }
    // 検証結果に問題がないのでデータベースへ追加
    if ( $_POST['action']=="add" AND !$stop){
        $category_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($category_id) : $category_id);
        $title=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($title) : $title);
        $body=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($body) : $body);
        $user_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($user_id) : $user_id);
        $query = "INSERT INTO posts VALUES (NULL,
        "."'".$category_id."','".$user_id."','".$title."','".$body."', NULL)";
        $result = $connection->query($query);
        if (DB::isError($result))
        {
            die ("Could not query the database: <br>". $query. " ".DB::errorMessage($result));
        }
        echo ("Posted successfully.<br />");
        $stop=TRUE;
    }
}
if ($_POST['action']=="edit" and !$stop) {
    $category_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($category_id) : $category_id);
    $title=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($title) : $title);
    $body=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($body) : $body);
    $user_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($user_id) : $user_id);
    $post_id=mysql_real_escape_string(get_magic_quotes_gpc(  ) ? stripslashes($post_id) : $post_id);

    $query = "UPDATE posts SET category_id ='".$category_id."',
    title ='".$title."',body='".$body."' WHERE post_id='".$post_id."'
     AND user_id='".$user_id."'";
    $result = $connection->query($query);
    if (DB::isError($result)){
        die ("Could not query the database: <br>". $query. " ".
        DB::errorMessage($result));
    }
    echo ("Updated successfully.<br />");
    $stop=TRUE;
}
if (!$stop){
    // 空のフォームを表示して
    // 空のデータを作成する
    $result_row=array('title'=>NULL,'body'=>NULL);
    $posts[]=$result_row;
    // カテゴリを取得する
    $query = "SELECT category_id, category FROM categories";
    $smarty->assign('categories',$connection->getAssoc($query));
    $smarty->assign('posts',$posts);
    $smarty->assign('action','add');
    $smarty->display('post_form.tpl');
}

if ($found_error) {
    // 入力された内容を変数に格納して
    // フォームを再表示する
    $result_row=array('title'=>"$title",'body'=>"$body",'post_id'=>"$post_id");
    $posts[]=$result_row;
    $smarty->assign('action',$action);
    $smarty->assign('posts',$posts);
    $smarty->display('post_form.tpl');
}
// フッタを表示する
$smarty->display('footer.tpl');

?>
