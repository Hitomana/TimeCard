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

// 作業中または学習中の場合はタスク画面に遷移
if ((getTaskStatus($_SESSION['user-id']) == '10') || (getTaskStatus($_SESSION['user-id']) == '20')) {
    redirect('../view/working.php');
    exit();
}

// 作業中でも学習中でもなければトップ画面に遷移
if (getTaskStatus($_SESSION['user-id']) == '99') {
    redirect('../view/index.php');
    exit();
}

// タスク種別振り分け
if (getTaskStatus($_SESSION['user-id']) == '01') {
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
                <h2 class="form-title">さて、一息つきますか☕</h2>
                <p id="now">ーーーーー</p>
                <form method="post" action="../controller/restart_controller.php">
                    <input type="checkbox" id="restart" name="restart" hidden>
                    <input type="hidden" name="csrf-token" value="<?php echo escp($_SESSION['csrf-token']);?>">
                    <input class="post-btn" type="submit" value="再開" onclick="sendRestartForm();">
                </form>

                <div>
                    <input class="post-btn" type="button" value="<?php echo $taskKindName;?>終了" onclick="setFinishForm()">
                </div>
            </div>
        </main>

        <div id="finish-form">	        
	        <p class="modal-text"><a id="modal-close" class="button-link" onclick="finish_form_close()" >×</a>　<?php echo $taskKindName;?>内容を記入してください</p>
            <form method="post" action="../controller/finish_controller.php" onsubmit="return textConfirm(document.getElementById('content').value);">
                <textarea id="content" class="input-text" name="detail" cols="40" rows="10" placeholder="140字以内で記入してください"><?php echo getDetail(); ?></textarea>
                <input type="hidden" name="csrf-token" value="<?php echo escp($_SESSION['csrf-token']);?>">
                <input type="checkbox" id="restart" name="restart" hidden>
                <input type="checkbox" id="finish" name="finish" hidden>
                <input class="post-btn" type="submit" value="送信" onclick="sendFinishForm();">
            </form>
        </div>
        <div id="modal-overlay" onclick="modaloverlayClick()"></div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="../view/js/animation.js"></script>
        <script src="./js/clock.js"></script>
        <script src="./js/text.js"></script>
        <script>
            function finish_form_close(){
                document.getElementById("finish-form").style.display = "none";
                document.getElementById("modal-overlay").style.display = "none";
            }

            function setFinishForm () {
                document.getElementById("finish-form").style.display = "block";
                document.getElementById("modal-overlay").style.display = "block";
            }

            function sendRestartForm() {
                document.getElementById('restart').checked = true;
            }

            function sendFinishForm() {
                document.getElementById('finish').checked = true;
                document.getElementById('restart').checked = false;
            }

            function modaloverlayClick(){
                document.getElementById("finish-form").style.display = "none";
                document.getElementById("modal-overlay").style.display = "none";
            }

        </script>
        <script>
            window.history.pushState(null, null, null);
            window.addEventListener("popstate", function() {
                window.history.pushState(null, null, null);
                return;
            });
        </script>
    </body>
</html>
<?php
// タスク詳細の取得
function getDetail() {
    
    require_once('../model/const.php');
    require_once(FUNCTION_FILE);
    require_once(SECRET);

    try {
        $pdo = new PDO(DNS, USER, PW, getPDOoptions());        
        if (getTaskStatus($_SESSION['user-id']) == '01') {
            
            $workId = 'w'.sprintf('%07d', dataCountOfTable(WORKS));
            $sql = "SELECT detail FROM ".WORKS." WHERE id = ?;";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $workId, PDO::PARAM_STR);
            $stmt->execute();
                
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $detail = $result['detail'];
        }
            
        if (getTaskStatus($_SESSION['user-id']) == '02') {
            
            $learnId = 'l'.sprintf('%07d', dataCountOfTable(LEARNS));           
            $sql = "SELECT detail FROM ".LEARNS." WHERE id = ?;";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $learnId, PDO::PARAM_STR);
            $stmt->execute();
                
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $detail = $result['detail'];    
        }
  
    } catch (PDOException $e) {
  
        print('データを取得できませんでした');
        exit();
  
    } finally {
  
        $pdo = null;
        return $detail;
  
    }
}