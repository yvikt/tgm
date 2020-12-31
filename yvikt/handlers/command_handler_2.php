<?php

function command_handler_2($text, &$session){
  global $commands;
  $session[3] = $session[4];

  // обработка команд
  switch ($text) {

    // 'начать'
    case $commands[6]:
      $session[4] = 6;
      create_quiz($session);// создать quiz по user_id
      return [
          'text' => 'Начали',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(7)]
      ];

    // 'отказаться' (от прохождения теста)
    case $commands[7]:
      $session[4] = 7;
      $session[5] = 0; // конец режима quiz - снова обычный юзер
      return [
          'text' => 'Вы можете пройти тест в любое удобное для вас время.',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];

    // 'прекратить'
    case $commands[8]:
      $session[4] = 8;
      $session[5] = 0; // конец режима quiz - снова обычный юзер
      archivate($session[1]);//TODO сообщить о попытке и отправить отчет
      return [
          'text' => 'Тест не пройден до конца. Успехов в последующих попытках',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];


  }
}
