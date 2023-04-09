<?php
session_start();

// 定数の参照
require_once('../model/const.php');

// リソースの参照
require_once(SECRET);
require_once(FUNCTION_FILE);

// キャッシュ無効化
cacheInvalidation();

// CSRF対策
$_SESSION['csrf-token'] = createToken();

// 強制ブラウズ対策
if (empty($_SESSION['user-id'])) {
    $_SESSION['error-status'] = 2;    // 不正なリクエスト
    redirect('../view/login.php');
    exit();
}

// 休憩状態の場合は休憩画面に遷移
if ((getTaskStatus($_SESSION['user-id']) == '01') || (getTaskStatus($_SESSION['user-id']) == '02')) {
    redirect('../view/resting.php');
    exit();
}

// 作業中または学習中の場合はタスク画面に遷移
if ((getTaskStatus($_SESSION['user-id']) == '10') || (getTaskStatus($_SESSION['user-id']) == '20')) {
    redirect('../view/working.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
    <?php include_once('../model/head.php');?>
        <link rel="stylesheet" href="./css/form.css">
        <link rel="stylesheet" href="./css/modal.css">
    </head>
    <body onload="startTimer();">
    <?php include_once('../layout/header.php');?>
        <main id="input-form">
            <div id="container">
                <h2 class="form-title">さぁ、頑張りましょう！</h2>
                <p id="now">ーーーーー</p>
                <form method="post" action="../controller/to_working_controller.php">
                    <input type="checkbox" id="work" name="work" hidden>
                    <input type="hidden" name="csrf-token" value="<?php echo $_SESSION['csrf-token'];?>">
                    <input class="post-btn" type="submit" value="作業開始" onclick="stopTimer(); document.getElementById('work').checked = true; document.getElementById('learn').checked = false;">
                </form>
                <form method="post" action="../controller/to_working_controller.php">
                    <input type="checkbox" id="learn" name="learn" hidden>
                    <input type="hidden" name="csrf-token" value="<?php echo $_SESSION['csrf-token'];?>">
                    <input class="post-btn" type="submit" value="学習開始" onclick="stopTimer(); document.getElementById('learn').checked = true; document.getElementById('work').checked = false;">
                </form>
            </div>
        </main>
        <?php include_once('../layout/footer.php');?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="../view/js/animation.js"></script>
        <script src="./js/clock.js"></script>
    </body>
</html>