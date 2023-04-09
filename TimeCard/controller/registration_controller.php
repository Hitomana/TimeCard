<?php
session_start();
    
// 定数の参照
require_once('../model/const.php');

// リソースの参照
require_once(SECRET);
require_once(FUNCTION_FILE);

// POST情報
$user = isset($_POST['user-name']) ? $_POST['user-name'] : null;
$userLoginId = isset($_POST['user-id']) ? $_POST['user-id'] : null;
$userPassword = isset($_POST['user-password']) ? $_POST['user-password'] : null;
$csrfToken = isset($_POST['csrf-token']) ? $_POST['csrf-token'] : null;
$token = isset($_POST['g-recaptcha-response'] ) ? escp($_POST['g-recaptcha-response']) : null; 
$action = isset($_POST['action'] ) ? escp($_POST['action']) : null;

// 登録完了画面に渡す
$_SESSION['sub-token'] = $csrfToken;

// ID生成
$userManageId = sprintf('%04d', dataCountOfTable(USERS));

// 必須項目チェック
if (empty($user) || empty($userLoginId) || empty($userPassword)) {

    $_SESSION['error-status'] = 1;    // 入力エラー
    redirect('../view/signup.php');
    exit();

}

// CSRFチェック
if (empty($csrfToken) || ($csrfToken != $_SESSION['csrf-token'])) {

    $_SESSION['error-status'] = 2;    // 不正なリクエスト
    redirect('../view/signup.php');
    exit();

}

// ログインIDの重複チェック
try {

    $pdo = new PDO(DNS, USER, PW, getPDOOptions());
    $sql = "SELECT COUNT(*) AS cnt FROM ".USERS." WHERE login_id = ?;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $userLoginId, PDO::PARAM_STR);
    $stmt->execute();
    $confirm = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    print('データの取得に失敗しました');
    exit();

} finally {

    $pdo = null;

    // ログインIDが1つでも重複する場合
    if ($confirm['cnt'] > 0) {
        $_SESSION['error-status'] = 4;    // 重複エラー
        redirect('../view/signup.php');
        exit();
    }
}

// パスワードの重複チェック
try {
    $pdo = new PDO(DNS, USER, PW, getPDOOptions());
    $sql = "SELECT pass, salt FROM ".USERS.";";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $confirm = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    print('データの取得に失敗しました');
    exit();    

} finally {
    foreach ($confirm as $element) {
        if (password_verify($userPassword.$element['salt'], $element['pass'])) {
            $_SESSION['error-status'] = 4;    // 重複エラー
            redirect('../view/signup.php');
            exit();
        } 
    }
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
        // パスワードハッシュ化
        $userSalt = substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, 20);
        $userPassword = password_hash($userPassword.$userSalt, PASSWORD_DEFAULT);

        // 登録処理
        try {
            $pdo = new PDO(DNS, USER, PW, getPDOOptions());       
            $sql = "INSERT INTO ".USERS." (id, login_id, pass, salt, user_name, last_login, last_logout) VALUES (?, ?, ?, ?, ?, ?, ?);";
            $stmt = $pdo->prepare($sql);
            $pdo->beginTransaction();

            $stmt->bindValue(1, $userManageId);
            $stmt->bindValue(2, $userLoginId);
            $stmt->bindValue(3, $userPassword);
            $stmt->bindValue(4, $userSalt);
            $stmt->bindValue(5, $user);
            $stmt->bindValue(6, NULL);
            $stmt->bindValue(7, NULL);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            print('データの登録に失敗しました');
            exit();    
        } finally {
            $pdo = null;

            // 登録完了画面に遷移
            redirect('../view/registration.php');
            exit();
        }
    }   
}