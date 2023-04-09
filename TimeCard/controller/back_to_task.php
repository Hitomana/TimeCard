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

    // どのページに遷移するかを制御
    $status = getTaskStatus($_SESSION['user-id']);
                
    switch ($status) {

    // 休憩中
    case 0:
    case 1:
    case 2:
 
        // 休憩画面に遷移
        redirect('../view/resting.php');
        exit();
        break;
 
    // 作業中または学習中
    case 10:
    case 20:

        // 作業画面に遷移
        redirect('../view/working.php');
        exit();
        break;

    // それ以外
    default:

        // トップ画面に遷移                               
        redirect('../view/index.php');
        exit();
        break;

    }
}

