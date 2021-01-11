<?php

function command_handler_1($text, $chat_id, &$session){
  global $commands;
  $session[3] = $session[4];
  switch ($text) {

    case $commands[121]:
      $session[4] = 10;
      $session[5] = 0; // перестать быть "общительным" :-(
//      global $chat_id;
//      chat_archive($chat_id);
      // TODO пользователь покинул чат
      return [
          'text' => 'Главное меню',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];

    case $commands[5]:
      if (is_expert($session)) { //  || is_observer($session)
        del_expert($chat_id);
        $session[4] = 10;
        $session[2] = 0; // стать юзером
        $session[5] = 0; // перестать быть "общительным" :-(
        return [
            'text' => 'вы снова обычный юзер (',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
        ];
      }; break;

      // временно

    case '__unban': break;

    default:
      if($session[2] == 1) {
        return ['text' => "ping $text"];
      }
  }
}