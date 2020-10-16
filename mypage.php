<?php
//共通変数・関数ファイルを読込み deta
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

// 画面表示用データ取得
//================================
$u_id = $_SESSION['user_id'];
// DBから商品データを取得
$productData = getMyProducts($u_id);
// DBから連絡掲示板データを取得
$bordData = getMyMsgsAndBord($u_id);
// DBからお気に入りデータを取得
$likeData = getMyLike($u_id);



// DBからきちんとデータがすべて取れているかのチェックは行わず、取れなければ何も表示しないこととする

debug('取得した商品データ：'.print_r($productData,true));
debug('取得した掲示板データ：'.print_r($bordData,true));
debug('取得したお気に入りデータ：'.print_r($likeData,true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$Page_Title = 'マイページ';
require('./head/head-mypage.php');
?>

<body class="page-mypage page-2colum page-logined">

  <!-- メニュー -->
  <?php
      require('header.php');
    ?>


    <p id="js-show-msg" style="display:none;" class="msg-slide">
   <?php echo getSessionFlash('msg_success'); ?>
    </p>


  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <h1 class="page-title">MYPAGE</h1>
    <!-- Main -->
    <section id="main">
      <section class="list panel-list">
        <h2 class="title" style="margin-bottom:15px;">
          登録案件一覧
        </h2>

        <?php
        rsort($productData);
        if(!empty($productData)):
        foreach ($productData as $key => $val):{
        if ($key >= '4') {
          break;
         }
        }
        ?>

        <a href="caseDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
          <div class="panel-head">
            <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['id']); ?>">
          </div>

          <div class="panel-body">
            <p class="panel-title"><?php echo sanitize($val['name']); ?> <span class="price">¥<?php echo sanitize(number_format($val['price'])); ?></span></p>
          </div>
        </a>
        <?php
              endforeach;
             endif;
            ?>
      </section>


      <section class="list list-table">
        <h2 class="title">
          連絡掲示板一覧
        </h2>
        <table class="table">
          <thead>
            <tr>
              <th>最新送信日時</th>
              <th>送信者名</th>
              <th>メッセージ</th>
            </tr>
          </thead>
          <tbody>
            <?php
              rsort($bordData);
              if(!empty($bordData)){
                foreach($bordData as $key => $val){
                  if ($key >= '4') {
                    break;
                  }
                 if(!empty($val['msg'])){
                    $msg = array_shift($val['msg']);
             ?>
            <tr>
              <td><?php echo sanitize(date('Y.m.d H:i:s',strtotime($msg['send_date']))); ?></td>

              <td><?php echo sanitize($msg['name']); ?>
              </td>

              <td><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>"><?php echo mb_substr(sanitize($msg['msg']),0,40); ?>...</a></td>
            </tr>
            <?php
                  }else{
             ?>
            <tr>
              <td>--</td>
              <td>|</td>
              <td><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>">まだメッセージはありません</a></td>
            </tr>
            <?php
                  }
                }
              }
            ?>
          </tbody>
        </table>
      </section>

      <section class="list panel-list">
        <h2 class="title" style="margin-bottom:15px;">
          お気に入り一覧
        </h2>

        <?php
        rsort($likeData);
        if(!empty($likeData)):
        foreach($likeData as $key =>
        $val):{ if ($key >= '4') {
            break;
           }
          }
            ?>
        <a href="caseDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
          <div class="panel-head">
            <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
          </div>
          <div class="panel-body">
            <p class="panel-title"><?php echo sanitize($val['name']); ?> <span class="price">¥<?php echo sanitize(number_format($val['price'])); ?></span></p>
          </div>
        </a>

        <?php
            endforeach;
           endif;
          ?>

      </section>
    </section>
    <!-- サイドバー -->
    <?php
        require('sidebar_mypage.php');
      ?>
  </div>

  <!-- footer -->
  <?php
     require('footer.php');
    ?>

