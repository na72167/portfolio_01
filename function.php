<?php

//ログ出力関係の設定
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

$debug_flg = true;

function debug($string){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$string);
  }
}

function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug( 'ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
  }
}

// デバッグ関係ここまで



// セッション準備・セッション有効期限を延長する

//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");

//ガーベージコレクションが削除するセッションの有効期限を設定（第二引数の数字はセッションの有効期限を1ヶ月にする為の式。30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);

//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);

//セッションを使う
session_start();

//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//セッション関係はここまで



//ここからバリテーション関係やDB関係の関数

 //エラーメッセージ関係の定数
 define('ERROR_MS_01','入力必須です');
 define('ERROR_MS_02','Emailの形式で入力してください');
 define('ERROR_MS_03','パスワード(再入力)が合っていません');
 define('ERROR_MS_04','半角英数字のみご利用いただけます');
 define('ERROR_MS_05','6文字以上で入力してください');
 define('ERROR_MS_06','256文字以内で入力してください');
 define('ERROR_MS_07','エラーが発生しました。しばらく経ってからやり直してください。');
 define('ERROR_MS_08','そのEmailはすでに登録されています');
 define('ERROR_MS_09','メールアドレスまたはパスワードが違います');
 define('ERROR_MS_10', '電話番号の形式が違います');
 define('ERROR_MS_11', '郵便番号の形式が違います');
 define('ERROR_MS_12', '古いパスワードが違います');
 define('ERROR_MS_13', '古いパスワードと同じです');
 define('ERROR_MS_14', '文字で入力してください');
 define('ERROR_MS_15', '正しくありません');
 define('ERROR_MS_16', '有効期限が切れています');
 define('ERROR_MS_17', '半角数字のみご利用いただけます');
 define('SUCCESS_MS_01', 'パスワードを変更しました');
 define('SUCCESS_MS_02', 'プロフィールを変更しました');
 define('SUCCESS_MS_03', 'メールを送信しました');
 define('SUCCESS_MS_04', '登録しました');
 define('SUCCESS_MS_05', '受注しました！相手と連絡を取りましょう！');

//エラメ出力用の空配列
$err_ms = array();

//DB接続関数
function dbConnect(){
  //DBへの接続準備
  $dsn = 'mysql:dbname=portfolioDB;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

function isLogin(){
  // ログインしている場合
  if( !empty($_SESSION['login_date']) ){
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです。');

      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です。');
      return true;
    }

  }else{
    debug('未ログインユーザーです。');
    return false;
  }
}

//バリテーション関係の関数

//入力チェック
function validRequired($string,$key){
  if(empty($string)){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_01;
  }
}

//email確認
function validEmail($string, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $string)){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_02;
  }
}

//確認用パスワードのチェック
function validMatch($string1, $string2, $key){
  if($string1 !== $string2){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_03;
  }
}

//半角チェック
function validHalf($string, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $key)){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_04;
  }
}

//最小文字数チェック
function validMinLen($string, $key, $min = 6){
  if(mb_strlen($string) < $min){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_05;
  }
}

//最大文字数チェック
function validMaxLen($string, $key, $max = 255){
  if(mb_strlen($string) > $max){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_06;
  }
}

//電話番号形式チェック
function validTel($string, $key){
  if(!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $string)){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_10;
  }
}
//郵便番号形式チェック
function validZip($string, $key){
  if(!preg_match("/^\d{7}$/", $string)){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_11;
  }
}
//半角数字チェック
function validNumber($string, $key){
  if(!preg_match("/^[0-9]+$/", $string)){
    global $err_ms;
    $err_ms[$key] = ERROR_MS_04;
  }
}


//固定長チェック
function validLength($string, $key, $len = 8){
  if( mb_strlen($string) !== $len ){
    global $err_ms;
    $err_ms[$key] = $len.ERROR_MS_14;
  }
}
//パスワードチェック
function validPass($str, $key){
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}
//selectboxチェック
function validSelect($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = ERROR_MS_15;
  }
}
//エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}


//email重複確認
function validEmailDup($email){
  global $err_ms;

  try {
  //DB接続処理関数
    $dbh = dbConnect();
  //DBからemailデータを引っ張ってくる処理を詰める処理等
    $sql = 'SELECT count(*) FROM users WHERE email = :email';
    $data = array(':email' => $email);
  //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
  // クエリ結果を変数に代入
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if(!empty(array_shift($result))){
      $err_ms['email'] = ERROR_MS_08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_ms['common'] = ERROR_MS_07;
  }
}

//SQL実行関数
function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  $stmt->execute($data);
  return $stmt;
}

//データベース編集関係の関数など
function getFormData($string){
  global $dbFormData;
  // ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_ms[$string])){
      //POSTにデータがある場合
      if(isset($_POST[$string])){//金額や郵便番号などのフォームで数字や数値の0が入っている場合もあるので、issetを使うこと
        return $_POST[$string];
      }else{
        //ない場合（フォームにエラーがある＝POSTされてるハズなので、まずありえないが）はDBの情報を表示
        return $dbFormData[$string];
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合（このフォームも変更していてエラーはないが、他のフォームでひっかかっている状態）
      if(isset($_POST[$string]) && $_POST[$string] !== $dbFormData[$string]){
        return $_POST[$string];
      }else{//そもそも変更していない
        return $dbFormData[$string];
      }
    }
  }else{
    if(isset($_POST[$string])){
      return $_POST[$string];
    }
  }
}

