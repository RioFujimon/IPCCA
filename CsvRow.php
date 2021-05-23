<?php
class CsvRow {
    
    public $csv_data_row = array();
    public $csv_head_row = array();
    public $rby_array = array();
    public $data_header_array = array();
    public $student_num = 0;
    public $flag = false;
    public $isError = false;

    //コンストラクタ
    function __construct($row_data){
        $this->checkHeadOrData($row_data);
        $this->createHeadCsvRow($row_data);
        $this->createDataCsvRow($row_data);
        if (empty($this->csv_data_row) === false && $this->flag === true && isset($_POST['create'])) {
            $this->getStudentNum();
            $this->countRBY();
        }

        if (empty($this->csv_data_row) === false && $this->flag === true && isset($_POST['analysis'])) {
            $this->getStudentNum();
            $this->countRBYForAnalysis();
        }

        if (empty($this->csv_data_row) === false && $this->flag === false) {
            if ( (strcmp('user ID', $this->csv_data_row[1]) === 0) || (strcmp('user code', $this->csv_data_row[1]) === 0) ) {
                $this->getStudentNum();
                $this->transformIdAndCode($this->csv_data_row[1]);
            }else {
                $this->getStudentNum();
                $this->transformDataHeader();
            }
        }
    }

    /* ############################################################################################################################# */
    //以下はfunction()
    /* ############################################################################################################################# */

    //一行分のCSVデータを作成する
    function splitStr($row_data){                                               //一行分のデータをCsvクラスから受け取る
        $char = null;                                                           //１文字を格納するための変数
        $string = null;                                                         //カンマ区切りした単語を格納
        $start = 0;                                                             //文字を切り取るスタート位置を格納する変数
        $isDoubleQuaotation = false;                                            //ダブルクウォートがあるかを判定

        for ($i=0; $i < strlen($row_data); $i++) {                              //一行分のデータ(String型)の長さの分だけforループを回す
            $char = substr($row_data, $start, 1);                               //一行分のデータのstart位置から一文字分切り取る
            if( (strcmp($char, ',') != 0) && ($isDoubleQuaotation == false) ){  //１文字が「,」ではなく、ダブルクウォートが一度出現していない時
                $string .=  $char;                                              //単語に１文字連結
                if( strcmp($char, '"') == 0){                                   //ダブルクォートが出てきた時
                    $isDoubleQuaotation = true;                                 //$isDoubleQuaotationの値をtrueに変更(trueの時は出現している状態)
                }
            }else if($isDoubleQuaotation == true){                              //ダブルクォートが一度出現している時
                $string .= $char;                                               //単語に１文字連結
                if( strcmp($char, '"') == 0){                                   //ダブルクォートが出現した時
                    $isDoubleQuaotation = false;                                //$isDoubleQuaotationの値をfalseに変更(falseの時は出現していない時)
                }
            }else{
                array_push($this->csv_data_row, $string);                       //単語を配列に格納
                $string = null;                                                 //文字を初期化
            } 
                $start++;                                                       //文字の切り取り位置を次に進める
        }
    }

    //一行分のCSVデータを作成する
    function splitStrForHead($row_data){                                        //一行分のデータをCsvクラスから受け取る
        $char = null;                                                           //１文字を格納するための変数
        $string = null;                                                         //カンマ区切りした単語を格納
        $start = 0;                                                             //文字を切り取るスタート位置を格納する変数
        $isDoubleQuaotation = false;                                            //ダブルクウォートがあるかを判定

        $row_data = $this->replaceBlankAsCommaForHead($row_data);

        for ($i=0; $i < strlen($row_data); $i++) {                              //一行分のデータ(String型)の長さの分だけforループを回す
            $char = substr($row_data, $start, 1);                               //一行分のデータのstart位置から一文字分切り取る
            if( (strcmp($char, ',') != 0) && ($isDoubleQuaotation == false) ){  //１文字が「,」ではなく、ダブルクウォートが一度出現していない時
                $string .=  $char;                                              //単語に１文字連結
                if( strcmp($char, '"') == 0){                                   //ダブルクォートが出てきた時
                    $isDoubleQuaotation = true;                                 //$isDoubleQuaotationの値をtrueに変更(trueの時は出現している状態)
                }
            }else if($isDoubleQuaotation == true){                              //ダブルクォートが一度出現している時
                $string .= $char;                                               //単語に１文字連結
                if( strcmp($char, '"') == 0){                                   //ダブルクォートが出現した時
                    $isDoubleQuaotation = false;                                //$isDoubleQuaotationの値をfalseに変更(falseの時は出現していない時)
                }
            }else{
                array_push($this->csv_head_row, $string);                       //単語を配列に格納
                $string = null;                                                 //文字を初期化
            } 
                $start++;                                                       //文字の切り取り位置を次に進める
        }
    }

