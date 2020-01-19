<?php
require_once('../../set.php');

if ($_POST["successfully"] != "2") die("不正なアクセスです。\nフォームが入力されていません。");

//フォーム設定ファイル読み込み
$userformdata = array();

for ($i = 0; $i <= 9; $i++) {
    if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
    $userformdata[$i] = json_decode(file_get_contents(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
}

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;
//必須の場合のパターン・文字種・文字数
if($_POST["userid"] == "") $invalid = TRUE;
else if(!preg_match('/^[0-9a-zA-Z]*$/', $_POST["userid"])) $invalid = TRUE;
else if(mb_strlen($_POST["userid"]) > 20) $invalid = TRUE;

$IP = getenv("REMOTE_ADDR");

//登録中のユーザーID横取り阻止（保証期間は30分）
$conflict = FALSE;
if (file_exists(DATAROOT . 'users_reserve/')) {
    foreach (glob(DATAROOT . 'users_reserve/*.txt') as $filename) {
        $filedata = json_decode(file_get_contents($filename), true);
        if ($filedata["expire"] <= time()) {
            unlink($filename);
            continue;
        }
        // 自分自身だったら通してあげる
        if ($filedata["ip"] == $IP) continue;
        if (basename($filename, ".txt") == $_POST["userid"]) {
            $conflict = TRUE;
            break;
        }
    }
}

//登録済みの中から探す
foreach (glob(DATAROOT . 'users/*.txt') as $filename) {
    if (basename($filename, ".txt") == $_POST["userid"]) {
        $conflict = TRUE;
        break;
    }
}

if ($conflict) die('<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ユーザーID重複</title>
</head>
<body>
<p>大変申し訳ございませんが、このユーザーIDは既に使用されているため、登録出来ません。<br>
お手数をお掛けしますが、別のユーザーIDをご利用願います。</p>
<p><a href="#" onclick="javascript:window.history.back(-1);return false;">こちらをクリックして、ユーザーID入力画面にお戻り下さい。</a></p>
</body>
</html>');

//必須の場合のパターン 文字数
if($_POST["nickname"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["nickname"]) > 30) $invalid = TRUE;

//メールアドレス形式確認　必須・一致確認
if($_POST["email"] == "") $invalid = TRUE;
else if(!preg_match('/.+@.+\..+/', $_POST["email"])) $invalid = TRUE;
else if($_POST["email"] != $_POST["emailagn"]) $invalid = TRUE;

//必須の場合のパターン・文字数・一致確認
if($_POST["password"] == "") $invalid = TRUE;
else if(mb_strlen($_POST["password"]) > 30) $invalid = TRUE;
else if(mb_strlen($_POST["password"]) < 8) $invalid = TRUE;
else if($_POST["password"] != $_POST["passwordagn"]) $invalid = TRUE;

//必須の場合
if($_POST["state"] != "c") $invalid = TRUE;

//カスタム内容
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
        if ($_FILES["custom-" . $array["id"]]['error'] == 4) {
            if ($array["required"] == "1") $invalid = TRUE;
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
        else {
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

//sectokをもっかいチェック
if (file_exists(DATAROOT . 'mail/invitation/' . $_POST["towhom"] . '.txt')) {
    $filedata = json_decode(file_get_contents(DATAROOT . 'mail/invitation/' . $_POST["towhom"] . '.txt'), true);
    if ($filedata["sectok"] !== $_POST["sectok"]) $invalid = TRUE;
} else $invalid = TRUE;


if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');



$browser = $_SERVER['HTTP_USER_AGENT'];

$eventname = file_get_contents(DATAROOT . 'eventname.txt');

//パスワードハッシュ化
$hash = password_hash($_POST["password"], PASSWORD_BCRYPT);

//ユーザー情報格納
//lastipとlastbrは、最終ログイン時のIPとブラウザ情報
//ログインしたときにこのipかbrが違う場合にセキュリティ通知する（不正ログインされた時に発見しやすい）
$userid = $_POST["userid"];
$nickname = $_POST["nickname"];
$userfile = $userid . '.txt';
$email = $_POST["email"];
$state = 'c';
$userdata = array(
    "nickname" => $nickname,
    "email" => $email,
    "pwhash" => $hash,
    "state" => $state,
    "admin" => 0,
    "lastip" => $IP,
    "lastbr" => $browser
);

//メール記載用
$onmail = array();

//カスタムデータ格納　添付ファイルは専用フォルダに保存 別場所に拡張子
foreach ($userformdata as $array) {
    if ($array["type"] == "attach") {
        $fileto = DATAROOT . 'users_attach/' . $userid . '/';
        if (!file_exists($fileto)) {
            if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
        }
        if ($_FILES["custom-" . $array["id"]]['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["custom-" . $array["id"]]["tmp_name"];
            $ext = substr(basename($_FILES["custom-" . $array["id"]]["name"]), strrpos(basename($_FILES["custom-" . $array["id"]]["name"]), '.') + 1);
            $savename = $array["id"];
            if (!move_uploaded_file($tmp_name, $fileto . $savename)) die('ファイルのアップロードに失敗しました。アップロードのリクエストが不正だったか、サーバーサイドで何かしらの問題が生じた可能性があります。');
            $userdata[$array["id"]] = $ext;
            $onmail[] = "《" . $array["title"] . "》（ファイルをアップロードしました。）";
        } else $onmail[] = "《" . $array["title"] . "》（無し）";
        continue;
    }
    if ($array["type"] == "radio" or $array["type"] == "dropdown") {
        $userdata[$array["id"]] = htmlspecialchars_decode($_POST["custom-" . $array["id"]]);
        $onmail[] = "《" . $array["title"] . "》" . $_POST["custom-" . $array["id"]];
        continue;
    }
    if ($array["type"] == "check") {
        if ($_POST["custom-" . $array["id"]] == "") {
            $userdata[$array["id"]] = array();
            $onmail[] = "《" . $array["title"] . "》（無し）";
            continue;
        }
        foreach ((array)$_POST["custom-" . $array["id"]] as $key => $value) {
            $userdata[$array["id"]][$key] = htmlspecialchars_decode($value);
        }
        $onmail[] = "《" . $array["title"] . "》" . implode("、", $userdata[$array["id"]]);
        continue;
    }
    if ($array["type"] == "textbox2") {
        $userdata[$array["id"] . "-1"] = $_POST["custom-" . $array["id"] . "-1"];
        $userdata[$array["id"] . "-2"] = $_POST["custom-" . $array["id"] . "-2"];
        $onmail[] = "《" . $array["title"] . "》" . $_POST["custom-" . $array["id"] . "-1"] . "、" . $_POST["custom-" . $array["id"] . "-2"];
        continue;
    }
    $userdata[$array["id"]] = $_POST["custom-" . $array["id"]];
    $onmail[] = "《" . $array["title"] . "》" . $_POST["custom-" . $array["id"]];
}

$statej = "共同運営者";

$userdatajson =  json_encode($userdata);

if (file_put_contents(DATAROOT . 'users/' . $userfile, $userdatajson) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//立場別の一覧
$statedata = "$userid\n";
$statedtp = DATAROOT . 'users/_co.txt';

if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');

//この人をファイル確認メンバーに入れる？
if (file_exists(DATAROOT . 'examsetting.txt')) {
    $examsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);

    if ($examsetting["submit_add"] == "1") {
        $statedata = "$userid\n";
        $statedtp = DATAROOT . 'exammember_submit.txt';
        if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    }
    if ($examsetting["edit_add"] == "1") {
        $statedata = "$userid\n";
        $statedtp = DATAROOT . 'exammember_edit.txt';
        if (file_put_contents($statedtp, $statedata, FILE_APPEND | LOCK_EX) === FALSE) die('ユーザーデータの書き込みに失敗しました。');
    }
}

//メール本文形成
$onmail = implode("\n", $onmail);
$date = date('Y/m/d H:i:s');
$content = "$nickname 様

$eventname のポータルサイトのアカウントの設定が完了しました。
登録内容は以下の通りです。

《ユーザーID》$userid
《ニックネーム》$nickname
《メールアドレス》$email
《立場》$statej
$onmail

　登録時のIPアドレス　：$IP
　登録時のブラウザ情報：$browser
　登録日時　　　　　　：$date
";

//内部関数で送信
sendmail($email, 'アカウントの設定完了通知', $content);

//招待リンクを消す
unlink(DATAROOT . 'mail/invitation/' . $_POST["towhom"] . '.txt');

//主催者に事後報告
$promoter = id_state('p');
$nicknamep = nickname($promoter[0]);

$content = "$nicknamep 様

$eventname のポータルサイトに、共同運営者 $nickname 様がアカウントを登録しましたのでお知らせ致します。
登録内容は以下の通りです。

$onmail

　登録時のIPアドレス：$IP
　登録日時　　　　　：$date
";

//内部関数で送信
sendmail(email($promoter[0]), '共同運営者が登録されました', $content);

//ログイン状態に
session_start();
if ($_SESSION['authinfo'] !== 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $_SESSION['userid']) {
    $_SESSION['userid'] = $userid;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['email'] = $email;
    $_SESSION['state'] = $state;
    $_SESSION['admin'] = 0;
    $_SESSION['situation'] = 'registered';
    $_SESSION['expire'] = time() + (30 * 60);
    $_SESSION['useragent'] = $browser;
    $_SESSION['authinfo'] = 'MAD合作・合同誌向けファイル提出システム_' . $siteurl . '_' . $userid;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="0; URL='../../mypage/index.php'" />
<title>リダイレクト中…</title>
</head>
<body>
しばらくお待ち下さい…
</body>
</html>
