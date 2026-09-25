<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Russian strings for quizaccess_proctorcore.
 *
 * @package    quizaccess_proctorcore
 * @copyright  2026 SENTAL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ProctorCore';
$string['privacy:metadata'] = 'Правило доступа ProctorCore хранит настройки теста в local_proctorcore. Данные сессий и организаций обрабатываются компонентом local_proctorcore.';
$string['proctorcore:bypass'] = 'Обходить ограничения доступа ProctorCore к тесту';
$string['settingsheader'] = 'ProctorCore';
$string['enabled'] = 'Включить ProctorCore для этого теста';
$string['enabled_help'] = 'Подключает тест к local_proctorcore и серверу прокторинга. До создания попытки в Moodle учащийся проходит обязательные проверки, подтверждения и проверку личности.';
$string['requirehttps'] = 'Требовать HTTPS';
$string['requirehttps_help'] = 'Блокирует доступ учащегося, если сайт открыт без HTTPS. Отключайте только на контролируемом тестовом сервере.';
$string['requirecamera'] = 'Требовать проверку камеры';
$string['requirecamera_help'] = 'Требует исправную камеру во время автоматической проверки перед экзаменом.';
$string['requiremicrophone'] = 'Требовать проверку микрофона';
$string['requiremicrophone_help'] = 'Требует исправный микрофон во время автоматической проверки перед экзаменом.';
$string['requiresnapshot'] = 'Требовать снимок личности';
$string['requiresnapshot_help'] = 'Требует, чтобы браузер сделал кадр с камеры во время предварительной проверки. Это подтверждает готовность камеры; биометрическое сравнение выполняется отдельно.';
$string['requirerulesack'] = 'Требовать явное согласие с правилами экзамена';
$string['ruleshtml'] = 'Правила экзамена';
$string['ruleshtml_help'] = 'Правила, показанные перед экзаменом. Разделяйте короткие правила символом |, чтобы вывести их адаптивным списком в две колонки. Вместе с сессией сохраняются хеш точного текста, язык, пользователь, IP-адрес и время подтверждения.';
$string['error:rulesrequired'] = 'Введите правила экзамена, если требуется их подтверждение.';
$string['recoveryheading'] = 'Раздел 5.3 — восстановление соединения';
$string['allowresume'] = 'Разрешить возврат к той же попытке после потери соединения';
$string['allowresume_help'] = 'Учащийся может снова открыть ту же попытку теста Moodle. Существующие ответы и исходный таймер сохраняются.';
$string['resumewindowsecs'] = 'Окно переподключения (секунды)';
$string['resumewindowsecs_help'] = 'Значение по спецификации — 600 секунд (10 минут). Допустимый диапазон: от 60 до 3600 секунд.';
$string['timerheading'] = 'Таймер и предупреждения';
$string['timerenabled'] = 'Включить таймер теста Moodle';
$string['timerenabled_help'] = 'Использует встроенное ограничение времени и автоматическую отправку Moodle, поэтому переподключение не сбрасывает таймер.';
$string['durationminutes'] = 'Продолжительность экзамена (минуты)';
$string['durationminutes_help'] = 'Стандартная продолжительность SPC — 120 минут.';
$string['warningsenabled'] = 'Включить предупреждения таймера';
$string['warningsenabled_help'] = 'Сохраняет моменты предупреждений для интерфейса участника.';
$string['warningcount'] = 'Количество предупреждений';
$string['warningtimes'] = 'Время предупреждений (оставшиеся минуты)';
$string['warningtimes_help'] = 'Значения через запятую, например: 15,5.';
$string['durationinvalid'] = 'Введите продолжительность экзамена от 1 до 10080 минут.';
$string['warningcountinvalid'] = 'Выберите от 1 до 10 предупреждений.';
$string['warningtimescountmismatch'] = 'Введите ровно {$a} разных значений времени предупреждения.';
$string['warningtimeinvalid'] = 'Каждое время предупреждения должно быть больше 0 и меньше продолжительности экзамена — {$a} мин.';
$string['proctoringenabled'] = 'Для этого теста включён ProctorCore.';
$string['timerdescription'] = 'Таймер экзамена: {$a} мин.';
$string['warningsdescription'] = 'Предупреждения при остатке {$a} мин.';
$string['recoverydescription'] = 'Отключённую попытку можно возобновить в течение {$a} с теми же ответами и исходным таймером.';
$string['recoverydisabled'] = 'Восстановление соединения для этого теста отключено.';
$string['httpsrequired'] = 'Для этого теста с прокторингом требуется HTTPS. Попросите администратора открыть сайт по защищённому адресу HTTPS.';
$string['accessblocked'] = 'Попытка с прокторингом не может продолжиться: {$a}';
$string['accessblockedgeneric'] = 'Попытка с прокторингом не может продолжиться, потому что ProctorCore недоступен. Свяжитесь с администратором.';
$string['statusconnected'] = 'Соединение прокторинга активно';
$string['statusreconnecting'] = 'Интернет восстановлен. Проверяется сессия ProctorCore…';
$string['statuslost'] = 'Соединение потеряно. Ответы Moodle остаются в этой попытке; переподключитесь в отведённое время.';
$string['statusinterrupted'] = 'Сессия ProctorCore прервана. Переподключитесь, чтобы продолжить ту же попытку теста.';
$string['reconnectbutton'] = 'Переподключиться и продолжить';
$string['error:invalidresumewindow'] = 'Окно переподключения должно составлять от 60 до 3600 секунд.';
$string['error:missinglocalplugin'] = 'Компонент local_proctorcore или его API раздела 4.1 недоступен.';
$string['error:missingrecoveryapi'] = 'API записи раздела 1.1 или восстановления раздела 5.3 компонента local_proctorcore недоступен.';
$string['error:missingconfigtable'] = 'Таблица local_proctorcore_quizcfg отсутствует.';
$string['error:missingcmid'] = 'Не удалось определить ID модуля курса для теста.';
$string['error:previewnotproctored'] = 'Предварительные попытки преподавателя не контролируются.';
$string['error:attemptnotinprogress'] = 'Исходная попытка теста больше не выполняется.';
$string['error:sessionowner'] = 'Сессия ProctorCore принадлежит другому пользователю.';
$string['error:precheckpending'] = 'Обязательные проверки перед экзаменом ещё не пройдены.';
$string['error:sessionclosed'] = 'Сессия ProctorCore закрыта ({$a}).';
$string['error:invalidsessionstatus'] = 'Сессия ProctorCore имеет неподдерживаемый статус ({$a}).';
$string['error:activationbusy'] = 'Сессия ProctorCore сейчас активируется. Повторите попытку.';
$string['error:missingserversession'] = 'ID сессии Server B отсутствует. Сначала выполните раздел 4.1.';
$string['previewbutton'] = 'Предварительный просмотр прокторинга';
$string['preflightdescription'] = 'До создания попытки Moodle учащийся должен пройти автоматическую проверку браузера и оборудования ProctorCore.';
$string['precheckformheader'] = 'Проверки прокторинга';
$string['requireidentity'] = 'Требовать автоматическую проверку личности по лицу';
$string['requireidentity_help'] = 'До создания попытки Moodle сравнивает серию кадров с живой камеры с повторно используемым зашифрованным шаблоном лица учащегося. Результат определяется настроенным правилом несовпадения.';
$string['identitymismatchmode'] = 'Действие при несовпадении личности';
$string['identitymismatchmode_help'] = 'Переопределяет действие организации или платформы, когда оценка лица с камеры ниже порога проверки.';
$string['identitymismatchmode_inherit'] = 'Использовать правило организации или платформы';
$string['identitymismatchmode_block'] = 'Заблокировать вход в тест';
$string['identitymismatchmode_review'] = 'Разрешить вход и потребовать ручную проверку';
$string['identitymismatchmode_fail'] = 'Разрешить вход и отметить прокторинг как непройденный';
$string['identitythreshold'] = 'Переопределение порога проверки личности';
$string['identitythreshold_help'] = 'Оставьте пустым, чтобы использовать порог организации или платформы. Допустимый диапазон: от 0,85 до 1,00.';
$string['error:identitythreshold'] = 'Введите порог проверки личности от 0,85 до 1,00 или оставьте поле пустым.';
$string['error:identitypending'] = 'Обязательная проверка личности не пройдена.';
