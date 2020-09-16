<script type="text/javascript">
<!--
//日付チェック（参考：https://web-designer.cman.jp/html_ref/abc_list/input_sample2/）
function date_check(str){
  var ok = true;
  var wdate = str.value;
  var wresult = "";
  var wlength = "";
  var wyear = "";
  var wmonth = "";
  var wday = "";

// 数字,-以外の入力チェック
  wresult = /[^\d-]/.test(wdate);
  if (wresult){
    ok=false;
    return(ok);
  }

// 入力文字数チェック
  wlength = wdate.length;
  if (wlength!=10){
    ok=false;
    return(ok);
  }

// 年月日に分割　＆　フォーマットチェック
// yyyy-mm-dd形式の場合
  wresult = wdate.split("-");
  if (wresult.length!=1 & wresult.length!=3){
    ok=false;
    return(ok);
  }

// フォーマットチェック
  if ((wresult[0].length!=4) | (wresult[1].length!=2) | (wresult[2].length!=2)){
    ok=false;
    return(ok);
  }
  wyear=Number(wresult[0]);
  wmonth=Number(wresult[1]);
  wday=Number(wresult[2]);

// 月日範囲チェック
  if (wmonth<1 | wmonth>12){
    ok=false;
    alert("月は01～12の範囲で入力して下さい。");
    return(ok);
  }
  if (wday<1 | wday>31){
    ok=false;
    alert("日は01～31の範囲で入力して下さい。");
    return(ok);
  }
  return(ok);
}

//時刻チェック（参考：https://web-designer.cman.jp/html_ref/abc_list/input_sample2/）
function time_check(time){
  var ok = true;
  var wtime = time.value;
  var wresult = "";
  var wlength = "";
  var wyear = "";
  var wmonth = "";
  var wday = "";

// 数字,:以外の入力チェック
  wresult = /[^\d\:]/.test(wtime);
  if (wresult){
    ok=false;
    return(ok);
  }

// 入力文字数チェック
  wlength = wtime.length;
  if (wlength!=5){
    ok=false;
    return(ok);
  }

// 時分秒に分割　＆　フォーマットチェック
  wresult = wtime.split(":");
  if (wresult.length!=2){
    ok=false;
    return(ok);
  }

// 時分の桁数チェック（秒のチェックは実施しない（時分のチェック結果と同一のため))
  if (wresult[0].length!=2 | wresult[1].length!=2){
    ok=false;
    return(ok);
  }

  whour=Number(wresult[0]);
  wminute=Number(wresult[1]);

// 時分秒範囲チェック
  if (whour<0 | whour>23){
    ok=false;
    return(ok);
  }
  if (wminute<0 | wminute>59){
    ok=false;
    return(ok);
  }

  return(ok);
}

//チェック系関数　問題無ければ0を、そうでなければエラーメッセージを返す
//必須・任意関連（テキストボックス、エリア）
function check_required(type, item) {
  if (type == "1" && item === "") return "\n入力されていません。";
  return 0;
}

//必須・任意関連（テキストボックス×2）
function check_required2(type, item, item2) {
  if (type == "1") {
    if (item === "" || item2 === "")
    return "いずれかの入力欄が入力されていません。";
  }
  if (type == "2") {
    if (item === "" && item2 === "")
    return "いずれの入力欄も入力されていません。";
  }
  return 0;
}

//テキスト系の最大最小（0だとチェックしない）
function check_maxmin(max, min, item) {
  if (max != 0) {
    if (item.length > max) return "文字数が多すぎます。" + max + "文字以内に抑えて下さい。";
  }
  if (min != 0) {
    if (item.length < min && item.length > 0) return "文字数が少なすぎます。" + min + "文字以上になるようにして下さい。";
  }
  return 0;
}

//添付ファイル拡張子　参考　https://zukucode.com/2017/12/javascript-input-file-ext.html
function check_ext(name, reg) {
  if (!name.toUpperCase().match(reg)) {
    return "指定した拡張子でないため、このファイルはアップロード出来ません。";
  }
  return 0;
}


//項目チェックのショートカット
function check_textbox(val) {
    var valid = 1;
    var problem = 0;
    var item = document.getElementById("custom-" + val.id).value;
    var result = check_required(val.required, item);
    if (result != 0) {
        problem = 1;
        valid = 0;
        document.getElementById("custom-" + val.id + "-errortext").innerHTML = result;
    } else {
        if (val.max != "") var vmax = parseInt(val.max);
        else var vmax = 9999;
        if (val.min != "") var vmin = parseInt(val.min);
        else var vmin = 0;
        result = check_maxmin(vmax, vmin, item);
        if (result != 0) {
            problem = 1;
            valid = 0;
            document.getElementById("custom-" + val.id + "-errortext").innerHTML = result;
        }
    }
    if (valid) {
        document.getElementById("custom-" + val.id).classList.add("is-valid");
        document.getElementById("custom-" + val.id).classList.remove("is-invalid");
    } else {
        document.getElementById("custom-" + val.id).classList.add("is-invalid");
        document.getElementById("custom-" + val.id).classList.remove("is-valid");
    }
    return problem;
}

