<?php
require_once('../../../set.php');
setup_session();
$titlepart = '共通情報入力画面の編集';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

if (!file_exists(DATAROOT . 'form/userinfo/')) {
    if (!mkdir(DATAROOT . 'form/userinfo/', 0777, true)) die_mypage('ディレクトリの作成に失敗しました。');
}

//SESSIONデータある？
if (!isset($_SESSION["userformdata"])) {
    $_SESSION["userformdata"] = array();
    for ($i = 0; $i <= 9; $i++) {
        if (!file_exists(DATAROOT . 'form/userinfo/' . "$i" . '.txt')) break;
        $_SESSION["userformdata"][$i] = json_decode(file_get_contents_repeat(DATAROOT . 'form/userinfo/' . "$i" . '.txt'), true);
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
<p><strong>最大10個まで</strong>入力事項を追加出来ます。各項目について、入力必須かそうでないかを設定出来ます。</p>
<p>※求める共通情報が特に無い場合は、何も設定せずに下の「変更内容を保存し適用する」ボタンを押して下さい。</p>
<?php
if (file_exists(DATAROOT . 'form/userinfo/done.txt')) echo '<div class="border border-warning system-border-spacer">
共通情報の入力項目は既に保存・公開されています。設定内容を変更する事は出来ますが、変更は最小限にとどめる事をお勧め致します。
</div>';
else echo '<div class="border border-warning system-border-spacer">
共通情報の入力項目は後から変更出来ますが、変更は最小限にとどめる事をお勧め致します。
</div>';
?>
<p><a data-toggle="collapse" href="#detail" role="button" aria-expanded="false" aria-controls="detail" class="system-foldable-content-link collapsed">
<i class="bi bi-chevron-double-down"></i> サポートしている入力形式（クリック／タップで開閉）
</a></p>
<div class="collapse" id="detail">
<ul>
<li>テキストボックス（1行のみの入力欄）</li>
<ul><li>文字数制限（●●文字以上●●文字以下）を設けられます。</li></ul>
<li>テキストエリア（複数行対応の入力欄）</li>
<ul><li>比較的長い文を書くのに適しています。文字数制限（●●文字以上●●文字以下）を設けられます。</li></ul>
<li>ラジオボタン（複数の選択肢から1つだけ選ぶ丸いボタン）</li>
<li>チェックボックス（1つあるいは複数の選択肢から選ぶ四角いボタン・複数選択可）</li>
<ul><li>「●●●●の場合は左にチェックして下さい」といった使い方をする場合はチェックボックスを選んで下さい。</li></ul>
<li>ドロップダウンリスト（複数の選択肢から1つだけ選ぶ・クリックすると下に選択肢がニュッと出てくる）</li>
<li>添付ファイル</li>
<ul><li>プロフィール画像を求める場合などにご活用下さい。</li></ul>
</ul>
<p>※「テキストボックス」「テキストエリア」「ドロップダウンリスト」は、同じ設問内に最大5つまで入力欄を並列して設置可能です。</p>
</div>
<p class="small">※ここでの設定内容は、<strong>最終的に「変更内容を保存し適用する」ボタンを押さないと実際の入力画面に反映されません。</strong>保存せずにログアウトした場合、未保存の設定内容は失われます。</p>
<h2>共通情報入力画面のプレビュー・設定</h2>
<div class="border border-primary system-border-spacer">
<?php
for ($i = 0; $i <= 10; $i++) {
    if (!isset($_SESSION["userformdata"][$i])) {
        echo '<div class="form-group" id="custom-item-' . $i . '"><label for="type" class="font-weight-bold">項目の新規追加</label>';
        if ($i != 10) echo '<form class="form-inline" name="form" action="add.php" method="get" onSubmit="return check()">
<input type="hidden" name="number" value="' . "$i" . '">
<select name="type" class="form-control" id="type" onChange="if (this.value !== &quot;&quot;) document.form.submit();">
<option value="">【選択して下さい】</option>
<option value="textbox">テキストボックス</option>
<option value="textarea">テキストエリア</option>
<option value="radio">ラジオボタン</option>
<option value="check">チェックボックス</option>
<option value="dropdown">ドロップダウンリスト</option>
<option value="attach">添付ファイル</option>
</select></form>';
        else echo '<div>※項目数が最大に達しているため、新規追加出来ません。</div>';
        echo '</div>';
        break;
    }
    echo '<div class="form-group" id="custom-item-' . $i . '">';
    echo_custom_item($_SESSION["userformdata"][$i], FALSE, TRUE);
    if ($i != 0) {
        echo '<form class="form-inline d-inline-block" name="form_moveup_' . $i . '" action="orderanddel_handle.php" method="post"><input type="hidden" name="number" value="' . "$i" . '"><input type="hidden" name="do" value="up">';
        csrf_prevention_in_form();
        echo '<button type="submit" class="btn btn-light"><i class="bi bi-chevron-double-up"></i> 上に移動</button></form> ';
    } else echo '<button type="button" class="btn btn-light" disabled><i class="bi bi-chevron-double-up"></i> 上に移動</button> ';
    if (isset($_SESSION["userformdata"][$i + 1])) {
        echo '<form class="form-inline d-inline-block" name="form_movedown_' . $i . '" action="orderanddel_handle.php" method="post"><input type="hidden" name="number" value="' . "$i" . '"><input type="hidden" name="do" value="down">';
        csrf_prevention_in_form();
        echo '<button type="submit" class="btn btn-light"><i class="bi bi-chevron-double-down"></i> 下に移動</button></form> ';
    } else echo '<button type="submit" class="btn btn-light" disabled><i class="bi bi-chevron-double-down"></i> 下に移動</button> ';
    echo '<a href="' . $_SESSION["userformdata"][$i]["type"] . '.php?number=' . "$i" . '" class="btn btn-primary"><i class="bi bi-pencil-square"></i> 編集</a> ';
    echo '<form class="form-inline d-inline-block" name="form_delete_' . $i . '" action="orderanddel_handle.php" method="post" onSubmit="return set_form_confirmation_modal(\'form_delete_' . $i . '\', \'<p>この項目を削除します。よろしければ「削除する」を押して下さい。</p>\', \'削除確認\', \'<i class=&quot;bi bi-check-circle-fill&quot;></i> 続行する\', \'danger\');"><input type="hidden" name="number" value="' . "$i" . '"><input type="hidden" name="do" value="delete">';
    csrf_prevention_in_form();
    echo '<button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> 項目を削除</button></form>';
    echo '</div>';
}
?>
</div>
<form class="form-inline d-inline-block" name="form_apply" action="apply.php" method="post" onSubmit="return set_form_confirmation_modal('form_apply', '<p>設定内容を、実際の共通情報入力画面に適用します。よろしければ「送信する」を押して下さい。</p>', '保存確認');">
<?php csrf_prevention_in_form(); ?>
<button type="submit" class="btn btn-primary"><i class="bi bi-check2-square"></i> 変更内容を保存し適用する</button>
</form>
<form class="form-inline d-inline-block" name="form_dispose" action="dispose.php" method="post" onSubmit="return set_form_confirmation_modal('form_dispose', '<p>現在の設定内容を、保存せず取り消します。実際の共通情報入力画面は変更されません。よろしければ「続行する」を押して下さい。</p>', '取消確認', '<i class=&quot;bi bi-check-circle-fill&quot;></i> 続行する', 'warning');">
<?php csrf_prevention_in_form(); ?>
<button type="submit" class="btn btn-secondary"><i class="bi bi-trash"></i> 変更内容を保存せず破棄する</button>
</form>
<script type="text/javascript">
function check(){
    if(document.form.type.value === ""){
        return false;
    }
}
</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
