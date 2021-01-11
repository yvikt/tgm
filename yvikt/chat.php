<?php

$chat_dir = 'communication/chats/';
$archive_dir = 'communication/chat_archive/';
$expert_dir = 'communication/experts/';
$user_dir = 'communication/users/';
$que_dir = 'communication/que/';

function create_chat($chat_id, $expert_id){ // chat_id = пользователь (читай user_id), expert_id = эксперт
  global $chat_dir;
  // сначала создается файл для логирования чата
  $chat_file = $chat_dir . $chat_id;
  $f = fopen($chat_file, 'a+');
  fputs($f, "чат между пользователем $chat_id и экспертом $expert_id");
  fclose($f);
}

function connect($chat_id, $expert_id){ // соединение путем прописывания id-шников в файлы друг друга
  global $expert_dir;
  global $user_dir;
  //"соединяем" пользователя с экспертом
  $f = fopen("{$expert_dir}{$expert_id}", 'w');
  fputs($f, "$chat_id");
  fclose($f);
  //"соединяем" эксперта с пользователем
  $f = fopen("{$user_dir}{$chat_id}", 'w');
  fputs($f, "$expert_id");
  fclose($f);
}

function disconnect($chat_id, $expert_id){ // разъединение - удалить файл пользователя, записать 0 в файл эксперта
  global $expert_dir;
  global $user_dir;
  unlink("{$user_dir}{$chat_id}");
  $f = fopen("{$expert_dir}{$expert_id}", 'w');
  fputs($f, '0');
  fclose($f);
}
function get_my_user($chat_id){ // сообщает эксперту с каким юзером он общается
  global $expert_dir;
  return file_get_contents("{$expert_dir}{$chat_id}"); // считывает со своего файла id юзера
}
function get_my_expert($chat_id){ // сообщает пользователю с каким экспертом он общается
  global $user_dir;
  return file_get_contents("{$user_dir}{$chat_id}"); // считывает со своего файла id эксперта
}

function chat_log($chat_id, $text){
  global $chat_dir;
  $chat_file = $chat_dir . $chat_id;
  $f = fopen($chat_file, 'a+');
  fputs($f, $text);
  fclose($f);
}

// уже не нужна
function chat_archive($chat_id){
  global $chat_dir;
  global $archive_dir;
  $time = time();
  rename("./$chat_dir/$chat_id", "./$archive_dir/{$time}_{$chat_id}");
}

// очередь сообщений связанных с действиями пользователей, если нет доступных экспертов
function que_push($chat_id, $message)
{
  global $que_dir;
  $time = time();
  $f = fopen("{$que_dir}{$time}", 'a');
  fputs($f, "$chat_id\n$message");
  fclose($f);
}

// отправка накопившихся сообщений вновь подключившемуся ээксперту
function que_pop($chat_id, &$session){
  global $que_dir;
  $files = array_slice(scandir($que_dir), 2);
  foreach ($files as $file){
    $que_message = file_get_contents("{$que_dir}{$file}");
    $outgoing_data = [
        'chat_id' => $chat_id,
        'text' => $que_message,
        'parse_mode' => 'HTML',
        'reply_markup' => [ "inline_keyboard" => [[[ "text" => "начать общение", "callback_data" => "begin_chat"]]] ]
    ];
    $api_response = sendToTelegram($outgoing_data);
    file_put_contents('logs/outgoing_json.log', json_encode($outgoing_data) . "\n", FILE_APPEND);
    file_put_contents('logs/telegram_api_response_json.log', json_encode($api_response) . "\n", FILE_APPEND);
    session_update($session);

    unlink("{$que_dir}{$file}");
  }
}

function add_to_ban($user_id)
{
  $f = fopen('communication/ban_list', 'a');
  fputs($f, "$user_id\n");
  fclose($f);
}

function is_banned($user_id)
{
  $content = file_get_contents('communication/ban_list');
  $banned_users = explode("\n", $content);
  return in_array($user_id, $banned_users);
}
