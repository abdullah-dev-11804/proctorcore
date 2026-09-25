<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Kazakh strings for quizaccess_proctorcore.
 *
 * @package    quizaccess_proctorcore
 * @copyright  2026 SENTAL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ProctorCore';
$string['privacy:metadata'] = 'ProctorCore тестке қатынау ережесі тест баптауларын local_proctorcore ішінде сақтайды. Сессия және ұйым деректерін local_proctorcore басқарады.';
$string['proctorcore:bypass'] = 'ProctorCore тестке қатынау шектеулерін айналып өту';
$string['settingsheader'] = 'ProctorCore';
$string['enabled'] = 'Осы тест үшін ProctorCore жүйесін қосу';
$string['enabled_help'] = 'Осы тестті local_proctorcore және прокторинг серверімен байланыстырады. Moodle талпынысты жасамас бұрын білім алушы міндетті тексерулерден, растаулардан және жеке басты тексеруден өтеді.';
$string['requirehttps'] = 'HTTPS талап ету';
$string['requirehttps_help'] = 'Сайт HTTPS арқылы ашылмаса, білім алушының қатынауын бұғаттайды. Тек бақыланатын сынақ серверінде өшіріңіз.';
$string['requirecamera'] = 'Камера тексеруін талап ету';
$string['requirecamera_help'] = 'Емтихан алдындағы автоматты тексеру кезінде жұмыс істейтін камераны талап етеді.';
$string['requiremicrophone'] = 'Микрофон тексеруін талап ету';
$string['requiremicrophone_help'] = 'Емтихан алдындағы автоматты тексеру кезінде жұмыс істейтін микрофонды талап етеді.';
$string['requiresnapshot'] = 'Жеке бас кадрын талап ету';
$string['requiresnapshot_help'] = 'Алдын ала тексеру кезінде браузерден камера кадрын түсіруді талап етеді. Бұл түсіру дайындығын растайды; биометриялық салыстыру бөлек орындалады.';
$string['requirerulesack'] = 'Емтихан ережелерін нақты растауды талап ету';
$string['ruleshtml'] = 'Емтихан ережелері';
$string['ruleshtml_help'] = 'Емтихан алдында көрсетілетін ережелер. Қысқа ережелерді | таңбасымен бөліңіз, сонда олар бейімделетін екі бағанды тізім ретінде көрсетіледі. Сессиямен бірге нақты мәтіннің хэші, тіл, пайдаланушы, IP мекенжайы және растау уақыты жазылады.';
$string['error:rulesrequired'] = 'Растау талап етілсе, емтихан ережелерін енгізіңіз.';
$string['recoveryheading'] = '5.3-бөлім — байланысты қалпына келтіру';
$string['allowresume'] = 'Байланыс үзілгеннен кейін сол талпынысқа оралуға рұқсат ету';
$string['allowresume_help'] = 'Білім алушы сол Moodle тест талпынысын қайта аша алады. Бар жауаптар мен бастапқы таймер сақталады.';
$string['resumewindowsecs'] = 'Қайта қосылу аралығы (секунд)';
$string['resumewindowsecs_help'] = 'Сипаттамадағы мән — 600 секунд (10 минут). Рұқсат етілген аралық: 60–3600 секунд.';
$string['timerheading'] = 'Таймер және ескертулер';
$string['timerenabled'] = 'Moodle тест таймерін қосу';
$string['timerenabled_help'] = 'Moodle жүйесінің уақыт шектеуі мен автоматты жіберуін қолданады, сондықтан қайта қосылғанда таймер қалпына келмейді.';
$string['durationminutes'] = 'Емтихан ұзақтығы (минут)';
$string['durationminutes_help'] = 'SPC әдепкі ұзақтығы — 120 минут.';
$string['warningsenabled'] = 'Таймер ескертулерін қосу';
$string['warningsenabled_help'] = 'Қатысушы интерфейсі үшін ескерту уақыттарын сақтайды.';
$string['warningcount'] = 'Ескертулер саны';
$string['warningtimes'] = 'Ескерту уақыттары (қалған минут)';
$string['warningtimes_help'] = 'Үтірмен бөлінген мәндер, мысалы: 15,5.';
$string['durationinvalid'] = 'Емтихан ұзақтығын 1–10080 минут аралығында енгізіңіз.';
$string['warningcountinvalid'] = '1–10 ескерту таңдаңыз.';
$string['warningtimescountmismatch'] = 'Дәл {$a} түрлі ескерту уақытын енгізіңіз.';
$string['warningtimeinvalid'] = 'Әр ескерту уақыты 0-ден үлкен және {$a} минуттық емтихан ұзақтығынан аз болуы керек.';
$string['proctoringenabled'] = 'Осы тест үшін ProctorCore қосылған.';
$string['timerdescription'] = 'Емтихан таймері: {$a} минут.';
$string['warningsdescription'] = '{$a} минут қалғанда ескерту беріледі.';
$string['recoverydescription'] = 'Байланысы үзілген талпынысты сол жауаптармен және бастапқы таймермен {$a} ішінде жалғастыруға болады.';
$string['recoverydisabled'] = 'Осы тест үшін байланысты қалпына келтіру өшірілген.';
$string['httpsrequired'] = 'Бұл прокторинг тесті HTTPS талап етеді. Әкімшіден сайтты қауіпсіз HTTPS мекенжайы арқылы ашуды сұраңыз.';
$string['accessblocked'] = 'Прокторинг талпынысы жалғаса алмайды: {$a}';
$string['accessblockedgeneric'] = 'ProctorCore қолжетімсіз болғандықтан прокторинг талпынысы жалғаса алмайды. Әкімшіге хабарласыңыз.';
$string['statusconnected'] = 'Прокторинг байланысы белсенді';
$string['statusreconnecting'] = 'Интернет қалпына келді. ProctorCore сессиясы тексерілуде…';
$string['statuslost'] = 'Байланыс үзілді. Moodle жауаптары осы талпыныста сақталады; рұқсат етілген уақытта қайта қосылыңыз.';
$string['statusinterrupted'] = 'ProctorCore сессиясы үзілді. Сол тест талпынысын жалғастыру үшін қайта қосылыңыз.';
$string['reconnectbutton'] = 'Қайта қосылып, жалғастыру';
$string['error:invalidresumewindow'] = 'Қайта қосылу аралығы 60–3600 секунд болуы керек.';
$string['error:missinglocalplugin'] = 'local_proctorcore немесе оның 4.1-бөлім API интерфейсі қолжетімсіз.';
$string['error:missingrecoveryapi'] = 'local_proctorcore 1.1-бөлім түсіру немесе 5.3-бөлім қалпына келтіру API интерфейсі қолжетімсіз.';
$string['error:missingconfigtable'] = 'local_proctorcore_quizcfg кестесі жоқ.';
$string['error:missingcmid'] = 'Тест курстық модулінің ID мәнін анықтау мүмкін болмады.';
$string['error:previewnotproctored'] = 'Оқытушының алдын ала қарау талпыныстары бақыланбайды.';
$string['error:attemptnotinprogress'] = 'Бастапқы тест талпынысы енді орындалып жатқан жоқ.';
$string['error:sessionowner'] = 'ProctorCore сессиясы басқа пайдаланушыға тиесілі.';
$string['error:precheckpending'] = 'Емтихан алдындағы міндетті тексерулер аяқталмады.';
$string['error:sessionclosed'] = 'ProctorCore сессиясы жабық ({$a}).';
$string['error:invalidsessionstatus'] = 'ProctorCore сессиясының күйіне қолдау көрсетілмейді ({$a}).';
$string['error:activationbusy'] = 'ProctorCore сессиясы іске қосылуда. Қайта көріңіз.';
$string['error:missingserversession'] = 'Server B сессиясының ID мәні жоқ. Алдымен 4.1-бөлімді аяқтаңыз.';
$string['previewbutton'] = 'Прокторингті алдын ала қарау';
$string['preflightdescription'] = 'Moodle талпынысты жасамас бұрын білім алушы ProctorCore браузері мен құрылғыларының автоматты тексеруінен өтуі керек.';
$string['precheckformheader'] = 'Прокторинг тексерулері';
$string['requireidentity'] = 'Бет арқылы жеке басты автоматты растауды талап ету';
$string['requireidentity_help'] = 'Moodle талпынысты жасамас бұрын тікелей камера кадрларын білім алушының қайта қолданылатын шифрланған бет үлгісімен салыстырады. Нәтижені бапталған сәйкессіздік саясаты анықтайды.';
$string['identitymismatchmode'] = 'Жеке бас сәйкеспегендегі әрекет';
$string['identitymismatchmode_help'] = 'Тікелей бет бағасы тексеру шегінен төмен болғанда ұйым немесе платформа әрекетін қайта анықтайды.';
$string['identitymismatchmode_inherit'] = 'Ұйым немесе платформа саясатын қолдану';
$string['identitymismatchmode_block'] = 'Тестке кіруді бұғаттау';
$string['identitymismatchmode_review'] = 'Кіруге рұқсат беріп, қолмен тексеруді талап ету';
$string['identitymismatchmode_fail'] = 'Кіруге рұқсат беріп, прокторингті сәтсіз деп белгілеу';
$string['identitythreshold'] = 'Жеке басты растау шегін қайта анықтау';
$string['identitythreshold_help'] = 'Ұйым немесе платформа шегін қолдану үшін бос қалдырыңыз. Рұқсат етілген аралық: 0,85–1,00.';
$string['error:identitythreshold'] = 'Жеке басты растау шегін 0,85–1,00 аралығында енгізіңіз немесе бос қалдырыңыз.';
$string['error:identitypending'] = 'Міндетті жеке басты растау өтпеді.';
