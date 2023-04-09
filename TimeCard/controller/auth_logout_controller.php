<?php
session_start();

// 定数の参照
require_once('../model/const.php');

// リソースの参照
require_once(SECRET);
require_once(FUNCTION_FILE);
    
// 強制ブラウズ対策
if (empty($_SESSION['user-id'])) {    

    // 何も処理せずログイン画面に遷移
    redirect('../view/login.php');
    exit();

} else {    

    // 最終ログアウト時刻を更新
    updateLogoutDate($_SESSION['user-id']);

    // セッション変数を破棄
    unset($_SESSION['user-id']);
    
    // ログイン画面に遷移
    redirect('../view/login.php');
    exit(); 
}