    //「head」部分のデータの配列を作るメソッド
    function createHeadCsvRow($row_data){
        $char = null;                                                           //一文字を格納する変数
        $char = substr($row_data, 0, 1);                                        //一行分のデータから先頭の一文字を切り取り、$charに格納
        if (strcmp($char, "h") == 0 && !isset($_POST['create'])) {              //先頭の一文字が「h」であった場合
            $this->splitStrForHead($row_data);
        }

        if (strcmp($char, "h") == 0 && isset($_POST['create'])) {
            $this->splitStrForHead($row_data);
        }
    }

    //「data」部分をの配列を作るメソッド
    function createDataCsvRow($row_data){
        $char = null;                                                           //一文字を格納する変数
        $char = substr($row_data, 0, 1);                                        //先頭から一文字を切り取る
        if (strcmp($char, "d") == 0) {                                          //先頭の位置文字が「d」だった場合
            $row_data_tmp = explode(",", $row_data);                            //一時変数「$row_data_tmp」に「,」で$row_dataを分割した配列を格納
            if (empty($row_data_tmp[1]) === true) {                             //$row_data_tmpの要素番号1が空だった場合
                $this->flag = true;
                 $row_data =  $this->replaceBlankAsComma($row_data);            //$row_dataをfunction replaceBlankAsComma()に渡してデータを整形
                 $this->splitStr($row_data);                                    //function splitStr()に渡し、data部分の配列を作成
            }else {                                                             //$row_data_tmpの要素番号1が空ではなかった場合
                $row_data =  $this->replaceBlankAsComma($row_data);
                $row_data_tmp = explode(",", $row_data);
                for ($i=0; $i < count($row_data_tmp); $i++) {                   //$row_data_tmpの要素数分forループを回す
                    array_push($this->csv_data_row, $row_data_tmp[$i]);         //メンバ変数「$csv_data_row」に$row_data_tmpの各要素を格納する
                }

            }
        }
    }

    //一行分のデータの最後が「,」で終了しているかどうかを判定するメソッド
    function replaceBlankAsComma($row_data){
        if (strcmp(substr($row_data, -2, 1), ",") != 0) {                       //文字列$row_dataの最後が「,」で終了していない場合
            $row_data = substr_replace($row_data, ",", -2, 1);                  //文字列$row_dataの最後を「,」に置換
        }
        return $row_data;                                                       //整形した$row_dataをreturnする
    }

    //一行分のデータの最後が「,」で終了しているかどうかを判定するメソッド
    function replaceBlankAsCommaForHead($row_data){
        if (strcmp(substr($row_data, -2, 1), ",") != 0) {                       //文字列$row_dataの最後が「,」で終了していない場合
            if (strcmp(substr($row_data, -2, 1), '"') == 0) {
                $row_data = substr_replace($row_data, ",", -1, 1); 
            }else {
                //$row_data = substr_replace($row_data, ",", -2, 1);                  //文字列$row_dataの最後を「,」に置換
                $row_data = $row_data.',';
            }
        }
        return $row_data;                                                       //整形した$row_dataをreturnする
    }

    //CSVファイルの一行がhead部分かを判定
    function isHead(){
        if((empty($this->csv_head_row) === false) && (strcmp($this->csv_head_row[0], "h") == 0) ){ //メンバ変数「$csv_head_row」が空ではなく、要素番号０が「h」の場合
            return true;                                                                           //falseを返す
        }else {                                                                                    //それ以外の場合
            return false;                                                                          //trueを返す
        }
    }

    //CSVファイルの一行がbody部分かを判定
    function isData(){
        if((empty($this->csv_data_row) === false) && (strcmp($this->csv_data_row[0], "d") == 0 ) ){ //メンバ変数「$csv_data_row」が空ではなく、要素番号０が「d」の場合
            return true;                                                                            //falseを返す
        }else {                                                                                     //それ以外の場合
            return false;                                                                           //trueを返す
        }
    }

