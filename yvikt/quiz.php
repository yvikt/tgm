<?php
$quiz_dir = 'quiz';

function create_quiz(&$session){
  global $questions_1;
  global $questions_2;
  global $quiz_dir;
  if($session[5] == 2) $questions = $questions_1;//легкий уровень
  if($session[5] == 3) $questions = $questions_2;//сложный уровень
  $user_id = $session[1];
  $dir = "$quiz_dir/$user_id";//отдельная папка для каждого пользователя
  if(!is_dir($dir)){
    mkdir($dir);
  }
  $count = count($questions);

  $f1 = fopen("$dir/progress", 'w');//создаем новый progress файл
                                                       //(предыдущий  курсор нужен для составления отчета)
  fputs($f1, "0,0,0,$count,0");//строка заголовка: предыдущий курсор, курсор, № текущий вопрос, всего вопросов, правильных ответов
  fclose($f1);

  $f2 = fopen("$dir/quiz", 'w');//создаем новый quiz файл
  foreach ($questions as $question){
    $row = '';// вес вопроса, вопрос, правильный ответ, ответ-1, ответ-2, ответ-3 ...
    $write = array_search(true, $question['answers']);
    $row  .= "{$question['weight']},\"{$question['question']}\",\"$write\"";
    foreach (array_keys($question['answers']) as $answer){
      $row .= ",\"$answer\"";
    }
    $row .= "\n";
    fputs($f2, $row);
  }
  fclose($f2);

  //для формирования отчета прохождения теста создаем соответственный файл
  $f3 = fopen("$dir/report", 'w');//создаем новый report файл
  fclose($f3);
}

function next_poll(&$session){
  global $quiz_dir;
  $user_id = $session[1]; // $session[1] это и есть $user_id
  $dir = "$quiz_dir/$user_id";
  $f1 = fopen("$dir/progress", 'c+');
  $progress = fgetcsv($f1);
  $progress[0] = $progress[1];//сохраняем предыдущее значение курсора
  
  $f2 = fopen("$dir/quiz", 'r');
  fseek($f2, intval($progress[1]));// перемещаем курсор
  $question = fgetcsv($f2);//считываем очередную строку с вопросом и ответами
  $progress[1] = ftell($f2);//сохраняем последнее положение курсора
  fclose($f2);

  $progress[2] += 1;
  rewind($f1);
  fputcsv($f1, $progress);
  fclose($f1);

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
//  $params['open_period'] = 10;

  $session[6] = $write; // сохраняем номер правильного ответа в сессию, используя поле для сообщения
  return $params;
}

function get_answer($answer, &$session){
  global $quiz_dir;
  $user_id = $session[1]; // $session[1] это и есть $user_id
  $dir = "$quiz_dir/$user_id";

  $f1 = fopen("$dir/progress", 'c+');
  $progress = fgetcsv($f1);

  report_log($answer, $user_id, $progress[2]);

  if($session[6] == $answer){ // сверяем пришедший ответ с правильным, ранее сохраненным в сессии
    $progress[4] += 1;//увеличиваем кол-во правильных
    rewind($f1);
    fputcsv($f1, $progress);
    $session[6] = 'correct';
  //  return ['text' => 'correct'];
  }
  else{
    $session[6] = 'wrong';
  //  return ['text' => 'wrong'];
  }
  fclose($f1);

  if($progress[2] == $progress[3]){ // если это ответ на последний вопрос
    $report = report_get($user_id);

    $percent = round($progress[4] / $progress[3] * 100);
    $total = "{$progress[4]} из {$progress[3]}";

    // TODO+ отправить отчет студенту и эксперту
    $uf = fopen("sessions/$user_id", 'r');
    $user = fgetcsv($uf); // извлекаем имя студента для отправки отчета эксперту
    $user_name = "first_name: $user[0]\nlast_name: $user[1]\nlogin: $user[2]\n";
    $user_report = "$user_name\n$report\n$total = $percent%";

    if($expert_id = any_expert()) {
      $outgoing_data['chat_id'] = $expert_id;
      $outgoing_data['text'] = "$user_report"; // !!! важно - двойные кавычки нужны для правильной интерполяции символов &#x2705
      $outgoing_data['parse_mode'] = 'HTML';
      sendToTelegram($outgoing_data);
    }
    else{
      que_push($user_id, $user_report);
    }

    archivate($user_id);

    return [
        'text' => "<b>Результаты теста</b>\n\n" . $report . "\nВы ответили на $total вопросов. Это $percent%",
        'reply_markup' => ['resize_keyboard' => true, 'keyboard' => keyboard(8)]//запрос контакта
    ];
  }
  return ['text' => ' ']; //эта строка пустой ответ (костыль, чтобы дополнительно обновлялась сессия, но это не обязательно)
                          //в файле bot.php это первый из последних трех if-ов
}

function report_get($user_id){
  global $quiz_dir;
  $dir = "$quiz_dir/$user_id";
  return file_get_contents("$dir/report");
}

function report_log($answer, $user_id, $current_question){
  global $quiz_dir;
  $dir = "$quiz_dir/$user_id";

  $f1 = fopen("$dir/progress", 'r');//считываем progress
  $progress = fgetcsv($f1);
  fclose($f1);

  $f2 = fopen("$dir/quiz", 'r');
  fseek($f2, intval($progress[0]));// перемещаем курсор на предыдущий вопрос (тот на который пришел ответ)
  $question = fgetcsv($f2);
  fclose($f2);

  $log = '';
  $log .= "<b>$current_question.</b> {$question[1]}\n";
  $answers = array_slice($question, 3);
  $write = array_search($question[2], $answers);//ищем индекс правильного ответа, так как $answer это тоже индекс

  if($answer == $write){
    $mark = '&#x2705';
  }
  else $mark = '&#x274C';
  $log .= "$mark<i>{$answers["$answer"]}</i>";
  if($answer != $write){
    $log .= " <b><i>({$answers["$write"]})</i></b>";
  }
  $log .= "\n\n";

  $f = fopen("$dir/report", 'a+');
  fputs($f, "$log");
  fclose($f);
}

function archivate($user_id){
  global $quiz_dir;
  $dir = "$quiz_dir/$user_id";
  $archive_dir = "$dir/archive";
  if(!is_dir($archive_dir)){
    mkdir($archive_dir);
  }
  $time = time();
  rename("./$dir/progress", "./$archive_dir/$time-progress");
  rename("./$dir/quiz", "./$archive_dir/$time-quiz");
  rename("./$dir/report", "./$archive_dir/$time-report");
}


$questions_1 = [
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


$questions_2 = [
    [
        'weight' => 3,
        'question' => 'Какова окружность Земли?',
        'answers' => [
            '40000 км' => true,
            '40000 миль' => false,
            '40 миллионов км' => false,
            '40 километров' => false
        ]
    ],
    [
        'weight' => 1,
        'question' => 'Какое расстояние от Солца до Земли?',
        'answers' => [
            '500 млн км' => false,
            '150 млн км' => true,
            '1 световой год' => false,
            '1 световой день' => false
        ]
    ],
    [
        'weight' => 2,
        'question' => 'Какая самая близкая к нам звезда?',
        'answers' => [
            'Андромеда' => false,
            'Солнце' => true,
            'Альфа Центавра' => false,
            'Полярная звезда' => false
        ]
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