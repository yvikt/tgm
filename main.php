<?php
include 'secrets.php';
include 'templates/home.php';
include 'templates/telegram.php';
include 'templates/monitor.php';
include 'methods/send_message.php';
include 'methods/delete_message.php';
include 'methods/reply_keyboard.php';
include 'methods/inline_keyboard.php';
include 'methods/delete_keyboard.php';
include 'methods/quiz.php';

//$path = explode('?', $_SERVER['REQUEST_URI'])[0]; // если сайт находится в корне
// SUB_PATH объявлена в файле nav.php
$path = explode(SUB_PATH, (explode('?', $_SERVER['REQUEST_URI'])[0]))[1]; // если сайт находится в подпапке

switch ($path) {
  case '/':
    echo draw_home();
    break;
  // отправка заросов РУКАМИ -> ТЕЛЕГРАМ (ручная отправка)
  case '/send_message': // отправка обычного сообщения
    send_message();
    break;
  case '/delete_message': // удалить сообщения
    delete_message();
    break;
  case '/reply_keyboard': // основная клавиатура
    reply_keyboard();
    break;
  case '/inline_keyboard': // инлайн клавиатура
    inline_keyboard();
    break;
  case '/delete_keyboard': // скрыть клавиатуру
    delete_keyboard();
    break;
  case '/quiz': // quiz
    quiz();
    break;
    // логи
  case '/site_in': // последний ответ(ы) ТЕЛЕГРАМ -> САЙТ
    site_in();
    break;
  case '/site_out': // последний запрос(ы) САЙТ -> ТЕЛЕГРАМ
    site_out();
    break;
  case '/bot_in': // входящий запрос ТЕЛЕГРАМ -> БОТ (то что пришло боту)
    bot_in();
    break;
  case '/bot_out': // исходящий запрос БОТ -> ТЕЛЕГРАМ (то что было отправлено ботом)
    bot_out();
    break;
  case '/api_answer': // API ТЕЛЕГРАМ -> BOT (ответ API боту)
    api_answer();
    break;
  default:
    echo 'Not found';
}
