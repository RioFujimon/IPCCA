<?php
include('./Csv.php');
$is_post = false;

if (isset($_POST['process'])) {
    $csv1 = new Csv($_FILES['upload_csv']['tmp_name'][0]);        //ファイルパスをCsvクラスのコンストラクタに渡す
    $csv2 = new Csv($_FILES['upload_csv']['tmp_name'][1]);        //ファイルパスをCsvクラスのコンストラクタに渡す

    // echo $csv1->head_array[5][2];
    // echo $csv2->head_array[5][2];
    $replaced_word1 = removeUnnecessaryFromWord($csv1);           //英単語から「”」「 」「\n」を取り除く
    $replaced_word2 = removeUnnecessaryFromWord($csv2);           //英単語から「”」「 」「\n」を取り除く
    $is_unmatch = matchContent($replaced_word1, $replaced_word2, $csv1, $csv2); //２つの英文の内容が一致しているかを判定する
    echo $is_unmatch;
    $is_history_error = checkHistoryError($csv1, $csv2);

    //$isMatchHistoryAndTwoFile = matchHistoryAndTwoFile($csv1, $csv2);
    $is_normalized_csv = null;
    $is_normalized_csv = checkNormarizedCsv($csv1->head_array[11][2], $csv2->head_array[11][2]);

    //エラーページ移動する
    //is_history_error：historyが存在しているか
    //is_unmatch：英文の内容が一致しているか
    //1.csvファイルのheaderデータ内のhistory に「(N)」が入っているか
    //2.ノーマライズが指定されているか
    //上記の2点の条件が一致しているかを判定するメソッドの返り値
    //moveErrorPage($is_history_error, $is_unmatch, $is_normalized_csv);

    //1.csvファイルどうしの英文の内容が一致しているか
    //2.csvファイル1のheaderにHistoryが存在しているか
    //3.csvファイル2のheaderにHistoryが存在しているか
    //上記の3つが成立しているかを判定
    if ($is_unmatch === false && $csv1->isHistory === true && $csv2->isHistory === true) {
        //POST['process']の値が「'add'」（結合）の時
        if ($_POST['process'] === 'add') {
            //headerの「history」部分を構成する処理
            for ($i=0; $i < count($csv1->head_array); $i++) { 
                for ($j=0; $j < count($csv1->head_array[$i]); $j++) { 
                    if (strcmp($csv1->head_array[$i][$j], 'history') === 0) {
                        //csvファイル1とcsvファイル2のheader部分のhistoryから100人当量されていないかを判定する
                        //100人当量されていない場合は処理を続行する
                        //$is_normalized_csvの値が「null」ではなく、「false」の時
                        if (is_null($is_normalized_csv) === false && $is_normalized_csv === 1) {
                            //historyを作成する
                            $history = '('.$csv1->head_array[$i][$j+1].' + '.$csv2->head_array[$i][$j+1].')';
                            $_SESSION['history'] = serialize($history);   
                        }else{
                            $_SESSION['normalize_error_msg2'] = "CSVファイルどうしを「結合」する場合は、100人当量されていないCSVファイルを選択してください";
                        }
                    }
                }
            }

            for ($i=0; $i < count($csv1->head_array); $i++) { 
                if ( (strcmp($csv1->head_array[$i][1], "material ID") !== 0) && (strcmp($csv1->head_array[$i][1], "material name") !== 0) && (strcmp($csv1->head_array[$i][1], "history") !== 0)) {
                    $csv1->head_array[$i][2] = "for analysis";
                }   
            }
            for ($i=0; $i < count($csv2->head_array); $i++) { 
                if ( (strcmp($csv2->head_array[$i][1], "material ID") !== 0) && (strcmp($csv2->head_array[$i][1], "material name") !== 0) && (strcmp($csv2->head_array[$i][1], "history") !== 0)) {
                    $csv2->head_array[$i][2] = "for analysis";
                }   
            }
        
            //「結合」処理を行う
            $result_array = addCsv($csv1, $csv2);
        }

        //POST['process']の値が「'substract'」（差分）の時
        if ($_POST['process'] === 'substract') {
            if (is_null($is_normalized_csv) === false && $is_normalized_csv === 0) {
                echo $is_normalized_csv;
                $_SESSION['normalize_error_msg2'] = "選択された2つのCSVファイルは既に100人当量されたものです";
                moveErrorPage($is_history_error, $is_unmatch, -1);
            }
            //headerの「history」部分を構成する処理
            for ($i=0; $i < count($csv1->head_array); $i++) {
                for ($j=0; $j < count($csv1->head_array[$i]); $j++) { 
                    //csvファイルのheader内にhistoryが存在している時
                    if (strcmp($csv1->head_array[$i][$j], 'history') === 0) {
                        //historyを作成
                        $history = '('.$csv1->head_array[$i][$j+1].' - '.$csv2->head_array[$i][$j+1].')(N)';
                        $_SESSION['history'] = serialize($history);
                    }
                }
            }

            //100人当量を行う処理
            $csv1 = normalizeCsv($csv1);
            $csv2 = normalizeCsv($csv2);
            //「差分」を行う処理
            $result_array = substractCsv($csv1, $csv2);
        }

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
    
        //SESSION変数に「結果」「ファイル名」「csvファイル」をセットする
        $_SESSION['result'] = serialize($result_array);
        $_SESSION['name'] = serialize($file_name);
        $_SESSION['csv1'] = serialize($csv1);

        //ダウロード直前の確認画面の処理
        echo '<div class="left-space">';
        for ($i=0; $i < count($csv1->head_array); $i++) { 
            for ($j=0; $j < count($csv1->head_array[$i]); $j++) { 
                if (strcmp($csv1->head_array[$i][$j], 'material ID') === 0) {
                    echo '<p class="text"><strong>material ID：'.$csv1->head_array[$i][$j+1].'</strong></p>';
                }
    
                if (strcmp($csv1->head_array[$i][$j], 'material name') === 0) {
                    echo '<p class="text"><strong>material name：'.$csv1->head_array[$i][$j+1].'</strong></p>';
                }
            }
        }
        echo '<p class="text"><strong>ダウンロードファイル名：'.$file_name.'</strong></p>';
        echo '<p class="text"><strong>解析内容：'.$history.'</strong></p>';
        echo '<p class="text"><strong>ファイルの解析が正常に終了しました</strong></p>';
        echo '<p class="text"><strong>解析結果をダウンロードする場合は、「ファイルをダウンロード」を押してください</strong></p>';
        echo '</div>';
        echo '<form action="./download.php" method="POST" enctype="multipart/form-data">';
        echo '<div class="btn_wraper">';
        echo '<button type="submit" class="v-btn v-btn--contained theme--light v-size--default yellow" name="download" value="ファイルをダウンロード" style="background-color: #FFEB3B;">';
        echo '<span class="v-btn__content">';
        echo '<strong>ファイルをダウンロード</strong>';
        echo '</span>';
        echo '</button>';
        echo '</div>';
        echo '</form>';
        echo '<div class="a_wraper">';
        echo '<a href="./sessionDestroy.php">ホームへ戻るにはこちらをクリックしてください</a>';
        echo '</div>';
    }
}else {
    $is_post = true;
}

