<?php
$quiz_dir = 'quiz';
/*
  pre-A1 = starter
  A1 = Elementary
  A2 = Pre-int

  B1 = Int
  B2 = Upper
  C1 = Advanced

Suggested entry points are:
    • For Starter and Elementary level students, questions 1–50
    • For Pre-intermediate level students, questions 51–100
    • For Intermediate level students, questions 101–150
    • For Upper Intermediate and Advanced level students, questions 150–200

Scoring and placement
    • 1–15 = Starter level                  pre-A1
    • 15–30 = Elementary Unit 1                 A1
    • 31–50 = Elementary Unit 7                 A1
    • 51–75 = Pre-intermediate Unit 1           A2
    • 76–100 = Pre-intermediate Unit 7          A2

    • 101–125 = Intermediate Unit 1             B1
    • 126–150 = Intermediate Unit 6             B1
    • 151–170 = Upper Intermediate Unit 1       B2
    • 171–185 = Upper Intermediate Unit 6       B2
    • 186+ = Advanced level                     С1
*/
if(!function_exists('str_putcsv'))
{
  function str_putcsv($input, $delimiter = ',', $enclosure = '"')
  {
    // Open a memory "file" for read/write...
    $fp = fopen('php://temp', 'r+');
    // ... write the $input array to the "file" using fputcsv()...
    fputcsv($fp, $input, $delimiter, $enclosure);
    // ... rewind the "file" so we can read what we just wrote...
    rewind($fp);
    // ... read the entire line into a variable...
    $data = fread($fp, 1048576);
    // ... close the "file"...
    fclose($fp);
    // ... and return the $data to the caller, with the trailing newline from fgets() removed.
    return rtrim($data, "\n");
  }
}

function get_chunk($array, $begin, $end, $count){
  $arr = array_slice($array, $begin, $end - $begin);
  shuffle($arr);
  $arr = array_splice($arr, 0, $count);
  return $arr;
}
function create_quiz(&$session){
  global $quiz_dir;
  $user_id = $session[1];
  $dir = "$quiz_dir/$user_id";//отдельная папка для каждого пользователя
  if(!is_dir($dir)){
    mkdir($dir);
  }
  $all_questions = file('test.csv');

  //делаем выборку вопросов(строк) из файла test.csv согласно уровню
  // в каждом тесте("низкий уровень" и "высокий уровень") по 40 вопросов
  // выбираем от 7 до 13 вопросов из каждого подуровня чтобы получилось 40
  $selected_questions = [];
  if($session[5] == 2){ //легкий уровень
    $begin = 1;
    $l1 = 15; // pre-A1 15  5
    $l2 = 30; // A1     15  7
    $l3 = 50; // A1     20  8
    $l4 = 75; // A2     25  10
    $l5 = 100;// A2     25  10
    // end
    $selected_questions = get_chunk($all_questions, $begin-1, $l1-1, 5);
    $selected_questions = array_merge($selected_questions, get_chunk($all_questions, $l1, $l2-1, 7));
    $selected_questions = array_merge($selected_questions, get_chunk($all_questions, $l2, $l3-1, 8));
    $selected_questions = array_merge($selected_questions, get_chunk($all_questions, $l3, $l4-1, 10));
    $selected_questions = array_merge($selected_questions, get_chunk($all_questions, $l4, $l5-1, 10));
  }
  if($session[5] == 3){ //сложный уровень
    $begin = 101;
    $l1 = 125; // B1    25  10
    $l2 = 150; // B1    25  10
    $l3 = 170; // B2    20  8
    $l4 = 185; // B2    15  7
    $l5 = 200; // C1    15  5
    // end
    $selected_questions = get_chunk($all_questions, $begin-1, $l1-1, 10);
    $selected_questions = array_merge($selected_questions, get_chunk($all_questions, $l1, $l2-1, 10));
    $selected_questions = array_merge($selected_questions, get_chunk($all_questions, $l2, $l3-1, 8));
    $selected_questions = array_merge($selected_questions, get_chunk($all_questions, $l3, $l4-1, 7));
    $selected_questions = array_merge($selected_questions, get_chunk($all_questions, $l4, $l5-1, 5));
  }
  $count = count($selected_questions);

  $f1 = fopen("$dir/progress", 'w');//создаем новый progress файл
                                                       //(предыдущий  курсор нужен для составления отчета)
  fputs($f1, "0,0,0,$count,0,0,0,0,0,0,0,0,0,0");//строка заголовка [0-3]: предыдущий курсор, курсор, № текущий вопрос, всего вопросов,
  // правильных ответов(10 уровней) [4-13]: pre-A1,A1,A1,A2,A2,B1,B1,B2,B2,C1
  fclose($f1);

  foreach ($selected_questions as &$string){ // перемешивание ответов
    $question = str_getcsv($string); // csv-строка -> массив
    $answers = array_slice($question, 2); // ответы
    $write_index = $question[1]; // индекс правильного ответа
    $write_answer = $answers[$write_index]; // правильный ответ
    shuffle($answers); // перемешиваем ответы
    $new_write_index = array_search($write_answer, $answers); // поиск индекса правильного ответа после перемешивания
//    $question[1] = $new_write_index;
    $question = [$question[0], $new_write_index];
    $question = array_merge($question, $answers);
    $string = str_putcsv($question) . "\n"; // массив -> csv-строка
  }

  file_put_contents("$dir/quiz", $selected_questions);

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
  $params['type'] = 'quiz';
  $params['is_anonymous'] = false;

  $params['question'] = "$progress[2]. $question[0]"; // номер и сам вопрос
  $answers = array_slice($question, 2); // ответы
  $write_index = $question[1]; // индекс правильного ответа

  $params['options'] = json_encode($answers);
  $params['correct_option_id'] = $write_index;
  $params['explanation'] = $answers[$write_index];
//  $params['open_period'] = 10;

  $session[6] = $write_index; // сохраняем номер правильного ответа в сессию, используя поле для сообщения
  return $params;
}

