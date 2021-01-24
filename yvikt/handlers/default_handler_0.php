<?php
// Artificial Intelligence
// Искуственный Интеллект

function default_handler_0($text, &$session){
  global $bot_name;
  global $snippets;
  if(strpos($text, '/start ') == 0) { // deep linking - переход с внешней ссылки
    $command = explode(' ', $text)[1];
    if ($command == '001' || $command == '002') {
      return [
//        'text' => "команда: $command",
          'text' => $snippets['greeting'],
          'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
      ];
    }
  }
  switch ($text) {
    case 'кто я':
      if (is_user($session)) {
        return [ 'text' => 'Вы пользователь бота' ];
      }
      if (is_expert($session)) { // здесь это бессмысленно, так как эксперт всегда общительный $session[2] == 1
        return [ 'text' => 'Вы эксперт' ];
      }
      if (is_observer($session)) {
        return [ 'text' => 'Вы наблюдатель' ];
      }; break;
    case 'ты кто':
    case 'кто ты':
    case 'кто вы':
      return [ 'text' => "я бот $bot_name" ]; break;

    case 'здравствуйте':
    case 'привет':
      if($session[5] == 0) { // только в режиме общения человек <-> бот
        return ['text' => "Здравствуйте. Я бот $bot_name"];
      }; break;

    default:
      if($session[5] == 0 ) {
        return ['text' => '&#x274C я вас не понял'];
      }
  }
}