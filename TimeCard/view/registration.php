<?php
session_start();

// 定数の参照
require_once('../model/const.php');

// リソースの参照
require_once(SECRET);
require_once(FUNCTION_FILE);

// キャッシュ無効化
cacheInvalidation();

// CSRFチェック
if (empty($_SESSION['sub-token']) || ($_SESSION['sub-token'] != $_SESSION['csrf-token'])) {

    $_SESSION['error-status'] = 2;    // 不正なリクエスト
    redirect('../view/login.php');
    exit();

} else {

    unset($_SESSION['sub-token']);
}

?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
    <?php include_once('../model/head.php');?>
        <link rel="stylesheet" href="./css/form.css">
    </head>
    <body>
        <div>  
            <form id="input-form" action="../controller/auth_login_controller.php" method="post" name="input-form">
                <?php include_once('../layout/error.php');?>
                <div id="container">
                    <h2 class="form-title">ご登録ありがとうございます。これでユーザー登録完了です！<br><br>よろしくお願いいたします！</h2>
                    <div class="form-btn">
                        <input type="button" class="post-btn" value="<?php echo LOGIN.'へ';?>" onclick="location.href='./login.php'">
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>