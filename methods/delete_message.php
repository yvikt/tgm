<?php

// TODO сделать указатель на номер сообщения
// TODO и возможность удалять диапазон сообщений (например последние 10)

function delete_message(){
  session_start();
  if($_POST) { // если пришел POST запрос
    $params = [];
    $params['chat_id'] = $_POST['chat_id'];
    $params['message_id'] = htmlspecialchars($_POST['message_id']);
    $method = 'deleteMessage';

    file_put_contents('logs/site_out.log', print_r($params, 1));// log

    $response = sendRequest($method, $params);// отправка запроса
    file_put_contents('logs/site_in.log', print_r($response, 1));// log

    $_SESSION['info'] = 'сообщение удалено';

    header('Location:' . SUB_PATH . '/delete_message');
    exit;
  }

  $template =
      '<div class="form-container">
        <form class="" method="POST" action="' . SUB_PATH . '/delete_message">
          <input type="text" name="chat_id" placeholder="chat_id" value="' . ME . '">
          <input type="text" name="message_id" placeholder="message_id" value="' . $_SESSION['last_message_id'] . '">
          <input type="submit" value="send">
        </form>';
  $template .= $_SESSION['info'] ."\n";
  $template .= '</div>';
  echo $template;
  $_SESSION['info'] = ' ';
}