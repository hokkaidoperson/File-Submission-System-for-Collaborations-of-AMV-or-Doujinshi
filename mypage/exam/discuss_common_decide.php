<?php
require_once('../../set.php');
setup_session();
session_validation();

$subject = basename($_POST["subject"]);

//議論ログ
if (!file_exists(DATAROOT . 'exam_edit_discuss/' . $subject . '.txt')) die('ファイルが存在しません。');
$discussdata = json_decode(file_get_contents(DATAROOT . 'exam_edit_discuss/' . $subject . '.txt'), true);

//投票の回答データ
$answerdata = json_decode(file_get_contents(DATAROOT . 'exam_edit/' . $subject . '.txt'), true);
if ($answerdata["_state"] != 1) die();

$memberfile = DATAROOT . 'exammember_edit.txt';

$submitmem = file($memberfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$key = array_search("_promoter", $submitmem);
if ($key !== FALSE) {
    $submitmem[$key] = id_promoter();
    $noprom = FALSE;
} else $noprom = TRUE;

if ($_SESSION["state"] == 'g' or $_SESSION["state"] == 'o') die();

if ($_SESSION["state"] != 'p' and $noprom == FALSE) redirect("./index.php");

if (!file_exists(DATAROOT . 'form/userinfo/done.txt') or !file_exists(DATAROOT . 'examsetting.txt')) redirect("./index.php");


csrf_prevention_validate();
if (!file_exists(DATAROOT . 'exam_edit/' . $subject . '.txt')) die('ファイルが存在しません。');
list($author, $dummy, $editid) = explode('_', $subject);
if (!file_exists(DATAROOT . "users/$author.txt")) die('ファイルが存在しません。');

//送られた値をチェック　ちゃんとフォーム経由で送ってきてたら引っかからないはず（POST直接リクエストによる不正アクセスの可能性も考えて）
$invalid = FALSE;

switch ($_POST["ans"]) {
    case "1": break;
    case "2": break;
    default: $invalid = TRUE;
}

if($_POST["reason"] == ""){
  if($_POST["ans"] == "2") $invalid = TRUE;
  else if(length_with_lb($_POST["reason"]) > 500) $invalid = TRUE;
}

if ($invalid) die('リクエスト内容に不備がありました。入力フォームを介さずにアクセスしようとした可能性があります。もし入力フォームから入力したにも関わらずこのメッセージが表示された場合は、システム制作者にお問い合わせ下さい。');

if (array_search($_SESSION["userid"], $submitmem) === FALSE) die();

//理由通知の設定呼び出し
$examsetting = json_decode(file_get_contents(DATAROOT . 'examsetting.txt'), true);


//結果を保存
$answerdata["_state"] = 2;
$answerdata["_result"] = $_POST["ans"];
$discussdata["comments"]["-system_" . time()] = "最終結論の入力が完了し、議論を終了しました。";

$filedatajson = json_encode($answerdata);
if (file_put_contents(DATAROOT . 'exam_edit/' . $subject . '.txt', $filedatajson) === FALSE) die('回答データの書き込みに失敗しました。');

$filedatajson = json_encode($discussdata);
if (file_put_contents(DATAROOT . 'exam_edit_discuss/' . $subject . '.txt', $filedatajson) === FALSE) die('議論データの書き込みに失敗しました。');

//入力内容を読み込んで書き換え
$formdata = json_decode(file_get_contents(DATAROOT . "users/$author.txt"), true);
$formdata["common_editing"] = 0;
if ($answerdata["_commonmode"] === "new") $formdata["common_acceptance"] = $_POST["ans"];
else if ($_POST["ans"] == 1) {
    $formdata["common_acceptance"] = 1;
    $changeddata = json_decode(file_get_contents(DATAROOT . "edit/" . $author . "/common.txt"), true);
    foreach($changeddata as $key => $data) {
        if (strpos($key, "_add") !== FALSE or strpos($key, "_delete") !== FALSE) {
            $fileto = DATAROOT . 'files/' . $author . '/common/';
            if (!file_exists($fileto)) {
                if (!mkdir($fileto, 0777, true)) die('ディレクトリの作成に失敗しました。');
            }
            $tmp = explode("_", $key);
            $partid = $tmp[0];
            if ($tmp[1] === "add") {
                foreach ($data as $fileplace => $name) {
                    rename(DATAROOT . 'edit_files/' . $author . '/common/' . $partid . "_$fileplace", DATAROOT . 'files/' . $author . '/common/' . $partid . "_$fileplace");
                }
                if (!is_array($formdata[$partid])) $formdata[$partid] = array();
                $formdata[$partid] = array_merge($formdata[$partid], $data);
            }
            if ($tmp[1] === "delete") {
                foreach ($data as $name) {
                    unlink(DATAROOT . 'files/' . $author . '/common/' . $partid . "_$name");
                    unset($formdata[$partid][$name]);
                }
            }
            continue;
        }
        $formdata[$key] = $data;
    }
}
$filedatajson =  json_encode($formdata);
if (file_put_contents(DATAROOT . "users/$author.txt", $filedatajson) === FALSE) die('作品データの書き込みに失敗しました。');

$authornick = nickname($author);

switch ($_POST["ans"]){
    case 1:
        $contentpart = '承認しても問題無いという結論になったため、この内容を承認しました。
情報の提出者に承認の通知をしました。';
        $subject = '議論の結果（承認・共通情報）';
        $authorsubject = '内容を承認しました（共通情報）';
        break;
    case 2:
        $contentpart = '問題があるという結論になったため、この内容を拒否しました。
情報の提出者に拒否の通知をしました。';
        $subject = '議論の結果（拒否・共通情報）';
        $authorsubject = '内容の承認が見送られました（共通情報）';
    break;
}

//内部関数で送信
foreach ($submitmem as $key) {
    if ($author == $key) continue;
    $nickname = nickname($key);
    $content = "$nickname 様

$authornick 様の共通情報について、最終的な結論が入力されたため、議論を終了しました。
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

あなたの共通情報について、イベントの運営メンバーが確認しました。
確認の結果、内容に問題が無いと判断されたため、この内容は承認されました。

$eventname にご参加頂き、ありがとうございます。


【共通情報を修正したい場合】
ファイル提出の締め切りを迎える前であれば、ポータルサイトのマイページから、共通情報の修正を行えます。
共通情報を修正する場合は、マイページにログイン後、「共通情報の入力・編集」をクリックして下さい。
";
    break;
    case 2:
        if ($answerdata["_commonmode"] === "new") $changeinfo = "そのため、大変恐れ入りますが、この内容の承認を見送らせて頂きます。";
        else $changeinfo = "そのため、大変恐れ入りますが、この内容の承認を見送らせて頂きます。\n現在は、変更前の内容を維持したままの状態となっています。";
        $content = "$authornick 様

あなたの共通情報について、イベントの運営メンバーが確認しました。
確認の結果、その内容に問題があると判断されました。
$changeinfo


【問題があると判断された理由（ファイル確認者によるコメント）】
$reasons

【再編集をするには】
問題があるとされる内容を修正の上、共通情報を再編集する事が出来ます（ファイル提出の締め切り前まで）。
マイページにログイン後、「共通情報の入力・編集」をクリックして下さい。
";
    break;
}
sendmail(email($author), $authorsubject, $content);
unlink(DATAROOT . "edit/$author/common.txt");

switch ($_POST["ans"]){
    case 1:
        register_alert("結論を送信し、議論を終了しました。<br><br>承認しても問題無いという結論になったため、<b>この共通情報を承認しました</b>。<br>情報の提出者に承認の通知をしました。", "success");
    break;
    case 2:
        register_alert("結論を送信し、議論を終了しました。<br><br>問題があるという結論になったため、<b>この共通情報を拒否しました</b>。<br>情報の提出者に拒否の通知をしました。", "success");
    break;
}

redirect("./index.php");
