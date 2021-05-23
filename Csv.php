<?php
require_once("./CsvRow.php");
class Csv{
    public $csv_row = null;               //CsvRowクラスのオブジェクトを格納するための変数
    public $isError = false;              //エラーが発生しているかどうかの判定に用いる変数
    public $isDoubleQuaot = false;        //ダブルクォートがあるかどうかを判定するための変数
    public $isHistory = false;            //historyタグがhead_arrayの中にあるか判定するための変数
    public $isContinue = false;
    public $linked_row_data = "";         //連結した一行分のデータを格納するための変数
    public $row_num = 0;
    public $head_array = array();         //「head」部分の一行分のCsvRowデータを保存する配列
    public $data_array = array();         //「data」部分の一行分のCsvRowデータを保存する配列
    public $count_array = array();        //集計したRed,Blue,Yellowのデータを格納するための変数
    public $data_header_array = array();  //「data」の「user ID」「user code」「data header」部分を格納する配列
    public $error_array = array();        //ファイルのエラー情報を格納する配列

    //コンストラクター
    function __construct($file_path){

        if (filesize($file_path) === 0) {
            $this->isError = true;
            array_push($this->error_array, 'file sizeが0byteです');
        }

        $fp = fopen($file_path, "r");                                                                  //fileを「read」でオープンする

        if($fp){
            while ( ($row_data = fgets($fp)) !== false ) {                                             //ファイルを一行読み込みし、$row_dataに格納する
                //mb_convert_variables("UTF-8", "SJIS", $row_data);                                      //ファイル読み込み時に「sjis-win」から「UTF-8」に文字コードを変換して文字化けを防止
                $row_data = mb_convert_encoding($row_data, 'UTF-8', 'sjis-win');
                $this->linkRowData($row_data);                                                         //function linkRowData()に一行分のデータを渡し、CsvRowクラスのオブジェクトをインスタンス化する
                
                if ( ($this->csv_row->isHead() === true) || ($this->csv_row->isData() === true ) ) {   //「head」または「data」部分である時

                    if ($this->csv_row->isError === false) {
                        array_push($this->error_array, null);
                    }

                    if($this->csv_row->isHead() === true) {                                            //一行分のデータが「head」部分である時
                        if ($this->isDoubleQuaot === false) {                                          //
                            array_push($this->head_array, $this->csv_row->csv_head_row);               //$head_arrayに一行分のデータを追加
                        }
                    }

                    if($this->csv_row->isData() === true) {                                            //一行分のデータが「data」部分である時
                        if ($this->isDoubleQuaot === false) {                                          //
                            array_push($this->data_array, $this->csv_row->csv_data_row);               //$data_arrayに一行分のデータを追加
                            array_push($this->count_array, $this->csv_row->rby_array);                 //$count_arrayに集計したRed,Blue,Yellowのデータを追加
                            array_push($this->data_header_array, $this->csv_row->data_header_array);   //$data_header_arrayに「user ID」「user code」「data header」のデータを格納する
                        }
                    }

                }else {                                                                                //「head」または「data」ではない時
                    if ($this->csv_row->isError === true && $this->isContinue === true) {
                        $this->isError = true;
                        array_push($this->error_array, true);
                    }else {
                        array_push($this->error_array, false);
                    }
                }

                $this->row_num++;
                $this->isContinue = false;
            }
           
            for ($i=0; $i < count($this->head_array); $i++) { 
                if ($this->head_array[$i][1] === 'history') {
                    $this->isHistory = true;
                }
            }

            if ($this->isHistory === false && isset($_POST['memo'])) {
                array_push($this->head_array, ["h", "history", $_POST['memo']]); 
            }

            if (!feof($fp)) {
                $this->isError = true;
                if ($this->isError === true) {
                    echo "Error: unexpected fgets() fail\n";
                }
            }

            fclose($fp);                                                                               //ファイルをクローズする
        }

        for ($i=0; $i < count($this->head_array); $i++) { 
            if ($this->head_array[$i][1] === 'memo') {
                array_splice($this->head_array, $i, 1);
            }
        }

        for ($i=0; $i < count($this->count_array); $i++) { 
            if (empty($this->count_array[$i]) === true) {
                array_shift($this->count_array);
                $i--;
            }
        }
    }

    function linkRowData($row_data){
        $start = 0;                                                                                   //文字を切り取るスタート位置を格納する変数
        $char = '';                                                                                   //一文字の分データを格納するための変数
        if ($this->isDoubleQuaot === false) {                                                         //$isDoubleQuaotの値がfalseの時
            for ($i=0; $i < strlen($row_data); $i++) {                                                //文字列の長さの分だけforループを回す
                $char = substr($row_data, $start, 1);                                                 //文字列を$start位置から一文字切り取る
                if (strcmp($char, '"') == 0) {                                                        //一文字が「"」の時
                    $this->isDoubleQuaot = !$this->isDoubleQuaot;                                     //$isDoubleQuaotのtrueとfalseを入れ替える
                }
                ++$start;                                                                             //文字の切り取り位置を一つ進める
            }

            if ($this->isDoubleQuaot === true) {                                                      //$isDoubleQuaotの値がtrueの時（通常はfalse）
                $this->linked_row_data = $this->linked_row_data.$row_data;                            //$linked_row_dataに$rowを連結する
            }else {                                                                                   //$isDoubleQuaotの値がfalseの時
                $this->csv_row = new CsvRow($row_data);                                               //CsvRowクラスのオブジェクトをインスタンス化する
                $this->isContinue = true;
            }
        }else {                                                                                       //$isDoubleQuaotの値がtrueの時
            for ($i=0; $i < strlen($row_data); $i++) {                                                //文字列の長さの分だけforループを回す
                $char = substr($row_data, $start, 1);                                                 //文字列を$start位置から一文字切り取る
                if (strcmp($char, '"') == 0) {                                                        //一文字が「"」の時
                    $this->isDoubleQuaot = !$this->isDoubleQuaot;                                     //$isDoubleQuaotのtrueとfalseを入れ替える
                }
                ++$start;                                                                             //文字の切り取り位置を一つ進める
            }

            $this->linked_row_data = $this->linked_row_data.$row_data;                                //$linked_row_dataに$rowを連結する

            if ($this->isDoubleQuaot === false) {                                                     //$isDoubleQuaotの値がfalseの時
                $this->csv_row = new CsvRow($this->linked_row_data);                                  //CsvRowクラスのオブジェクトをインスタンス化する
                $this->isContinue = true;
                $this->linked_row_data = "";                                                          //$linked_row_dataの値を初期化する
            }
        }
    }
}
?>
