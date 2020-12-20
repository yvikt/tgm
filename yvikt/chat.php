<?php

$chat_dir = 'communications/chats/';
$archive_dir = 'communications/chat_archive/';
$expert_dir = 'communication/experts/';
$user_dir = 'communication/users/';

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