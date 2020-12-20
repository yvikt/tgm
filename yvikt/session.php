<?php

# date:user_id:level:prev_kb:kb:state:message        :user_id or chat_id
# [0] date : when message was received
# [1] id : user id
# [2] role : user=0 expert=1 observer=2 admin=9
# [3] previous : previous session (keyboard template etc)
# [4] current : current(last) session (keyboard template etc)
# [5] state : 0/1 - is chatting now, 2,3 - quiz
# [6] text : received message
# [7] id : user id to communicate

function seekLastLine($f) {
  $pos = -2;
  do {
    fseek($f, $pos--, SEEK_END);
    $ch = fgetc($f);
  } while ($ch != "\n");
}


function session_check($chat_id){
  $user_dir = 'sessions/';
  $session_file = $user_dir . $chat_id;
  return file_exists($session_file);
}

function session_init($receive_data){
  $user_dir = 'sessions/';
  $date = $receive_data['message']['date'];
  $id = $receive_data['message']['from']['id'];// from-user
  $chat_id = $receive_data['message']['chat']['id'];// from-chat
  $first_name = $receive_data['message']['from']['first_name'];
  $last_name = $receive_data['message']['from']['last_name'];
  $username = $receive_data['message']['from']['username'];
  $role = 0;
  $keyboard = 1;
  $prev_keyboard = 0;
  $state = 0;
  $text = $receive_data['message']['text'];
  $id_2 = 0;
  $session_file = $user_dir . $chat_id; // сессионный файл именуем chat_id
  $f = fopen($session_file, 'a+');
  fputcsv($f, [$first_name, $last_name, $username, $id]);
  fputcsv($f, [$date, $id, $role, $prev_keyboard, $keyboard, $state, $text, $id_2]); // в сессиях сохраняем $id юзера
  fclose($f);
}

# перед обработкой входящего сообщения
function session_get($chat_id){
  $user_dir = 'sessions/';
  $session_file = $user_dir . $chat_id;
  $f = fopen($session_file, 'r');
  seekLastLine($f);
  $session = str_getcsv(fgets($f));
  fclose($f);
  return $session;
}

function session_update($session){
  $user_dir = 'sessions/';
  $chat_id = $session[1];
  $session_file = $user_dir . $chat_id;
  $f = fopen($session_file, 'a+');
  fputcsv($f, $session);
  fclose($f);
}