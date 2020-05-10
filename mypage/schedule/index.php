<?php
require_once('../../set.php');
setup_session();
$titlepart = '受付開始・締切メールの自動配信';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

if (!file_exists(DATAROOT . 'form/submit/done.txt')) die_mypage('<h1>設定エラー</h1>
<p>この機能を利用する前に、まず「イベント情報編集」から、提出期間などの設定を行って下さい。</p>
<p><a href="../index.php">マイページトップに戻る</a></p>');


//日時の計算
$general = json_decode(file_get_contents(DATAROOT . 'form/submit/general.txt'), true);

$date = array();

//開始日時
$date['from_just'] = (int)$general["from"];

//開始日時の3，2，1日前
$date['from_1'] = $date['from_just'] - 24 * 60 * 60;
$date['from_2'] = $date['from_1'] - 24 * 60 * 60;
$date['from_3'] = $date['from_2'] - 24 * 60 * 60;

//締切日時
$date['until_just'] = (int)$general["until"];

//開始日時の3，2，1日前
$date['until_1'] = $date['until_just'] - 24 * 60 * 60;
$date['until_2'] = $date['until_1'] - 24 * 60 * 60;
$date['until_3'] = $date['until_2'] - 24 * 60 * 60;

//ループ処理用（この意味の「ループ」って「roop」じゃなくて「loop」なんだけど直すのめんどくさいからそのままにするわ…）
$roop = array('from_3', 'from_2', 'from_1', 'from_just', 'until_3', 'until_2', 'until_1', 'until_just');
$dsp = array('受付開始3日前', '受付開始2日前', '受付開始前日', '受付開始直後', '締切3日前', '締切2日前', '締切前日', '締切直後');

$current = time();

?>

<h1>受付開始・締切メールの自動配信</h1>
<p>以下のうち希望する日時を選択すると、全ユーザー（ファイル提出を行わないシステム管理者（非参加者）を除く）に、通知メールを配信出来ます。<br>
メールの内容は、「提出受付開始（締切）○日前になりました」、「提出受付を開始しました」、「提出を締め切りました」といったものです。</p>
<p><b>メールの自動配信を行うには、サーバーOSのスケジュール機能（Windowsの場合はタスクスケジューラー、Linuxの場合はCron）と連携させる必要があります</b>。<br>
もしあなたがシステム管理者でない場合、スケジュール機能について、以下の青枠の中に記載している内容と、メールの配信日時をシステム管理者にお伝え下さい。<br>
設定方法がよく分からない場合は、メールの配信日時を過ぎた後にシステムのページ（ログイン画面など）にアクセスする事でもメールを配信出来ます（その際、ログインは不要です）。</p>
<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
<b>【メール自動配信の設定方法】</b><br>
メールの配信日時を迎えた際に、以下のPHPファイルを実行するように、タスクスケジューラーやCronを設定して下さい。<br>
実行するPHPファイル：<code><?php echo PAGEROOT; ?>mail_scheduler.php</code>
</div>
<form name="form" action="handle.php" method="post" onSubmit="return check()" style="margin-top:1em; margin-bottom:1em;">
<?php csrf_prevention_in_form(); ?>
<div class="form-group">
<?php
foreach ($roop as $key => $value) {
    echo '<div class="form-check">';
    echo '<input id="' . $value . '" class="form-check-input" type="checkbox" name="schedule[]" value="' . $value . '"';
    if (file_exists(DATAROOT . 'mail_schedule/' . $value . '.txt')) echo ' checked="checked"';
    if ($date[$value] <= $current) echo ' disabled="disabled"';
    echo '>';
    echo '<label class="form-check-label" for="' . $value . '">' . $dsp[$key] . '（' . date('Y年n月j日G時i分s秒', $date[$value]) . '）';
    if ($date[$value] <= $current) echo '<span class="text-danger">【配信日時を過ぎているため、選択出来ません】</span>';
    echo '</label>';
    echo '</div>';
}
?>
</div>
<button type="submit" class="btn btn-primary" id="submitbtn">設定変更</button>
</form>

<script type="text/javascript">
<!--
// 内容確認　problem変数で問題があるかどうか確認　probidなどで個々の内容について確認
function check(){

  if(window.confirm('設定を変更します。よろしいですか？')){
    submitbtn = document.getElementById("submitbtn");
    submitbtn.disabled = "disabled";
    return true;
  } else{
    return false;
  }

}

// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