function get_answer($answer, &$session){
  global $quiz_dir;
  $user_id = $session[1]; // $session[1] это и есть $user_id
  $dir = "$quiz_dir/$user_id";

  $f1 = fopen("$dir/progress", 'c+');
  $progress = fgetcsv($f1);

  report_log($answer, $user_id, $progress[2]);// номер ответа, id, номер вопроса

  if($session[6] == $answer){ // сверяем пришедший № ответа с правильным, ранее сохраненным в сессии
    // TODO сделать учет согласно уровней $progress[4-13]
//    $progress[4] += 1;//увеличиваем кол-во правильных
    $qn = $progress[2];// $qn question number
    switch ($session[5]){ // сложный или простой уровень
      case 2:
          switch ($qn) { // в зависимости от текущего номера вопроса
            // 5:1-5=pre-A, 7:6-12=A1a, 8:13-20=A1b, 10:21-30=A2a, 10:31-40=A2b
            case ($qn <= 5):                $progress[4] += 1; break;
            case ($qn >= 6 && $qn <= 12):   $progress[5] += 1; break;
            case ($qn >= 13 && $qn <= 20):  $progress[6] += 1; break;
            case ($qn >= 21 && $qn <= 30):  $progress[7] += 1; break;
            case ($qn >= 31 && $qn <= 40):  $progress[8] += 1; break;
          };break;
        case 3:
          switch ($qn) { // в зависимости от текущего номера вопроса
            // 10:1-10=B1a, 10:11-20=B1b, 8:21-28=B2a, 7:29-35=B2b, 5:36-40=C1
            case ($qn <= 10):               $progress[9] += 1; break;
            case ($qn >= 11 && $qn <= 20):  $progress[10] += 1; break;
            case ($qn >= 21 && $qn <= 28):  $progress[11] += 1; break;
            case ($qn >= 29 && $qn <= 35):  $progress[12] += 1; break;
            case ($qn >= 36 && $qn <= 40):  $progress[13] += 1; break;
          };break;
        }
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

    // TODO вычисление (определение) уровня языка на основе балов за каждый подуровень
    switch ($session[5]) { // сложный или простой уровень
      case 2:// 5:pre-A, 7:A1a, 8:A1b, 10:A2a, 10:A2b
        $preA= $progress[4];
        $a1a = $progress[5];
        $a1b = $progress[6];
        $a2a = $progress[7];
        $a2b = $progress[8];
        $result = $preA + $a1a + $a1b + $a2a + $a2b; // общее кол-во баллов
        $detail_result = "pre-A: $preA(5)\nA1a: $a1a(7), A1b: $a1b(8)\nA2a: $a2a(10), A2b: $a2b(10)"; // подробно
        switch ($result){
          case ($result <= 5): $level = 'pre-A1 (Starter)'; break;
          case ($result > 5 && $result <= 15): $level = 'начало A1 (Elementary)'; break;
          case ($result > 15 && $result <= 20): $level = 'хороший A1 (Elementary)'; break;
          case ($result > 20 && $result <= 30): $level = 'начало A2 (Pre-intermediate)'; break;
          case ($result > 35): $level = 'хороший A2 (Pre-intermediate)'; break;
        }; break;
      case 3:// 10:B1a, 10:B1b, 8:B2a, 7:B2b, 5:C1
        $b1a = $progress[9];
        $b1b = $progress[10];
        $b2a = $progress[11];
        $b2b = $progress[12];
        $c1  = $progress[13];
        $result = $b1a + $b1b + $b2a + $b2b + $c1; // общее кол-во баллов
        $detail_result = "B1a: $b1a(10), B1b: $b1b(10)\nB2a: $b2a(8), B2b: $b2b(7)\nC1: $c1(5)"; // подробно
        switch ($result){
          case ($result <= 7): $level = 'начало B1 (Intermediate) или ниже. Рекомендуем пройти тестирование на уровень A1-A2'; break;
          case ($result <= 10): $level = 'начало B1 (Intermediate)'; break;
          case ($result > 10 && $result <= 20): $level = 'хороший B1 (Intermediate)'; break;
          case ($result > 20 && $result <= 28): $level = 'начало B2 (Upper Intermediate)'; break;
          case ($result > 28 && $result <= 35): $level = 'хороший B2 (Upper Intermediate)'; break;
          case ($result > 35): $level = 'B2 - начало C1 (Advanced)'; break;
        }
    }

    $percent = round($result / $progress[3] * 100); // правильных/всего * 100 = %
    $total = "$result из $progress[3]";

    // TODO+ отправить отчет студенту и эксперту
    $uf = fopen("sessions/$user_id", 'r');
    $user = fgetcsv($uf); // извлекаем имя студента для отправки отчета эксперту
    $user_name = "first_name: $user[0]\nlast_name: $user[1]\nlogin: $user[2]\n";
    $detail_report = "$user_name\n$report\n$total = $percent%\nДетализация:\n$detail_result";

    if($expert_id = any_expert()) { // отправка эксперту либо в очередь
      $outgoing_data_to_expert = [
          'chat_id' => $expert_id,
          'text' => "$detail_report", // !!! важно - двойные кавычки нужны для правильной интерполяции символов &#x2705
          'parse_mode' => 'HTML',
        //TODO
//          'reply_markup' => [ "inline_keyboard" => [[[ "text" => "начать общение", "callback_data" => "begin_chat"]]] ]
      ];
      sendToTelegram($outgoing_data_to_expert);
    }
    else{
      que_push($user_id, $detail_report);
    }

    archivate($user_id);

    return [ // это отправка отчета пользователю
        'text' => "<b>Результаты теста</b>\n\n$report\nВы ответили правильно на $total вопросов.\n" .
                   "Предположительно Ваш уровень языка соответствует уровню $level.\n" .
                   "Для более детального анализа и консультации, пожалуйста свяжитесь с экспертом.",
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

function report_log($answer, $user_id, $current_question){// номер ответа, id, номер вопроса
  global $quiz_dir;
  $dir = "$quiz_dir/$user_id";

  $f1 = fopen("$dir/progress", 'r');//считываем progress
  $progress = fgetcsv($f1);
  fclose($f1);

  $f2 = fopen("$dir/quiz", 'r');
  fseek($f2, intval($progress[0]));// перемещаем курсор на предыдущий вопрос (тот на который пришел ответ)
  $question_scv = fgetcsv($f2);
  fclose($f2);

  $answers = array_slice($question_scv, 2);
  $write = $question_scv[1]; // индекс правильного ответа
  if($answer == $write) $mark = '&#x2705'; else $mark = '&#x274C';
  $result = "$mark<i>{$answers["$answer"]}</i>";
  if($answer != $write){// в случае неправильного ответа указываем правильный в скобках
    $result .= " ({$answers["$write"]})";
  }
  $question_sentence = $question_scv[0];
  $output = str_replace('____', $result, $question_sentence);

  $log = "<b>$current_question.</b> $output\n\n";


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
  // 1 - 5
    [
        'weight' => 'A1',
        'question' => "Tim and Sue_____teachers.",
        'answers' => [
            " are " => true,
            " is " => false,
            " isn't " => false,
            " aren't " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "This is Sébastien. He's_____.",
        'answers' => [
            " Japan" => false,
            " Spain" => false,
            " Italy" => false,
            " French" => true
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "They_____Welsh. They're from Scotland.",
        'answers' => [
            " aren't " => true,
            " is " => false,
            " isn't " => false,
            " are " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "_____’s your first name?",
        'answers' => [
            "Who" => false,
            "What" => true,
            "How" => false,
            "Where" => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "Those are your books and_____are mine.",
        'answers' => [
            " these " => true,
            " this " => false,
            " it " => false,
            " that " => false
        ]
    ],
  // 6 - 10
    [
        'weight' => 'A1',
        'question' => "I love music but I_____like TV.",
        'answers' => [
            " do " => false,
            " does " => false,
            " don't " => true,
            " doesn't " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "_____she like sport?",
        'answers' => [
            "Do " => false,
            "Does " => true,
            "Is " => false,
            "Don't " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "When_____have lunch?",
        'answers' => [
            " is he " => false,
            " he's " => false,
            " do he " => false,
            " does he " => true
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "Do you like_____DVDs?",
        'answers' => [
            " watch " => false,
            " watching " => true,
            " watches " => false,
            " to watching " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "Peter’s_____name is Michael.",
        'answers' => [
            " brother's " => true,
            " sister is " => false,
            " brother " => false,
            " sisters " => false
        ]
    ],
  // 11 - 15
    [
        'weight' => 'A1',
        'question' => "He hasn't got_____brothers and sisters.",
        'answers' => [
            " some " => false,
            " any " => true,
            " the " => false,
            " a " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "They went to the beach with some friends_____Sunday.",
        'answers' => [
            " at " => false,
            " the " => false,
            " on " => true,
            " in " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "_____two armchairs and a sofa in the living room.",
        'answers' => [
            " It's " => false,
            " There are " => true,
            " There have " => false,
            " There's " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "You_____buy shoes in a post office.",
        'answers' => [
            " can to " => false,
            " can " => false,
            " can't " => true,
            " are " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "The cinema is_____the bank.",
        'answers' => [
            " next " => false,
            " in front " => false,
            " opposite " => true,
            " under " => false
        ]
    ],
  // 16 - 20
    [
        'weight' => 'A1',
        'question' => "There is_____butter in the fridge.",
        'answers' => [
            " one " => false,
            " some " => true,
            " any " => false,
            " an " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "How_____vegetables do you eat every day?",
        'answers' => [
            " long " => false,
            " much " => false,
            " many " => true,
            " more " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "We_____born in 1985.",
        'answers' => [
            " is" => false,
            " were " => true,
            " was " => false,
            " did " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "My birthday is on February_____.",
        'answers' => [
            " 10rd" => false,
            " 10st" => false,
            " 10nd" => false,
            " 10th" => true
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "_____they do a lot of sport when they were at school?",
        'answers' => [
            "Were " => false,
            "Do " => false,
            "Was " => false,
            "Did " => true
        ]
    ],
  // 21 - 25
    [
        'weight' => 'A1',
        'question' => "We_____to New Zealand when I was six.",
        'answers' => [
            " move " => false,
            " moves " => false,
            " moved " => true,
            " moving " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "Is Chinese food_____than Thai food?",
        'answers' => [
            " best " => false,
            " more good " => false,
            " better " => true,
            " well " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "Can you tell me the_____to the library?",
        'answers' => [
            " road " => false,
            " way " => true,
            " street " => false,
            " place " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "They_____their homework now.",
        'answers' => [
            " are do " => false,
            " did " => false,
            " are doing " => true,
            " does " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "He goes to work_____train",
        'answers' => [
            " in " => false,
            " on " => false,
            " by the " => false,
            " by " => true
        ]
    ],
  // 25 - 30
    [
        'weight' => 'A1',
        'question' => "You_____drive a car in the centre of town. It isn’t allowed",
        'answers' => [
            " don't have to " => false,
            " can " => false,
            " have to " => false,
            " can't " => true
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "You_____to walk, you can take a bus.",
        'answers' => [
            " mustn't " => false,
            " have " => false,
            " must " => false,
            " don't have " => true
        ]
    ],
    [
        'weight' => 'A2',
        'question' => "He_____to move to another country",
        'answers' => [
            " want " => false,
            "'d like " => true,
            " likes " => false,
            " goes " => false
        ]
    ],
    [
        'weight' => 'A2',
        'question' => "I’m_____learn to cook.",
        'answers' => [
            " go to " => false,
            " going " => false,
            " going to " => true,
            " go " => false
        ]
    ],
    [
        'weight' => 'A2',
        'question' => "Don't stay up late or you_____be tired tomorrow",
        'answers' => [
            " must " => false,
            " won't " => false,
            "'ll " => true,
            " should " => false
        ]
    ],
  // 31 - 35
    [
        'weight' => 'A2',
        'question' => "_____you spoken to Jenny?",
        'answers' => [
            "Did " => false,
            "Do " => false,
            "Have " => true,
            "Has " => false
        ]
    ],
    [
        'weight' => 'A2',
        'question' => "_____does that jacket cost?",
        'answers' => [
            "How often " => false,
            "How long " => false,
            "How many " => false,
            "How much " => true
        ]
    ],
    [
        'weight' => 'A2',
        'question' => "_____you like a coffee?",
        'answers' => [
            "Will " => false,
            "Would " => true,
            "Did " => false,
            "Do " => false
        ]
    ],
    [
        'weight' => 'A2',
        'question' => "I_____to go home now.",
        'answers' => [
            "'m wanting " => false,
            " will want " => false,
            " want " => true,
            " wanting " => false
        ]
    ],
    [
        'weight' => 'A2',
        'question' => "I’m not keen on_____.",
        'answers' => [
            " run" => false,
            " to running" => false,
            " to run" => false,
            " running" => true
        ]
    ],
  // 36 - 40
    [
        'weight' => 'A1',
        'question' => "_____laptop is that? Is it Bob’s?",
        'answers' => [
            "Which " => false,
            "Who " => false,
            "What " => false,
            "Whose " => true
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "I_____dinner when I heard a strange noise.",
        'answers' => [
            " was cook " => false,
            " did cook " => false,
            " was cooking  " => true,
            " am cooking " => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "I_____wear a uniform to school.",
        'answers' => [
            " use to " => false,
            " didn't use to " => false,
            " used " => false,
            " didn't used to " => true
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "Can I try this coat_____, please?",
        'answers' => [
            " in" => false,
            " on " => true,
            " to" => false,
            " up" => false
        ]
    ],
    [
        'weight' => 'A1',
        'question' => "It’s_____beautiful house I've ever seen",
        'answers' => [
            " most " => false,
            " more " => false,
            " the most " => true,
            " a most " => false
        ]
    ],
  // 41 - 44
    [
        'weight' => 'A1',
        'question' => "I think travelling by plane is_____easier than travelling by car.",
        'answers' => [
            " more " => false,
            " most " => false,
            " " => true,
            " the most " => false
        ]
    ],
    [
        'weight' => 'B1',
        'question' => "He_____to work in his company’s office in Shanghai.",
        'answers' => [
            " sent " => false,
            " was " => false,
            " was sent " => true,
            " was send " => false
        ]
    ],
    [
        'weight' => 'B1',
        'question' => "What will happen if he_____here in time?",
        'answers' => [
            " doesn't get " => true,
            " won't get " => false,
            " will get " => false,
            " isn't get " => false
        ]
    ],
    [
        'weight' => 'B1',
        'question' => "She told me she_____buy me a new phone.",
        'answers' => [
            " don't " => false,
            " won't to " => false,
            "'ll " => false,
            "'d " => true
        ]
    ],
];

$questions_0 = [
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