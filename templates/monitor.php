<?php

function site_in(){
  $buff = 'последний ответ(ы) ТЕЛЕГРАМ -> САЙТ';
  $buff .= '<pre>';
  $buff .= file_get_contents('req_logs/site_in.log');
  $buff .= '</pre>';
  echo $buff;
}

function site_out(){
  $buff = 'последний запрос(ы) САЙТ -> ТЕЛЕГРАМ';
  $buff .= '<pre>';
  $buff .= file_get_contents('req_logs/site_out.log');
  $buff .= '</pre>';
  echo $buff;
}

function bot_in(){
  $buff = 'входящий запрос ТЕЛЕГРАМ -> БОТ (то что пришло боту)';
  $buff .= '<pre>';
  $buff .= file_get_contents('yvikt/req_logs/bot_in.log');
  $buff .= '</pre>';
      echo $buff;
}

function bot_out(){
  $buff = 'исходящий запрос БОТ -> ТЕЛЕГРАМ (то что было отправлено ботом)';
  $buff .= '<pre>';
  $buff .= file_get_contents('yvikt/req_logs/bot_out.log');
  $buff .= '</pre>';
  echo $buff;
}