<?php

function quiz(){
  session_start();
  if($_POST) { // если пришел POST запрос
    $params = [];
    $params['chat_id'] = $_POST['chat_id'];
    $params['question'] = 'How many cats do you have?';
    $params['options'] = json_encode(['one', 'two', 'three']);
    $params['is_anonymous'] = false;
    $params['type'] = 'quiz';
    $params['correct_option_id'] = 2;
    $params['explanation'] = 'be attentive and count again ;-)';
    $method = 'sendPoll';

    file_put_contents('req_logs/site_out.log', print_r($params, 1));// log

    $response = sendRequest($method, $params);// отправка запроса
    file_put_contents('req_logs/site_in.log', print_r($response, 1));// log

    $_SESSION['last_message_id'] = $response['result']['message_id'];
    $_SESSION['info'] = 'quiz отправлен';

    header('Location:' . SUB_PATH . '/quiz');
    exit;
  }

  $template =
      '<div class="form-container">
        <form class="" method="POST" action="' . SUB_PATH . '/quiz">
          <input type="text" name="chat_id" placeholder="chat_id" value="' . ME . '">
          <input type="submit" value="send">
        </form>';
  $template .= $_SESSION['info'] ."\n";
  $template .= '</div>';
  echo $template;
  $_SESSION['info'] = ' ';
}