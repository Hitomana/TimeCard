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
    $restart = isset($_POST['restart']) ? $_POST['restart'] : null;         // 再開フラグ（作業または休憩）
    $detail = isset($_POST['detail']) ? $_POST['detail'] : null;            // 作業または学習の詳細
    $csrfToken = isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null; // CSRF対策用トークン   

    // CSRF 対策
    if (empty($_POST['csrf-token']) || ($csrfToken != $_SESSION['csrf-token'])) {

        $_SESSION['error-status'] = 2;    // 不正なリクエスト
        redirect('../view/login.php');
        exit();            

    }

    // 作業または学習を再開する場合
    if (isset($restart)) {    

        // 休憩終了時刻を記録
        recordRestFinish($_SESSION['user-id']);

        // 作業途中だった場合
        if (getTaskStatus($_SESSION['user-id']) == '01') {

            // タスク状態を更新し、新しい作業レコードを登録する
            updateTaskStatus($_SESSION['user-id'], '10');
            insertWork($_SESSION['user-id']);
        }

        // 学習途中だった場合
        if (getTaskStatus($_SESSION['user-id']) == '02') {

            // タスク状態を更新し、新しい学習レコードを登録する
            updateTaskStatus($_SESSION['user-id'], '20');
            insertLearn($_SESSION['user-id']);
        }

        // 作業中または学習中に戻す
        redirect('../view/working.php');
        exit();
    }
}