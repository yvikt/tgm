<?php

function keyboard($number)
{
  switch ($number) {
    case 1:
      return [

          [
              ['text' => 'про обучение'],       # -> 11
              ['text' => 'задать вопрос']       # -> 12
          ],
          [
              ['text' => 'категории курсов'],   # -> 13
              ['text' => 'пройти тестирование'] # -> 14
          ]
      ];
    case 11:
      return [
          [
              ['text' => 'НАЗАД'],              # -> 1
              ['text' => 'как проходят уроки']
          ],
          [
              ['text' => 'стоимость'],
              ['text' => 'преимущества']
          ]
      ];
    case 12:
      # /tobeadmin - стать админом - пароль
      # /tobeexpert - стать экспертом - пароль
      return [
          [
              ['text' => 'завершить общение'] # -> 1
          ]
      ];
    case 13:
      return [
          [
              ['text' => 'НАЗАД'],             # -> 1
              ['text' => 'взрослые']
          ],
          [
              ['text' => 'подростки'],
              ['text' => 'дети']
          ]
      ];
    case 14:
      return [
          [
              ['text' => 'НАЗАД']              # -> 1
          ],
          [
              ['text' => 'низкий уровень'],
              ['text' => 'высокий уровень']
          ]
      ];
  }
}


# отрисовка клавиатуры
//function render_keyboard($state){
//  $keyboard = '';
//  return $keyboard;
//}

