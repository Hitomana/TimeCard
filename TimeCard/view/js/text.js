// テキスト入力の確認
const textConfirm = str => {
    let flag = false;
  
    if (spaceReject(str).length === 0) {
        alert('何も入力されていません');

        flag = false;

    } else if (spaceReject(str).length > 140) {
        alert('140字以内で記入してください');

        flag = false;

    } else {

        flag = true;
    
    }
    
    return flag;
}

// 空白を拒否する
const spaceReject = text => {
    return text.replace(/[\n 　]/g, '');
}

// フォーム入力の確認
const checkInput = () => {

    // それぞれの入力値を取得する
    let userNameLength = document.getElementById('user-name').value.length;
    let userLoginIDLength = document.getElementById('user-id').value.length;
    let userPasswordLength = document.getElementById('user-password').value.length;
    
    // パスワードに使える文字は半角英数字のみ
    let userLoginID = document.getElementById('user-id').value;
    let userPassword = document.getElementById('user-password').value;
    let regex = new RegExp(/^[a-zA-Z0-9]+$/);
  
    // 名前は4文字以上8文字以下
    // ログインIDは8文字以上16文字以下
    // パスワードは10文字以上16文字以下、かつ半角英数字の使用、かつパスワードスコア5以上
    if (((userNameLength >= 4) && (userNameLength <= 8)) && 
       ((userLoginIDLength >= 8) && (userLoginIDLength <= 16) && (regex.test(userLoginID))) &&
       ((userPasswordLength >= 10) && (userPasswordLength <= 16) && 
        (regex.test(userPassword)) && (passwordCheck(userPassword) >= 5))) {

        // 条件を満たす
        return true;

    } else {

        // 条件を満たさない
        document.getElementById('input-caution').innerHTML = '☆以下の要件をすべて満たしてください<br>' + 
                                                             '・名前は4文字以上8文字以下<br><br>' +
                                                             '・ログインIDは8文字以上16文字以下<br>' + 
                                                             '　かつ半角英数字の使用<br><br>' + 
                                                             '・パスワードは10文字以上16文字以下<br>' + 
                                                             '　かつ半角英数字の使用<br>' + 
                                                             '　かつパスワード強度5以上';
        
        return false;

    }
}

// パスワード強度のチェック
const passwordCheck = text => {

    let score = 0;    // パスワードスコア（強度）
    let regexNumber = new RegExp(/^[\x30-\x39]/);    // 数字
    let regexUpper = new RegExp(/^[\x41-\x5A]/);     // 半角英字（大文字）
    let regexLower = new RegExp(/^[\x61-\x7A]/);     // 半角英字（小文字）

    // 数字
    for (let i = 0; i < text.length - 1; i++) {
        let ch =  text.charAt(i);
        let nextCh =  text.charAt(i + 1);

        // 隣り合う文字が異なる種類の文字である場合、スコアアップ
        if (ch.match(regexNumber) && (!nextCh.match(regexNumber))) {
            score++;
        }
    }

    // 半角英字（大文字）
    for (let i = 0; i < text.length - 1; i++) {
        let ch =  text.charAt(i);
        let nextCh =  text.charAt(i + 1);
        if (ch.match(regexUpper) && (!nextCh.match(regexUpper))){
            score++;
        }
    }

    // 半角英字（小文字）
    for (let i = 0; i < text.length - 1; i++) {
        let ch =  text.charAt(i);
        let nextCh =  text.charAt(i + 1);
        if (ch.match(regexLower) && (!nextCh.match(regexLower))){
            score++;
        }
    }
    document.getElementById('password-score').innerHTML = 'パスワード強度：' + score;

    return score;
  }