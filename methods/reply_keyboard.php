<?php

// keyboard	=> Array of Array of KeyboardButton
$reply_keyboard_markup = [
    'resize_keyboard' => true,
//  'one_time_keyboard' => true,
    'keyboard' => [
        [
            ['text' => 'button-1'],
            ['text' => 'button-2'],
        ],
        [
            ['text' => 'button-3'],
            ['text' => 'button-4'],
        ]
    ]
];

function reply_keyboard(){
  session_start();
  global $reply_keyboard_markup;
  $method = 'sendMessage';
  $reply_markup = $reply_keyboard_markup;
  $params['chat_id'] = ME;
  $params['text'] = 'reply keyboard';
  $params['reply_markup'] = json_encode($reply_markup);

  file_put_contents('logs/site_out.log', print_r($params, 1));// log

  $response = sendRequest($method, $params);// отправка запроса
  file_put_contents('logs/site_in.log', print_r($response, 1));// log

  $_SESSION['info'] = 'основная клавиатура добавлена';
  if(!strpos($_SERVER['HTTP_REFERER'],'reply_keyboard')) {
    header('Location:' . $_SERVER['HTTP_REFERER']);
  }
  else{
    header('Location: ' . SUB_PATH);
  }
}