if ($is_post === true) {
    echo '<div class="err_msg">';
    echo '<strong>ページがリロードされた可能性があります。</strong>';
    echo '<strong>このシステムはリーロードに対応していません。</strong>';
    echo '</div>';
}

/* ######################################################################################################################################################################################### */
/*以下からは各部分で使うfunction()が定義されている */
/* ######################################################################################################################################################################################### */

//解析用csvファイルが100人当量されたものかどうかを判定するメソッド
//2つのcsvファイルが100人当量されている場合は返り値「true」を返す
//2つのcsvファイルの内1つでも100人当量されていない場合は「false」を返す
function checkNormarizedCsv($csv1_history_value, $csv2_history_value){
    //csvファイルのheaderのhistory部分の文字列から「後ろ三文字」を抽出
    $csv1_normalized_mark = substr($csv1_history_value, -3);
    $csv2_normalized_mark = substr($csv2_history_value, -3);

    if ($csv1_normalized_mark === '(N)' && $csv2_normalized_mark === '(N)') {
        return 0;
    }
    else if ($csv1_normalized_mark !== '(N)' && $csv2_normalized_mark !== '(N)') {
        return 1;
    }
    else{
        return -1;
    }
}

function removeUnnecessaryFromWord($csv){
    $word_array = array();
    $target = array('"', ' ', '\r\n', '\r', '\n');
    for ($i=0; $i < count($csv->count_array); $i++) {
        if (empty($csv->count_array[$i]) === false) {
            $start_char = substr($csv->count_array[$i][5], 0, 1);
            $end_char = substr($csv->count_array[$i][5], -1, 1);
            if ( (strcmp($start_char, '"') == 0)  && (strcmp($end_char, '"') == 0) ) {
                array_push($word_array, str_replace($target, '', $csv->count_array[$i][5]));
            }else {
                array_push($word_array, str_replace($target, '', $csv->count_array[$i][5]));
            }
        }else {
            array_push($word_array, null);
        }
    }
    return $word_array;
}

