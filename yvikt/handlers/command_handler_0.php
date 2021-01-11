<?php
// клавиатура передается один раз - переходы внутри одного уровня
// не требуют повторной передачи разметки.
// поэтому в секциях /// KEYBOARD 11, 12, 13 \\\ ответы состоят только из текста


function greeting($keyboard){
  global $snippets;
  return [
      'text'   => $snippets['greeting'],
      'reply_markup' => [ 'resize_keyboard' => true, 'keyboard' => $keyboard ]
  ];
}

function command_handler_0($text, $chat_id, &$session){
  global $commands;
  global $snippets;
  // $previous = $session[3];
  $session[3] = $session[4];
  switch ($text) {                 // !!! Номер команды это одно, номер клавиатуры это другое !!!
    case $commands[1]: // '/start'
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
          'text' => $snippets['про обучение'],
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(11)]
      ];

    case $commands[12]:
      if($expert_id = any_free_expert()) { // есть ли свободные эксперты?
        $session[4] = 12;
        $session[5] = 1;  // быть "общительным" :-)`1
        create_chat($chat_id, $expert_id);
        connect($chat_id, $expert_id);
        return [
            'text' => 'Вы подключены к эксперту и можете задать вопрос либо прекратить общение.',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(12)]
        ];
      }
      else {
        $session[4] = 10;
        que_push($chat_id, "хотел пообщаться");
        return [
            'text' => 'К сожалению, свободных экспертов нет. Пожалуйста попробуйте позже либо мы с вами свяжемся как только сможем.',
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
      return [ 'text' => $snippets['как проходят уроки'] ];
    case $commands[112]:
      $session[4] = 112;
      return [ 'text' => $snippets['стоимость'] ];
    case $commands[113]:
      $session[4] = 113;
      return [ 'text' => $snippets['преимущества'] ];
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
      return [ 'text' => $snippets['взрослые'] ];
    case $commands[132]:
      $session[4] = 132;
      return [ 'text' => $snippets['подростки'] ];
    case $commands[133]:
      $session[4] = 133;
      return [ 'text' => $snippets['дети'] ];
    case $commands[134]:
      $session[4] = 134;
      return [ 'text' => $snippets['обучение преподавателей'] ];
    case $commands[135]:
      $session[4] = 135;
      return [ 'text' => $snippets['подготовка экзаменам'] ];

    /// KEYBOARD 14 \\\  QUIZ
    case $commands[141]:
      $session[4] = 141;
      $session[5] = 2;

      return [ 'text' => "Давайте начнем тест вашего уровня языка. Вам нужно будет ответить на 40 вопросов. Кнопка $commands[906] прерывает тест. В таком случае Вы можете пройти тест в другой раз.",
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(6)]
      ];
    case $commands[142]:
      $session[4] = 142;
      $session[5] = 3;
      return [ 'text' => "Давайте начнем тест вашего уровня языка. Вам нужно будет ответить на 40 вопросов. Кнопка $commands[906] прерывает тест. В таком случае Вы можете пройти тест в другой раз.",
      'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(6)]
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
        que_pop($chat_id, $session); // отправка всех накопившися сообщений
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
