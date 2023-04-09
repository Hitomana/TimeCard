<?php
if (empty($_SESSION['error-status'])) {
    // エラー情報初期化
    $_SESSION['error-status'] = 0;
} else {
    switch (intval($_SESSION['error-status'])) {
        case 1:
            echo '<p class="caution">必須項目が入力されていません</p>';    
            break;
        case 2:
            echo '<p class="caution">不正なリクエストです</p>';
            break;
        case 3:
            echo '<p class="caution">ログインに失敗しました</p>';
            break;
        case 4:
            echo '<p class="caution">入力された情報は既に登録されています</p>';  
            break;
        default:
            echo '';
            break;
    }
    // エラー情報初期化
    $_SESSION['error-status'] = 0;
}
?>