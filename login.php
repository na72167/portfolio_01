 <?php

//バリ関係の関数やDB関係ファイルの読込み

    require('function.php');

    debug('「「「「「');
    debug('ログインページ');
    debug('「「「「「');
    debugLogStart();

    //ログイン確認関係のファイルを読み込む
    require('auth.php');

  if(!empty($_POST)){
    debug('POST送信があります。');

    //変数にユーザー情報を代入
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    //パスワードが入力されているかどうかを関数側に伝える変数
    $pass_save = (!empty($_POST['pass_save'])) ? true : false; //略記法

    //emailの形式チェック
    validEmail($email, 'email');
    //emailの最大文字数チェック
    validMaxLen($email, 'email');

    //パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    //パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    //パスワードの最小文字数チェック
    validMinLen($pass, 'pass');

    //未入力チェック
    validRequired($email, 'email');
    validRequired($pass, 'pass');

    //ログイン判定と処理

    if(empty($err_ms)){
    debug('バリデーションOK。');
    try {

      $dbh = dbConnect();

      // 登録情報を引っ張り出す処理(delete_flg = 0は退会処理をしたアカウントと既存アカウントを区別する為につけている。)
      $sql = 'SELECT password,email,id  FROM users WHERE email = :email AND delete_flg = 0';

      //email変数を扱いやすくする為にキーをつける処理を挟む。
      $data = array(':email' => $email);

      // クエリ文実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ結果の値を取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      //第二引数のtrueの意味を調べる====
      debug('クエリ結果の中身：'.print_r($result,true));

      // パスワード照合(result内にはstmtの一つしか要素が無いはずなのにshiftを使っているかを確認する)

       if(!empty($result) && password_verify($pass, array_shift($result))){
        debug('パスワードがマッチしました。');

        //ログイン有効期限（デフォルトを１時間とする。この後の処理で30日分伸ばす為調整しやすい数字をデフォルトにしている。）
        $LoginLimit = 60*60;

        // 最終ログイン日時を現在日時に
        $_SESSION['login_date'] = time(); //time関数はunixタイムスタンプを使っている為,1970年1月1日からtime関数を使った時点の日時間を秒数に変えた数字を入る。例1970/1/1 00:01:28に使った場合88がlogin_dateのvalueとなる。

        // ログイン保持にチェックがある場合
        if($pass_save){
          debug('ログイン情報確認');
          // ログイン有効期限を30日にしてセット
          $_SESSION['login_limit'] = $LoginLimit * 24 * 30;
        }else{
          debug('ログイン情報がありません。');
          // 次回からログイン保持しないので、ログイン有効期限を1時間後にセット
          $_SESSION['login_limit'] = $LoginLimit;
        }
        // ユーザーIDを格納
        $_SESSION['user_id'] = $result['id'];

        debug('セッション変数の中身：'.print_r($_SESSION,true));

        debug('マイページへ移動します。');
        header("Location:mypage.php"); //マイページへ

      }else{
        debug('パスワードが会いません。');
        $err_ms['pass'] = ERROR_MS_09;
      }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_ms['common'] = ERROR_MS_07;
    }
   }
  }
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<');
?>


 <?php
    $Page_Title = 'ログインページ';
    require('./head/head-login.php');
    ?>

 <body>

   <?php
    require('header.php');
    ?>

   <div id="contents">

     <section id="main">

       <div class="form-container">

         <form action="" method="post" class="form">
           <h2 class="title">ログイン</h2>

           <!--例外処理関係のエラー文の出力-->
           <div class="area-msg">
             <?php
                if(!empty($err_ms['common'])) echo $err_ms['common'];
               ?>
           </div>

           <!--email関係の処理-->

           <!--エラー発生時色変え用classを追加する処理(emailフォーム用)-->

           <!--メアド入力フォーム-->
           <label class="<?php if(!empty($err_ms['email'])) echo 'err'; ?>">
             Email
             <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
           </label>

           <div class="area-ms">
             <?php
             if(!empty($err_ms['email'])) echo $err_ms['email'];
             ?>
           </div>


           <!--パスワード入力フォーム-->
           <label class="<?php if(!empty($err_ms['pass'])) echo 'err'; ?>">
             パスワード
             <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
           </label>

           <div class="area-msg">
             <?php
              if(!empty($err_ms['pass'])) echo $err_ms['pass'];
              ?>
           </div>

           <label>
             <input type="checkbox" name="pass_save">次回ログインを省略する
           </label>

           <div class="btn-container">
             <input type="submit" class="btn btn-mid" value="ログイン">
           </div>

         </form>
       </div>

     </section>

   </div>

   <?php
     require('footer.php');
    ?>

 </body>

 </html>
