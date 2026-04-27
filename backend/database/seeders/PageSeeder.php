<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title' => [
                    'lv' => 'Par klubu',
                    'en' => 'About the Club',
                    'ru' => 'О клубе',
                    'cs' => 'O klubu',
                    'de' => 'Über den Verein',
                ],
                'body' => [
                    'lv' => "Blossfechten Riga ir vēsturisko Eiropas cīņas mākslu (HEMA) klubs, kas dibināts 2022. gadā Rīgā. Mēs esam Fencer's Guild biedri un strādājam pēc Joahima Meijera 1570. gada traktāta 'Grūndlīche Beschreibung der Freyen Ritterlichen und Adelichen Kunst des Fechtens'. Mūsu galvenais ierocis ir garais zobens, bet apmācība ietver arī cīniņu bez ieročiem (Ringen) un citus ieročus.",
                    'en' => "Blossfechten Riga is a Historical European Martial Arts (HEMA) club founded in Riga in 2022. We are members of the Fencer's Guild and train from the 1570 treatise of Joachim Meyer, 'A Thorough Description of the Free, Chivalric, and Noble Art of Fencing'. Our core weapon is the longsword; our curriculum also covers wrestling (Ringen) and additional weapons.",
                    'ru' => "Blossfechten Riga — клуб исторических европейских боевых искусств (HEMA), основанный в Риге в 2022 году. Мы состоим в Гильдии фехтовальщиков и занимаемся по трактату Иоахима Мейера 1570 года. Основное оружие — длинный меч; программа также охватывает борьбу (Ringen) и другое оружие.",
                    'cs' => "Blossfechten Riga je klub historických evropských bojových umění (HEMA), založený v Rize v roce 2022. Jsme členy Fencer's Guild a cvičíme podle traktátu Joachima Meyera z roku 1570. Hlavní zbraní je dlouhý meč; program zahrnuje také zápas (Ringen) a další zbraně.",
                    'de' => "Blossfechten Riga ist ein Verein für Historische Europäische Kampfkünste (HEMA), gegründet 2022 in Riga. Wir sind Mitglieder der Fencer's Guild und trainieren nach dem Traktat von Joachim Meyer aus dem Jahr 1570. Unsere Hauptwaffe ist das Langschwert; das Curriculum umfasst zudem Ringen und weitere Waffen.",
                ],
            ],
            [
                'slug' => 'meyer',
                'title' => [
                    'lv' => 'Meijera skola',
                    'en' => 'The Meyer School',
                    'ru' => 'Школа Мейера',
                    'cs' => 'Meyerova škola',
                    'de' => 'Die Meyer-Schule',
                ],
                'body' => [
                    'lv' => "Joahims Meijers (1537–1571) bija Strasbūras Frībruderu zobentehniķu meistars. Viņa 1570. gada darbs ir visplašākā 16. gadsimta vācu fehtbuhas tradīcijas apkopojums. Mūsu programma aptver četrus zobena apguves līmeņus: Skolnieks, Brīvais Skolnieks, Instruktors un Fehtmeisters. Papildus garajam zobenam apmācām dusaku, rapīru, kāju, halebardi un cīniņu bez ieročiem.",
                    'en' => "Joachim Meyer (1537–1571) was a master of the Strasbourg Freifechter. His 1570 compendium is the most comprehensive single-volume survey of the 16th-century German Fechtbuch tradition. Our curriculum advances through four stages: Scholar, Free Scholar, Instructor, and Fechtmeister. Beyond the longsword, students study dussack, rapier, staff, halberd, and Ringen.",
                    'ru' => "Иоахим Мейер (1537–1571) — мастер страсбургских Freifechter. Его компендиум 1570 года — самый полный однотомный обзор немецкой традиции Fechtbuch XVI века. Программа состоит из четырёх ступеней: Ученик, Свободный ученик, Инструктор и Фехтмейстер. Помимо длинного меча изучаются дюсак, рапира, шест, алебарда и Ringen.",
                    'cs' => "Joachim Meyer (1537–1571) byl mistrem štrasburských Freifechter. Jeho kompendium z roku 1570 je nejucelenější jednosvazkový přehled německé fechtbuchové tradice 16. století. Program má čtyři stupně: Žák, Svobodný žák, Instruktor a Fechtmistr. Kromě dlouhého meče studenti trénují dusák, rapír, hůl, halapartnu a Ringen.",
                    'de' => "Joachim Meyer (1537–1571) war Meister der Straßburger Freifechter. Sein Kompendium von 1570 ist die umfassendste einbändige Darstellung der deutschen Fechtbuchtradition des 16. Jahrhunderts. Unser Curriculum durchläuft vier Stufen: Schüler, Freischüler, Lehrer und Fechtmeister. Neben dem Langschwert werden Dussack, Rapier, Stab, Hellebarde und Ringen geübt.",
                ],
            ],
            [
                'slug' => 'curriculum-intro',
                'title' => [
                    'lv' => 'Mācību programma',
                    'en' => 'Curriculum',
                    'ru' => 'Программа',
                    'cs' => 'Učební plán',
                    'de' => 'Lehrplan',
                ],
                'body' => [
                    'lv' => 'I.–III. līmenis ir formāli pārbaudāmais programmas kodols, ko apgūst aptuveni trijos gados nopietnu studiju. IV.–V. līmenis ir garākais ceļš uz meistarību un pienākums mācīt tālāk.',
                    'en' => 'Levels I–III are the formally examined core of the curriculum, completed in roughly three years of dedicated study. Levels IV–V mark the longer path to mastery and the duty to teach.',
                    'ru' => 'Уровни I–III — формально экзаменуемое ядро программы, осваиваемое примерно за три года последовательного обучения. Уровни IV–V — более долгий путь к мастерству и обязанность учить.',
                    'cs' => 'Úrovně I.–III. tvoří formálně zkoušené jádro plánu, které se zvládá zhruba za tři roky soustavného studia. Úrovně IV.–V. představují delší cestu k mistrovství a povinnost umění předávat.',
                    'de' => 'Stufen I–III bilden den formal geprüften Kern des Lehrplans und werden in etwa drei Jahren ernsthaften Studiums erarbeitet. Stufen IV–V markieren den längeren Weg zur Meisterschaft und die Pflicht, weiterzugeben.',
                ],
            ],
            [
                'slug' => 'curriculum-i',
                'title' => [
                    'lv' => 'Iesācējs',
                    'en' => 'Novice',
                    'ru' => 'Новичок',
                    'cs' => 'Nováček',
                    'de' => 'Anfänger',
                ],
                'body' => [
                    'lv' => 'Stājas, kāju darbs, četri atvērumi. Droša vingrināšanās ar federu.',
                    'en' => 'Stance, footwork, the four openings. Safe drilling with the feder.',
                    'ru' => 'Стойки, работа ног, четыре открытия. Безопасные упражнения с федером.',
                    'cs' => 'Postoje, práce nohou, čtyři otevření. Bezpečné cvičení s federem.',
                    'de' => 'Stand, Schritt, die vier Blößen. Sicheres Üben mit dem Feder.',
                ],
            ],
            [
                'slug' => 'curriculum-ii',
                'title' => [
                    'lv' => 'Skolnieks',
                    'en' => 'Scholar',
                    'ru' => 'Ученик',
                    'cs' => 'Žák',
                    'de' => 'Schüler',
                ],
                'body' => [
                    'lv' => 'Meijera Zornhau, Krumphau, Zwerchhau, Schielhau un Scheitelhau.',
                    'en' => 'Meyer’s Zornhau, Krumphau, Zwerchhau, Schielhau and Scheitelhau.',
                    'ru' => 'Удары Мейера: Zornhau, Krumphau, Zwerchhau, Schielhau и Scheitelhau.',
                    'cs' => 'Meyerovy seky Zornhau, Krumphau, Zwerchhau, Schielhau a Scheitelhau.',
                    'de' => 'Meyers Zornhau, Krumphau, Zwerchhau, Schielhau und Scheitelhau.',
                ],
            ],
            [
                'slug' => 'curriculum-iii',
                'title' => [
                    'lv' => 'Brīvais skolnieks',
                    'en' => 'Free Scholar',
                    'ru' => 'Свободный ученик',
                    'cs' => 'Svobodný žák',
                    'de' => 'Freischüler',
                ],
                'body' => [
                    'lv' => 'Roku darbs, vīšana un Nach māksla. Dusaks.',
                    'en' => 'Handwork, winding, and the art of the Nach. Dussack.',
                    'ru' => 'Работа рук, винтование и искусство Nach. Дюсак.',
                    'cs' => 'Práce rukou, vinutí a umění Nach. Dusák.',
                    'de' => 'Handarbeit, Winden und die Kunst des Nach. Dussack.',
                ],
            ],
            [
                'slug' => 'curriculum-iv',
                'title' => [
                    'lv' => 'Instruktors',
                    'en' => 'Instructor',
                    'ru' => 'Инструктор',
                    'cs' => 'Instruktor',
                    'de' => 'Lehrer',
                ],
                'body' => [
                    'lv' => 'Rapīra, kājs un halebarde. Mācīšanas metodika. Ringen.',
                    'en' => 'Rapier, staff and halberd. Teaching methodology. Ringen.',
                    'ru' => 'Рапира, шест и алебарда. Методика преподавания. Ringen.',
                    'cs' => 'Rapír, hůl a halapartna. Metodika výuky. Ringen.',
                    'de' => 'Rapier, Stab und Hellebarde. Unterrichtsmethodik. Ringen.',
                ],
            ],
            [
                'slug' => 'curriculum-v',
                'title' => [
                    'lv' => 'Fehtmeisters',
                    'en' => 'Fechtmeister',
                    'ru' => 'Фехтмейстер',
                    'cs' => 'Fechtmistr',
                    'de' => 'Fechtmeister',
                ],
                'body' => [
                    'lv' => 'Pilnā traktāta meistarība un pienākums to nodot tālāk.',
                    'en' => 'Mastery of the full treatise and the duty to pass it on.',
                    'ru' => 'Владение полным трактатом и обязанность передать его.',
                    'cs' => 'Mistrovství celého traktátu a povinnost jej předávat dál.',
                    'de' => 'Meisterschaft des gesamten Traktats und die Pflicht, es weiterzugeben.',
                ],
            ],
            [
                'slug' => 'home-hero',
                'title' => [
                    'lv' => 'Garā zobena māksla',
                    'en' => 'The Art of the Longsword',
                    'ru' => 'Искусство длинного меча',
                    'cs' => 'Umění dlouhého meče',
                    'de' => 'Die Kunst des Langschwerts',
                ],
                'body' => [
                    'lv' => 'Vēsturiskās Eiropas cīņas mākslas Rīgā kopš 2022. gada pēc Joahima Meijera 1570. gada traktāta.',
                    'en' => 'Historical European martial arts in Riga since 2022, trained from the 1570 treatise of Joachim Meyer.',
                    'ru' => 'Исторические европейские боевые искусства в Риге с 2022 года — по трактату Иоахима Мейера 1570 года.',
                    'cs' => 'Historická evropská bojová umění v Rize od roku 2022, cvičíme podle traktátu Joachima Meyera z roku 1570.',
                    'de' => 'Historische europäische Kampfkünste in Riga seit 2022, nach dem Traktat Joachim Meyers von 1570.',
                ],
            ],
            [
                'slug' => 'home-identity',
                'title' => [
                    'lv' => 'Brīvā, bruņnieciskā un cēlā paukošanās māksla — atdzimusi Rīgā.',
                    'en' => 'The free, chivalric, and noble art of fencing — reborn in Riga.',
                    'ru' => 'Свободное, рыцарское и благородное искусство фехтования — возрождённое в Риге.',
                    'cs' => 'Svobodné, rytířské a ušlechtilé umění šermu — znovuzrozené v Rize.',
                    'de' => 'Die freie, ritterliche und adelige Kunst des Fechtens — wiedergeboren in Riga.',
                ],
                'body' => [
                    'lv' => 'Mēs esam specializēts HEMA klubs, kas dziļi pēta tieši Joahima Meijera 1570. gada garā zobena traktātu — nevis vispārēja vēsturiskā cīņas māksla, bet viens avots, pētīts rūpīgi un apzinīgi.',
                    'en' => 'We are a specialist HEMA club focused exclusively on the 1570 longsword treatise of Joachim Meyer — one source, studied deeply and honestly, not general historical fencing.',
                    'ru' => 'Мы — специализированный HEMA-клуб, сосредоточенный исключительно на трактате Иоахима Мейера 1570 года о длинном мече. Один источник, изучаемый глубоко и добросовестно — без смешения стилей.',
                    'cs' => 'Jsme specializovaný HEMA klub zaměřený výhradně na traktát Joachima Meyera o dlouhém meči z roku 1570 — jeden pramen, studovaný poctivě a do hloubky, nikoliv obecné historické šermy.',
                    'de' => 'Wir sind ein spezialisierter HEMA-Verein, der sich ausschließlich dem Langschwert-Traktat von Joachim Meyer (1570) widmet — eine Quelle, gründlich und ehrlich studiert, kein allgemeines historisches Fechten.',
                ],
            ],
            [
                'slug' => 'home-pillar-tradition',
                'title' => [
                    'lv' => 'Dzīva tradīcija',
                    'en' => 'A living tradition',
                    'ru' => 'Живая традиция',
                    'cs' => 'Živá tradice',
                    'de' => 'Lebende Tradition',
                ],
                'body' => [
                    'lv' => '16. gadsimta traktāti — garais zobens, dusaks, rapīra, kājs un Ringen.',
                    'en' => 'Treatises of the 16th century — longsword, dussack, rapier, staff, and Ringen.',
                    'ru' => 'Трактаты XVI века — длинный меч, дюсак, рапира, шест и Ringen.',
                    'cs' => 'Traktáty 16. století — dlouhý meč, dusák, rapír, hůl a Ringen.',
                    'de' => 'Traktate des 16. Jahrhunderts — Langschwert, Dussack, Rapier, Stab und Ringen.',
                ],
            ],
            [
                'slug' => 'home-pillar-progression',
                'title' => [
                    'lv' => 'Strukturēta izaugsme',
                    'en' => 'Structured progression',
                    'ru' => 'Ступенчатая программа',
                    'cs' => 'Strukturovaný postup',
                    'de' => 'Strukturierter Weg',
                ],
                'body' => [
                    'lv' => 'Četri līmeņi no Skolnieka līdz Fehtmeistaram. Ilgtermiņa studijas, nevis vingrošanas nodarbība.',
                    'en' => 'Four stages from Scholar to Fechtmeister. Long-term study, not a fitness class.',
                    'ru' => 'Четыре ступени — от Ученика до Фехтмейстера. Долгое обучение, а не фитнес.',
                    'cs' => 'Čtyři stupně od Žáka k Fechtmistrovi. Dlouhodobé studium, ne fitness.',
                    'de' => 'Vier Stufen vom Schüler zum Fechtmeister. Langfristiges Studium, kein Fitnesskurs.',
                ],
            ],
            [
                'slug' => 'home-pillar-community',
                'title' => [
                    'lv' => 'Atvērta ģilde',
                    'en' => 'An open guild',
                    'ru' => 'Открытая гильдия',
                    'cs' => 'Otevřený cech',
                    'de' => 'Offene Zunft',
                ],
                'body' => [
                    'lv' => 'Fencer’s Guild biedri. Iesācēji un pieredzējuši fehtētāji laipni gaidīti.',
                    'en' => 'Members of the Fencer’s Guild. Beginners and experienced fencers are welcome.',
                    'ru' => 'Члены Гильдии фехтовальщиков. Новички и опытные — добро пожаловать.',
                    'cs' => 'Členové Fencer’s Guild. Začátečníci i zkušení šermíři vítáni.',
                    'de' => 'Mitglieder der Fencer’s Guild. Anfänger und erfahrene Fechter sind willkommen.',
                ],
            ],
            [
                'slug' => 'home-cta',
                'title' => [
                    'lv' => 'Atnāc uz treniņu',
                    'en' => 'Come to a training',
                    'ru' => 'Приходите на тренировку',
                    'cs' => 'Přijďte na trénink',
                    'de' => 'Zum Training kommen',
                ],
                'body' => [
                    'lv' => 'Atnāciet uz treniņu un iepazīstiet klubu. Nāciet ērtā apģērbā un treniņa apavos — ekipējumu nodrošinām. Dalība ir pēc ielūguma pēc dažiem apmeklējumiem.',
                    'en' => 'Visit a training to meet the club. Wear comfortable clothes and training shoes — we provide gear. Membership is by invitation after a few visits.',
                    'ru' => 'Приходите на тренировку и познакомьтесь с клубом. Удобная одежда и спортивная обувь — снаряжение мы выдадим. Членство — по приглашению после нескольких посещений.',
                    'cs' => 'Přijďte na trénink a poznejte klub. Pohodlné oblečení a sálovou obuv — vybavení zapůjčíme. Členství je na pozvání po několika návštěvách.',
                    'de' => 'Kommen Sie zum Training und lernen Sie den Verein kennen. Bequeme Kleidung und Hallenschuhe — Ausrüstung stellen wir. Die Mitgliedschaft erfolgt nach einigen Besuchen auf Einladung.',
                ],
            ],
            [
                'slug' => 'home-cta-note',
                'title' => [
                    'lv' => 'Pirmais treniņš ir bez maksas.',
                    'en' => 'Your first training is free.',
                    'ru' => 'Первая тренировка бесплатна.',
                    'cs' => 'První trénink je zdarma.',
                    'de' => 'Das erste Training ist kostenlos.',
                ],
                'body' => [
                    'lv' => '', 'en' => '', 'ru' => '', 'cs' => '', 'de' => '',
                ],
            ],
            [
                'slug' => 'contact-header',
                'title' => [
                    'lv' => 'Kontakti', 'en' => 'Contact', 'ru' => 'Контакты', 'cs' => 'Kontakt', 'de' => 'Kontakt',
                ],
                'body' => [
                    'lv' => 'Atnāciet uz treniņu un iepazīstiet klubu. Nāciet ērtā apģērbā un treniņa apavos — ekipējumu nodrošinām. Dalība ir pēc ielūguma pēc dažiem apmeklējumiem.',
                    'en' => 'Visit a training to meet the club. Wear comfortable clothes and training shoes — we provide gear. Membership is by invitation after a few visits.',
                    'ru' => 'Приходите на тренировку и познакомьтесь с клубом. Удобная одежда и спортивная обувь — снаряжение мы выдадим. Членство — по приглашению после нескольких посещений.',
                    'cs' => 'Přijďte na trénink a poznejte klub. Pohodlné oblečení a sálovou obuv — vybavení zapůjčíme. Členství je na pozvání po několika návštěvách.',
                    'de' => 'Kommen Sie zum Training und lernen Sie den Verein kennen. Bequeme Kleidung und Hallenschuhe — Ausrüstung stellen wir. Die Mitgliedschaft erfolgt nach einigen Besuchen auf Einladung.',
                ],
            ],
            [
                'slug' => 'contact-come',
                'title' => [
                    'lv' => 'Atnāc uz treniņu', 'en' => 'Come to a training', 'ru' => 'Приходите на тренировку', 'cs' => 'Přijďte na trénink', 'de' => 'Zum Training kommen',
                ],
                'body' => [
                    'lv' => 'Pirmais treniņš ir bez maksas.', 'en' => 'Your first training is free.', 'ru' => 'Первая тренировка бесплатна.', 'cs' => 'První trénink je zdarma.', 'de' => 'Das erste Training ist kostenlos.',
                ],
            ],
            [
                'slug' => 'contact-follow',
                'title' => [
                    'lv' => 'Seko mums', 'en' => 'Follow us', 'ru' => 'Мы в соцсетях', 'cs' => 'Sledujte nás', 'de' => 'Folgen Sie uns',
                ],
                'body' => [ 'lv' => '', 'en' => '', 'ru' => '', 'cs' => '', 'de' => '' ],
            ],
            [
                'slug' => 'contact-form-intro',
                'title' => [
                    'lv' => 'Sūti ziņu', 'en' => 'Send us a message', 'ru' => 'Написать нам', 'cs' => 'Pošlete nám zprávu', 'de' => 'Schreiben Sie uns',
                ],
                'body' => [
                    'lv' => 'Ja jums ir jautājumi pirms apmeklējuma, rakstiet — mēs drīz atbildēsim.',
                    'en' => 'If you have questions before visiting, write to us and we will reply soon.',
                    'ru' => 'Если у вас есть вопросы перед посещением, напишите нам — скоро ответим.',
                    'cs' => 'Pokud máte před návštěvou dotazy, napište nám — brzy odpovíme.',
                    'de' => 'Wenn Sie Fragen vor dem Besuch haben, schreiben Sie uns — wir antworten bald.',
                ],
            ],
            [
                'slug' => 'members-header',
                'title' => [
                    'lv' => 'Galvenie biedri', 'en' => 'Core Members', 'ru' => 'Основные участники', 'cs' => 'Hlavní členové', 'de' => 'Kernmitglieder',
                ],
                'body' => [
                    'lv' => 'Fehtētāji, kas tur namu.', 'en' => 'The fencers who hold up the house.', 'ru' => 'Фехтовальщики, на которых стоит клуб.', 'cs' => 'Šermíři, kteří drží dům.', 'de' => 'Die Fechter, die das Haus tragen.',
                ],
            ],
            [
                'slug' => 'members-fallback',
                'title' => [ 'lv' => '', 'en' => '', 'ru' => '', 'cs' => '', 'de' => '' ],
                'body' => [
                    'lv' => 'Detalizēti apraksti drīzumā. Iepazīstiet klubu klātienē treniņā.',
                    'en' => 'Detailed bios are coming soon. Meet the club in person at a training.',
                    'ru' => 'Подробные биографии скоро появятся. Познакомьтесь с клубом лично на тренировке.',
                    'cs' => 'Podrobné medailonky brzy. Klub poznáte osobně na tréninku.',
                    'de' => 'Ausführliche Porträts folgen. Lernen Sie den Verein persönlich beim Training kennen.',
                ],
            ],
            [
                'slug' => 'members-instructors',
                'title' => [ 'lv' => 'Instruktori', 'en' => 'Instructors', 'ru' => 'Инструкторы', 'cs' => 'Instruktoři', 'de' => 'Lehrer' ],
                'body' => [ 'lv' => '', 'en' => '', 'ru' => '', 'cs' => '', 'de' => '' ],
            ],
            [
                'slug' => 'members-other',
                'title' => [ 'lv' => 'Biedri', 'en' => 'Members', 'ru' => 'Участники', 'cs' => 'Členové', 'de' => 'Mitglieder' ],
                'body' => [ 'lv' => '', 'en' => '', 'ru' => '', 'cs' => '', 'de' => '' ],
            ],
            [
                'slug' => 'schedule-header',
                'title' => [ 'lv' => 'Treniņi', 'en' => 'Training', 'ru' => 'Тренировки', 'cs' => 'Tréninky', 'de' => 'Training' ],
                'body' => [
                    'lv' => 'Pirmais treniņš ir bez maksas.', 'en' => 'Your first training is free.', 'ru' => 'Первая тренировка бесплатна.', 'cs' => 'První trénink je zdarma.', 'de' => 'Das erste Training ist kostenlos.',
                ],
            ],
            [
                'slug' => 'schedule-regular',
                'title' => [ 'lv' => 'Pastāvīgais grafiks', 'en' => 'Regular schedule', 'ru' => 'Постоянное расписание', 'cs' => 'Pravidelný rozvrh', 'de' => 'Regelmäßiger Plan' ],
                'body' => [ 'lv' => '', 'en' => '', 'ru' => '', 'cs' => '', 'de' => '' ],
            ],
            [
                'slug' => 'schedule-upcoming',
                'title' => [ 'lv' => 'Tuvākās nodarbības', 'en' => 'Upcoming sessions', 'ru' => 'Ближайшие занятия', 'cs' => 'Nadcházející tréninky', 'de' => 'Kommende Einheiten' ],
                'body' => [
                    'lv' => 'Pagaidām nav ieplānotu nodarbību — lūdzu, atgriezieties drīz.',
                    'en' => 'No sessions scheduled yet — please check back soon.',
                    'ru' => 'Пока запланированных занятий нет — загляните позже.',
                    'cs' => 'Zatím nejsou naplánovány žádné tréninky — vraťte se brzy.',
                    'de' => 'Bisher sind keine Einheiten geplant — schauen Sie bald wieder vorbei.',
                ],
            ],
        ];

        foreach ($pages as $p) {
            Page::firstOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
