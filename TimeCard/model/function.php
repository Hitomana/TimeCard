<?php

// 定数を参照する
const CONST_FILE = '../model/const.php';

// キャッシュを無効化する
function cacheInvalidation() {
    header( 'Expires: Thu, 01 Jan 1970 00:00:00 GMT' );
    header( 'Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT' );

    // HTTP/1.1
    header( 'Cache-Control: no-store, no-cache, must-revalidate' );
    header( 'Cache-Control: post-check=0, pre-check=0', FALSE );

    // HTTP/1.0
    header( 'Pragma: no-cache' );
}

// PDOオプションを取得する
function getPDOOptions() {
    return array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
                 PDO::ATTR_EMULATE_PREPARES => false);
}

// トークンを生成する(CSRF対策用)
function createToken() {    
    // 16*2=32byte
    $bytes = openssl_random_pseudo_bytes(16);
    return bin2hex($bytes);
}

// HTMLにおける特殊文字をエスケープ処理する(XSS対策用)
// 引数:対象文字列、文字コード
function escp(string $str, string $charset = 'UTF-8'): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, $charset);
}

// リダイレクト処理
// 引数:リダイレクトの対象となるページのURL（相対パス）
function redirect($url) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ./'.$url);
}

// 現時点において登録されているデータ数の取得
// 引数:対象のテーブル名
function dataCountOfTable($tableName) {
    require_once(CONST_FILE);
    require_once(SECRET);
    try {    
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "SELECT COUNT(*) AS cnt FROM ".$tableName.";";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        print('データの取得に失敗しました');
        exit();
    } finally {
        $pdo = null;
        return $rows['cnt'];
    }
}

// ユーザーIDの取得
// 引数:ログインID
function manageIdFetch($argId) {
    require_once(CONST_FILE);
    require_once(SECRET);
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "SELECT id FROM ".USERS." WHERE login_id = ?;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $argId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        print('データの取得に失敗しました');
        exit();
    } finally {
        $pdo = null;
        return $result['id'];    
    }
}

