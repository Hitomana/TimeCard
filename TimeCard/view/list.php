<?php
session_start();

// 定数の参照
require_once('../model/const.php');

// リソースの参照
require_once(SECRET);
require_once(FUNCTION_FILE);

// キャッシュ無効化
cacheInvalidation();

// 強制ブラウズ対策
if (empty($_SESSION['user-id'])) {
    
    $_SESSION['error-status'] = 2;    // 不正なリクエスト
    redirect('../view/login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
    <?php include_once('../model/head.php');?>
        <link rel="stylesheet" href="./css/list.css">
    </head>
    <body>
        <?php include_once('../layout/header.php');?>
        <main>
            <form action="./list.php" method="get" id="search-form">
                <input type="date" id="search" name="search" class="input-text" value="<?php if (isset($_GET['search'])) { echo $_GET['search']; } else { echo date('Y-m-d'); }  ?>">
                <div id="select-work-and-learn">
                    <label for="work"><input type="radio" name="radio" id="work" value="0" checked>作業</label>
                    <label for="learn"><input type="radio" name="radio" id="learn" value="1" <?php if (isset($_GET['radio']) && $_GET['radio'] == "1") echo 'checked'; ?>>学習</label>
                </div>
                <button type="submit" id="search-btn" class="search-btn"><img src="../image/search.webp" alt="検索ボタン" class="search-btn"></button>    
            </form>     
        </main>
        <div id="contents"></div>
        <?php

            // 作業データ取得
            function workListGet($argId, $argDate) {
                require_once('../model/const.php');
                require_once(SECRET);
                require_once(FUNCTION_FILE);
                $works = array();
                    try {
                    $pdo = new PDO(DNS, USER, PW, getPDOoptions());
                    $sql = "SELECT id, start, end, detail FROM ".WORKS." WHERE manage_id = ? AND DATE(start) = '".$argDate."';";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(1, $argId, PDO::PARAM_STR);
                    $stmt->execute();
                    while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $works[] = array(
                            'start' => $result['start'],
                            'end' => $result['end'],
                            'detail' => $result['detail']
                        );
                    }
                    return json_encode($works, true);
                } catch (PDOException $e) {
                    print('データを取得できませんでした');
                    exit();
                }
            }

            // 学習データ取得
            function learnListGet($argId, $argDate) {
                require_once('../model/const.php');
                require_once(SECRET);
                require_once(FUNCTION_FILE);
                $learns = array();
                try {
                    $pdo = new PDO(DNS, USER, PW, getPDOoptions());
                    $sql = "SELECT id, start, end, detail FROM ".LEARNS." WHERE manage_id = ? AND DATE(start) = '".$argDate."';";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(1, $argId, PDO::PARAM_STR);
                    $stmt->execute();
                    while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $learns[] = array(
                            'start' => $result['start'],
                            'end' => $result['end'],
                            'detail' => $result['detail']
                        );
                    }
                    return json_encode($learns, true);
                } catch (PDOException $e) {
                    print('データを取得できませんでした');
                    exit();
                }
            }

            // タスクの種別に応じて取得データを変える
            function taskKind() {

                if (isset($_GET['search'])) {
                    $searchDate = $_GET['search'];
                } else {
                    $searchDate = date('Y-m-d');
                }

                if (isset($_GET['radio'])) {
                    if ($_GET['radio'] === '1') {
                        $activeList = learnListGet($_SESSION['user-id'], $searchDate);
                        
                    } else {
                        $activeList = workListGet($_SESSION['user-id'], $searchDate);
                    }
                } else {
                    $activeList = workListGet($_SESSION['user-id'], $searchDate);
                }

                return $activeList;
            }
        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script src="../view/js/animation.js"></script>
        <script>
            
            // PHP呼び出し
            let acts = <?= taskKind(); ?>;
            let obj = acts;

            // 取得データ表示部
            if (!Object.keys(obj).length) {
                document.getElementById("contents").innerHTML = '現在データはありません';
            } else {
                let table = document.createElement('table');
                table.setAttribute('style', 'border-bottom: 2px solid blue; border-spacing: 0; margin-bottom: 100px;');
                table.setAttribute("id", "task-list");

                document.getElementById('contents').appendChild(table);
                let thead = document.createElement('thead');

                let tbody = document.createElement('tbody');

                table.appendChild(thead);
                table.appendChild(tbody);
        
                let head = document.createElement('tr');        
                thead.appendChild(head);

                let actDate = document.createElement('th');
                let actStart = document.createElement('th');
                let actEnd = document.createElement('th');
                let actDetail = document.createElement('th');

                actDate.setAttribute('style', 'border-bottom: 4px solid blue;');
                actStart.setAttribute('style', 'border-bottom: 4px solid blue;');
                actEnd.setAttribute('style', 'border-bottom: 4px solid blue;');
                actDetail.setAttribute('style', 'border-bottom: 4px solid blue;');

                head.appendChild(actDate);
                head.appendChild(actStart);
                head.appendChild(actEnd);
                head.appendChild(actDetail);

                actDate.innerHTML = '日付'
                actStart.innerHTML = '開始時刻';
                actEnd.innerHTML = '終了時刻';
                actDetail.innerHTML = '詳細';

                actDate.setAttribute('id', 'date');
                actStart.setAttribute('id', 'start');
                actEnd.setAttribute('id', 'end');
                actDetail.setAttribute('id', 'detail');        

                for (let i in acts) {

                    let dt;
                    let start;
                    let end;
                    let detail;

                    dt = obj[i]['start'].substr(5, 2) + '/' + obj[i]['start'].substr(8, 2);
                    start = obj[i]['start'].substr(-8, 8);
                    end = obj[i]['end'];

                    if (end === null) {

                        // 未登録の時刻がある場合は「未」と表示する
                        end = '<span style="display: block; width: 60px;">未</span>';                        
                        
                    } else {

                        end = obj[i]['end'].substr(-8, 8);   
                    }
                    
                    detail = obj[i]['detail'];

                    let bodyData = document.createElement('tr');

                    tbody.appendChild(bodyData);

                    let dateRow = document.createElement('td');
                    let startRow = document.createElement('td');
                    let endRow = document.createElement('td');
                    let detailRow = document.createElement('td');

                    dateRow.setAttribute('style', 'border-bottom: 1px solid blue;');
                    startRow.setAttribute('style', 'border-bottom: 1px solid blue;');
                    endRow.setAttribute('style', 'border-bottom: 1px solid blue;');
                    detailRow.setAttribute('style', 'border-bottom: 1px solid blue;');

                    bodyData.appendChild(dateRow);
                    bodyData.appendChild(startRow);
                    bodyData.appendChild(endRow);
                    bodyData.appendChild(detailRow);

                    dateRow.setAttribute("class", "date-row");             
                    startRow.setAttribute("class", "start-row");
                    endRow.setAttribute("class", "end-row");
                    detailRow.setAttribute("class", "detail-row");
    
                    dateRow.innerHTML = dt;
                    startRow.innerHTML = start;
                    endRow.innerHTML = end;
                    detailRow.innerHTML = detail; 
                }
            }
        </script>        
    </body>
</html>