<?php
session_start();
session_regenerate_id(true);

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

// 休憩中の場合はタスク画面に遷移
if ((getTaskStatus($_SESSION['user-id']) == '01') || (getTaskStatus($_SESSION['user-id']) == '02')) {
    redirect('../view/resting.php');
    exit();
}

// 休憩中でもタスク進行中でもなければトップ画面に遷移
if (getTaskStatus($_SESSION['user-id']) == '99') {
    redirect('../view/index.php');
    exit();
}

// タスク種別振り分け
if (getTaskStatus($_SESSION['user-id']) == '10') {
    $taskKindName = '作業';
} else {
    $taskKindName = '学習';
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
                <h2 class="form-title">１時間ごとに休憩しよう！</h2>
                <p id="now">ーーーーー</p>
                <div>
                    <input class="post-btn" type="button" value="休憩" onclick="setRestForm()">
                </div>
                <div>
                    <input class="post-btn" type="button" value="<?php echo $taskKindName;?>終了" onclick="setFinishForm()">
                </div>
            </div>
        </main>
        <div id="rest-form">
	        <p class="modal-text"><a id="modal-close" class="button-link" onclick="rest_form_close()" >×</a>　進捗を記入してください</p>
            <form method="post" action="../controller/to_resting_controller.php" onsubmit="return textConfirm(document.getElementById('progress').value);">
                <textarea id="progress" class="input-text" name="detail" cols="40" rows="10" placeholder="140字以内で記入してください"></textarea>
                <input type="hidden" name="csrf-token" value="<?php echo escp($_SESSION['csrf-token']);?>">
                <input type="checkbox" id="rest" name="rest" hidden>
                <input class="post-btn" type="submit" value="送信" onclick="sendRestForm();">
            </form>
        </div>
        <div id="finish-form">	        
	        <p class="modal-text"><a id="modal-close" class="button-link" onclick="finish_form_close()">×</a>　<?php echo $taskKindName;?>内容を記入してください</p>
            <form method="post" action="../controller/finish_controller.php" onsubmit="return textConfirm(document.getElementById('content').value);">
                <textarea id="content" class="input-text" name="detail" cols="40" rows="10" placeholder="140字以内で記入してください"></textarea>
                <input type="hidden" name="csrf-token" value="<?php echo escp($_SESSION['csrf-token']);?>">
                <input type="checkbox" id="finish" name="finish" hidden>
                <input id="post-btn" class="post-btn" type="submit" value="送信" onclick="sendFinishForm();">
            </form>
        </div>
        <div id="modal-overlay" onclick="modaloverlayClick()"></div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="../view/js/animation.js"></script>
        <script src="./js/clock.js"></script>
        <script src="./js/text.js"></script>
        <script>
            function rest_form_close(){
                document.getElementById("rest-form").style.display = "none";
                document.getElementById("modal-overlay").style.display = "none";
            }

            function finish_form_close(){
                document.getElementById("finish-form").style.display = "none";
                document.getElementById("modal-overlay").style.display = "none";
            }

            function setRestForm () {
	            document.getElementById("rest-form").style.display = "block";
	            document.getElementById("modal-overlay").style.display = "block";
            }

            function setFinishForm () {
	            document.getElementById("finish-form").style.display = "block";
	            document.getElementById("modal-overlay").style.display = "block";
            }

            function sendRestForm() {
                document.getElementById('finish').checked = false;
                document.getElementById('rest').checked = true;
            }

            function sendFinishForm() {
                document.getElementById('finish').checked = true;
                document.getElementById('rest').checked = false;
            }

            function modaloverlayClick(){
                document.getElementById("rest-form").style.display = "none";
                document.getElementById("finish-form").style.display = "none";
            	document.getElementById("modal-overlay").style.display = "none";
            }
        </script>
    </body>
</html>