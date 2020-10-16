<?php

 // ログイン関係の確認処理・自動ログアウト処理

 //ログイン成功時
if( !empty($_SESSION['login_date']) ){
  debug('ログイン済みユーザーです。');

 // 現在日時が最終ログイン日時+有効期限を超えているかの判定(time関数はunixタイムスタンプを使っている為,1970年1月1日からtime関数を使った時点の日時間を秒数に変えた数字を入る。例1970/1/1 00:01:28に使った場合88がlogin_dateのvalueとなる。)
if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
  debug('ログイン有効期限オーバーです。');

 //セッション削除(ログイン判定に引っかからせてログアウトさせる。)
  session_destroy();

 //ログインページへリダイレクト
  header("Location:login.php");
}else{
  debug('ログイン有効期限以内です。');
  //time関数で現在時刻を引っ張ってきて入れ直す。(最終ログイン日時を現在日時に更新)
  $_SESSION['login_date'] = time();
 }
}
?>