//2つのcsvファイルの英文の内容が一致しているか判定するメソッド
function matchContent($replaced_word1, $replaced_word2, $csv1, $csv2){
    $is_unmatch = false;
    $unmatch_array = array();

    for ($i=0; $i < count($replaced_word1); $i++) { 
        $replaced_sjis_word1 = mb_convert_encoding($replaced_word1[$i], 'SJIS');
        $replaced_sjis_word2 = mb_convert_encoding($replaced_word2[$i], 'SJIS');

        $error_msg_array = array();
        if ( (md5($replaced_sjis_word1) !== md5($replaced_sjis_word2)) && ($csv1->head_array[5][2] !== $csv2->head_array[5][2]) ) {
            array_push($error_msg_array, $i);
            array_push($error_msg_array, true);
            array_push($error_msg_array, $replaced_word1[$i]);
            array_push($error_msg_array, $replaced_word2[$i]);
            array_push($unmatch_array, $error_msg_array);
            $is_unmatch = true;
        }else {
            array_push($error_msg_array, $i);
            array_push($error_msg_array, false);
            array_push($error_msg_array, null);
            array_push($error_msg_array, null);
            array_push($unmatch_array, $error_msg_array);
        }
    }

    if ($is_unmatch === true) {
        $_SESSION['unmatch_msg_array'] = $unmatch_array;
    }

    return $is_unmatch;
}

//ファイル中に「history」タグが存在しない時にエラーメッセージを表示するメソッド
function checkHistoryError($csv1, $csv2){
    if ($csv1->isHistory === false || $csv2->isHistory === false) {             //csvファイルの中に「history」タグが存在しない場合

        $_SESSION['history_error_msg1'] = '';
        $_SESSION['history_error_msg2'] = '';
        $_SESSION['normalize_error_msg'] = '';


        if($csv1->isHistory === false) {                                        //csv1中に「history」タグが存在しない場合
            $_SESSION['history_error_msg1'] = $_FILES['upload_csv']['name'][0].'中に「history」タグが存在しません';
        }

        if ($csv2->isHistory === false) {                                       //csv2中に「history」タグが存在しない場合
            $_SESSION['history_error_msg2'] = $_FILES['upload_csv']['name'][1].'中に「history」タグが存在しません';
        }

        return false;
    }

    if ($csv1->isHistory === true && $csv2->isHistory === true) {
        for ($i=0; $i < count($csv1->head_array); $i++) { 
            if ( ($csv1->head_array[$i][1] === 'history') && ($csv2->head_array[$i][1] === 'history') ) {
                if (strcmp(substr($csv1->head_array[$i][2], -3), '(N)')!=0 && strcmp(substr($csv2->head_array[$i][2], -3), '(N)')==0) {
                    $_SESSION['normalize_error_msg'] = '100人当量されたファイルと100人当量されていないファイルが混在しています';
                    return false;
                }
    
                if (strcmp(substr($csv1->head_array[$i][2], -3), '(N)')==0 && strcmp(substr($csv2->head_array[$i][2], -3), '(N)')!=0) {
                    $_SESSION['normalize_error_msg'] = '100人当量されたファイルち100人当量されていないファイルが混在しています';
                    return false;
                }
            }
        }
    }

    return true;
}

//1.csvファイルのheaderデータ内のhistory に「(N)」が入っているか
//2.ノーマライズが指定されているか
//上記の2点の条件が一致しているかを判定するメソッド
//引数：csvファイル1, csvファイル2
//返り値：エラーがない時「true」、エラーがある時「false」

