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
?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
    <?php include_once('../model/head.php');?>
        <link rel="stylesheet" href="./css/form.css">
    </head>
    <body>
        <form id="input-form" action="../controller/registration_controller.php" method="post" name="input-form" onsubmit="return checkInput();">
            <?php include_once('../layout/error.php');?>
            <div id="container">
                <h2 class="form-title">ユーザー登録</h2>
                <div class="form-unit">
                    <p id="input-caution"></p>
                </div>
                <div class="form-unit">
                    <label class="label">お名前（ハンドルネーム可）</label><br>
                    <input class="input-text" id="user-name" type="text" name="user-name"><br>
                </div>
                <div class="form-unit">
                    <label class="label">ID</label><br>
                    <input class="input-text" id="user-id" type="text" name="user-id"><br>
                </div>
                <div class="form-unit">
                    <label class="label">パスワード</label><br>
                    <input class="input-text" id="user-password" type="password" name="user-password" onchange="passwordCheck(document.getElementById('user-password').value);">
                    <p id="password-score"></p>
                </div>
                <input type="hidden" name="csrf-token" value="<?php echo escp($_SESSION['csrf-token']);?>">
                <div class="form-btn">
                    <input type="submit" id="submit" class="post-btn" value="登録">
                </div>
            </div>
        </form>
        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo V3_SITEKEY;?>"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="./js/text.js"></script>
        <script>
            jQuery(function($) {
                $('#input-form').submit(function(event) {
                event.preventDefault();

                let actionName = 'signup';

                grecaptcha.ready(function() {
                  grecaptcha.execute('<?php echo V3_SITEKEY;?>', {action: actionName}).then(function(token) {
                    $('#input-form').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                    $('#input-form').prepend('<input type="hidden" name="action" value="' + actionName + '">');
                    $('#input-form').unbind('submit').submit();
                  });
                });
              });
            });
        </script>
    </body>
</html>