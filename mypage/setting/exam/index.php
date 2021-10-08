<?php
require_once('../../../set.php');
setup_session();
$titlepart = 'ファイル確認に関する設定';
require_once(PAGEROOT . 'mypage_header.php');

no_access_right(array("p"), TRUE);

if (file_exists(DATAROOT . 'examsetting.txt')) $examsetting = json_decode(file_get_contents_repeat(DATAROOT . 'examsetting.txt'), true);
if (file_exists(DATAROOT . 'exammember_submit.txt')) $submitmem = file(DATAROOT . 'exammember_submit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
else $submitmem = array();
if (file_exists(DATAROOT . 'exammember_edit.txt')) $editmem = file(DATAROOT . 'exammember_edit.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
else $editmem = array();

?>

<h1>ファイル確認に関する設定</h1>
<p>ファイル確認（提出された作品や情報を確認し、承認するかどうか決める作業）に関する設定をします。</p>
<form name="form" action="handle.php" method="post" onSubmit="return check()" class="system-form-spacer">
<?php csrf_prevention_in_form(); ?>
<h2>ファイル確認の担当者</h2>
<p>主催者・共同運営者のうち、誰がファイル確認を担当するか設定出来ます。</p>
<p>最低でも1人は、ファイル確認のメンバーが必要となります。共同運営者からの辞退などでファイル確認者が誰もいなくなった場合、主催者がファイル確認担当者として自動的に追加されます。</p>
<p>担当者のうち1名をリーダーとして指定出来ます。リーダーがいる場合、リーダーは下記機能の権限を持ちます。共同運営者からの辞退などでリーダーが運営チームを外れる場合、リーダーがいない設定に変更されます。</p>
<ul>
    <li>ファイル確認について「修正待ち」「承認見送り」で即決し、かつ、提出者宛てにその理由を通知する設定になっている場合に、提出者宛てに送信するメールに記載する理由の文を入力する機能（リーダーがいない場合、メンバーが記入した理由文が全てメールに記載されます。）</li>
    <li>ファイル確認について投票していないメンバーがおり、連絡が取れない場合に投票を強制的に締め切る機能（リーダーがいない場合、主催者がメンバーにいるなら主催者が、そうでないならメンバー全員に機能が解放されます。）</li>
    <li>承認可否に関する議論の終了（最終対応入力）機能（リーダーがいない場合は直上に同じ。）</li>
</ul>
<div class="border border-primary system-border-spacer">
<div class="form-group">
新規提出時、及び提出ファイル（メイン）の変更時の確認担当者（複数選択可）【必須】
<?php
$choices = id_state('c');

echo '<div class="form-check">';
echo '<input id="submit_promoter" class="form-check-input" type="checkbox" name="submitmem[]" value="_promoter"';
if (array_search("_promoter", $submitmem) !== FALSE) echo ' checked="checked"';
echo ' onChange="check_individual(&quot;submitmem&quot;);">';
echo '<label class="form-check-label" for="submit_promoter">主催者（' . hsc(nickname($_SESSION["userid"])) . '）</label>';
echo '</div>';
foreach ($choices as $choice) {
    $disp = hsc(nickname($choice));
    echo '<div class="form-check">';
    echo '<input id="submit_choice_' . $choice . '" class="form-check-input" type="checkbox" name="submitmem[]" value="' . $choice . '"';
    if (array_search($choice, $submitmem) !== FALSE) echo ' checked="checked"';
    echo ' onChange="check_individual(&quot;submitmem&quot;);">';
    echo '<label class="form-check-label" for="submit_choice_' . $choice . '">' . $disp . '</label>';
    echo '</div>';
}
?>
<div id="submitmem-errortext" class="system-form-error"></div>
<br>
<div class="form-check">
<input id="submit_add" class="form-check-input" type="checkbox" name="submit_add" value="1"<?php
if (isset($examsetting["submit_add"]) and $examsetting["submit_add"] == "1") echo ' checked="checked"';
?>>
<label class="form-check-label" for="submit_add">共同運営者が増えた際、そのユーザーを新規提出時・提出ファイルの変更時の確認メンバーとして自動的に追加する</label>
</div>
</div>
<div class="form-group">
<label for="submitmem_leader">リーダーの設定</label>
<?php
echo '<select id="submitmem_leader" class="form-control" name="submit_leader">';
echo '<option value=""';
if (isset($examsetting["submit_leader"]) and $examsetting["submit_leader"] == "") echo ' selected';
echo '>（リーダーを設定しない）</option>';
echo '<option id="submitmem_leader_choice__promoter" value="_promoter"';
if (array_search("_promoter", $submitmem) === FALSE) echo ' disabled';
if (isset($examsetting["submit_leader"]) and $examsetting["submit_leader"] == "_promoter") echo ' selected';
echo '>主催者（' . hsc(nickname($_SESSION["userid"])) . '）</option>';
foreach ($choices as $choice) {
    $disp = hsc(nickname($choice));
    echo '<option id="submitmem_leader_choice_' . $choice . '" value="' . $choice . '"';
    if (array_search($choice, $submitmem) === FALSE) echo ' disabled';
    if (isset($examsetting["submit_leader"]) and $examsetting["submit_leader"] == $choice) echo ' selected';
    echo '>' . $disp . '</option>';
}
echo "</select>";
?>
</div>
</div>
<div class="border border-primary system-border-spacer">
<div class="form-group">
作品編集時（提出ファイル自体は変更しない場合）、及び共通情報設定時の確認担当者（複数選択可）【必須】
<?php
echo '<div class="form-check">';
echo '<input id="edit_promoter" class="form-check-input" type="checkbox" name="edit[]" value="_promoter"';
if (array_search("_promoter", $editmem) !== FALSE) echo ' checked="checked"';
echo ' onChange="check_individual(&quot;edit&quot;);">';
echo '<label class="form-check-label" for="edit_promoter">主催者（' . hsc(nickname($_SESSION["userid"])) . '）</label>';
echo '</div>';
foreach ($choices as $choice) {
    $disp = hsc(nickname($choice));
    echo '<div class="form-check">';
    echo '<input id="edit_choice_' . $choice . '" class="form-check-input" type="checkbox" name="edit[]" value="' . $choice . '"';
    if (array_search($choice, $editmem) !== FALSE) echo ' checked="checked"';
    echo ' onChange="check_individual(&quot;edit&quot;);">';
    echo '<label class="form-check-label" for="edit_choice_' . $choice . '">' . $disp . '</label>';
    echo '</div>';
}
?>
<div id="edit-errortext" class="system-form-error"></div>
<br>
<div class="form-check">
<input id="edit_add" class="form-check-input" type="checkbox" name="edit_add" value="1"<?php
if (isset($examsetting["edit_add"]) and $examsetting["edit_add"] == "1") echo ' checked="checked"';
?>>
<label class="form-check-label" for="edit_add">共同運営者が増えた際、そのユーザーを作品編集・共通情報設定時の確認メンバーとして自動的に追加する</label>
</div>
</div>
<div class="form-group">
<label for="edit_leader">リーダーの設定</label>
<?php
echo '<select id="edit_leader" class="form-control" name="edit_leader">';
echo '<option value=""';
if (isset($examsetting["edit_leader"]) and $examsetting["edit_leader"] == "") echo ' selected';
echo '>（リーダーを設定しない）</option>';
echo '<option id="edit_leader_choice__promoter" value="_promoter"';
if (array_search("_promoter", $editmem) === FALSE) echo ' disabled';
if (isset($examsetting["edit_leader"]) and $examsetting["edit_leader"] == "_promoter") echo ' selected';
echo '>主催者（' . hsc(nickname($_SESSION["userid"])) . '）</option>';
foreach ($choices as $choice) {
    $disp = hsc(nickname($choice));
    echo '<option id="edit_leader_choice_' . $choice . '" value="' . $choice . '"';
    if (array_search($choice, $editmem) === FALSE) echo ' disabled';
    if (isset($examsetting["edit_leader"]) and $examsetting["edit_leader"] == $choice) echo ' selected';
    echo '>' . $disp . '</option>';
}
echo "</select>";
?>
</div>
</div>
<h2>その他</h2>
<div class="form-group">
作品が修正待ち・承認見送りになった際の送信者への通知について【必須】
<div class="form-check">
<input id="reason-notice" class="form-check-input" type="radio" name="reason" value="notice" <?php
if (isset($examsetting["reason"]) and $examsetting["reason"] == "notice") echo 'checked="checked"';
?> onChange="check_individual(&quot;reason&quot;);">
<label class="form-check-label" for="reason-notice">修正待ち・承認見送りになった理由を記載する</label>
</div>
<div class="form-check">
<input id="reason-dont-a" class="form-check-input" type="radio" name="reason" value="dont-a" <?php
if (isset($examsetting["reason"]) and $examsetting["reason"] == "dont-a") echo 'checked="checked"';
?> onChange="check_individual(&quot;reason&quot;);">
<label class="form-check-label" for="reason-dont-a">修正待ち・承認見送りになった理由は記載しないが、「承認されなかった理由についてはお問い合わせ下さい」という旨の文を付け加える</label>
</div>
<div class="form-check">
<input id="reason-dont-b" class="form-check-input" type="radio" name="reason" value="dont-b" <?php
if (isset($examsetting["reason"]) and $examsetting["reason"] == "dont-b") echo 'checked="checked"';
?> onChange="check_individual(&quot;reason&quot;);">
<label class="form-check-label" for="reason-dont-b">修正待ち・承認見送りになった理由は記載せず、「承認されなかった理由についてはお答えしかねます」という旨の文を付け加える</label>
</div>
<div id="reason-errortext" class="system-form-error"></div>
</div>
<div class="form-group">
提出者の匿名化
<div class="form-check">
<input id="anonymous" class="form-check-input" type="checkbox" name="anonymous" value="1" <?php
if (isset($examsetting["anonymous"]) and $examsetting["anonymous"] == "1") echo 'checked="checked"';
?>>
<label class="form-check-label" for="anonymous">ファイル・情報の提出者が誰か分からない（匿名化された）状態で確認を行う場合は、左のチェックボックスにチェックして下さい。</label>
</div>
<small class="form-text">※このオプションをオンにすると、ファイル確認の際、次のような状態になります。<br>1. 提出者が誰か表示されなくなります。<br>2. ファイル名から提出者が特定される事を防ぐため、ファイル名が伏せられます。</small>
</div>
<br>
<button type="submit" class="btn btn-primary">設定変更</button>
<?php
echo_modal_confirm("入力内容に問題は見つかりませんでした。<br>現在の入力内容の通りに設定を変更してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。");
?>
</form>

<script type="text/javascript">

function check_individual(id) {
    if (id === "submitmem") {
        document.getElementById("submitmem-errortext").innerHTML = "";
        var f = document.getElementsByName("submitmem[]");
        result = 0;
        for(var j = 0; j < f.length; j++ ){
            if(f[j].checked){
                result = 1;
                document.getElementById("submitmem_leader_choice_" + f[j].value).disabled = false;
            } else {
                document.getElementById("submitmem_leader_choice_" + f[j].value).disabled = true;
                if (document.getElementById("submitmem_leader").value === f[j].value) document.getElementById("submitmem_leader").value = "";
            }
        }
        if(result == 0){
            document.getElementById("submitmem-errortext").innerHTML = "いずれかを選択して下さい。";
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-invalid");
                f[j].classList.remove("is-valid");
            }
        } else {
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-valid");
                f[j].classList.remove("is-invalid");
            }
        }
        return;
    }
    if (id === "edit") {
        document.getElementById("edit-errortext").innerHTML = "";
        var f = document.getElementsByName("edit[]");
        result = 0;
        for(var j = 0; j < f.length; j++ ){
            if(f[j].checked){
                result = 1;
                document.getElementById("edit_leader_choice_" + f[j].value).disabled = false;
            } else {
                document.getElementById("edit_leader_choice_" + f[j].value).disabled = true;
                if (document.getElementById("edit_leader").value === f[j].value) document.getElementById("edit_leader").value = "";
            }
        }
        if(result == 0){
            document.getElementById("edit-errortext").innerHTML = "いずれかを選択して下さい。";
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-invalid");
                f[j].classList.remove("is-valid");
            }
        } else {
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-valid");
                f[j].classList.remove("is-invalid");
            }
        }
        return;
    }
    if (id === "reason") {
        var notselected = 0;
        document.getElementById("reason-errortext").innerHTML = "";
        if(typeof document.form.reason.innerHTML === 'string') {
            if(!document.form.reason.checked){
                notselected = 1;
            }
        } else {
            if(document.form.reason.value === ""){
                notselected = 1;
            }
        }
        var f = document.getElementsByName("reason");
        if ( notselected == 1 ) {
            document.getElementById("reason-errortext").innerHTML = "いずれかを選択して下さい。";
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-invalid");
                f[j].classList.remove("is-valid");
            }
        } else {
            for(var j = 0; j < f.length; j++ ){
                f[j].classList.add("is-valid");
                f[j].classList.remove("is-invalid");
            }
        }
        return;
    }
}
function check(){

    var problem = 0;
    document.getElementById("submitmem-errortext").innerHTML = "";
    var f = document.getElementsByName("submitmem[]");
    result = 0;
    for(var j = 0; j < f.length; j++ ){
        if(f[j].checked){
            result = 1;
            document.getElementById("submitmem_leader_choice_" + f[j].value).disabled = false;
        } else {
            document.getElementById("submitmem_leader_choice_" + f[j].value).disabled = true;
            if (document.getElementById("submitmem_leader").value === f[j].value) document.getElementById("submitmem_leader").value = "";
        }
    }
    if(result == 0){
        problem = 1;
        document.getElementById("submitmem-errortext").innerHTML = "いずれかを選択して下さい。";
        for(var j = 0; j < f.length; j++ ){
            f[j].classList.add("is-invalid");
            f[j].classList.remove("is-valid");
        }
    } else {
        for(var j = 0; j < f.length; j++ ){
            f[j].classList.add("is-valid");
            f[j].classList.remove("is-invalid");
        }
    }
    document.getElementById("edit-errortext").innerHTML = "";
    var f = document.getElementsByName("edit[]");
    result = 0;
    for(var j = 0; j < f.length; j++ ){
        if(f[j].checked){
            result = 1;
            document.getElementById("edit_leader_choice_" + f[j].value).disabled = false;
        } else {
            document.getElementById("edit_leader_choice_" + f[j].value).disabled = true;
            if (document.getElementById("edit_leader").value === f[j].value) document.getElementById("edit_leader").value = "";
        }
    }
    if(result == 0){
        problem = 1;
        document.getElementById("edit-errortext").innerHTML = "いずれかを選択して下さい。";
        for(var j = 0; j < f.length; j++ ){
            f[j].classList.add("is-invalid");
            f[j].classList.remove("is-valid");
        }
    } else {
        for(var j = 0; j < f.length; j++ ){
            f[j].classList.add("is-valid");
            f[j].classList.remove("is-invalid");
        }
    }
    var notselected = 0;
    document.getElementById("reason-errortext").innerHTML = "";
    if(typeof document.form.reason.innerHTML === 'string') {
        if(!document.form.reason.checked){
            notselected = 1;
        }
    } else {
        if(document.form.reason.value === ""){
            notselected = 1;
        }
    }
    var f = document.getElementsByName("reason");
    if ( notselected == 1 ) {
        problem = 1;
        document.getElementById("reason-errortext").innerHTML = "いずれかを選択して下さい。";
        for(var j = 0; j < f.length; j++ ){
            f[j].classList.add("is-invalid");
            f[j].classList.remove("is-valid");
        }
    } else {
        for(var j = 0; j < f.length; j++ ){
            f[j].classList.add("is-valid");
            f[j].classList.remove("is-invalid");
        }
    }

    if ( problem == 0 ) {
        $('#confirmmodal').modal();
        $('#confirmmodal').on('shown.bs.modal', function () {
            document.getElementById("submitbtn").focus();
        });
    }
    return false;
}


</script>
<?php
require_once(PAGEROOT . 'mypage_footer.php');