//エラーページに移動するメソッド
function moveErrorPage($is_history_error, $is_unmatch, $is_normalized_csv){
    if ($is_history_error === false || $is_unmatch === true || $is_normalized_csv == -1) {
        //エラーページに移動
        header('Location: ./showError.php');
        //処理を全て終了する
        exit();
    }
}

//数値データの「結合処理」を行うメソッド
function addCsv($csv1, $csv2){
    $add_red = 0;
    $add_blue = 0;
    $add_yellow = 0;
    // $normalize_red = 0;
    // $normalize_blue = 0;
    // $normalize_yellow = 0;
    $resulet_array = array();
    for ($i=0; $i < count($csv1->count_array); $i++) {
        if (empty($csv1->count_array[$i]) === false) {
            //ノーマライズが指定されず、２つのファイルがノーマライズされていない場合
            if (!isset($_POST['normalize']) && strcmp(substr($csv1->head_array[11][2], -3), '(N)')!= 0 && strcmp(substr($csv2->head_array[11][2], -3), '(N)')!= 0) {
                $add_red = (double)$csv1->count_array[$i][6] + (double)$csv2->count_array[$i][6];
                $add_blue = (double)$csv1->count_array[$i][7] + (double)$csv2->count_array[$i][7];
                $add_yellow = (double)$csv1->count_array[$i][8] + (double)$csv2->count_array[$i][8];
                array_push($resulet_array,  array($csv1->count_array[$i][0], $csv1->count_array[$i][1], $csv1->count_array[$i][2], $csv1->count_array[$i][3], $csv1->count_array[$i][4], $csv1->count_array[$i][5], $add_red, $add_blue, $add_yellow));
            }
        }
    }
    return $resulet_array;
}

//数値データの「差分処理」を行うメソッド
function substractCsv($csv1, $csv2){
    $normalize_red = 0;
    $normalize_blue = 0;
    $normalize_yellow = 0;
    $resulet_array = array();
    for ($i=0; $i < count($csv1->count_array); $i++) {
        if (empty($csv1->count_array[$i]) === false) {
            $normalize_red = ((double)$csv1->count_array[$i][6] - (double)$csv2->count_array[$i][6]);
            $normalize_blue = ((double)$csv1->count_array[$i][7] - (double)$csv2->count_array[$i][7]);
            $normalize_yellow = ((double)$csv1->count_array[$i][8] - (double)$csv2->count_array[$i][8]);
            array_push($resulet_array, array($csv1->count_array[$i][0], $csv1->count_array[$i][1], $csv1->count_array[$i][2], $csv1->count_array[$i][3], $csv1->count_array[$i][4], $csv1->count_array[$i][5], $normalize_red, $normalize_blue, $normalize_yellow));
        }
    }

    return $resulet_array;
}

//100人当量を行うメソッド
function normalizeCsv($csv){
    $normalize_red = 0;
    $normalize_blue = 0;
    $normalize_yellow = 0;
    $sutudent_num = doubleval($csv->head_array[8][2]);

    for ($i=0; $i < count($csv->head_array); $i++) { 
        if ( (strcmp($csv->head_array[$i][1], "material ID") !== 0) && (strcmp($csv->head_array[$i][1], "material name") !== 0) && (strcmp($csv->head_array[$i][1], "history") !== 0)) {
            $csv->head_array[$i][2] = "for analysis";
        }   
    }

    for ($i=0; $i < 2; $i++) { 
        $csv->data_header_array[$i][6] = "for analysis( (ハイライティング集計数*100)/人数 )";
        $csv->data_header_array[$i][7] = "for analysis( (ハイライティング集計数*100)/人数 )";
        $csv->data_header_array[$i][8] = "for analysis( (ハイライティング集計数*100)/人数 )";
    }

    for ($i=0; $i < count($csv->count_array); $i++) { 
        $normalize_red = ($csv->count_array[$i][6] * 100) / $sutudent_num;
        $normalize_blue = ($csv->count_array[$i][7] * 100) / $sutudent_num;
        $normalize_yellow = ($csv->count_array[$i][8] * 100) / $sutudent_num;
        $csv->count_array[$i][6] = $normalize_red;
        $csv->count_array[$i][7] = $normalize_blue;
        $csv->count_array[$i][8] = $normalize_yellow;
    }

    return $csv;
}
?>
