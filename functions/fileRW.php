<?php
//ファイル読み書き関連関数

//JSON読み書き用
class JsonRW {
    public $array;
    public $file_exists;
    private $filename;
    
    public function __construct($filename) {
        $this->filename = $filename;
        $this->array = json_unpack($filename);
        if ($this->array !== FALSE) $this->file_exists = TRUE;
        else {
            $this->array = [];
            $this->file_exists = FALSE;
        }
    }
    
    public function write($filename = NULL) {
        if ($filename == NULL) $filename = $this->filename;
        if (json_pack($filename, $this->array)) $this->file_exists = TRUE;
    }
}

//配列をjsonにパックして保存（ファイルの場所、配列の順）
//file_put_contentsの結果をリターン
function json_pack($filename, $array) {
    $arrayjson = json_encode($array);
    return file_put_contents_repeat($filename, $arrayjson);
}

//jsonのファイルをほどいた配列を返す
//ファイルが無い場合はFALSE
function json_unpack($filename) {
    if (!file_exists($filename)) return FALSE;
    return json_decode(file_get_contents_repeat($filename), true);
}

function file_get_contents_repeat($filename) {
    for ($i=0; $i<20; $i++) {
        $result = file_get_contents($filename);
        if ($result !== FALSE) return $result;
        sleep(0.2 + 0.2 * $i);
    }
    return FALSE;
}

function file_put_contents_repeat($filename, $data, $flags = 0) {
    for ($i=0; $i<20; $i++) {
        $result = file_put_contents($filename, $data, $flags);
        if ($result !== FALSE) return $result;
        sleep(0.2 + 0.2 * $i);
    }
    return FALSE;
}

//再帰的にディレクトリを削除する関数（引用：https://www.sejuku.net/blog/78776）
function remove_directory($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        if (is_dir("$dir/$file")) {
            remove_directory("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    // 指定したディレクトリを削除
    return rmdir($dir);
}

//ディレクトリコピー　引用　https://tyama-blog.blog.ss-blog.jp/2016-03-27
function dir_copy($dir_name, $new_dir){
    if (!is_dir($new_dir)) {
        mkdir($new_dir, 0777, true);
    }

    if (is_dir($dir_name)) {
        if ($dh = opendir($dir_name)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == "." || $file == "..") {
                    continue;
                }
                if (is_dir($dir_name . "/" . $file)) {
                    dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
                } else {
                    copy($dir_name . "/" . $file, $new_dir . "/" . $file);
                }
            }
            closedir($dh);
        }
    }
    return true;
}

//ZIPパック　引用　https://blog.ver001.com/php-zip-archive/
function zipSub($za, $path, $parentPath = '') {
    $dh = opendir($path);
    while (($entry = readdir($dh)) !== false) {
        if ($entry == '.' || $entry == '..') {
        } else {
            $localPath = $parentPath.$entry;
            $fullpath = $path.'/'.$entry;
            if (is_file($fullpath)) {
                $za->addFile($fullpath, $localPath);
            } else if (is_dir($fullpath)) {
                $za->addEmptyDir($localPath);
                zipSub($za, $fullpath, $localPath.'/');
            }
        }
    }
    closedir($dh);
}

//getid3
function get_file_info($filepath) {
    if (!file_exists($filepath)) return FALSE;

    global $getid3;
    $file_info = $getid3->analyze($filepath);
    getid3_lib::CopyTagsToComments($file_info);
    return $file_info;
}

function get_playtime($filepath) {
    $file_info = get_file_info($filepath);
    if (isset($file_info['playtime_seconds'])) return (int)$file_info['playtime_seconds'];
    return FALSE;
}

function get_resolution($filepath) {
    $file_info = get_file_info($filepath);
    $array = [];
    if (isset($file_info['video']['resolution_x'])) $array[] = $file_info['video']['resolution_x'];
    else $array[] = FALSE;
    if (isset($file_info['video']['resolution_y'])) $array[] = $file_info['video']['resolution_y'];
    else $array[] = FALSE;
    return $array;
}
