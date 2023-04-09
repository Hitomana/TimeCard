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
        <div>  
            <form id="input-form" action="../controller/auth_login_controller.php" method="post" name="input-form">

                <!-- エラー表示部（エラー発生時） -->
                <?php include_once('../layout/error.php');?>
    
                <div id="container">
                    <h2 class="form-title"><?php echo LOGIN;?></h2>
                    <div class="form-unit">
                        <label class="label">ログインID</label><br>
                        <input class="input-text" type="text" name="login-id"><br>
                    </div>
                    <div class="form-unit">
                        <label class="label">パスワード</label><br>
                        <input class="input-text" type="password" name="login-password"><br>
                    </div>
                    <input type="hidden" name="csrf-token" value="<?php echo $_SESSION['csrf-token'];?>">
                    <div class="form-btn">
                        <input type="submit" class="post-btn" value="<?php echo LOGIN;?>">
                    </div>
                </div>
            </form>
        </div>
        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo V3_SITEKEY;?>"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script>
            jQuery(function($) {
              $('#input-form').submit(function(event) {
              event.preventDefault();
              let actionName = 'login';
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