<?php

# date:user:expert:admin:keyboard:button:message:chatting
# [0] date : when message was received
# [1] id : user id
# [2] level : user=0 expert=1 observer=2 admin=9
# [3] previous : previous session (keyboard template etc)
# [4] current : current(last) session (keyboard template etc)
# [5] state : 0/1 (true/false) - is chatting now with expert/user
# [6] text : received message

function seekLastLine($f) {
  $pos = -2;
  do {
    fseek($f, $pos--, SEEK_END);
    $ch = fgetc($f);
  } while ($ch != "\n");
}


function session_check($chat_id){
  $user_dir = 'user/';
  $session_file = $user_dir . $chat_id;
  return file_exists($session_file);
}

function session_init($receive_data){
  $user_dir = 'user/';
  $date = $receive_data['message']['date'];
  $id = $receive_data['message']['from']['id'];// from-user
  $chat_id = $receive_data['message']['chat']['id'];// from-chat
  $first_name = $receive_data['message']['from']['first_name'];
  $last_name = $receive_data['message']['from']['last_name'];
  $username = $receive_data['message']['from']['username'];
  $user_level = 0;
  $keyboard = 1;
  $prev_keyboard = 0;
  $state = 0;
  $text = $receive_data['message']['text'];
  $session_file = $user_dir . $chat_id; // сессионный файл именуем chat_id
  $f = fopen($session_file, 'a+');
  fputcsv($f, [$first_name, $last_name, $username, $id]);
  fputcsv($f, [$date, $id, $user_level, $prev_keyboard, $keyboard, $state, $text]); // в сессиях сохраняем $id юзера
  fclose($f);
}

# перед обработкой входящего сообщения
function session_reed($chat_id){
  $user_dir = 'user/';
  $session_file = $user_dir . $chat_id;
  $f = fopen($session_file, 'r');
  seekLastLine($f);
  $session = str_getcsv(fgets($f));
  fclose($f);
  return $session;
}

function session_update($session){
  $user_dir = 'user/';
  $chat_id = $session[1];
  $session_file = $user_dir . $chat_id;
  $f = fopen($session_file, 'a+');
  fputcsv($f, $session);
  fclose($f);
}