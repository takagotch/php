<?php
// Auth_HTTPでユーザ情報も返す例
require_once('config.php');
require_once('db_login.php');
require_once('Auth/HTTP.php');
// PEAR DBと同じ接続文字列を使用する
$AuthOptions = array(
                     'dsn'=>"mysql://$db_username:$db_password@$db_host/$db_database",
                     'table'=>"users", // テーブル名
                     'usernamecol'=>"username", // ユーザ名の列
                     'passwordcol'=>"password", // パスワード列
                     'cryptType'=>"md5", // パスワードの暗号化形式
                     'db_fields'=>"*" // 他の列も取得可能にする
);
$authenticate = new Auth_HTTP("DB", $AuthOptions);
// レルム名を設定
$authenticate->setRealm('Member Area');
// 認証エラー時のメッセージ
$authenticate->setCancelText('<h2>Access Denied</h2>');
// 認証の開始
$authenticate->start(  );
// ユーザ名とパスワードをデータベースと照合
if ($authenticate->getAuth(  )) {
    session_start(  );
    $smarty->assign('blog_title',$blog_title);
    $smarty->display('header.tpl');
    // セッション変数の格納
    $_SESSION['username'] = $authenticate->username;
    $_SESSION['first_name'] = $authenticate->getAuthData('first_name');
    $_SESSION['last_name'] = $authenticate->getAuthData('last_name');
    $_SESSION['user_id'] = $authenticate->getAuthData('user_id');
    echo "Login successful. Great to see you ";
    echo $authenticate->getAuthData('first_name');
    echo " ";
    echo $authenticate->getAuthData('last_name').".<br />";
    $smarty->display('footer.tpl');
}
?>
