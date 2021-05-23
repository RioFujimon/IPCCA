<?php
echo '<!DOCTYPE html>';
echo '<html lang="ja">';
echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">';
    echo '<link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">';
    echo '<link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">';
    echo '<link rel="stylesheet" href="./style.css">';
    echo '<title>Csv Analysis Web Application</title>';
echo '</head>';
echo '<body>';
    echo '<div id="app">';
    echo '<v-app>';
    echo '<div class="wrapper">';
    echo '<header class="header_wrapper">';
    echo '<h3><strong>Csv Analysis Web Application</strong></h3>';
    echo '</header>';
    echo '<main class="main_wrapper">';
    session_start();
    if (isset($_SESSION['error_array'])) {

      if (strcmp($_SESSION['error_array'][0], 'file sizeが0byteです') == 0) {
        echo '<div class="err_msg">';
          print_r($_SESSION['error_array'][0]);
          echo "</br>";
        echo '</div>';
      }
      
      $count = 0;
      $start = 0;
      $end = 0;
      echo '<div class="err_msg">';
      for ($i=0; $i < count($_SESSION['error_array']); $i++) { 
          if ($_SESSION['error_array'][$i] === true) {
            $count++;
            if ($count == 1) {
              $start = $i+1;
            }
          }else {
            if (10 <= $count) {
              $end = $i;
              $count = 0;
              echo '<strong>'.$start.'行目から'.$end.'行目までがheadまたはdataではありません'.'</strong>';
              echo '</br>';
              $start = 0;
            }else if(1 <= $count && $count < 10) {
              for ($j=0; $j < $count; $j++) {
                $line = ($start+$j);
                echo '<strong>'.$line.'行目がheadまたはdataではありません'.'</strong>';
                echo '</br>';
              }
              $count = 0;
              $start = 0;
            }
          } 
      }
      echo '</div>';
    }

    if (isset($_SESSION['history_error_msg1'])) {
      echo '<div class="err_msg">';
      echo '<strong>'.$_SESSION['history_error_msg1'].'</strong>';
      echo "</br>";
      echo '</div>';
    }

    if (isset($_SESSION['history_error_msg2'])) {
      echo '<div class="err_msg">';
          echo '<strong>'.$_SESSION['history_error_msg2'].'</strong>';
          echo "</br>";
      echo '</div>';
    }

    if (isset($_SESSION['normalize_error_msg'])) {
      echo '<div class="err_msg">';
          echo '<strong>'.$_SESSION['normalize_error_msg'].'</strong>';
          echo "</br>";
      echo '</div>';
    }

    if (isset($_SESSION['normalize_error_msg2'])) {
      echo '<div class="err_msg">';
          echo '<strong>'.$_SESSION['normalize_error_msg2'].'</strong>';
          echo "</br>";
      echo '</div>';
    }

    if (isset($_SESSION['unmatch_msg_array'])) {
          echo '<div class="err_msg">';
      $count = 0;
      $start = 0;
      $end = 0;

      for ($i=0; $i < count($_SESSION['unmatch_msg_array']); $i++) { 
        if ($_SESSION['unmatch_msg_array'][$i][1] === true) {
          $count++;
          if ($count == 1) {
            $start = $i+1;
          }
        }else {
          if (10 <= $count) {
            $end = $i;
            $count = 0;
            echo '<strong>word index：'.$start.'から'.$end.'にエラーがあります</strong>';
            echo '</br>';
            echo '</br>';
            $start = 0;
          }else if(1 <= $count && $count < 10) {
            echo '<strong>word index：'.($_SESSION['unmatch_msg_array'][$i][0]+2).'にエラーがあります</strong>';
            echo '</br>';
            echo '<strong>エラー内容：</strong>';
            echo '<strong>「'.$_SESSION['unmatch_msg_array'][$i-1][2].'」</strong>'.' '.'<strong>「'.$_SESSION['unmatch_msg_array'][$i-1][3].'」</strong>';
            echo "</br>";
            echo "</br>";
            $count = 0;
            $start = 0;
          }
        } 
      }
          echo '</div>';
    }
    echo '<div class="a_wraper">';
    echo '<a href="./index.html">ホームへ戻るにはこちらをクリックしてください</a>';
    echo '</div>';
    $_SESSION = array();
    session_destroy();
    echo '</main>';
    echo '<footer>';
      echo '<strong>&copy;2020 Sakamoto Lab</strong>';
    echo '</footer>';
    echo '</div>';
    echo '</v-app>';
    echo '</div>';
      echo '<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>';
      echo '<script src="https://unpkg.com/vue-router/dist/vue-router.js"></script>';
      echo '<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>';
      echo '<script src="./index.js"></script>';
echo '</body>';
echo '</html>';
?>
