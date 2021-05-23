<?php
require ("./Csv.php");
$is_unmatch = false;

//「解析用ファイル作成」ボタンが押された時の処理
if (isset($_POST['create'])) {
    session_start();
    $csv = new Csv($_FILES['upload_csv']['tmp_name']);            //ファイルパスをCsvクラスのコンストラクタに渡す
    moveErrorPage($csv);
    downloadCsvData($csv);                                        //解析結果をcsvファイルとしてダウンロードする
    $_SESSION = array();
    session_destroy();
}

function moveErrorPage($csv){
    if ($csv->isError === true) {
        $_SESSION['error_array'] = $csv->error_array;
        header('Location: ./showError.php');
        exit();
    }
}

//結果をCSVファイル形式で出力するメソッド
function downloadCsvData($csv){
    // $file_name = null;
    // date_default_timezone_set('Asia/Tokyo');
    // if (empty($_POST['name']) === false) {
    //     $file_name = $_POST['name'].'.csv';
    // }
    // else {
    //     $file_name = date("Ymd_His").'.csv';
    // }

    //ファイル名の作成
    $file_name = null;
    //ファイル名が何も入力されていなかった場合
    if (strlen($_POST['name']) == 0) {
        date_default_timezone_set('Asia/Tokyo');
        $file_name = date("Ymd_His").'.csv';
    }
    //ファイル名の拡張子が「.csv」で指定されていた場合
    else if(preg_match('/.+.csv/', $_POST['name'])){
        $_SESSION['name'] = $_POST['name'];
        $file_name = $_SESSION['name'];
    }
    //ファイル名の拡張子が指定されていなかった場合
    else {
        $_SESSION['name'] = $_POST['name'];
        $file_name = $_SESSION['name'].'.csv';
    }

    header("Content-Type: application/octet-stream");
    header('Content-Disposition: attachment; filename='.$file_name);
    
    for ($i=0; $i < count($csv->head_array); $i++) {
        if(empty($csv->head_array[$i]) === false){
            $csv->head_array[$i] = str_replace(array("\r\n", "\r", "\n"), '', $csv->head_array[$i]);
            for ($j=0; $j < count($csv->head_array[$i]); $j++) {
                if ($csv->head_array[$i][$j] !== "") {
                    echo mb_convert_encoding($csv->head_array[$i][$j].",", 'SJIS');
                }
            }
            echo mb_convert_encoding("\n", 'SJIS');
        }
    }

    for ($i=0; $i < count($csv->data_header_array); $i++) {
        if(empty($csv->data_header_array[$i]) === false){
            for ($j=0; $j < count($csv->data_header_array[$i]); $j++) { 
                echo mb_convert_encoding($csv->data_header_array[$i][$j].",", 'SJIS');
            }
            echo mb_convert_encoding("\n", 'SJIS');
        }
    }

    for ($i=0; $i < count($csv->count_array); $i++) {
        if(empty($csv->count_array[$i]) === false){
            for ($j=0; $j < count($csv->count_array[$i]); $j++) { 
                echo mb_convert_encoding($csv->count_array[$i][$j].",", 'SJIS');
            }
            echo mb_convert_encoding("\n", 'SJIS');
        }
    }
}
?>
