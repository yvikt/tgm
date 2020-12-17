<?php

$chat_dir = 'chat/';
$archive_dir = 'chat_archive/';

function create_chat_room($user_id){
  global $chat_dir;
  $chat_file = $chat_dir . $user_id;
  $f = fopen($chat_file, 'a+');
  fputs($f, $user_id);
  fclose($f);
}

function any_chat(){
  global $chat_dir;
  return scandir($chat_dir)[2];
}

function chat_log($chat_id, $text){
  global $chat_dir;
  $chat_file = $chat_dir . $chat_id;
    $f = fopen($chat_file, 'a+');
    fputs($f, $text);
    fclose($f);
}

function chat_archive($chat_id){
  global $chat_dir;
  global $archive_dir;
  $time = time();
  rename("./$chat_dir/$chat_id", "./$archive_dir/{$time}_{$chat_id}");
}