//商品情報を取得
function getMyProducts($u_id){
  debug('自分の商品情報を取得します。');
  debug('ユーザーID：'.$u_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM products WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


//カテゴリー情報を引っ張ってくる処理(registCase.php)

function getCategory(){
  debug('カテゴリー情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM category';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);


    //fetchALLは後で調べる
    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//案件関係を引っ張ってくる処理(mypage関係)

function getProduct($u_id, $p_id){
  debug('商品情報を取得します。');
  debug('ユーザーID：'.$u_id);
  debug('商品ID：'.$p_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM products WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//mypageに連絡掲示板関係を乗せる処理(mypage.php)
function getMyMsgsAndBord($u_id){
  debug('自分のmsg情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();

    // まず、掲示板レコード取得
    // SQL文作成
    $sql = 'SELECT * FROM bord AS b WHERE b.sale_user = :id OR b.buy_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);


    $rst = $stmt->fetchAll();
    if(!empty($rst)){
      foreach($rst as $key => $val){
        // SQL文作成
        $sql = 'SELECT * FROM message WHERE bord_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
        $data = array(':id' => $val['id']);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }

    if($stmt){
      // クエリ結果の全データを返却
      return $rst;
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


//お気に入り関係の処理
function getMyLike($u_id){
  debug('自分のお気に入り情報を取得します。');
  debug('ユーザーID：'.$u_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM good AS l LEFT JOIN products AS p ON l.product_id = p.id WHERE l.user_id = :u_id';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//案件登録関係の処理(registCase.php)

// 画像処理(後半のバリ関係の処理がまだイマイチなのでも少し詰める)
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('ファイル情報：'.print_r($file,true));

  //ここemptyは×。0が入る事が基本ないから。is_intは整数型かどうか確認するための処理。
  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
          case UPLOAD_ERR_OK: // OK
              break;
          case UPLOAD_ERR_NO_FILE: // ファイル未選択の場合
              debug('ファイルが選択されていません');
//          case UPLOAD_ERR_INI_SIZE:  // php.ini規定の画像サイズを越した場合
//          case UPLOAD_ERR_FORM_SIZE: // フォーム定義の画像サイズ超した場合
//              debug('ファイルサイズが大きすぎます');
          default: // その他の場合
              ('その他のエラーが発生しました');
      }


      //============ここから

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
          throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
          throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();

    }
  }
}

//案件登録処理関係ここまで

//連絡掲示板関係(msg.php関係の処理)
function getMsgsAndBord($id){
  debug('msg情報を取得します。');
  debug('掲示板ID：'.$id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT m.id AS m_id, product_id, bord_id, send_date, to_user, from_user, sale_user, buy_user, msg, b.create_date
    FROM message AS m RIGHT JOIN bord AS b ON b.id = m.bord_id WHERE b.id = :id ORDER BY send_date ASC';
    $data = array(':id' => $id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//商品情報一覧取得処理
function getProductOne($p_id){
  debug('商品情報を取得します。');
  debug('商品ID：'.$p_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成 p.○○のpはproductのp(asで略称を設定している。今回productsデータは必ず持ってきてほしいので外部結合(LEFTかRIGHT)を使う。)
    $sql = 'SELECT * FROM products AS p LEFT JOIN category AS c ON p.category_id = c.id WHERE p.id = :p_id AND p.delete_flg = 0 AND c.delete_flg = 0';
    $data = array(':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//連絡掲示板関係

function getUser($u_id){
  debug('ユーザー情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users  WHERE id = :u_id';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ成功の場合
    if($stmt){
      debug('クエリ成功。');
    }else{
      debug('クエリに失敗しました。');
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
  // クエリ結果のデータを返却
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

//連絡掲示板関係終わり

//案件一覧関係の処理

function getProductList($currentMinNum = 1, $category, $sort, $span = 20){
  debug('商品情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM products';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    if(!$stmt){
      return false;
    }

    // ページング用のSQL文作成
    $sql = 'SELECT * FROM products';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL：'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//その他の処理
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}

//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      //in_array・・・配列の有無を確認する
      if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}

//ページング関係
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}

//画像表示用関数
function showImg($path){
  if(empty($path)){
    return 'images/sample-img.png';
  }else{
    return $path;
  }
}

//sessionを１回だけ取得できる
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

//お気に入り関係の処理
function isLike($u_id, $p_id){
  debug('お気に入り情報があるか確認します。');
  debug('ユーザーID：'.$u_id);
  debug('商品ID：'.$p_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM `like` WHERE product_id = :p_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt->rowCount()){
      debug('お気に入りです');
      return true;
    }else{
      debug('特に気に入ってません');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
?>


