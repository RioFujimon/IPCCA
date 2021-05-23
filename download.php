<?php
include('./Csv.php');
session_start();

if (isset($_SESSION['csv1'])) {
    $csv1 = unserialize($_SESSION['csv1']);   
}else {
    $csv1 = null;
}

if (isset($_SESSION['history'])) {
    $history = unserialize($_SESSION['history']);   
}else {
    $history = null;
}

if (isset($_SESSION['result'])) {
    $resulet_array = unserialize($_SESSION['result']);   
}else {
    $resulet_array = null;
}

if (isset($_SESSION['name'])) {
    $file_name = unserialize($_SESSION['name']);
}else {
    $file_name = null;
}

//「結合」または「差分」したcsvファイルをダウンロードする
downloadCalculatedResult($csv1, $resulet_array, $history, $file_name);


//解析用csvファイルどうしの数値データを「結合」または「差分」した結果を
//csvファイルとしてダウンロードするメソッド
function downloadCalculatedResult($csv, $resulet_array, $history, $file_name){
    header("Content-Type: application/octet-stream");
    header('Content-Disposition: attachment; filename='.$file_name);

    //解析結果csvファイルのheader部分の「history」に内容を追加する
    for ($i=0; $i < count($csv->head_array); $i++) { 
        for ($j=0; $j < count($csv->head_array[$i]); $j++) {
            //header内に「history」が見つかった時
            if (strcmp($csv->head_array[$i][$j], 'history') === 0) {
                $csv->head_array[$i][$j+1] = $history; 
            }
        }
    }

    //解析結果csvファイルのheader部分を書き込んでいく
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

    //解析結果csvファイルのDataHeader部分を書き込んでいく
    for ($i=0; $i < count($csv->data_header_array); $i++) {
        if(empty($csv->data_header_array[$i]) === false){
            for ($j=0; $j < count($csv->data_header_array[$i]); $j++) { 
                echo mb_convert_encoding($csv->data_header_array[$i][$j].",", 'SJIS');
            }
            echo mb_convert_encoding("\n", 'SJIS');
        }
    }

    //解析結果csvファイルのData部分を書き込んでいく
    for ($i=0; $i < count($resulet_array); $i++) { 
        for ($j=0; $j < count($resulet_array[$i]); $j++) { 
            echo mb_convert_encoding($resulet_array[$i][$j].",", 'SJIS');
        }
        echo mb_convert_encoding("\n", 'SJIS');
    }
}
?>
