<?php
require_once('../../set.php');
setup_session();
session_validation();

$subject = basename($_POST["subject"]);

if (!file_exists(DATAROOT . 'exam_discuss/' . $subject . '.txt')) die('ファイルが存在しません。');
$discussdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam_discuss/' . $subject . '.txt'), true);

$submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

if ($_SESSION["state"] == 'g' or $_SESSION["state"] == 'o') die();

$leader = id_leader("submit");
if ($leader != NULL) {
    if ($leader != $_SESSION["userid"]) redirect("./index.php");
} else if ($_SESSION["state"] != 'p' and $noprom == FALSE) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/submit/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");


csrf_prevention_validate();
if (!file_exists(DATAROOT . 'exam/' . $subject . '.txt')) die('ファイルが存在しません。');
$answerdata = json_decode(file_get_contents_repeat(DATAROOT . 'exam/' . $subject . '.txt'), true);
list($author, $id) = explode("/", $answerdata["_realid"]);
if (!file_exists(DATAROOT . "submit/" . $author . "/" . $id . ".txt")) die('ファイルが存在しません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

switch ($_POST["ans"]) {
    case "1": break;
    case "2": break;
    case "3": break;
    default: $invalid = TRUE;
}

if($_POST["reason"] == ""){
  if($_POST["ans"] == "2" || $_POST["ans"] == "3") $invalid = TRUE;
  else if(length_with_lb($_POST["reason"]) > 500) $invalid = TRUE;
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

if ($answerdata["_state"] != 1) die();

if (array_search($_SESSION["userid"], $submitmem) === FALSE) die();

//理由通知の設定呼び出し
$examsetting = json_decode(file_get_contents_repeat(DATAROOT . 'examsetting.txt'), true);


//結果を保存
$answerdata["_state"] = 2;
$answerdata["_result"] = ["opinion" => $_POST["ans"], "reason" => $_POST["reason"]];
$discussdata["comments"]["-system_" . time()] = "最終結論の入力が完了し、議論を終了しました。";

$filedatajson = json_encode($answerdata);
if (file_put_contents_repeat(DATAROOT . 'exam/' . $subject . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');

$filedatajson = json_encode($discussdata);
if (file_put_contents_repeat(DATAROOT . 'exam_discuss/' . $subject . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');

//入力内容を読み込んで書き換え
$formdata = json_decode(file_get_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt"), true);
$formdata["exam"] = $_POST["ans"];
$filedatajson =  json_encode($formdata);
if (file_put_contents_repeat(DATAROOT . "submit/" . $author . "/" . $id . ".txt", $filedatajson) === FALSE) die('作品データの書き込みに失敗しました。');

$authornick = nickname($author);

switch ($_POST["ans"]){
    case 1:
        $contentpart = '承認しても問題無いという結論になったため、この作品を承認しました。
作品の提出者に承認の通知をしました。';
        $subject = '議論の結果（承認・' . $formdata["title"] . '）';
        $authorsubject = '作品を承認しました（' . $formdata["title"] . '）';
        break;
    case 2:
        $contentpart = '軽微な修正が必要であるという結論になったため、この作品を修正待ち状態にしました。
作品の提出者に、修正依頼の通知をしました。';
        $subject = '議論の結果（修正待ち・' . $formdata["title"] . '）';
        $authorsubject = '作品を修正して下さい（' . $formdata["title"] . '）';
    break;
    case 3:
        $contentpart = '内容上の問題が多い、もしくは重大な問題があるという結論になったため、この作品を拒否しました。
作品の提出者に拒否の通知をしました。';
        $subject = '議論の結果（拒否・' . $formdata["title"] . '）';
        $authorsubject = '作品の承認が見送られました（' . $formdata["title"] . '）';
    break;
}

//内部関数で送信
foreach ($submitmem as $key) {
    if ($author === (string)$key) continue;
    $nickname = nickname($key);
    $content = "$nickname 様

作品「" . $formdata["title"] . "」について、最終的な結論が入力されたため、議論を終了しました。
$contentpart

ファイル確認および議論へのご協力、ありがとうございます。
";
    sendmail(email($key), $subject, $content);
}

//提出者向け
if ($examsetting["reason"] == "notice") {
    $reasons = "◇" . $_POST["reason"] . "\n\n";
}
else if ($examsetting["reason"] == "dont-a") $reasons = "大変お手数ですが、今回の判断の理由につきましては主催者に直接お尋ね願います。\n\n";
else if ($examsetting["reason"] == "dont-b") $reasons = "大変恐れ入りますが、今回の判断の理由につきましてはお答え致しかねます。\n\n";
switch ($_POST["ans"]){
    case 1:
        $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、ファイル内容に問題が無いと判断されたため、この作品は承認されました。

$eventname にご参加頂き、ありがとうございます。


【提出内容の修正・削除をしたい場合や、作品を追加提出したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、提出内容の修正・削除や、追加提出を行えます。
提出内容を修正・削除する場合は、マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
追加提出をする場合は、「作品を提出する」をクリックし、改めて作品の提出を行って下さい。
";
    break;
    case 2:
        $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、ファイルの軽微な修正が必要と判断されました。
お手数ですが、以下をご確認頂き、ファイルの再提出をして頂けますと幸いです。


【修正が必要と判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには（ファイル提出の締め切り前まで）】
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。


【既にファイル提出の締め切りを迎えている場合】
大変お手数ですが、主催者にご相談願います。
主催者が認めた場合は、締め切り後であっても入力内容の編集を行えます。
";
    break;
    case 3:
        $content = "$authornick 様

あなたの作品「" . $formdata["title"] . "」について、イベントの運営メンバーが確認しました。
確認の結果、提出されたファイルは、内容などの観点上、本イベントに相応しくないと判断されました。
そのため、大変恐れ入りますが、この作品の承認を見送らせて頂きます。


【相応しくないと判断された理由（ファイル確認者によるコメント）】
$reasons

【再提出をするには】
本イベントに相応しくないとされる内容を修正の上、ファイルを再提出する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「提出済み作品一覧・編集」（主催者の場合は「参加者・作品の一覧・編集」）をクリックして下さい。
作品の一覧から「" . $formdata["title"] . "」を探して選択し、「入力内容の編集」を選択して下さい。
以降は、画面の指示に従って操作して下さい。
";
    break;
}
sendmail(email($author), $authorsubject, $content);


switch ($_POST["ans"]){
    case 1:
        register_alert("結論を送信し、議論を終了しました。<br><br>承認しても問題無いという結論になったため、<b>この作品を承認しました</b>。<br>作品の提出者に承認の通知をしました。", "success");
    break;
    case 2:
        register_alert("結論を送信し、議論を終了しました。<br><br>軽微な修正が必要であるという結論になったため、<b>この作品を修正待ち状態にしました</b>。<br>作品の提出者に、修正依頼の通知をしました。", "success");
    break;
    case 3:
        register_alert("結論を送信し、議論を終了しました。<br><br>このイベントに相応しくないという結論になったため、<b>この作品を拒否しました</b>。<br>作品の提出者に拒否の通知をしました。", "success");
    break;
}

redirect("./index.php");
