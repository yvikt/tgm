<?php


function greeting($keyboard){
  return [
      'text'   => 'Здравствуйте! Я бот BOT_NAME ! Выберите пункт который вас интересует.',
      'reply_markup' => [ 'resize_keyboard' => true, 'keyboard' => $keyboard ]
  ];
}

function handler_0($text, &$session){
  $session[3] = $session[4];
  $session[4] = 1;// сброс сессии
  return [
      'text' => 'начнем с начала',
      'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(1)]
  ];
}

function handler_1($text, &$session){
  $previous = $session[3];
  $session[3] = $session[4];
  switch ($text) {
    case 'про обучение':
      $session[4] = 11;
      return [
          'text' => 'Наше обучение самое лучшее. Выберите интересующий вас вопрос',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(11)]
      ];
    case 'задать вопрос':
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
        $session[4] = 1;
        return [
            'text' => 'К сожалению, свободных экспертов нет. Пожалуйста попробуйте позже либо мы с вами свяжемся.',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(1)]
        ];
      }
    case 'категории курсов':
      $session[4] = 13;
      return [
          'text' => 'выберите категории согласно возрасту',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(13)]
      ];
    case 'пройти тестирование':
      $session[4] = 14;
      return [
          'text' => 'выберите уровень по которому желаете пройти тестирование',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(14)]
      ];

//////////////////////////////// SERVICE \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    case 'ee': // expert
      $session[3] = $session[4];
      if (is_user($session)){
//        if ($previous == 12) { // нужно два раза ввести слово 'ee'
//        if ($session[4] == 12 && $previous == 1) { // 'ee' срабатывает при переходе с 1 на 12
          $session[2] = 1; // стать экспертом
          $session[5] = 1; // быть "общительным" :-)
          global $id;
          be_expert($id);
          return [
              'text' => 'вы стали ЭКСПЕРТОМ. теперь сообщения от пользователей будут перенапрвляться вам для того чтобы вы ответили на их вопросы',
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => [[['text' => 'qq']]]]
//              'reply_markup' => false
          ];
//        }
      }; break;

    case 'oo': // observer
      $session[3] = $session[4];
      if (is_user($session)){
//        if ($previous == 12) { // нужно два раза ввести слово 'ee'
//        if ($session[4] == 12 && $previous == 1) { // 'ee' срабатывает при переходе с 1 на 12
          $session[2] = 2; // стать наблюдателем
          global $id;
          be_observer($id);
          return [
              'text' => 'вы стали НАБЛЮДАТЕЛЕМ. теперь вы будуте получать уведомления обо всех действиях всех пользователей',
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => [[['text' => 'qq']]]]
//              'reply_markup' => false
          ];
//        }
      }; break;

    case 'qq':
      if (is_expert($session) || is_observer($session)) {
        global $id;
        del_observer($id);
        del_expert($id);
        $session[4] = 1;
        $session[2] = 0; // стать юзером
        $session[5] = 0; // перестать быть "общительным" :-(
        return [
            'text' => 'вы снова обычный юзер (',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(1)]
        ];
      }; break;

    //////////////////////////////// SERVICE \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
  }
}

function handler_11($text, &$session){
  // $previous = $session[3]; // для многоуровнего меню
  switch ($text) {
    case 'НАЗАД':
      $session[3] = $session[4];
      $session[4] = 1;
      //$session[4] = $previous;
      return [
          'text' => 'Главное меню',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(1)]
      ];
    case 'как проходят уроки':
      $session[3] = $session[4];
      $session[4] = 11;
      return [
          'text' => 'уроки проходят в группах до 6 человек 2 раза в неделю',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(11)]
      ];
    case 'стоимость':
      $session[3] = $session[4];
      $session[4] = 11;
      return [
          'text' => 'стоимость индивидуальных занятий. стоимость групповых занятий',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(11)]
      ];
    case 'преимущества':
      $session[3] = $session[4];
      $session[4] = 11;
      return [
          'text' => 'здесь описание преимуществ онлай обучения в целом и возмодности нашей платформы',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(11)]
      ];
  }
}

function handler_12($text, &$session){
//  $previous = $session[3];
  switch ($text) {

    case 'завершить общение':
      $session[3] = $session[4];
      $session[4] = 1;
      $session[5] = 0; // перестать быть "общительным" :-(
      global $chat_id;
      chat_archive($chat_id);
      // TODO пользователь покинул чат
      return [
          'text' => 'Главное меню',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(1)]
      ];

  }
}

function handler_13($text, &$session){
//  $previous = $session[3];
  switch ($text) {
    case 'НАЗАД':
      $session[3] = $session[4];
      $session[4] = 1;
//      $session[4] = $previous;
      return [
          'text' => 'Главное меню',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(1)]
      ];
    case 'взрослые':
      $session[3] = $session[4];
      $session[4] = 13;
      return [
          'text' => 'здесь перечисляются курсы для взрослых',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(13)]
      ];
    case 'подростки':
      $session[3] = $session[4];
      $session[4] = 13;
      return [
          'text' => 'здесь перечисляются курсы для подростков',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(13)]
      ];
    case 'дети':
      $session[3] = $session[4];
      $session[4] = 13;
      return [
          'text' => 'здесь перечисляются курсы для детей',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(13)]
      ];
  }
}

function handler_14($text, &$session){
//  $previous = $session[3];
  $session[3] = $session[4];
  switch ($text) {
    case 'НАЗАД':
      $session[3] = $session[4];
      $session[4] = 1;
//      $session[4] = $previous;
      return [
          'text' => 'Главное меню',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(1)]
      ];
    case 'низкий уровень':
      $session[3] = $session[4];
      $session[4] = 14;
      return [
          'text' => 'здесь будет тест для низкого уровня',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(14)]
      ];
    case 'высокий уровень':
      $session[3] = $session[4];
      $session[4] = 14;
      return [
          'text' => 'здесь будет тест для высокого уровня',
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(14)]
      ];
  }
}

function handler_default($text, &$session){
  if($session[5] == 0) {
    switch ($text) {
      case 'кто я':
        if (is_user($session)) {
          return [
              'text' => 'Вы пользователь бота',
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard($session[4])]
          ];
        }
        if (is_expert($session)) {
          return [
              'text' => 'Вы эксперт',
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard($session[4])]
          ];
        }
        if (is_observer($session)) {
          return [
              'text' => 'Вы наблюдатель',
              'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard($session[4])]
          ];
        };
        break;

      case 'ты кто':
      case 'кто ты':
      case 'кто вы':
        return [
            'text' => 'я бот BOT_NAME',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard($session[4])]
        ];
      default:
        return ['text' => 'я вас не понял',
            'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard($session[4])]];
    }
  }
//  else{
//    return ['text' => '',
//        'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard($session[4])]];
//  }
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
