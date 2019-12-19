<?php
require_once('../../set.php');
session_start();
//ログインしてない場合はログインページへ
if (!isset($_SESSION['userid'])) {
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'../../index.php?redirto=mypage/account/index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

if ($_POST["successfully"] != "1") die("不正なアクセスです。\nフォームが入力されていません。");

//パスワード認証
$userdata = json_decode(file_get_contents(DATAROOT . 'users/' . $_SESSION['userid'] . '.txt'), true);
if (!password_verify($_POST["password"], $userdata["pwhash"])) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>認証エラー</title>
</head>
<body>
<p>現在のパスワードが誤っています。お手数ですが、入力をやり直して下さい。</p>
<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、設定画面にお戻り下さい。</a></p>
</body>
</html>');

//フォーム設定ファイル読み込み
$userformdata = array();

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $userformdata[$i] = json_decode(file_get_contents(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
}

//提出期間外だとメールアドレス以外変更不可
//disable属性を使用していて、値が送られてこない可能性があるのでそれを調べつつ
if (outofterm('userform') != FALSE) $disable = FALSE;
else $disable = TRUE;
if ($_SESSION["state"] == 'p') $disable = FALSE;

if (before_deadline()) $disable = FALSE;

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;
//必須の場合のパターン 文字数
if($_POST["nickname"] == "") {
    if (!$disable) $invalid = TRUE;
}
else if(mb_strlen($_POST["nickname"]) > 30) $invalid = TRUE;

//メールアドレス形式確認　必須・一致確認
if($_POST["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_POST["email"])) $invalid = TRUE;
else if($_POST["email"] != $_POST["emailagn"]) $invalid = TRUE;

//カスタム内容

if ($_SESSION["state"] != 'o' and !$disable) {

foreach ($userformdata as $array) {
    if ($array["type"] == "textbox2") {
      $item = $_POST["custom-" . $array["id"] . "-1"];
      $item2 = $_POST["custom-" . $array["id"] . "-2"];
      $result = check_required2($array["required"], $item, $item2);
      if ($result != 0) $invalid = TRUE;
      if ($item != "") {
        if ($array["max"] != "") $vmax = (int) $array["max"];
        else $vmax = 9999;
        if ($array["min"] != "") $vmin = (int) $array["min"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item);
        if ($result != 0) $invalid = TRUE;
      }
      if ($item2 != "") {
        if ($array["max2"] != "") $vmax = (int) $array["max2"];
        else $vmax = 9999;
        if ($array["min2"] != "") $vmin = (int) $array["min2"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item2);
        if ($result != 0) $invalid = TRUE;
      }
    } else if ($array["type"] == "textbox" || $array["type"] == "textarea") {
      $item = $_POST["custom-" . $array["id"]];
      $result = check_required($array["required"], $item);
      if ($result != 0) $invalid = TRUE;
      else {
        if ($array["max"] != "") $vmax = (int) $array["max"];
        else $vmax = 9999;
        if ($array["min"] != "") $vmin = (int) $array["min"];
        else $vmin = 0;
        $result = check_maxmin($vmax, $vmin, $item);
        if ($result != 0) $invalid = TRUE;
        }
    } else if ($array["type"] == "check") {
        $f = $_POST["custom-" . $array["id"]];
        if ($f == "") $f = array();
        if((array)$f == array() && $array["required"] == "1") $invalid = TRUE;
    } else if ($array["type"] == "radio" || $array["type"] == "dropdown") {
      $item = $_POST["custom-" . $array["id"]];
      $result = check_required($array["required"], $item);
      if ($result != 0) $invalid = TRUE;
    } else if ($array["type"] == "attach") {
        $name = $_FILES["custom-" . $array["id"]]['name'];
        if ($_POST["custom-" . $array["id"] . "-delete"] == "1" and $array["required"] == "1") $invalid = TRUE;
        if ($_FILES["custom-" . $array["id"]]['error'] == 4) {
            if ($array["required"] == "1") {
                if (isset($userdata[$array["id"]]) and $userdata[$array["id"]] == '') $invalid = TRUE;
            }
        }
        else if ($_FILES["custom-" . $array["id"]]['error'] == 1) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ファイル　アップロードエラー</title>
</head>
<body>
<p>ファイルのアップロードに失敗しました。アップロードしようとしたファイルのサイズが、サーバーで扱えるファイルサイズを超えていました。<br>
お手数ですが、サーバーの管理者にお問い合わせ下さい。</p>
<p>問い合わせの際、サーバーの管理者に以下の事項をお伝え下さい。<br>
<b>ユーザーがアップロードしようとしたファイルのサイズが、php.ini の upload_max_filesize ディレクティブの値を超えていたため、アップロードが遮断されました。<br>
php.ini の設定を見直して下さい。</b></p>
</body>
</html>');
        else if ($_FILES["custom-" . $array["id"]]['error'] == 3) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ファイル　アップロードエラー</title>
</head>
<body>
<p>ファイルのアップロードに失敗しました。通信環境が悪かったために、アップロードが中止された可能性があります。<br>
通信環境を見直したのち、再度送信願います。</p>
</body>
</html>');
        else if ($_FILES["custom-" . $array["id"]]['error'] == 0) {
          $ext = $array["ext"];
          $ext = str_replace(",", "|", $ext);
          $ext = strtoupper($ext);
          $reg = '/\.(' . $ext . ')$/i';
          if (!preg_match($reg, $name)) $invalid = TRUE;
          else {
            $size = $_FILES["custom-" . $array["id"]]['size'];
            if ($array["size"] != "") $oksize = (int) $array["size"];
            else $oksize = FILE_MAX_SIZE;
            $oksize = $oksize * 1024 * 1024;
            if ($size > $oksize) $invalid = TRUE;
          }
        }
    }

}

}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

//変更点
$changed = array();

if (!$disable and $userdata["nickname"] != $_POST["nickname"]) {
    $changed[] = "【ニックネーム】" . htmlspecialchars($userdata["nickname"]) . " → " . htmlspecialchars($_POST["nickname"]);
    $userdata["nickname"] = $_POST["nickname"];
}
if ($userdata["email"] != $_POST["email"]) {
    $changed[] = "【メールアドレス】" . htmlspecialchars($userdata["email"]) . " → " . htmlspecialchars($_POST["email"]);
    $userdata["email"] = $_POST["email"];
}

//カスタムデータ格納　添付ファイルは専用フォルダに保存 別場所に拡張子
if ($_SESSION["state"] != 'o' and !$disable) {

foreach ($userformdata as $array) {
    if ($array["type"] == "attach") {
        $fileto = DATAROOT . 'users_attach/' . $_SESSION['userid'] . '/';
        if (!file_exists($fileto)) {
            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
        }
        if ($_FILES["custom-" . $array["id"]]['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["custom-" . $array["id"]]["tmp_name"];
            $ext = substr(basename($_FILES["custom-" . $array["id"]]["name"]), strrpos(basename($_FILES["custom-" . $array["id"]]["name"]), '.') + 1);
            $savename = $array["id"];
            if (!move_uploaded_file($tmp_name, $fileto . $savename)) die('ファイルのアップロードに失敗しました。アップロードのリクエストが不正だったか、サーバーサイドで何かしらの問題が生じた可能性があります。');
            $userdata[$array["id"]] = $ext;
            $changed[] = "【" . $array["title"] . "】新しいファイルに変更しました。";
        }
        if ($_POST["custom-" . $array["id"] . "-delete"] == "1") {
            $savename = $array["id"];
            if (!unlink($fileto . $savename)) die('ファイルの削除に失敗しました。');
            $userdata[$array["id"]] = "";
            $changed[] = "【" . $array["title"] . "】ファイルを削除しました。";
        }
        continue;
    }
    if ($array["type"] == "radio" or $array["type"] == "dropdown") {
        $decode = htmlspecialchars_decode($_POST["custom-" . $array["id"]]);
        if ($userdata[$array["id"]] != $decode) {
            $changed[] = "【" . $array["title"] . "】" . $userdata[$array["id"]] . " → " . $decode;
            $userdata[$array["id"]] = $decode;
        }
        continue;
    }
    if ($array["type"] == "check") {
        $oldcompare = implode("、", (array)$userdata[$array["id"]]);
        $newcompare = implode("、", (array)$_POST["custom-" . $array["id"]]);
        $newdecode = htmlspecialchars_decode($newcompare);
        if ($oldcompare != $newdecode) {
            $changed[] = "【" . $array["title"] . "】" . $oldcompare . " → " . $newdecode;
            $userdata[$array["id"]] = array();
            foreach ((array)$_POST["custom-" . $array["id"]] as $key => $value) {
                $userdata[$array["id"]][$key] = htmlspecialchars_decode($value);
            }
        }
        continue;
    }
    if ($array["type"] == "textbox2") {
        if ($userdata[$array["id"] . "-1"] != $_POST["custom-" . $array["id"] . "-1"]) {
            $changed[] = "【" . $array["title"] . "（1つ目の入力欄）】" . $userdata[$array["id"] . "-1"] . " → " . $_POST["custom-" . $array["id"] . "-1"];
            $userdata[$array["id"] . "-1"] = $_POST["custom-" . $array["id"] . "-1"];
        }
        if ($userdata[$array["id"] . "-2"] != $_POST["custom-" . $array["id"] . "-2"]) {
            $changed[] = "【" . $array["title"] . "（2つ目の入力欄）】" . $userdata[$array["id"] . "-2"] . " → " . $_POST["custom-" . $array["id"] . "-2"];
            $userdata[$array["id"] . "-2"] = $_POST["custom-" . $array["id"] . "-2"];
        }
        continue;
    }
    if ($userdata[$array["id"]] != $_POST["custom-" . $array["id"]]) {
        $changed[] = "【" . $array["title"] . "】" . $userdata[$array["id"]] . " → " . $_POST["custom-" . $array["id"]];
        $userdata[$array["id"]] = $_POST["custom-" . $array["id"]];
    }
}

}

if ($changed == array()) {
    $_SESSION['situation'] = 'others_nochange';
    die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL=\'index.php\'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>');
}

$userdatajson =  json_encode($userdata);

if (file_put_contents(DATAROOT . 'users/' . $_SESSION['userid'] . '.txt', $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

$_SESSION['nickname'] = $userdata["nickname"];
$_SESSION['email'] = $userdata["email"];

$changed = implode("\n", $changed);

//メール本文形成
$nickname = $_SESSION['nickname'];
$date = date('Y年n月j日G時i分s秒');
$content = "$nickname 様

$eventname のポータルサイトのマイページで、登録内容が変更されました。

この通知は、$eventname のポータルサイトであなたの登録情報に変更があった際（ファイルの新規提出など）に、
それが不正ログインによる変更でないかどうか確認するためにお送りしているものです。


【あなた自身の操作で変更した場合】
ご確認ありがとうございます。引き続きポータルサイトをご利用下さい。
このメールは削除しても構いません。


【変更した覚えが無い場合】
第三者があなたのアカウントに不正ログインした可能性があります。直ちに、パスワードの変更を行って下さい。

あなたの操作でログインした後、「アカウント情報編集」→「パスワード変更」の順に選択して下さい。


【変更情報】
$changed
";
//内部関数で送信
sendmail($_SESSION['email'], '登録内容変更通知', $content);


$_SESSION['situation'] = 'others_changed';
$changed = htmlspecialchars($changed);
$changed = str_replace(array("\r\n", "\r", "\n"), "\n", $changed);
$_SESSION['situation2'] = str_replace("\n", "<br>", $changed);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='index.php'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
