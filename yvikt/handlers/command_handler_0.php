<?php
// клавиатура передается один раз - переходы внутри одного уровня
// не требуют повторной передачи разметки.
// поэтому в секциях /// KEYBOARD 11, 12, 13 \\\ ответы состоят только из текста

function greeting($keyboard){
  return [
      'text'   => 'Здравствуйте! Я бот BOT_NAME ! Выберите пункт который вас интересует.',
      'reply_markup' => [ 'resize_keyboard' => true, 'keyboard' => $keyboard ]
  ];
}

function command_handler_0($text, $chat_id, &$session){
  global $commands;
  // $previous = $session[3];
  $session[3] = $session[4];
  switch ($text) {                 // !!! Номер команды это одно, номер клавиатуры это другое !!!
    case $commands[1]: // 'start'
    case $commands[2]: // '/'
      $session[4] = 10;// сброс сессии
      return [
          'text' => 'начнем с начала',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];

    case $commands[10]:
      $session[4] = 10;
      return [
          'text' => 'Главное меню',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];

      /// KEYBOARD 10 \\\
    case $commands[11]:
      $session[4] = 11;
      return [
          'text' => 'Наше обучение самое лучшее. Выберите интересующий вас вопрос',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(11)]
      ];

    case $commands[12]:
      if($expert_id = any_expert()) { // есть ли свободные эксперты?
        $session[4] = 12;
        $session[5] = 1;  // быть "общительным" :-)`1
        create_chat($chat_id, $expert_id);
        connect($chat_id, $expert_id);
        return [
            'text' => 'Вы подключены к эксперту.',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(12)]
        ];
      }
      else {
        $session[4] = 10;
        return [
            'text' => 'К сожалению, свободных экспертов нет. Пожалуйста попробуйте позже либо мы с вами свяжемся.',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
        ];
      }

    case $commands[13]:
      $session[4] = 13;
      return [
          'text' => 'выберите категории согласно возрасту',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(13)]
      ];
    case $commands[14]:
      $session[4] = 14;
      return [
          'text' => 'выберите уровень по которому желаете пройти тестирование',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(14)]
      ];

      /// KEYBOARD 11 \\\
    case $commands[111]:
      $session[4] = 111;
      return [ 'text' => 'уроки проходят в группах до 6 человек 2 раза в неделю' ];
    case $commands[112]:
      $session[4] = 112;
      return [ 'text' => 'стоимость индивидуальных занятий. стоимость групповых занятий' ];
    case $commands[113]:
      $session[4] = 113;
      return [ 'text' => 'здесь описание преимуществ онлай обучения в целом и возмодности нашей платформы' ];
/*
      /// KEYBOARD 12 \\\
    case $commands[121]:
      $session[4] = 10;
      $session[5] = 0; // перестать быть "общительным" :-(
      global $chat_id;
      chat_archive($chat_id);
      // TODO пользователь покинул чат
      return [
          'text' => 'Главное меню',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];
*/
    /// KEYBOARD 13 \\\
    case $commands[131]:
      $session[4] = 131;
      return [ 'text' => 'здесь перечисляются курсы для взрослых' ];
    case $commands[132]:
      $session[4] = 132;
      return [ 'text' => 'здесь перечисляются курсы для подростков' ];
    case $commands[133]:
      $session[4] = 133;
      return [ 'text' => 'здесь перечисляются курсы для детей' ];

    /// KEYBOARD 14 \\\  QUIZ
    case $commands[141]:
      $session[4] = 141; // ???
      $session[5] = 2; // quiz-1

      return [ 'text' => "Давайте начнем тест вашего уровня языка. Вам нужно будет ответить на 40 вопросов. Кнопка $commands[6] прерывает тест. В таком случае Вы можете пройти тест в другой раз.",
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(6)]
      ];
    case $commands[142]:
      $session[4] = 142; // ???
      $session[5] = 3; // quiz-2 ???
      return [ 'text' => "Давайте начнем тест вашего уровня языка. Вам нужно будет ответить на 40 вопросов. Кнопка $commands[6] прерывает тест. В таком случае Вы можете пройти тест в другой раз.",
      'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(6)]
      ];


      /*
    case $commands[6]:
      $session[4] = 6;
      $session[5] = 0; // конец режима quiz - снова обычный юзер
      return [
          'text' => 'Тест не защитан. Спасибо за вашу попытку. Вы можете попробовать в другой раз. Успехов! Оставьте свой контакт простым нажатием на кнопку.',
          'reply_markup' => ['resize_keyboard' => true,
             // 'one_time_keyboard' => true,
              'keyboard' => keyboard(7)]
      ];
      */
    // 'отказаться' (от отпраки контакта)
    case $commands[7]:
      $session[4] = 7;
      return [
          'text' => 'Главное меню. (оставить контакт отказались)',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];

    case $commands[9]: // ? не реагирует как на команду
      $session[4] = 7;
      return [
          'text' => 'Спасибо. С вами свяжется наш эксперт.',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];


      /// SERVICE \\\
    case $commands[3]: // be expert
      if (is_user($session)){
//        if ($previous == 12) { // нужно два раза ввести слово 'ee'
//        if ($session[4] == 12 && $previous == 1) { // 'ee' срабатывает при переходе с 1 на 12
        $session[4] = 3;
        $session[2] = 1; // стать экспертом
        $session[5] = 1; // быть "общительным" :-)
//        global $id;
        be_expert($chat_id); // TODO избавиться от global $id
        return [
            'text' => 'вы стали ЭКСПЕРТОМ. теперь сообщения от пользователей будут перенапрвляться вам для того чтобы вы ответили на их вопросы',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(5)]
//              'reply_markup' => false // отключить клавиатуру
        ];
//        }
      }; break;


    case $commands[4]: // observer
      if (is_user($session)){
        $session[2] = 2; // стать наблюдателем
        global $id;
        be_observer($id); // TODO избавиться от global $id
        return [
            'text' => 'вы стали НАБЛЮДАТЕЛЕМ. теперь вы будуте получать уведомления обо всех действиях всех пользователей',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(5)]
//              'reply_markup' => false
        ];
      }; break;


      // Удалить (временная заплатка)
//    default:
//      if($session[5] ==0 ) {
//        return ['text' => 'я вас не понял'];
//      }

  }
}


function is_user($session){
  return $session[2] == 0;
}
function is_expert($session){
  return $session[2] == 1;
}
function is_observer($session){
  return $session[2] == 2;
}
function is_admin($session){
  return $session[2] == 3;
}
