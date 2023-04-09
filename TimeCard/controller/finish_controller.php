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
    $finishFlag = isset($_POST['finish']) ? $_POST['finish'] : null;        // 終了フラグ（作業または休憩）
    $detail = isset($_POST['detail']) ? $_POST['detail'] : null;            // 作業または学習の詳細
    $csrfToken = isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null; // CSRF対策用のトークン

    // CSRF 対策
    if (empty($_POST['csrf-token']) || ($csrfToken != $_SESSION['csrf-token'])) {
        $_SESSION['error-status'] = 2;    // 不正なリクエスト
        redirect('../view/login.php');
        exit();            
    }

    // 終了ボタン押下時
    if (isset($finishFlag)) {

        // タスク状態を取得
        $status = getTaskStatus($_SESSION['user-id']);

        switch($status) {
            case 1:    // 休憩中（作業途中）の場合
                recordRestFinish($_SESSION['user-id']);
                updateTaskStatus($_SESSION['user-id'], '99');
                writeWorkDetail($_SESSION['user-id'], $detail);    
                break;
            case 2:    // 休憩中（学習途中）の場合
                recordRestFinish($_SESSION['user-id']);
                updateTaskStatus($_SESSION['user-id'], '99');
                writeLearnDetail($_SESSION['user-id'], $detail);    
                break;
            case 10:    // 作業途中の場合
                recordWorkFinish($_SESSION['user-id']);
                updateTaskStatus($_SESSION['user-id'], '99');
                writeWorkDetail($_SESSION['user-id'], $detail);
                break;
            case 20:    // 学習途中の場合
                recordLearnFinish($_SESSION['user-id']);
                updateTaskStatus($_SESSION['user-id'], '99');
                writeLearnDetail($_SESSION['user-id'], $detail);        
                break;
        }

        // タスク選択画面に遷移
        redirect('../view/index.php');   
        exit();
    }
}