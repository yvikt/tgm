<?php


// 1. есть один или более экспертов - файлы в папке expert
// 2. эксперт по умолчанию "общительный" - готов принимать сообщения от пользователей
// 3. эксперт может быть свободен(НЕобщается) = 0 или занят(Общается) = 1 (либо просто занят)
// 4. если есть свободный эксперт:
// 4a. создается чат (файл для логирования разговоров. имя файла = id пользователя)
// 4b. пользователь переводится в режим "общения" и в его сессии прописывается id эксперта
// 4c. эксперт переводится в режим "общения" и в его сессии прописывается id пользователя
// 4d. routing производится согласно статусу "общение" и руководствуясь id-шниками в сессии каждого
// 5. если свободных нет - создается очередь: (очередь также создается если эксперт не ответил пользователю за 20 секунд)
// 5a. создается файл очереди (имя файла = id пользователя)
// 5b. пользователю отправляется уведомление "свяжемся позже"
// 5c. эксперту отправляется уведомление о имени пользователя и инлайн кнопка - "начать общение"
// 5d. если эксперт решит начать отложенное общение сначала произведется проверка пользователя на режим "quiz" и на наличие\отсутствие файла чата (не общается ли он другим экспертом)
// 5e. если пользователь проходит quiz, эксперту будет отправлен соответствующий ответ
// 6a. как только эксперт освобождается или регистрируется ищется файл с id пользователя в папке очередей
// 6b. если соединение удалось (пользователь не занят quiz), файл очереди удаляется


// QUIZ
// 1. нажав на кнопку "начать тест", пользователь переводится врежим quiz
// 2. создается файл quiz (csv-файл в котором каждая строка - один шаг quiz-а)
// 3. по завершению quiz-а сообщается результат и предлагается оставить свой контакт
// 4. эксперту отправляется уведомление с результатами, которые отобразятся когда он будет в режиме "свободен"


