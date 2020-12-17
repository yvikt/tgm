<?php

function delete_keyboard(){
  session_start();
  $method = 'sendMessage';
  $reply_markup = [ 'remove_keyboard' => true ];
  $params['chat_id'] = ME;
  $params['text'] = 'remove keyboard';
  $params['reply_markup'] = json_encode($reply_markup);

  file_put_contents('req_logs/site_out.log', print_r($params, 1));// log

  $response = sendRequest($method, $params);// отправка запроса
  file_put_contents('req_logs/site_in.log', print_r($response, 1));// log

  $_SESSION['info'] = 'клавиатура удалена';
  if(!strpos($_SERVER['HTTP_REFERER'],'delete_keyboard')) {
    header('Location:' . $_SERVER['HTTP_REFERER']);
  }
  else{
    header('Location: ' . SUB_PATH);
  }
}