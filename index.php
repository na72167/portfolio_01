<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　トップページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

?>
<!--外部ファイルなど読み込む処理-->
<?php
     $Page_Title = 'ホーム';
     require('./head/head.php');
    ?>

<body>

  　　
  <!--ヘッダー読み込み-->
  <?php
     require('header.php');
    ?>

  <!--メイン画像関係-->
  <main>

    <section class="hero" id="top-title">
      <div class="hero-title">Connecting people・Connecting case<br><span>人々と案件を繋ぐ</span></div>
    </section>

    <section id="about">
      <div class="container container-lightGray">
        <h2 class="container-title container-title-lightGray"><span>ABOUT</span></h2>
        <div class="container-body">
          <p>レスポンシブWebデザインとは、どんな大きさの画面でも見やすく、使いやすいWebサイトにするために必要なものです。
            1つのHTML（Webサイトに載せる情報）で配信し、CSS（情報の大きさや位置など表示の指定）はデバイスごとに用意して表示を変えさせます。
            内容は全て同じですが見せ方が違うということです。HTMLが１つなのでWebサイトに載せる情報を修正や更新する時に作業が1回で済みます。
            デバイスごとにHTMLがあると修正や更新の作業をHTMLの数だけしなくてはなりません。
            スマホに対応したWebサイトが検索結果の上位に表示されやすくなる「モバイルフレンドリー」や、スマホ用のページが順位の決め手になる「モバイルファーストインデックス」に対応できます。</p>
        </div>
      </div>
    </section>

    <section class="bgColor" id="latest case">
      <div class="container container-lightGray">
        <h2 class="container-title"><span>LATEST CASE</span></h2>
        <div class="container-body">
          <div class="panel-group panel-group-flex">
            <div class="panel panel-border panel-cource">
              <div class="panel-head">
                <span class="ft-corp ft-l">Bussiness</span>コース
              </div>
              <div class="panel-body">
                <img src="./images/price1.jpg" alt="">
              </div>
              <div class="panel-foot">
                <p>多色使いで髪に立体的な表情が生まれ必要な部分にカラーリングを施すので髪全体の負担が少なくなります。ハイライト、又はローライトなどが含まれます。</p>
              </div>
            </div>
            <div class="panel panel-border panel-cource panel-active">
              <span class="panel-badge">
                オス<br>スメ
              </span>
              <div class="panel-head">
                <span class="ft-corp ft-l">Advance</span>コース
              </div>
              <div class="panel-body">
                <img src="./images/price2.jpg" alt="">
              </div>
              <div class="panel-foot">
                <p>髪に水分をたっぷり保ちながらぷるんとした自然な仕上がりのデジタルパーマです。低温でロットを巻くので髪へのダメージを最小限に抑えられ、まとまりのある髪になります。</p>
              </div>
            </div>
            <div class="panel panel-border panel-cource">
              <div class="panel-head">
                <span class="ft-corp ft-l">Beginners</span>コース
              </div>
              <div class="panel-body">
                <img src="./images/price3.jpg" alt="">
              </div>
              <div class="panel-foot">
                <p>綺麗な髪はまず頭皮から。毛穴の詰まりを除去したり、頭皮のトリートメントで健康な髪と頭皮に。</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container container-ornament" id="contact">
      <h2 class="container-title"><span>CONTACT</span></h2>
      <div class="container-body">
        <form action="" class="form form-m">
          <input class="input input-l" type="text" placeholder="お名前">
          <input class="input input-l" type="email" placeholder="email">
          <textarea class="input input-l input-textarea mb-xxl" placeholder="お問い合わせ内容"></textarea>
          <button class="btn btn-corp btn-l">送信</button>
        </form>
      </div>
    </section>

  </main>

  <?php
     require('footer.php');
    ?>

</body>

</html>
