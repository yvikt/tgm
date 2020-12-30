<?php
$quiz_dir = 'quiz';

function create_quiz($user_id){
  global $questions;
  global $quiz_dir;
  $dir = "$quiz_dir/$user_id";
  if(!is_dir($dir)){
    mkdir($dir);
  }
  $f = fopen("$dir/".time(), 'w');
  $count = count($questions);
  fputs($f, "0,0,$count,0\n");// строка заголовка: курсор, № текущий вопрос, всего вопросов, правильных ответов
  foreach ($questions as $question){
    $row = '';// вес вопроса, вопрос, правильный ответ, ответ-1, ответ-2, ответ-3 ...
    $write = array_search(true, $question['answers']);
    $row  .= "${question['weight']},\"${question['question']}\",\"$write\"";
    foreach (array_keys($question['answers']) as $answer){
      $row .= ",\"$answer\"";
    }
    $row .= "\n";
    fputs($f, $row);
  }
  fclose($f);
}

function find_quiz($user_id){
  global $quiz_dir;
  $dir = "$quiz_dir/$user_id";
  $files = scandir($dir);
  $item = preg_grep('/^[0-9]/',$files); // находим файл имя которого начинается с цифры
  $quiz = array_pop($item);
  return "$quiz_dir/$user_id/$quiz";
}

function init_quiz($user_id){
  $file = find_quiz($user_id);
  $f = fopen($file, 'r+');
  $progress = fgetcsv($f);//считываем заголовок и как следствие курсор передвигается на первый вопрос (вторая строка)
  $progress[0] = ftell($f);// обновляем значение курсора (текущий вопрос пока = 0)
  rewind($f);
  fputcsv($f, $progress); // обновляем заголовок
  fclose($f);
}

function archive_quiz($user_id){
  global $quiz_dir;
  $user_quiz_dir = "$quiz_dir/$user_id";
  $files = scandir($user_quiz_dir);
  $item = preg_grep('/^[0-9]/',$files); // находим файл имя которого начинается с цифры
  $quiz = array_pop($item);

  $archive_dir = "$user_quiz_dir/archive";
  if(!is_dir($archive_dir)){
    mkdir($archive_dir);
  }
  rename("./$user_quiz_dir/$quiz", "./$archive_dir/$quiz");
}

function next_poll(&$session){
  $user_id = $session[1]; // $session[1] это и есть $user_id
  $file = find_quiz($session[1]);
  $f = fopen($file, 'c+');
  $progress = fgetcsv($f);
  fseek($f, intval($progress[0]));
  $question = fgetcsv($f);
  $progress[0] = ftell($f);
  $progress[1] += 1;
  rewind($f);
  fputcsv($f, $progress);
  fclose($f);

  $params = [];
  $params['chat_id'] = $user_id;
  $params['question'] = $question[1];
  $answers = array_slice($question, 3);
  $params['options'] = json_encode($answers);
  $params['is_anonymous'] = false;
  $params['type'] = 'quiz';
  $write = array_search($question[2], $answers);
  $params['correct_option_id'] = $write;
  $params['explanation'] = $answers[$write];

  $session[6] = $write; // сохраняем номер правильного ответа в сессию, используя поле для сообщения
  return $params;
}

function get_answer($answer, &$session){
  $user_id = $session[1];
  $file = find_quiz($user_id);
  $f = fopen($file, 'c+');
  $progress = fgetcsv($f);

  if($session[6] == $answer){ // пришедший ответ сверяем с правильным, ранее сохраненным в сессии
    $progress[3] += 1;
    rewind($f);
    fputcsv($f, $progress);
    fclose($f);
    $session[6] = 'correct';
  //  return ['text' => 'correct'];
  }
  else{
    $session[6] = 'wrong';
  //  return ['text' => 'wrong'];
  }

  if($progress[1] == $progress[2]){ // если это ответ на последний вопрос
    archive_quiz($user_id);
    $session[4] = 10; // возврат в главное меню
    $session[5] = 0; // выход из режима quiz
    $percent = $progress[3] / $progress[2] * 100 % 100;
    return [
        'text' => "Тест окончен. Вы ответили на {$progress[3]} из {$progress[2]} вопросов. Это $percent%",
        'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(10)]
    ];
  }
  return ['text' => ' ']; //эта строка пустой ответ (костыль, чтобы дополнительно обновлялась сессия, но это не обязательно)
                          //в файле bot.php это первый из последних трех if-ов
}

$questions = [
    [
        'weight' => 3,
        'question' => 'Насколько хорошо преподаватель мотивирует Вас к изучению предмета',
        'answers' => [
            'Отлично' => true,
            'Хорошо' => false,
            'Удовлетворительно' => false,
            'Не удовлетворительно' => false
        ]
    ],
    [
        'weight' => 1,
        'question' => 'Легко ли договориться с преподавателем о консультации?',
        'answers' => [
            'Легко' => false,
            'Нормально' => true,
            'Тяжело' => false,
            'Практически невозможно' => false
        ]
    ],
    [
        'weight' => 2,
        'question' => 'Хорошо ли преподаватель объяснил Вам систему оценки знаний по предмету?',
        'answers' => [
            'Отлично' => false,
            'Хорошо' => true,
            'Удовлетворительно' => false,
            'Не удовлетворительно' => false
        ]
    ],
    [
        'weight' => 1,
        'question' => 'Насколько справедливо, на Ваш взгляд, преподаватель оценил Ваши знания?',
        'answers' => [
            'Справедливо' => true,
            'Не справедливо' => false
        ]
    ],
    [
        'weight' => 2,
        'question' => 'Соответствовали ли задания преподавателя пройденному материалу?',
        'answers' => [
            'Соответствовали' => true,
            'Зачастую' => true,
            'Иногда' => false,
            'Не соответствовали' => false ]
    ],
    [
        'weight' => 3,
        'question' => 'Ваш преподаватель старается донести до Вас смысл или факты?',
        'answers' => [
            'В большей степени смысл' => false,
            'В большей степени факты' => false,
            'Хорошо совмещает' => true ]
    ]
];


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
  file_put_contents('logs/bot_out.log', '$data: '.print_r($send_data, 1)."\n");
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



/* ответ(подтверждение) API при отправке Опроса
[ok] => 1
    [result] => Array
(
    [message_id] => 3199
            [from] => Array
(
    [id] => 1482254975
                    [is_bot] => 1
                    [first_name] => yvikt
[username] => yvikt_bot
                )

            [chat] => Array
(
    [id] => 1268256583
                    [first_name] => Viktor
[last_name] => Y
[username] => vict0r
[type] => private
                )

            [date] => 1609093424
            [poll] => Array
(
    [id] => 5424673703508049996
                    [question] => How many cats do you have?
    [options] => Array
(
    [0] => Array
    (
        [text] => one
        [voter_count] => 0
                                )

                            [1] => Array
(
    [text] => two
    [voter_count] => 0
                                )

                            [2] => Array
(
    [text] => three
    [voter_count] => 0
                                )

                        )

                    [total_voter_count] => 0
                    [is_closed] =>
                    [is_anonymous] =>
                    [type] => quiz
[allows_multiple_answers] =>
                    [correct_option_id] => 2
                    [explanation] => be attentive and count again ;-)
                    [explanation_entities] => Array
(
)

                )

        )
  */