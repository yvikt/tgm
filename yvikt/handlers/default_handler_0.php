<?php
// Artificial Intelligence
// Искуственный Интеллект

function default_handler_0($text, &$session){

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
      return [ 'text' => 'я бот BOT_NAME' ]; break;

    case 'здравствуйте':
    case 'привет':
      if($session[5] == 0) { // только в режиме общения человек <-> бот
        return ['text' => 'Здравствуйте. Я бот BOT_NAME'];
      }; break;

      default:
      if($session[5] == 0 ) {
        return ['text' => 'я вас не понял'];
      }
  }
}