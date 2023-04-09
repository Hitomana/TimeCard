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
$workStart = isset($_POST['work']) ? $_POST['work'] : null;       // 作業フラグ
$learnStart = isset($_POST['learn']) ? $_POST['learn'] : null;    // 休憩フラグ
$restart = isset($_POST['restart']) ? $_POST['restart'] : null;   // 再開フラグ（作業または休憩）
$csrfToken = isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null;  // CSRF対策

// CSRF 対策
if (empty($_POST['csrf-token']) || ($csrfToken != $_SESSION['csrf-token'])) {
    $_SESSION['error-status'] = 2;    // 不正なリクエスト
    redirect('../view/login.php');
    exit();            
}

// 再開する場合
if (isset($restart)) {

    // 休憩終了を記録
    recordRestFinish($_SESSION['user-id']);

    // 各テーブルの状態から作業中か学習中かを判別する
    $status = getTaskStatus($_SESSION['user-id']);

    switch ($status)
    {
        // 作業再開
        case 1:
            
            updateTaskStatus($_SESSION['user-id'], '10');
            
            if (insertWork($_SESSION['user-id'])) {

                redirect('../view/working.php');
                exit();

            } else {

                redirect('../view/index.php');
                exit();

            }

            break;

        // 学習再開
        case 2:

            updateTaskStatus($_SESSION['user-id'], '20');
            
            if (insertLearn($_SESSION['user-id'])) {

                redirect('../view/working.php');
                exit();

            } else {

                redirect('../view/index.php');
                exit();

            }

            break;
        }

    // 作業または学習を開始する場合
    } else {

        // 作業開始
        if (isset($workStart)) {
    
            updateTaskStatus($_SESSION['user-id'], '10');

            if (insertWork($_SESSION['user-id'])) {
    
                redirect('../view/working.php');
                exit();
    
            } else {
    
                redirect('../view/index.php');
                exit();
    
            }
        }

        // 学習開始       
        if (isset($learnStart)) {               
 
            updateTaskStatus($_SESSION['user-id'], '20');
        
            if (insertLearn($_SESSION['user-id'])) {

                redirect('../view/working.php');
                exit();

            } else {

                redirect('../view/index.php');
                exit();

            }
        }
    }
}