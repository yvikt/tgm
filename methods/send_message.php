<?php

function send_message(){
  session_start();
//  global $reply_markup;
  if($_POST) { // если пришел POST запрос
    $params = [];
    $params['chat_id'] = $_POST['chat_id'];
    $params['text'] = htmlspecialchars($_POST['text']);
    $method = 'sendMessage';

    file_put_contents('req_logs/site_out.log', print_r($params, 1));// log

    $response = sendRequest($method, $params);// отправка запроса
    file_put_contents('req_logs/site_in.log', print_r($response, 1));// log

    $_SESSION['last_message_id'] = $response['result']['message_id'];
    $_SESSION['info'] = 'запрос отправлен';

    header('Location:' . SUB_PATH . '/send_message');
    exit;
  }

  $time = rand(1000,9999);
  $template =
      '<div class="form-container">
        <form class="" method="POST" action="' . SUB_PATH . '/send_message">
          <input type="text" name="chat_id" placeholder="chat_id" value="' . ME . '">
          <textarea name="text" rows="5">' . $time . '</textarea>
          <input type="submit" value="send">
        </form>';
  $template .= $_SESSION['info'] ."\n";
  $template .= '</div>';
  echo $template;
  $_SESSION['info'] = ' ';
}