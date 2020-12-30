<?php

function seekLastLine($f) {
  $pos = -2;
  do {
    fseek($f, $pos--, SEEK_END);
    $ch = fgetc($f);
  } while ($ch != "\n");
}

function site_in(){
  $buff = 'последний ответ(ы) ТЕЛЕГРАМ -> САЙТ';
  $buff .= '<pre>';
  $buff .= file_get_contents('logs/site_in.log');
  $buff .= '</pre>';
  echo $buff;
}

function site_out(){
  $buff = 'последний запрос(ы) САЙТ -> ТЕЛЕГРАМ';
  $buff .= '<pre>';
  $buff .= file_get_contents('logs/site_out.log');
  $buff .= '</pre>';
  echo $buff;
}

function bot_in(){
  $f = fopen('yvikt/logs/incoming_json_string.log', 'r');
  seekLastLine($f);
  $log = fgets($f);
  fclose($f);
  $buff = 'входящий запрос ТЕЛЕГРАМ -> БОТ (то что пришло боту)';
  $buff .= '<pre>';
  $buff .= print_r(json_decode($log, true), 1);
//  $buff .= print_r($log, 1);
  $buff .= '</pre>';
      echo $buff;
}

function bot_out(){
  $f = fopen('yvikt/logs/outgoing_json.log', 'r');
  seekLastLine($f);
  $log = fgets($f);
  fclose($f);
  $buff = 'исходящий запрос БОТ -> ТЕЛЕГРАМ (то что было отправлено ботом)';
  $buff .= '<pre>';
  $buff .= print_r(json_decode($log, true), 1);
  $buff .= '</pre>';
  echo $buff;
}

function api_answer(){
  $f = fopen('yvikt/logs/telegram_api_response_json.log', 'r');
  seekLastLine($f);
  $log = fgets($f);
  fclose($f);
  $buff = 'API ТЕЛЕГРАМ -> BOT (ответ API боту)';
  $buff .= '<pre>';
  $buff .= print_r(json_decode($log, true), 1);
  $buff .= '</pre>';
  echo $buff;
}