    //Errorがあるかを判定するメソッド
    function checkHeadOrData($row_data){
        $char = substr($row_data, 0, 1);
        $char2 = substr($row_data, 1, 1);
        if ( ((strcmp($char, 'h') === 0) && (strcmp($char2, ',') === 0)) || ((strcmp($char, 'd') === 0) && (strcmp($char2, ',') === 0))) {
            $this->isError = false;
        }else{
            $row_data = str_replace(array("\r\n", "\r", "\n"), '', $row_data);
            $array = explode(",", $row_data);
            for ($i=0; $i < count($array); $i++) { 
                if (empty($array[$i]) === false) {
                    $this->isError = true;
                }
            }
        }
    }

    //学生数を取得するメソッド
    function getStudentNum(){
        $this->student_num =  ( count($this->csv_data_row) - 6 ) / 3;                               //生徒数を取得※メンバ変数$csv_data_rowの要素数「0」〜「5」は生徒のデータではないので引く、Red,Blue,Yellowの３で割る
    }

    //dataのuser ID, user code部分行を整形するメソッド
    function transformIdAndCode($text){
        array_push($this->data_header_array, "d");
        array_push($this->data_header_array, $text);
        array_push($this->data_header_array, "");
        array_push($this->data_header_array, "");
        array_push($this->data_header_array, "");
        array_push($this->data_header_array, "");
        array_push($this->data_header_array, "for analysis(real)");
        array_push($this->data_header_array, "for analysis(real)");
        array_push($this->data_header_array, "for analysis(real)");
    }

    //data header部分の行を整形するメソッド
    function transformDataHeader(){
        array_push($this->data_header_array, "d");
        array_push($this->data_header_array, "data header");
        array_push($this->data_header_array, "line index");
        array_push($this->data_header_array, "word index");
        array_push($this->data_header_array, "serial number");
        array_push($this->data_header_array, "word");
        array_push($this->data_header_array, "red");
        array_push($this->data_header_array, "blue");
        array_push($this->data_header_array, "yellow");
    }

    //一行分のマーカーの色を集計する
    function countRBY(){
        $count_red = 0;                                                                            //赤色のマーカーの色をカウントするための変数
        $count_blue = 0;                                                                           //青色マーカーの色をカウントするための変数
        $count_yellow = 0;                                                                         //黄色マーカーの色をカウントするための変数
        $red_index = 0;                                                                            //赤色マーカーの要素番号を格納するための変数
        $blue_index = 0;                                                                           //青色マーカーの色の要素番号を格納するための変数
        $yellow_index = 0;                                                                         //黄色マーカーの色の要素番号を格納するための変数

        for($i = 1; $i <= $this->student_num; $i++){
            $red_index = 6 + (3*($i - 1));                                                         //赤色マーカーの要素番号を次に進める
            $blue_index = 7 + 3*($i - 1);                                                          //青色マーカーの要素番号を次に進める
            $yellow_index = 8 + 3*($i - 1);                                                        //黄色マーカーの要素番号を次に進める
            $count_red += (double)$this->csv_data_row[$red_index];                                 //赤色マーカーの色を集計
            $count_blue += (double)$this->csv_data_row[$blue_index];                               //青色マーカーの色を集計
            $count_yellow += (double)$this->csv_data_row[$yellow_index];                           //黄色マーカーの色を集計
        }

        $this->rby_array = [$this->csv_data_row[0], $this->csv_data_row[1], $this->csv_data_row[2], $this->csv_data_row[3], $this->csv_data_row[4], $this->csv_data_row[5], $count_red, $count_blue, $count_yellow]; //配列に赤・青・黄の集計情報を格納
    }

    function countRBYForAnalysis(){
        $count_red = (double)$this->csv_data_row[6];
        $count_blue = (double)$this->csv_data_row[7];
        $count_yellow = (double)$this->csv_data_row[8];
        $this->rby_array = [$this->csv_data_row[0], $this->csv_data_row[1], $this->csv_data_row[2], $this->csv_data_row[3], $this->csv_data_row[4], $this->csv_data_row[5], $count_red, $count_blue, $count_yellow];
    }
}
?>
