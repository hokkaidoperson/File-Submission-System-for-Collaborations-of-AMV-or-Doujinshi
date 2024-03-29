<?php
require_once('../../set.php');
$titlepart = '提出期間外にファイルや情報の提出・編集を認めるには';
require_once(PAGEROOT . 'help_header.php');
?>

<h1>提出期間外にファイルや情報の提出・編集を認めるには</h1>
<p>通常、提出期間外になったら、ファイルの新規提出や編集など、情報の操作機能にロックが掛かりますが、必要に応じて、ファイルの遅刻提出や情報の修正を個別に許可する事が出来ます。</p>
<p>マイページトップから「権限コントロール」→「提出期間外のファイル提出・情報編集権限を操作」の順に選択して下さい。<br>
参加者の一覧が表示されますので、対象となる参加者を選択して下さい。<br>
操作期限が切れるまでの時間を入力し、許可したい操作にチェックを入れて、<strong>最後に必ず「権限を変更する」を押下して下さい</strong>（押下しないと設定が保存されません）。</p>
<p>これで、提出期間外のファイル提出・情報操作権限を与える事が出来ました。当該参加者には、ファイル提出・情報操作が一時的に可能になった事を通知するメールが送信されます。</p>
<p>指定した時間が経過したら操作権限は自動的に無くなり、再度ロックされます。指定時間経過前に操作権限を無くしたい場合は、権限を無くしたい操作のチェックを外して再度保存して下さい。</p>

<?php
require_once(PAGEROOT . 'help_footer.php');
