<?php
session_start();
    
// 定数の参照
require_once('../model/const.php');

// リソースの参照
require_once(SECRET);
require_once(FUNCTION_FILE);

// 強制ブラウズ対策
if (empty($_SESSION['user-id'])) {

    // ログイン画面に遷移
    redirect('../view/login.php');
    exit();

} else {

    // POST情報
    $rest = isset($_POST['rest']) ? $_POST['rest'] : null;                  // 休憩フラグ 
    $detail = isset($_POST['detail']) ? escp($_POST['detail']) : null;            // 作業または学習の詳細
    $csrfToken = isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null; // CSRF対策用のトークン
   
    // CSRF 対策
    if (empty($_POST['csrf-token']) || ($csrfToken != $_SESSION['csrf-token'])) {
    
        $_SESSION['error-status'] = 2;    // 不正なリクエスト
        redirect('../view/login.php');
        exit();
    }

    if (isset($rest)) {
        
        if (insertRest($_SESSION['user-id'])) {
                
            // 作業途中だった場合
            if (getTaskStatus($_SESSION['user-id']) == '10') {

                recordWorkFinish($_SESSION['user-id']);
                updateTaskStatus($_SESSION['user-id'], '01');
                writeWorkDetail($_SESSION['user-id'], $detail);
                
                redirect('../view/resting.php');
                exit();
            } 
                
            // 学習途中だった場合
            if (getTaskStatus($_SESSION['user-id']) == '20') {
            
                recordLearnFinish($_SESSION['user-id']);
                updateTaskStatus($_SESSION['user-id'], '02');
                writeLearnDetail($_SESSION['user-id'], $detail);
                
                redirect('../view/resting.php');
                exit();
            }
        
        } else {

            print('処理に失敗しました');
            exit();
        }
    }
}