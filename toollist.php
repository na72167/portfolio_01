<!--アカウント作成関係処理-->
<?php
  //関数関係のファイルを纏めたもの
  require('function.php');

  debug('「「「「「「「「「「「「「「「「「「「');
  debug('ツールリストページ');
  debug('「「「「「「「「「「「「「');
  debugLogStart();

  //ログイン認証
  require('auth.php');
  ?>

<?php
    $Page_Title = 'ツールリストページ';
    require('./head/head-toollist.php');
   ?>

<body>
  <?php
    require('header.php');
    ?>

  <div id="contents" class="site-position">


    <div class="form-container">

      <div class="form">
        <h2 class="title">ツールリストページ</h2>
        <section id="main">
          <a href="registCase.php">案件を登録する</a>
          <a href="caseList.php">案件を見る</a>
          <a href="profEdit.php">プロフィール編集</a>
          <a href="passEdit.php">パスワード変更</a>
          <a href="withdrawal.php">退会</a>
        </section>
      </div>
    </div>


  </div>

  <?php
    require('footer.php');
   ?>
</body>

</html>
