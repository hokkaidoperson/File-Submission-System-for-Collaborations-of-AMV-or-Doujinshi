/* global Validator, file_size_max, URL */

const promise_callback_default = function(result) {
    if (result !== null) {
        scroll_and_focus(result);
        return false;
    }
    set_form_confirmation_modal("form");
};

const get_video_metadata = async function(filedata) {
    return new Promise(resolve => {
        const video = document.createElement('video');
        video.preload = 'metadata';
        video.addEventListener('loadedmetadata', () => {
            const returnArray = {width: video.videoWidth, height: video.videoHeight, playtime: parseInt(video.duration)};
            URL.revokeObjectURL(video.src);
            resolve(returnArray);
        });
        video.src = URL.createObjectURL(filedata);
    });
};

const get_image_metadata = async function(filedata) {
    return new Promise(resolve => {
        const img = new Image();
        img.onload = () => {
            const returnArray = {
                width: img.naturalWidth,
                height: img.naturalHeight
            };
            URL.revokeObjectURL(img.src);
            resolve(returnArray);
        };
        img.src = URL.createObjectURL(filedata);
    });
};

const get_audio_metadata = async function(filedata) {
    return new Promise(resolve => {
        const video = document.createElement('video');
        video.preload = 'metadata';
        video.addEventListener('loadedmetadata', () => {
            const returnArray = {playtime: parseInt(video.duration)};
            URL.revokeObjectURL(video.src);
            resolve(returnArray);
        });
        video.src = URL.createObjectURL(filedata);
    });
};

//バリデーション用の総合関数
//ジャンプ先のエラー項目IDと共にpromise_callbackを呼び出し（無ければnull）
//values：検証する欄のIDと値
//types：欄のタイプ
//rules：検証ルール
function form_validation(values, types, rules, item_refresh = null, callback_function_name = false) {
    let validation = new Validator(values, rules, {
        required: '入力されていません。',
        alpha_num: '半角英数字以外の文字が含まれています（記号も使用出来ません）。別のIDを指定して下さい。',
        min: {
            numeric: '数字が小さすぎます。:min以上の数字を指定して下さい。',
            string: '文字数が少なすぎます。:min文字以上で入力して下さい。'
        },
        max: {
            numeric: '数字が大きすぎます。:max以下の数字を指定して下さい。',
            string: '文字数が多すぎます。:max文字以内に抑えて下さい。'
        },
        email: '正しく入力されていません。入力されたメールアドレスをご確認下さい。',
        "confirmed.email": 'メールアドレスを再入力して下さい。入力済みの場合、入力されたメールアドレスを良くご確認下さい。',
        "confirmed.password": 'パスワードを再入力して下さい。入力済みの場合、入力されたパスワードを良くご確認下さい。',
        "regex.ext": '半角英数字（小文字）とカンマ以外の文字が含まれています。',
        "required_if.url": "「外部のファイルアップロードサービスを利用して送信する」を選択している場合は入力が必要です。",
        required_with: '入力する場合はいずれの入力欄にも入力が必要です。',
        required_without_all: 'いずれかの入力欄に入力が必要です。',
        url: '正しく入力されていません。入力されたURLをご確認下さい。'
    });
    validation.checkAsync(function() {
        Object.keys(types).forEach(function(id) {
            if (item_refresh !== null && item_refresh !== id) return;
            document.getElementById(id + "-errortext").innerHTML = "";
            change_item_color(id, types[id], true);
        });
        if (callback_function_name !== false) callback_function_name(null);
    }, function() {
        let jumpto = null;
        Object.keys(types).forEach(function(id) {
            if (item_refresh !== null && item_refresh !== id) return;
            document.getElementById(id + "-errortext").innerHTML = "";
            if (validation.errors.first(id)) {
                document.getElementById(id + "-errortext").innerHTML = validation.errors.first(id);
                change_item_color(id, types[id]);
                if (jumpto === null) jumpto = id;
            } else {
                change_item_color(id, types[id], true);
            }
        });
        if (callback_function_name !== false) callback_function_name(jumpto);
    });
}

