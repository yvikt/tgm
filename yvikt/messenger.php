<?php


# Обрабатка сообщения
# принимат сообщение и текущую клавиатуру
# обрабатывает сообщение и "сессию"
# отправляет ответ и [новую] клавиатуру
# •start•
function messenger($text, &$session){
  $kb_menu_1 = ['про обучение' ,'задать вопрос', 'категории курсов', 'пройти тестирование', 'ee', 'oo', 'qq' ];
  $kb_menu_11 = [ 'НАЗАД', 'как проходят уроки', 'стоимость', 'преимущества' ];
  $kb_menu_12 = [ 'завершить общение' ];
  $kb_menu_13 = [ 'НАЗАД', 'взрослые', 'подростки', 'дети' ];
  $kb_menu_14 = [ 'НАЗАД', 'низкий уровень', 'высокий уровень' ];

    switch ($text) {
      case '/start':
      case '/' :
        return handler_0($text, $session);

      case in_array($text, $kb_menu_1) :
        return handler_1($text, $session);

      case in_array($text, $kb_menu_11) :
        return handler_11($text, $session);

      case in_array($text, $kb_menu_12) :
        return handler_12($text, $session);

      case in_array($text, $kb_menu_13) :
        return handler_13($text, $session);

      case in_array($text, $kb_menu_14) :
        return handler_14($text, $session);

      default :
        return handler_default($text, $session);
    }
}











