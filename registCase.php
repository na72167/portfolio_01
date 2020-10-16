<?php

//共通変数・関数ファイルを読込み name
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　案件登録・編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

// 画面処理

// 画面表示用データ取得

// GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';

// DBから商品データを取得
$dbFormData = (!empty($p_id)) ? getProduct($_SESSION['user_id'], $p_id) : '';
// 新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
// DBからカテゴリデータを取得
$dbCategoryData = getCategory();

//デバッグ出力
debug('商品ID：'.$p_id);
debug('フォーム用DBデータ：'.print_r($dbFormData,true));
debug('カテゴリデータ：'.print_r($dbCategoryData,true));

// パラメータ改ざんチェック
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる。(urlのクエリパラメータを触ってリクエストした場合,dbformdataを初期化させる処理が走ってない為必ず空になる。)
//現段階ではまだ動かない
if(!empty($p_id) && empty($dbFormData)){
  debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
  header("Location:mypage.php"); //マイページへ
}

// POST送信時処理(print_rの第二引数にtrueを入れると戻り値が文字列になる。)
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));

  //変数にユーザー情報を代入
  $name = $_POST['name'];
  $category = $_POST['category_id'];

  //０や空文字の場合は０を入れる。デフォルトのフォームには０が入っている。
  $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;

  $comment = $_POST['comment'];

  //画像をアップロードし、パスを格納(uploadImg関数はアップロード画像のバリテーション関係などの処理をしている。)
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1') : '';
  // 画像を登録していないが既にDBに登録されている場合、(DBにコンソール等を使って直接テストデータを入れた場合など)パスを入れる。（POSTには反映されないのでエラー出る。)
  $pic1 = ( empty($pic1) && !empty($dbFormData['pic1']) ) ? $dbFormData['pic1'] : $pic1;

  //2,3の処理は1と同じくバリ処理＋DB確認など
  $pic2 = ( !empty($_FILES['pic2']['name']) ) ? uploadImg($_FILES['pic2'],'pic2') : '';
  $pic2 = ( empty($pic2) && !empty($dbFormData['pic2']) ) ? $dbFormData['pic2'] : $pic2;

  $pic3 = ( !empty($_FILES['pic3']['name']) ) ? uploadImg($_FILES['pic3'],'pic3') : '';
  $pic3 = ( empty($pic3) && !empty($dbFormData['pic3']) ) ? $dbFormData['pic3'] : $pic3;

  // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if(empty($dbFormData)){
    //未入力チェック
    validRequired($name, 'name');
    //最大文字数チェック
    validMaxLen($name, 'name');
    //セレクトボックスチェック
    validSelect($category, 'category_id');
    //最大文字数チェック
    validMaxLen($comment, 'comment', 500);
    //未入力チェック
    validRequired($price, 'price');
    //半角数字チェック
    validNumber($price, 'price');
  }else{
    if($dbFormData['name'] !== $name){
      //未入力チェック
      validRequired($name, 'name');
      //最大文字数チェック
      validMaxLen($name, 'name');
    }
    if($dbFormData['category_id'] !== $category){
      //セレクトボックスチェック
      validSelect($category, 'category_id');
    }
    if($dbFormData['comment'] !== $comment){
      //最大文字数チェック
      validMaxLen($comment, 'comment', 500);
    }
    if($dbFormData['price'] != $price){
      //未入力チェック
      validRequired($price, 'price');
      //半角数字チェック
      validNumber($price, 'price');
    }
  }

  if(empty($err_ms)){
    debug('バリデーションOKです。');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      // 編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
      if($edit_flg){
        debug('DB更新です。');
        $sql = 'UPDATE products SET name = :name, category_id = :category, price = :price, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :p_id';
        $data = array(':name' => $name , ':category' => $category, ':price' => $price, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
      }else{
        debug('DB新規登録です。');
        $sql = 'insert into products(name,user_id, category_id, price,comment, pic1, pic2, pic3,create_date ) values (:name, :u_id,:category,:price,:comment,:pic1, :pic2,:pic3,:date)';
        $data = array(':name' => $name , ':u_id' => $_SESSION['user_id'], ':category' => $category, ':price' => $price, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3,':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL：'.$sql);
      debug('流し込みデータ：'.print_r($data,true));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if($stmt){
        $_SESSION['msg_success'] = SUCCESS_MS_04;
        debug('マイページへ遷移します。');
        header("Location:mypage.php"); //マイページへ
      }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_ms['common'] = ERROR_MS_07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$Page_Title = (!$edit_flg) ? '新規案件登録' : '案件編集';
require('./head/head-registCase.php');
?>

<body class="page-profEdit page-2colum page-logined">

  <!-- メニュー -->
  <?php
    require('header.php');
    ?>

  <!-- メインコンテンツ -->

  <div id="contents" class="site-width">
    <h1 class="page-title"><?php echo (!$edit_flg) ? '新規案件登録' : '案件編集'; ?></h1>
    <!-- Main -->
    <div class="main-position">
      <section id="main">
        <div class="form-container">
          <form action="" method="post" class="form" enctype="multipart/form-data" style="width:100%;box-sizing:border-box;">
            <div class="area-msg">
              <?php
              if(!empty($err_ms['common'])) echo $err_ms['common'];
              ?>
            </div>
            <label class="<?php if(!empty($err_ms['name'])) echo 'err'; ?>">
              案件名<span class="label-require">必須</span>
              <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
            </label>

            <div class="area-msg">
              <?php
              if(!empty($err_ms['name'])) echo $err_ms['name'];
              ?>
            </div>

            <label class="<?php if(!empty($err_ms['category_id'])) echo 'err'; ?>">
              カテゴリ<span class="label-require">必須</span>

              <select name="category_id" id="">

                <option value="0" <?php if(getFormData('category_id') == 0 ){ echo 'selected'; } ?>>選択してください</option>
                <?php
                  foreach($dbCategoryData as $key => $val){
                ?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData('category_id') == $val['id'] ){ echo 'selected'; } ?>>
                  <?php echo $val['name']; ?>
                </option>
                <?php
                  }
                ?>
              </select>
            </label>
            <div class="area-msg">
              <?php
              if(!empty($err_ms['category_id'])) echo $err_ms['category_id'];
              ?>
            </div>
            <label class="<?php if(!empty($err_ms['comment'])) echo 'err'; ?>">
              詳細
              <textarea name="comment" id="js-count" cols="30" rows="10" style="height:150px;">-----詳細テンプレート-----・案件名・職種・勤務地（都道府県名）・勤務地(最寄り駅など)・単価(税込)・案件内容■スキル： ■人数 ： ■性別 ： ■国籍 ： ■特記<?php echo getFormData('comment'); ?></textarea>
            </label>
            <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>

            <div class="area-msg">
              <?php
              if(!empty($err_ms['comment'])) echo $err_ms['comment'];
              ?>
            </div>
            <label style="text-align:left;" class="<?php if(!empty($err_ms['price'])) echo 'err'; ?>">
              単価<span class="label-require">必須</span>
              <div class="form-group">
                <input type="text" name="price" style="width:150px" placeholder="10,000" value="<?php echo (!empty(getFormData('price'))) ? getFormData('price') : 0; ?>"><span class="option">円</span>
              </div>
            </label>
            <div class="area-msg">
              <?php
              if(!empty($err_ms['price'])) echo $err_ms['price'];
              ?>
            </div>
            <div style="overflow:hidden;">

              <div class="imgDrop-container">
                画像1

                <!--エラー関係の処理-->
                <label class="area-drop <?php if(!empty($err_ms['pic1'])) echo 'err'; ?>">

                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">

                  <!--ファイル送信処理-->
                  <input type="file" name="pic1" class="input-file">

                  <!--サンプル表示-->
                  <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic1'))) echo 'display:none;' ?>">

                  ドラッグ＆ドロップ
                </label>

                <div class="area-msg">
                  <?php
                  if(!empty($err_ms['pic1'])) echo $err_ms['pic1'];
                  ?>
                </div>
              </div>

              <div class="imgDrop-container">
                画像２
                <label class="area-drop <?php if(!empty($err_ms['pic2'])) echo 'err'; ?>">
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic2" class="input-file">
                  <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic2'))) echo 'display:none;' ?>">
                  ドラッグ＆ドロップ
                </label>
                <div class="area-msg">
                  <?php
                  if(!empty($err_ms['pic2'])) echo $err_ms['pic2'];
                  ?>
                </div>
              </div>
              <div class="imgDrop-container">
                画像３
                <label class="area-drop <?php if(!empty($err_ms['pic3'])) echo 'err'; ?>">
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic3" class="input-file">
                  <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic3'))) echo 'display:none;' ?>">
                  ドラッグ＆ドロップ
                </label>
                <div class="area-msg">
                  <?php
                  if(!empty($err_ms['pic3'])) echo $err_ms['pic3'];
                  ?>
                </div>
              </div>
            </div>

            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="<?php echo (!$edit_flg) ? '出案する' : '更新する'; ?>">
            </div>
          </form>
        </div>
      </section>

      <!-- サイドバー -->
      <?php
      require('sidebar_mypage.php');
      ?>
    </div>
  </div>
  <!-- footer -->

  <?php
     require('footer.php');
    ?>
</body>

</html>