function change_item_color(id, type, valid = false) {
    switch (type) {
        case "textbox":
        case "textarea":
        case "radio":
        case "check":
        case "dropdown":
            var f = document.getElementsByName(id);
            if (valid) {
                for(var j = 0; j < f.length; j++ ){
                    f[j].classList.add("is-valid");
                    f[j].classList.remove("is-invalid");
                }
                if (typeof document.getElementsByName(id + "_confirmation")[0] !== "undefined") {
                    document.getElementsByName(id + "_confirmation")[0].classList.add("is-valid");
                    document.getElementsByName(id + "_confirmation")[0].classList.remove("is-invalid");
                }
            } else {
                for(var j = 0; j < f.length; j++ ){
                    f[j].classList.add("is-invalid");
                    f[j].classList.remove("is-valid");
                }
                if (typeof document.getElementsByName(id + "_confirmation")[0] !== "undefined") {
                    document.getElementsByName(id + "_confirmation")[0].classList.add("is-invalid");
                    document.getElementsByName(id + "_confirmation")[0].classList.remove("is-valid");
                }
            }
            break;
    }
}

function scroll_and_focus(id) {
    //https://www.to-r.net/media/smooth_scrolling_2019/
    const rectTop = document.getElementById(id).getBoundingClientRect().top;
    const offsetTop = window.pageYOffset;
    const buffer = 50;
    const top = rectTop + offsetTop - buffer;
    window.scrollTo({
        top,
        behavior: "smooth"
    });
    document.getElementById(id).focus({preventScroll:true});
}

function get_value(id) {
    switch(types[id]) {
        case "check":
        case "radio":
            var f = document.getElementsByName(id);
            var result = "";
            for(var j = 0; j < f.length; j++ ){
                if(f[j].checked ){
                    result += f[j].value;
                }
            }
            return result;
        case "attach":
            return "0";
        default:
            return document.getElementsByName(id)[0].value;
    }
}

function validation_call(indv = null){
    var items = {};
    Object.keys(types).forEach(function(id) {
        items[id] = get_value(id);
    });
    if (indv !== null) form_validation(items, types, rules, indv);
    else form_validation(items, types, rules, null, promise_callback_default);

    return false;
}

//文字数カウント　参考　https://www.nishishi.com/javascript-tips/input-counter.html
function show_length(str, resultid) {
   document.getElementById(resultid).innerHTML = "現在 " + str.length + " 文字";
}

function form_setting_enable_form(num, items) {
    if (num === "") return;
    num = parseInt(num);
    items.forEach(function(id) {
        for(var i = 0; i < 5; i++ ){
            var element = document.getElementsByName(id + "[" + i + "]")[0];
            if (i < num) element.disabled = false;
            else element.disabled = true;
        }
    });
}

//添付ファイル拡張子　参考　https://zukucode.com/2017/12/javascript-input-file-ext.html
function check_ext(name, reg) {
    if (!name.toUpperCase().match(reg)) {
        return "指定した拡張子でないため、このファイルはアップロード出来ません。";
    }
    return 0;
}

