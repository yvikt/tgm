<?php

// inline_keyboard	=> Array of Array of InlineKeyboardButton
$inline_keyboard_markup = [ 'inline_keyboard' => [[
    ['text' => 'button-1', 'url' => 'https://google.com'],
    ['text' => 'button-2', 'callback_data' => '1']
]]];

function inline_keyboard(){
  session_start();
  global $inline_keyboard_markup;
  $method = 'sendMessage';
  $reply_markup = $inline_keyboard_markup;
  $params['chat_id'] = ME;
  $params['text'] = 'inline keyboard';
  $params['reply_markup'] = json_encode($reply_markup);

  file_put_contents('logs/site_out.log', print_r($params, 1));// log

  $response = sendRequest($method, $params);// отправка запроса
  file_put_contents('logs/site_in.log', print_r($response, 1));// log

  $_SESSION['info'] = 'инлайн клавиатура добавлена';
  if(!strpos($_SERVER['HTTP_REFERER'],'inline_keyboard')) {
    header('Location:' . $_SERVER['HTTP_REFERER']);
  }
  else{
    header('Location: ' . SUB_PATH);
  }
}