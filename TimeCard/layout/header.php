<?php
require_once('../model/const.php');
?>

 <!-- メニューを開いたときに画面を操作できないように制御する -->
<div id="modal"></div>

<header id="header">
    <h1 id="site-title"><?php echo SITE_TITLE;?></h1>
    <div id="menu-btn">
        <div class="menu"><span></span><span></span><span></span></div>
    </div>
    <nav id="header-item">
        <ul>
            <a href="./list.php"><li class="menu-list">タスク一覧</li></a>
            <a href="../controller/back_to_task.php"><li class="menu-list">作業に戻る</li></a>
            <a href="../controller/auth_logout_controller.php"><li class="menu-list"><?php echo LOGOUT;?></li></a>
        </ul>
    </nav>
</header>