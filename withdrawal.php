<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

//退会関係の処理
if(!empty($_POST)){
  debug('POST送信があります。');
  try {
    $dbh = dbConnect();
    // 退会周りのSQL文作成(今回は物理削除では無く論理削除の処理を使って退会処理を行う。
    //具体的な退会処理の内容は,対象データのフラグを立てる事でログイン時の対象から外させる事で擬似的にデータを削除した時と同じ状況になる様にしている。
    //この処理のメリットは物理削除と違い,退会後にも復元処理が出来る。フラグ立ててから一定時間後削除する処理を忘れずに。)
    $sql1 = 'UPDATE users SET  delete_flg = 1 WHERE id = :us_id';
    $sql2 = 'UPDATE product SET  delete_flg = 1 WHERE user_id = :us_id';
    $sql3 = 'UPDATE like SET  delete_flg = 1 WHERE user_id = :us_id';
    // データ流し込み
    $data = array(':us_id' => $_SESSION['user_id']);
    // クエリ実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    // クエリ実行成功の場合（最悪userテーブルのみ削除成功していれば良しとする。他のテーブルはuserテーブルと紐付けている為。）
    if($stmt1){
     //セッション削除
      session_destroy();
      debug('セッション変数の中身：'.print_r($_SESSION,true));
      debug('トップページへ遷移します。');
      header("Location:index.php");
    }else{
      debug('クエリが失敗しました。');
      $err_ms['common'] = 'ERROR_MS_07';
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_ms['common'] = MS07;
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$Page_Title = '退会';
require('./head/head-withdrawal.php');
?>


<body>

  <!-- メニュー -->
  <?php
    require('header.php');
    ?>

  <div id="contents" class="site-position">

    <section class="withdrawal">
      <!--メインコンテンツ-->

      <article class="main">

        <div class="form-container">

          <form action="" method="post">
            <h2 class="title">退会</h2>
            <!--例外処理関係のエラー文の出力-->

            <div class="area-msg">
              <?php
                  if(!empty($err_ms['common'])) echo $err_ms['common'];
                 ?>
            </div>

            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="退会する" name="submit">
            </div>

          </form>

        </div>
        <a href="mypage.php">&lt; マイページに戻る</a>

      </article>
    </section>
  </div>
  <?php
    require('footer.php');
    ?>
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <script src="./js/app.js"></script>

</body>

</html>
