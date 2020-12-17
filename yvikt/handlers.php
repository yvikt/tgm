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

function command_handler($text, &$session){
  global $commands;
  // $previous = $session[3];
  $session[3] = $session[4];
  switch ($text) {
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
      if(any_expert()) { // есть ли подключенные эксперты?
        // TODO сделать проверку свободен ли эксперт
        $session[4] = 12;
        global $chat_id;
        create_chat_room($chat_id);
        $session[5] = 1;  // быть "общительным" :-)
        return [
            'text' => 'соединяем с экспертом...',
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
      $session[4] = 11;
      return [ 'text' => 'уроки проходят в группах до 6 человек 2 раза в неделю' ];
    case $commands[112]:
      $session[4] = 11;
      return [ 'text' => 'стоимость индивидуальных занятий. стоимость групповых занятий' ];
    case $commands[113]:
      $session[4] = 11;
      return [ 'text' => 'здесь описание преимуществ онлай обучения в целом и возмодности нашей платформы' ];

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

    /// KEYBOARD 13 \\\
    case $commands[131]:
      $session[4] = 13;
      return [ 'text' => 'здесь перечисляются курсы для взрослых' ];
    case $commands[132]:
      $session[4] = 13;
      return [ 'text' => 'здесь перечисляются курсы для подростков' ];
    case $commands[133]:
      $session[4] = 13;
      return [ 'text' => 'здесь перечисляются курсы для детей' ];

    /// KEYBOARD 14 \\\
    case $commands[141]:
      $session[4] = 14;
      return [ 'text' => 'здесь будет тест для низкого уровня' ];
    case $commands[142]:
      $session[4] = 14;
      return [ 'text' => 'здесь будет тест для высокого уровня' ];

      /// SERVICE \\\
    case $commands[3]: // expert
      if (is_user($session)){
//        if ($previous == 12) { // нужно два раза ввести слово 'ee'
//        if ($session[4] == 12 && $previous == 1) { // 'ee' срабатывает при переходе с 1 на 12
        $session[2] = 1; // стать экспертом
        $session[5] = 1; // быть "общительным" :-)
        global $id;
        be_expert($id);
        return [
            'text' => 'вы стали ЭКСПЕРТОМ. теперь сообщения от пользователей будут перенапрвляться вам для того чтобы вы ответили на их вопросы',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(5)]
//              'reply_markup' => false
        ];
//        }
      }; break;

    case $commands[4]: // observer
      if (is_user($session)){
        $session[2] = 2; // стать наблюдателем
        global $id;
        be_observer($id);
        return [
            'text' => 'вы стали НАБЛЮДАТЕЛЕМ. теперь вы будуте получать уведомления обо всех действиях всех пользователей',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(5)]
//              'reply_markup' => false
        ];
      }; break;

    case $commands[5]:
      if (is_expert($session) || is_observer($session)) {
        global $id;
        del_observer($id);
        del_expert($id);
        $session[4] = 10;
        $session[2] = 0; // стать юзером
        $session[5] = 0; // перестать быть "общительным" :-(
        return [
            'text' => 'вы снова обычный юзер (',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
        ];
      }; break;

  }
}

function default_handler($text, &$session){
  if($session[5] == 0) {
    switch ($text) {
      case 'кто я':
        if (is_user($session)) {
          return [ 'text' => 'Вы пользователь бота' ];
        }
        if (is_expert($session)) {
          return [ 'text' => 'Вы эксперт' ];
        }
        if (is_observer($session)) {
          return [ 'text' => 'Вы наблюдатель' ];
        };
      case 'ты кто':
      case 'кто ты':
      case 'кто вы':
        return [ 'text' => 'я бот BOT_NAME' ];
      default:
        return [ 'text' => 'я вас не понял' ];
    }
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
  return $session[2] == 9;
}
