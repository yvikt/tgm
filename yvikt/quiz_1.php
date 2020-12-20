<?php

function any_quiz(){
  return true;
}

if (!any_quiz()) { exit; } // если нет никого в режиме quiz
else {
  // инициализируем переменную/ные для quiz
}

function start_quiz_1(){


}

/*
// если пришел контакт (пока что отправляем туда же от куда пришел)
// TODO сохранять или отправлять эксперту
if($incoming_data['message']['contact']){
  $text = "first name: {$incoming_data['message']['contact']['first_name']}\n";
  $text .= "last name: {$incoming_data['message']['contact']['last_name']}\n";
  $text .= "phone number: {$incoming_data['message']['contact']['phone_number']}\n";
  $f = fopen('contact.log', 'a+');
  fwrite($f, $text . "\n");
  fclose($f);

  $send_data = [
      'text' => 'Спасибо. С вами свяжется наш эксперт.',
      'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
  ];
  $send_data['chat_id'] = $chat_id;
  $res = sendToTelegram($send_data); // TODO почему это здесь, а не в конце
# запись последних отправленных ботом данных
  file_put_contents('req_logs/bot_out.log', '$data: '.print_r($send_data, 1)."\n");
  exit;
}
*/

// ответ пользователя
$poll_answer = [
    'poll_answer' => [
        'poll_id' => 5398111534100512783,
        'user' => [
            'id' => 258449877,
            'is_bot' => '',
            'first_name' => 'Viktor',
            'last_name' => 'Yakovenko',
            'username' => 'yvikt',
            'language_code' => 'ru',
            ],
        'option_ids' => [ 0 => 2 ]
    ]
];
// опрос - то что фактически отправляется ботом
$poll = [
    'id' => 5398111534100512783,
    'question' => 'How many cats do you have?',
    'options' => [
        '0' => [
            'text' => 'one',
            'voter_count' => 0
        ],
        [
        '1' => [
            'text' => 'two',
            'voter_count' => 0
        ],
        '2' => [
            'text' => 'three',
            'voter_count' => 0
            ]
        ]
    ],
    'total_voter_count' => 0,
    'is_closed' => '',
    'is_anonymous' => '',
    'type' => 'quiz',
    'allows_multiple_answers' => '',
    'correct_option_id' => 2,
    'explanation' => 'be attentive and count again ;-)',
    'explanation_entities' => []
];