// ログイン日時の更新
// 引数:ユーザーID
function updateLoginDate($argId) {
    require_once(CONST_FILE);
    require_once(SECRET);
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "UPDATE ".USERS." SET last_login = ? WHERE id = ?;";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();
        try {
            $stmt->bindValue(1, date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(2, $argId, PDO::PARAM_STR);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        print('データの更新に失敗しました');
        exit();
    } finally {
        $pdo = null;
    }
}

// ログアウト日時の更新
// 引数:ユーザーID
function updateLogoutDate($argId) {
    require_once(CONST_FILE);
    require_once(SECRET);
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "UPDATE ".USERS." SET last_logout = ? WHERE id = ?;";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();
    
        try {
            $stmt->bindValue(1, date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(2, $argId, PDO::PARAM_STR);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        print('データの更新に失敗しました');
        exit();    
    } finally {
        $pdo = null;
    }
}

// タスク状態の保持
// 引数:ユーザーID、タスク種別
function keepTaskStatus($argId, $taskKind) {
    require_once(CONST_FILE);
    require_once(SECRET);
    $statusID = 's'.sprintf('%03d', dataCountOfTable(STATUS) + 1);
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "INSERT INTO ".STATUS." (id, manage_id, task_status, registered) VALUES (?, ?, ?, ?);";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();

        $stmt->bindValue(1, $statusID);
        $stmt->bindValue(2, $argId);
        $stmt->bindValue(3, $taskKind);
        $stmt->bindValue(4, date('Y-m-d H:i:s'));
        $stmt->execute();
        $pdo->commit();
    } catch (PDOException $e) {
        print('データの登録に失敗しました');
        exit();
    } finally {
        $pdo = null;
    }
}

// タスク状態のレコード数が単一であるか確認する
// 引数:ユーザーID
function getTaskStatusRecord($argId) {
    require_once(CONST_FILE);
    require_once(SECRET);
    // 各テーブル内のデータ取得
    try {
        // 作業テーブル
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "SELECT count(*) as cnt FROM ".STATUS." WHERE manage_id = ?;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $argId, PDO::PARAM_STR);
        $stmt->execute();
        
        $cnt = $stmt->fetch(PDO::FETCH_ASSOC);
        $cnt = $cnt['cnt'];
    } catch (PDOException $e) {
        print('データの取得に失敗しました');
        exit();
    } finally {        
        $pdo = null;

        if ($cnt === 1) {
            // レコードが単一行の場合
            return true;
        } else {
            // 単一行のレコードの取得に失敗した場合
            return false;
        }
    }
}

// タスク状態の取得
// 引数:ユーザーID
function getTaskStatus($argId) {
    require_once(CONST_FILE);
    require_once(SECRET);
    // 各テーブル内のデータ取得
    try {
        // 作業テーブル
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "SELECT task_status FROM ".STATUS." WHERE manage_id = ?;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $argId, PDO::PARAM_STR);
        $stmt->execute();
        
        $status = $stmt->fetch(PDO::FETCH_ASSOC);
        $status = $status['task_status'];
    } catch (PDOException $e) {
        print('データの取得に失敗しました');
        exit();
    } finally {        
        $pdo = null;
        
        switch ($status) {
            // 休憩中
            case '00':
                return 0;
                break;
            // 作業 → 休憩
            case '01':
                return 1;
                break;
            // 学習 → 休憩
            case '02':
                return 2;
                break;
            // 作業中
            case '10':
                return 10;
                break;
            // 学習時
            case '20':
                return 20;
                break;
            // 開始前
            default:
                return 99;
                break;
        }        
    }
}

// タスク状態の更新
// 引数:ユーザーID
function updateTaskStatus($argId, $taskKind) {
    require_once(CONST_FILE);
    require_once(SECRET);    
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "UPDATE ".STATUS." set task_status = ? WHERE manage_id = ?;";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();
        try {
            $stmt->bindValue(1, $taskKind, PDO::PARAM_STR);
            $stmt->bindValue(2, $argId, PDO::PARAM_STR);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {        
            $pdo->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        print('データの更新に失敗しました');
        exit();
    } finally {
        $pdo = null;    
    }
}

// 作業詳細記録
// 引数:ユーザーID、テキスト
function writeWorkDetail($argId, $text) {
    require_once(CONST_FILE);
    require_once(SECRET);    
    // 作業IDを取得
    $workId = 'w'.sprintf('%07d', dataCountOfTable(WORKS));
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "UPDATE ".WORKS." set detail = ? WHERE id = ? AND manage_id = ?;";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();
        try {
            $stmt->bindValue(1, $text, PDO::PARAM_STR);
            $stmt->bindValue(2, $workId, PDO::PARAM_STR);
            $stmt->bindValue(3, $argId, PDO::PARAM_STR);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {        
            $pdo->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        print('データの更新に失敗しました');
        exit();
    } finally {
        $pdo = null;    
    }
}

// 学習詳細記録
// 引数:ユーザーID、テキスト
function writeLearnDetail($argId, $text) {
    require_once(CONST_FILE);
    require_once(SECRET);
    // 学習IDを取得
    // ここでは常に最新のデータにアクセスすることになるため、IDを引数で渡さず関数内で取得する
    $learnId = 'l'.sprintf('%07d', dataCountOfTable(LEARNS));
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "UPDATE ".LEARNS." set detail = ? WHERE id = ? AND manage_id = ?;";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();
        try {
            $stmt->bindValue(1, $text, PDO::PARAM_STR);
            $stmt->bindValue(2, $learnId, PDO::PARAM_STR);
            $stmt->bindValue(3, $argId, PDO::PARAM_STR);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        print('データの更新に失敗しました');
        exit();    
    } finally {
        $pdo = null;
    }
}

// 作業終了時刻の記録
// 引数:ユーザーID
function recordWorkFinish($argId) {
    require_once(CONST_FILE);
    require_once(SECRET);
    // 作業IDの取得
    $workId = 'w'.sprintf('%07d', dataCountOfTable(WORKS));
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "UPDATE ".WORKS." set end = ? WHERE id = ? AND manage_id = ?;";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();
        try {
            $stmt->bindValue(1, date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(2, $workId, PDO::PARAM_STR);
            $stmt->bindValue(3, $argId, PDO::PARAM_STR);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        print('データの更新に失敗しました');
        exit();    
    } finally {
        $pdo = null;
    }
}

// 学習終了時刻の記録
// 引数:ユーザーID
function recordLearnFinish($argId) {
    require_once(CONST_FILE);
    require_once(SECRET);
    // 学習IDの取得
    $learnId = 'l'.sprintf('%07d', dataCountOfTable(LEARNS));
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "UPDATE ".LEARNS." set end = ? WHERE id = ? AND manage_id = ?;";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();
        try {
            $stmt->bindValue(1, date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(2, $learnId, PDO::PARAM_STR);
            $stmt->bindValue(3, $argId, PDO::PARAM_STR);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;    
        }
    } catch (PDOException $e) {
        print('データの更新に失敗しました');
        exit();    
    } finally {
        $pdo = null;
    }    
}

// 休憩終了時刻の記録
// 引数:ユーザーID
function recordRestFinish($argId) {
    require_once(CONST_FILE);
    require_once(SECRET);    
    // 休憩IDの取得
    $restId = 'r'.sprintf('%07d', dataCountOfTable(RESTS));
    // 休憩終了時刻の記録
    try {    
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "UPDATE ".RESTS." set end = ? WHERE id = ? AND manage_id = ?;";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();    
        try {
            $stmt->bindValue(1, date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(2, $restId, PDO::PARAM_STR);
            $stmt->bindValue(3, $argId, PDO::PARAM_STR);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        print('データの更新に失敗しました');
        exit();        
    } finally {
        $pdo = null;   
    }
}

// 作業登録
// 引数:ユーザーID
function insertWork($argId)
{
    require_once(CONST_FILE);
    require_once(SECRET);    
    $succeed = false;
    $workId = 'w'.sprintf('%07d', dataCountOfTable(WORKS) + 1);
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "INSERT INTO ".WORKS." (id, manage_id, start, end, detail) VALUES (?, ?, ?, ?, ?);";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();

        $stmt->bindValue(1, $workId);
        $stmt->bindValue(2, $argId);
        $stmt->bindValue(3, date('Y-m-d H:i:s'));
        $stmt->bindValue(4, null);
        $stmt->bindValue(5, '');
        $stmt->execute();
        $pdo->commit();
    } catch (PDOException $e) {
        print('データの登録に失敗しました');
        exit();
    } finally {
        $pdo = null;
        $succeed = true;
        return $succeed;
    }
}

// 学習登録
// 引数:ユーザーID
function insertLearn($argId)
{
    require_once(CONST_FILE);
    require_once(SECRET);    
    $succeed = false;
    $learnId = 'l'.sprintf('%07d', dataCountOfTable(LEARNS) + 1);
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());
        $sql = "INSERT INTO ".LEARNS." (id, manage_id, start, end, detail) VALUES (?, ?, ?, ?, ?);";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();

        $stmt->bindValue(1, $learnId);
        $stmt->bindValue(2, $argId);
        $stmt->bindValue(3, date('Y-m-d H:i:s'));
        $stmt->bindValue(4, null);
        $stmt->bindValue(5, '');
        $stmt->execute();
        $pdo->commit();
    } catch (PDOException $e) {
        print('データの登録に失敗しました');
        exit();
    } finally {
        $pdo = null;
        $succeed = true;
        return $succeed;
    }
}

// 休憩登録
// 引数:ユーザーID
function insertRest($argId)
{
    require_once(CONST_FILE);
    require_once(SECRET);    
    $succeed = false;
    $restId = 'r'.sprintf('%07d', dataCountOfTable(RESTS) + 1);
    try {
        $pdo = new PDO(DNS, USER, PW, getPDOOptions());               
        $sql = "INSERT INTO ".RESTS." (id, manage_id, start, end) VALUES (?, ?, ?, ?);";
        $stmt = $pdo->prepare($sql);
        $pdo->beginTransaction();

        $stmt->bindValue(1, $restId);
        $stmt->bindValue(2, $argId);
        $stmt->bindValue(3, date('Y-m-d H:i:s'));
        $stmt->bindValue(4, null);
        $stmt->execute();
        $pdo->commit();
    } catch (PDOException $e) {
        print('データの登録に失敗しました');
        exit();
    } finally {
        $pdo = null;
        $succeed = true;
        return $succeed;   
    }
}