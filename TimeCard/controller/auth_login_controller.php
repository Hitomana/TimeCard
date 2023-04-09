<?php
session_start();

// 定数の参照
require_once('../model/const.php');

// リソースの参照
require_once(SECRET);
require_once(FUNCTION_FILE);

// POST情報
$postedLoginId = isset($_POST['login-id']) ? escp($_POST['login-id']) : null;
$postedPassword = isset($_POST['login-password']) ? escp($_POST['login-password']) : null;
$csrfToken = isset($_POST['csrf-token']) ? escp($_POST['csrf-token']) : null;
$token = isset($_POST['g-recaptcha-response'] ) ? escp($_POST['g-recaptcha-response']) : null; 
$action = isset($_POST['action'] ) ? escp($_POST['action']) : null;

// 必須項目チェック
if (empty($postedLoginId) || empty($postedPassword)) {    
    $_SESSION['error-status'] = 1;    // 必須項目の未入力
    redirect('../view/login.php');
    exit();
}

// CSRF 対策
if (empty($_POST['csrf-token']) || ($csrfToken != $_SESSION['csrf-token'])) {
    $_SESSION['error-status'] = 2;    // 不正なリクエスト
    redirect('../view/login.php');
    exit();            
}

// re CAPTCHA v3 によるbot判定
if ($token && $action) { 

    // シークレットキーの参照
    $secretKey = V3_SECRETKEY;
    $cUrl = curl_init();

    curl_setopt($cUrl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($cUrl, CURLOPT_POST, true );
            
    curl_setopt($cUrl, CURLOPT_POSTFIELDS, http_build_query(array(
        'secret' => $secretKey, 
        'response' => $token
    )));
      
    curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, true);

    $apiResponse = curl_exec($cUrl);
    curl_close($cUrl);
      
    $responseResult = json_decode($apiResponse);

    // success が false でアクション名が一致せず、スコアが 0.5 未満の場合は不合格
    if ((!$responseResult->success) && ($responseResult->action != $action) && ($responseResult->score < 0.5)) {
        $_SESSION['error-status'] = 3;    // ログインの失敗
        redirect('../view/login.php');
        exit();
    } else {
        
        // ログインチェック
        if (isset($postedLoginId) && isset($postedPassword)) {            

            try {

            // パスワードとソルトの取得
            $pdo = new PDO(DNS, USER, PW, getPDOOptions());
            $sql = "SELECT pass, salt FROM ".USERS." WHERE login_id = ?;";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $postedLoginId, PDO::PARAM_STR);
            $stmt->execute();       
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dbPassword = $result['pass'];
            $dbSalt = $result['salt'];

            } catch (PDOException $e) {

                print('データベースに接続できませんでした');
                exit();                

            } finally {

                $pdo = null;

                // ログイン
                if (password_verify($postedPassword.$dbSalt, $dbPassword)) {
            
                    // セッションハイジャック対策
                    session_regenerate_id(true);

                    // セッションにユーザーIDを格納
                    $_SESSION['user-id'] = manageIdFetch($postedLoginId);

                    // ログイン時刻の更新
                    updateLoginDate($_SESSION['user-id']);

                    // タスク状態を保持するレコードが単一でない（1行もない場合）、
                    // レコードを追加する
                    if (!getTaskStatusRecord($_SESSION['user-id'])) {
                        keepTaskStatus($_SESSION['user-id'], '99');
                    }

                    // どのページに遷移するかを制御
                    $status = getTaskStatus($_SESSION['user-id']);
                
                    switch ($status) {
                    // 休憩中
                    case 0:
                    case 1:
                    case 2:

                        redirect('../view/resting.php');
                        exit();
                        break;

                    // 作業中または学習中
                    case 10:
                    case 20:

                        redirect('../view/working.php');
                        exit();
                        break;

                    // それ以外
                    default:

                        redirect('../view/index.php');
                        exit();
                        break;
                    }
                } else {
            
                    $_SESSION['error-status'] = 3;    // ログインの失敗
                    redirect('../view/login.php');       
                    exit(); 
                }
            } 
        }
    }
}