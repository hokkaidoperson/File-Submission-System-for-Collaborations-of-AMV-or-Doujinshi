<?php
require_once('../../../set.php');
setup_session();
$titlepart = '共通情報入力画面の編集';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

if (!file_exists(DATAROOT . 'form/userinfo/')) {
    if (!mkdir(DATAROOT . 'form/userinfo/', 0777, true)) die_mypage('ディレクトリの作成に失敗しました。');
}

//SESSIONデータある？そして一時ファイルある？
if (!isset($_SESSION["userformdata"])) {
    $_SESSION["userformdata"] = array();
    if (file_exists(DATAROOT . 'form/userinfo/draft/')) {
        for ($i = 0; $i <= 9; $i++) {
            if (!file_exists(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt')) break;
            $_SESSION["userformdata"][$i] = json_decode(file_get_contents(DATAROOT . 'form/userinfo/draft/' . "$i" . '.txt'), true);
        }
        echo '<div class="border border-primary" style="padding:10px; margin-top:1em; margin-bottom:1em;">
一時ファイルを読み込みました。作業が完了したら、その内容を反映させるために「変更内容を保存し適用する」ボタンを必ず押して下さい。
</div>';
    } else {
        for ($i = 0; $i <= 9; $i++) {
            if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
            $_SESSION["userformdata"][$i] = json_decode(file_get_contents(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
        }
    }
}

//設定画面途中で抜け出したりしてない？
for ($i = 0; $i <= 9; $i++) {
    if (isset($_SESSION["userformdata"][$i])) {
        if (!isset($_SESSION["userformdata"][$i]["title"])) unset($_SESSION["userformdata"][$i]);
    } else break;
}

?>

<h1>共通情報入力画面の編集</h1>
<p>共通情報として入力を求める事項を設定します。</p>
<p>ポータルサイトのマイページトップの「共通情報の入力・編集」という項目から、ここで設定する共通情報を登録・編集出来ます。</p>
<p><b>最大10個まで</b>入力事項を追加出来ます。各項目について、入力必須かそうでないかを設定出来ます。</p>
<p>※求める共通情報が特に無い場合は、何も設定せずに下の「変更内容を保存し適用する」ボタンを押して下さい。</p>
<?php
if (file_exists(DATAROOT . 'form/userinfo/done.txt')) echo '<div class="border border-warning" style="padding:10px; margin-top:1em; margin-bottom:1em;">
共通情報の入力項目は既に保存・公開されています。設定内容を変更する事は出来ますが、変更は最小限にとどめる事をお勧め致します。
</div>';
else echo '<div class="border border-warning" style="padding:10px; margin-top:1em; margin-bottom:1em;">
共通情報の入力項目は後から変更出来ますが、変更は最小限にとどめる事をお勧め致します。
</div>';
?>
<p><a class="btn btn-primary" data-toggle="collapse" href="#detail" role="button" aria-expanded="false" aria-controls="detail">
詳細を開く
</a></p>
<div class="collapse" id="detail">
<div class="card card-body">
<p>サポートしている入力形式は以下の通りです。</p>
<ul>
<li>テキストボックス（1行のみの入力欄）</li>
<ul><li>文字数制限（●●文字以上●●文字以下）を設けられます。</li></ul>
<li>テキストボックス×2（1つの項目にテキストボックスが2つ付く）</li>
<ul><li>例えば、「出身都道府県と在住都道府県の2つの入力欄を設ける」場合などに便利です。通常のテキストボックス同様、文字数制限（●●文字以上●●文字以下）を設けられます。</li>
<li>入力必須の設定については、「任意」「いずれか必須」「どちらも必須」から選べます。</li></ul>
<li>テキストエリア（複数行対応の入力欄）</li>
<ul><li>比較的長い文を書くのに適しています。文字数制限（●●文字以上●●文字以下）を設けられます。</li></ul>
<li>ラジオボタン（複数の選択肢から1つだけ選ぶ丸いボタン）</li>
<li>チェックボックス（1つあるいは複数の選択肢から選ぶ四角いボタン・複数選択可）</li>
<ul><li>「●●●●の場合は左にチェックして下さい」といった使い方をする場合はチェックボックスを選んで下さい。</li></ul>
<li>ドロップダウンリスト（複数の選択肢から1つだけ選ぶ・クリックすると下に選択肢がニュッと出てくる）</li>
<li>添付ファイル</li>
<ul><li>プロフィール画像を求める場合などにご活用下さい。</li></ul>
</ul>
</div>
</div>
<p><font size="2">※公開前の設定内容は一時ファイルに保存されます。途中でブラウザを閉じてしまってもその一時ファイルを基に作業を再開出来ますが、<b>最終的に「変更内容を保存し適用する」ボタンを押さないと実際の入力画面に反映されません。</font></b></p>
<h2>共通情報入力画面の項目一覧</h2>
<p>変更したい項目の項目名をクリックして下さい。</p>
<p>実際の入力画面では、下表の順番で項目が並びます。</p>
<div class="table-responsive-md">
<table class="table table-hover table-bordered">
<tr>
<th>項目名</th><th>入力形式</th><th>必須・任意</th>
</tr>
<?php
for ($i = 0; $i <= 10; $i++) {
    if (!isset($_SESSION["userformdata"][$i])) {
        if ($i != 10) echo '<tr class="table-primary"><td><b>項目の新規追加</b></td><td colspan="2">
<form class="form-inline" name="form" action="add.php" method="get" onSubmit="return check()">
<input type="hidden" name="number" value="' . "$i" . '">
<select name="type" class="form-control" id="type">
<option value="">【選択して下さい】</option>
<option value="textbox">テキストボックス</option>
<option value="textbox2">テキストボックス×2</option>
<option value="textarea">テキストエリア</option>
<option value="radio">ラジオボタン</option>
<option value="check">チェックボックス</option>
<option value="dropdown">ドロップダウンリスト</option>
<option value="attach">添付ファイル</option>
</select>
<button type="submit" class="btn btn-primary" id="submitbtn">新規追加</button></td></tr>';
        else echo '<tr class="table-primary"><td><b>項目の新規追加</b></td><td colspan="2">※項目数が最大に達しているため、新規追加出来ません。</td></tr>';
        echo '<tr class="table-success"><td colspan="3"><a href="orderanddel.php">項目の順番変更 ／ 項目の削除</a></td></tr>';
        break;
    }
    echo "<tr>\n";
    echo '<td><a href="' . $_SESSION["userformdata"][$i]["type"] . '.php?number=' . "$i" . '">' . hsc($_SESSION["userformdata"][$i]["title"]) . '</a></td>';
    switch ($_SESSION["userformdata"][$i]["type"]) {
        case 'textbox':
            echo "<td>テキストボックス</td>";
            break;
        case 'textbox2':
            echo "<td>テキストボックス×2</td>";
            break;
        case 'textarea':
            echo "<td>テキストエリア</td>";
            break;
        case 'radio':
            echo "<td>ラジオボタン</td>";
            break;
        case 'check':
            echo "<td>チェックボックス</td>";
            break;
        case 'dropdown':
            echo "<td>ドロップダウンリスト</td>";
            break;
        case 'attach':
            echo "<td>添付ファイル</td>";
            break;
    }
    switch ($_SESSION["userformdata"][$i]["required"]) {
        case 0:
            echo "<td>任意</td>";
            break;
        case 1:
            echo "<td>必須</td>";
            break;
        case 2:
            echo "<td>いずれか必須</td>";
            break;
    }
    echo "</tr>\n";
}
?>
</table>
</div>
<p><a href="apply.php" class="btn btn-primary" role="button" onclick="return window.confirm('設定内容を、実際の共通情報入力画面に適用します。よろしいですか？')">変更内容を保存し適用する</a> 
<a href="dispose.php" class="btn btn-secondary" role="button" onclick="return window.confirm('現在の設定内容を、保存せず削除します。実際の共通情報入力画面は変更されません。よろしいですか？')">変更内容を保存せず破棄する</a></p>
<script type="text/javascript">
<!--
function check(){

  problem = 0;

//ちゃんと選んだの？
  if(document.form.type.value === ""){
    problem = 1;
  }

//問題ありの場合はエラー表示　ない場合は移動
if ( problem == 1 ) {
  alert( "入力形式を選んで下さい。" );
  return false;
}

  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";
  return true;

}

// -->
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