function check_textbox2(val) {
    var valid = 1;
    var problem = 0;
    var output = "";
    var item = document.getElementById("custom-" + val.id + "-1").value;
    var item2 = document.getElementById("custom-" + val.id + "-2").value;
    var result = check_required2(val.required, item, item2);
    if (result != 0) {
        problem = 1;
        valid = 0;
        output += result + "<br>";
    }
    if (item != "") {
        if (val.max != "") var vmax = parseInt(val.max);
        else var vmax = 9999;
        if (val.min != "") var vmin = parseInt(val.min);
        else var vmin = 0;
        result = check_maxmin(vmax, vmin, item);
        if (result != 0) {
            problem = 1;
            valid = 0;
            output += "【1つ目の入力欄】" + result + "<br>";
        }
    }
    if (item2 != "") {
        if (val.max2 != "") vmax = parseInt(val.max2);
        else vmax = 9999;
        if (val.min2 != "") vmin = parseInt(val.min2);
        else vmin = 0;
        result = check_maxmin(vmax, vmin, item2);
        if (result != 0) {
            problem = 1;
            valid = 0;
            output += "【2つ目の入力欄】" + result + "<br>";
        }
    }
    document.getElementById("custom-" + val.id + "-errortext").innerHTML = output;
    if (valid) {
        document.getElementById("custom-" + val.id + "-1").classList.add("is-valid");
        document.getElementById("custom-" + val.id + "-1").classList.remove("is-invalid");
        document.getElementById("custom-" + val.id + "-2").classList.add("is-valid");
        document.getElementById("custom-" + val.id + "-2").classList.remove("is-invalid");
    } else {
        document.getElementById("custom-" + val.id + "-1").classList.add("is-invalid");
        document.getElementById("custom-" + val.id + "-1").classList.remove("is-valid");
        document.getElementById("custom-" + val.id + "-2").classList.add("is-invalid");
        document.getElementById("custom-" + val.id + "-2").classList.remove("is-valid");
    }
    return problem;
}