async function check_attachments(val, elementid) {
    var name = document.getElementById(elementid).files;
    var output = "";
    var sizesum = 0;
    var filenumber = 0;
    var lengthsum = 0;
    if (val.filenumber == "") var filemax = 100;
    else var filemax = parseInt(val.filenumber);
    var ext = val.ext;
    ext = ext.replace(/,/g, "|");
    ext = ext.toUpperCase();
    var reg = new RegExp('\.(' + ext + ')$', 'i');
    if (val.size != "") var size = parseInt(val.size);
    else var size = file_size_max;
    size = size * 1024 * 1024;
    if (val.reso[0] != "") var widthmax = parseInt(val.reso[0]);
    else var widthmax = 99999999;
    if (val.reso[1] != "") var heightmax = parseInt(val.reso[1]);
    else var heightmax = 99999999;
    if (val.length != "") var lengthmax = parseInt(val.length);
    else var lengthmax = 99999999;
    if (val.worklength != "") var worklengthmax = parseInt(val.worklength);
    else var worklengthmax = 99999999;

    for (var j=0; j<name.length; j++) {
        var file = name[j];
        var result = check_ext(file.name, reg);
        if (result != 0) {
            output += "【" + file.name + "】指定した拡張子でないため、このファイルはアップロード出来ません。<br>";
        } else {
            if (file.type.startsWith('video/')) {
                var filedetails = await get_video_metadata(file);
                if (filedetails.width > widthmax || filedetails.height > heightmax){
                    output += "【" + file.name + "】ファイルの解像度が指定より上回っているため、このファイルはアップロード出来ません。<br>";
                } else {
                    sizesum += parseInt(file.size);
                    lengthsum += filedetails.playtime;
                }
            } else if (file.type.startsWith('image/')) {
                var filedetails = await get_image_metadata(file);
                if (filedetails.width > widthmax || filedetails.height > heightmax){
                    output += "【" + file.name + "】ファイルの解像度が指定より上回っているため、このファイルはアップロード出来ません。<br>";
                } else {
                    sizesum += parseInt(file.size);
                }
            } else if (file.type.startsWith('audio/')) {
                var filedetails = await get_audio_metadata(file);
                sizesum += parseInt(file.size);
                lengthsum += filedetails.playtime;
            } else {
                sizesum += parseInt(file.size);
            }
        }
    }
    var lengthsumdiff = lengthsum;
    var uploadedfs = JSON.parse(document.form[elementid].dataset.current);
    Object.keys(uploadedfs).forEach(function(key) {
       sizesum += parseInt(uploadedfs[key].size);
       lengthsum += parseInt(uploadedfs[key].playtime);
    });
    var deletenum = 0;
    for(var j = 0; j < document.getElementsByName(elementid + "-delete[]").length; j++ ){
        if(document.getElementsByName(elementid + "-delete[]").type === "hidden") break;
        if(document.getElementsByName(elementid + "-delete[]")[j].checked){
            var deletekey = document.getElementsByName(elementid + "-delete[]")[j].value;
            sizesum -= uploadedfs[deletekey]["size"];
            lengthsum -= uploadedfs[deletekey]["playtime"];
            lengthsumdiff -= uploadedfs[deletekey]["playtime"];
            deletenum++;
        }
    }
    filenumber = name.length + Object.keys(uploadedfs).length - deletenum;
    if(filenumber <= 0 && val.required == "1") {
        output += "必須項目のため、ファイルの個数を0個には出来ません。新規にファイルをアップロードするか、ファイルの削除を取りやめて下さい。<br>";
    } else if(filenumber > filemax) {
        output += "ファイルの個数が多すぎます（現在" + filenumber + "個）。ファイルを削除するか、新規アップロードを取りやめて下さい。<br>";
    }
    if (sizesum > size) {
        output += "ファイルの合計サイズが大きすぎます（現在" + parseInt(sizesum / 1024 / 1024) + "MB）。ファイルを削除するか、新規アップロードを取りやめて下さい。<br>";
    }
    if (lengthsum > lengthmax) {
        output += "ファイルの合計再生時間が大きすぎます（現在" + parseInt(lengthsum / 60) + "分" + (lengthsum % 60) + "秒）。ファイルを削除するか、新規アップロードを取りやめて下さい。<br>";
    }
    var worklengthsum = lengthsumdiff + parseInt(document.form[elementid].dataset.currentLengthSum);
    if (elementid === "submitfile" && worklengthsum > worklengthmax) {
        output += "全作品の総再生時間が許容範囲を超えてしまいます（現在" + parseInt(worklengthsum / 60) + "分" + (worklengthsum % 60) + "秒）。提出済みの別の作品を削除するか、この作品のファイルを削減して下さい。<br>";
    }
    if (output === "") return 0;
    return output;
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

function set_form_confirmation_modal(target_name, message = '<p>入力内容に問題は見つかりませんでした。</p><p>現在の入力内容を送信してもよろしければ「送信する」を押して下さい。<br>入力内容の修正を行う場合は「戻る」を押して下さい。</p>', title = '送信確認', button_text = '<i class="bi bi-check-circle-fill"></i> 送信する', button_class = 'primary') {
    document.getElementById('form_confirmation_modal_btn').dataset.sysFormid = target_name;
    document.getElementById('form_confirmation_modal_body').innerHTML = message;
    document.getElementById('form_confirmation_modal_title').innerHTML = title;
    document.getElementById('form_confirmation_modal_btn').innerHTML = button_text;
    document.getElementById('form_confirmation_modal_btn').classList.remove('btn-primary');
    document.getElementById('form_confirmation_modal_btn').classList.remove('btn-secondary');
    document.getElementById('form_confirmation_modal_btn').classList.remove('btn-success');
    document.getElementById('form_confirmation_modal_btn').classList.remove('btn-danger');
    document.getElementById('form_confirmation_modal_btn').classList.remove('btn-warning');
    document.getElementById('form_confirmation_modal_btn').classList.remove('btn-info');
    document.getElementById('form_confirmation_modal_btn').classList.remove('btn-light');
    document.getElementById('form_confirmation_modal_btn').classList.remove('btn-dark');
    document.getElementById('form_confirmation_modal_btn').classList.add('btn-' + button_class);
    $('#form_confirmation_modal').modal();
    return false;
}

function form_confirmation_modal_function() {
    var modal_button = document.getElementById('form_confirmation_modal_btn');
    modal_button.disabled = "disabled";
    document[modal_button.dataset.sysFormid].submit();
}

function set_link_confirmation_modal(href_name, message = '<p>現在の設定内容を保存せず、メニューに戻ります。よろしければ「取り消す」を押して下さい。<br>入力を続ける場合は「戻る」を押して下さい。</p>', title = '取消確認', button_text = '<i class="bi bi-trash"></i> 取り消す', button_class = 'warning') {
    document.getElementById('link_confirmation_modal_btn').setAttribute('href', href_name);
    document.getElementById('link_confirmation_modal_body').innerHTML = message;
    document.getElementById('link_confirmation_modal_title').innerHTML = title;
    document.getElementById('link_confirmation_modal_btn').innerHTML = button_text;
    document.getElementById('link_confirmation_modal_btn').classList.remove('btn-primary');
    document.getElementById('link_confirmation_modal_btn').classList.remove('btn-secondary');
    document.getElementById('link_confirmation_modal_btn').classList.remove('btn-success');
    document.getElementById('link_confirmation_modal_btn').classList.remove('btn-danger');
    document.getElementById('link_confirmation_modal_btn').classList.remove('btn-warning');
    document.getElementById('link_confirmation_modal_btn').classList.remove('btn-info');
    document.getElementById('link_confirmation_modal_btn').classList.remove('btn-light');
    document.getElementById('link_confirmation_modal_btn').classList.remove('btn-dark');
    document.getElementById('link_confirmation_modal_btn').classList.add('btn-' + button_class);
    $('#link_confirmation_modal').modal();
    return false;
}

function tick_all_toggler(name) {
    const is_checked = document.getElementById('tickall_' + name).checked;
    for (var i = 0; i<document.getElementsByName(name).length; i++){
        document.getElementsByName(name)[i].checked = is_checked;
    }
}

function tick_all_child_toggler(name) {
    var children = document.getElementsByName(name);
    for (var i = 0; i<children.length; i++){
        if(!children[i].checked){
            document.getElementById('tickall_' + name).checked = false;
            return;
        }
    }
    document.getElementById('tickall_' + name).checked = true;
}
