<?php

const SUB_PATH = '/tgm';

$request_path = explode(SUB_PATH, $_SERVER['REQUEST_URI'])[1];

$items = [
    '/' => 'Главная',
    '/send_message' => 'send message',
    '/delete_message' => 'delete message',
    '/reply_keyboard' => 'reply keyboard',
    '/inline_keyboard' => 'inline keyboard',
    '/delete_keyboard' => 'delete keyboard',
    '/quiz' => 'quiz',

    '/site_out' => 'сайт-ушло',
    '/site_in' => 'сайт-пришло',

    '/bot_in' => 'бот-пришло',
    '/bot_out' => 'бот-ушло',
    '/api_answer' => 'ответ API',
];

$nav = '<ul>';
foreach ($items as $path => $name){
  $li = '<li>';
  $active = '';
  if($path == $request_path) $active = 'active';
  else $active = '';
  $li .= '<a class="' . $active . '" href="' . SUB_PATH . $path . '">' . $name . '</a></li>';
  $nav .= $li;
  // <li class="right">
}
$nav .= '</ul>';

echo $nav;