function check_checkbox(val) {
    // 参考　http://javascript.pc-users.net/browser/form/checkbox.html
    var problem = 0;
    var f = document.getElementsByName("custom-" + val.id + "[]");
    var result = 0;
    for(var j = 0; j < f.length; j++ ){
        if(f[j].checked ){
            result = 1;
        }
    }
    if(result == 0 && val.required == "1"){
        problem = 1;
        document.getElementById("custom-" + val.id + "-errortext").innerHTML = "いずれかを選択して下さい。";
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
    return problem;
}

function check_radio(val) {
    var problem = 0;
    if(typeof document.form["custom-" + val.id].innerHTML === 'string') {
        if(document.form["custom-" + val.id].checked) var item = document.form["custom-" + val.id].value;
        else var item = "";
    } else var item = document.form["custom-" + val.id].value;
    var result = check_required(val.required, item);
    var f = document.getElementsByName("custom-" + val.id);
    if (result != 0) {
        problem = 1;
        document.getElementById("custom-" + val.id + "-errortext").innerHTML = "いずれかを選択して下さい。";
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
    return problem;
}

function check_dropdown(val) {
    var problem = 0;
    var item = document.form["custom-" + val.id].value;
    var result = check_required(val.required, item);
    if (result != 0) {
        problem = 1;
        document.getElementById("custom-" + val.id + "-errortext").innerHTML = "いずれかを選択して下さい。";
        document.form["custom-" + val.id].classList.add("is-invalid");
        document.form["custom-" + val.id].classList.remove("is-valid");
    } else {
        document.form["custom-" + val.id].classList.add("is-valid");
        document.form["custom-" + val.id].classList.remove("is-invalid");
    }
    return problem;
}

function check_attach(val, uploadedfs) {
    var name = document.getElementById("custom-" + val.id).files;
    var problem = 0;
    var valid = 1;
    var output = "";
    var sizesum = 0;
    var filenumber = 0;
    if (val.filenumber == "") var filemax = 100;
    else var filemax = parseInt(val.filenumber);
    var ext = val.ext;
    ext = ext.replace(/,/g, "|");
    ext = ext.toUpperCase();
    var reg = new RegExp('\.(' + ext + ')$', 'i');
    if (val.size != "") var size = parseInt(val.size);
    else var size = <?php echo FILE_MAX_SIZE; ?>;
    size = size * 1024 * 1024;

    for (var j=0; j<name.length; j++) {
        var file = name[j];
        var result = check_ext(file.name, reg);
        if (result != 0) {
            problem = 1;
            valid = 0;
            output += "【" + file.name + "】指定した拡張子でないため、このファイルはアップロード出来ません。<br>";
        } else {
            sizesum += parseInt(file.size);
        }
    }
    sizesum += parseInt(document.form["custom-" + val.id + "-currentsize"].value);
    var deletenum = 0;
    for(var j = 0; j < document.getElementsByName("custom-" + val.id + "-delete[]").length; j++ ){
        if(document.getElementsByName("custom-" + val.id + "-delete[]").type === "hidden") break;
        if(document.getElementsByName("custom-" + val.id + "-delete[]")[j].checked){
            sizesum -= uploadedfs[j];
            deletenum++;
        }
    }
    filenumber = parseInt(name.length) + parseInt(document.form["custom-" + val.id + "-already"].value) - deletenum;
    if(filenumber <= 0 && val.required == "1") {
        problem = 1;
        valid = 0;
        output += "必須項目のため、ファイルの個数を0個には出来ません。新規にファイルをアップロードするか、ファイルの削除を取りやめて下さい。<br>";
    } else if(filenumber > filemax) {
        problem = 1;
        valid = 0;
        output += "ファイルの個数が多すぎます（現在" + filenumber + "個）。ファイルを削除するか、新規アップロードを取りやめて下さい。<br>";
    }
    if (sizesum > size) {
        problem = 1;
        valid = 0;
        output += "ファイルの合計サイズが大きすぎます（現在" + sizesum / 1024 / 1024 + "MB）。ファイルを削除するか、新規アップロードを取りやめて下さい。<br>";
    }
    document.getElementById("custom-" + val.id + "-errortext").innerHTML = output;
    if (valid) {
        document.getElementById("custom-" + val.id).classList.add("is-valid");
        document.getElementById("custom-" + val.id).classList.remove("is-invalid");
    } else {
        document.getElementById("custom-" + val.id).classList.add("is-invalid");
        document.getElementById("custom-" + val.id).classList.remove("is-valid");
    }
    return problem;
}

function check_submitfile(val, uploadedfs) {
    var name = document.getElementById("submitfile").files;
    var problem = 0;
    var valid = 1;
    var output = "";
    var sizesum = 0;
    var filenumber = 0;
    if (val.filenumber == "") var filemax = 100;
    else var filemax = parseInt(val.filenumber);
    var ext = val.ext;
    ext = ext.replace(/,/g, "|");
    ext = ext.toUpperCase();
    var reg = new RegExp('\.(' + ext + ')$', 'i');
    if (val.size != "") var size = parseInt(val.size);
    else var size = <?php echo FILE_MAX_SIZE; ?>;
    size = size * 1024 * 1024;

    for (var j=0; j<name.length; j++) {
        var file = name[j];
        var result = check_ext(file.name, reg);
        if (result != 0) {
            problem = 1;
            valid = 0;
            output += "【" + file.name + "】指定した拡張子でないため、このファイルはアップロード出来ません。<br>";
        } else {
            sizesum += parseInt(file.size);
        }
    }
    sizesum += parseInt(document.form["submitfile-currentsize"].value);
    var deletenum = 0;
    for(var j = 0; j < document.getElementsByName("submitfile-delete[]").length; j++ ){
        if(document.getElementsByName("submitfile-delete[]").type === "hidden") break;
        if(document.getElementsByName("submitfile-delete[]")[j].checked){
            sizesum -= uploadedfs[j];
            deletenum++;
        }
    }
    filenumber = parseInt(name.length) + parseInt(document.form["submitfile-already"].value) - deletenum;
    if(filenumber <= 0) {
        problem = 1;
        valid = 0;
        output += "必須項目のため、ファイルの個数を0個には出来ません。新規にファイルをアップロードするか、ファイルの削除を取りやめて下さい。<br>";
    } else if(filenumber > filemax) {
        problem = 1;
        valid = 0;
        output += "ファイルの個数が多すぎます（現在" + filenumber + "個）。ファイルを削除するか、新規アップロードを取りやめて下さい。<br>";
    }
    if (sizesum > size) {
        problem = 1;
        valid = 0;
        output += "ファイルの合計サイズが大きすぎます（現在" + sizesum / 1024 / 1024 + "MB）。ファイルを削除するか、新規アップロードを取りやめて下さい。<br>";
    }
    document.getElementById("submitfile-errortext").innerHTML = output;
    if (valid) {
        document.getElementById("submitfile").classList.add("is-valid");
        document.getElementById("submitfile").classList.remove("is-invalid");
    } else {
        document.getElementById("submitfile").classList.add("is-invalid");
        document.getElementById("submitfile").classList.remove("is-valid");
    }
    return problem;
}

//文字数カウント　参考　https://www.nishishi.com/javascript-tips/input-counter.html
function ShowLength(str, resultid) {
   document.getElementById(resultid).innerHTML = "現在 " + str.length + " 文字";
}

//送信に時間が掛かるかもしれないので別のダイアログを呼び出す
function closesubmit() {
  submitbtn = document.getElementById("submitbtn");
  submitbtn.disabled = "disabled";
  $('#confirmmodal').modal('hide');
  $('#sendingmodal').modal({
    keyboard: false,
    backdrop: "static"
  });
  changed = false;
  document.form.submit();
}

// -->
</script>
