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
                    'lv' => "Blossfechten Riga ir vēsturisko Eiropas cīņas mākslu klubs, kas dibināts 2022. gadā Rīgā. Mēs esam Fencer's Guild biedri un strādājam pēc Joahima Meijera 1570. gada traktāta 'Grūndlīche Beschreibung der Freyen Ritterlichen und Adelichen Kunst des Fechtens'. Mūsu galvenais ierocis ir garais zobens, bet apmācība ietver arī cīniņu bez ieročiem (Ringen) un citus ieročus.",
                    'en' => "Blossfechten Riga is a Historical European Martial Arts club founded in Riga in 2022. We are members of the Fencer's Guild and train from the 1570 treatise of Joachim Meyer, 'A Thorough Description of the Free, Chivalric, and Noble Art of Fencing'. Our core weapon is the longsword; our curriculum also covers wrestling (Ringen) and additional weapons.",
                    'ru' => "Blossfechten Riga — клуб исторических европейских боевых искусств, основанный в Риге в 2022 году. Мы состоим в Гильдии фехтовальщиков и занимаемся по трактату Иоахима Мейера 1570 года. Основное оружие — длинный меч; программа также охватывает борьбу (Ringen) и другое оружие.",
                    'cs' => "Blossfechten Riga je klub historických evropských bojových umění, založený v Rize v roce 2022. Jsme členy Fencer's Guild a cvičíme podle traktátu Joachima Meyera z roku 1570. Hlavní zbraní je dlouhý meč; program zahrnuje také zápas (Ringen) a další zbraně.",
                    'de' => "Blossfechten Riga ist ein Verein für Historische Europäische Kampfkünste, gegründet 2022 in Riga. Wir sind Mitglieder der Fencer's Guild und trainieren nach dem Traktat von Joachim Meyer aus dem Jahr 1570. Unsere Hauptwaffe ist das Langschwert; das Curriculum umfasst zudem Ringen und weitere Waffen.",
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
                    'lv' => 'Mēs esam specializēts klubs, kas dziļi pēta tieši Joahima Meijera 1570. gada garā zobena traktātu — nevis vispārēja vēsturiskā cīņas māksla, bet viens avots, pētīts rūpīgi un apzinīgi.',
                    'en' => 'We are a specialist club focused exclusively on the 1570 longsword treatise of Joachim Meyer — one source, studied deeply and honestly, not general historical fencing.',
                    'ru' => 'Мы — специализированный клуб, сосредоточенный исключительно на трактате Иоахима Мейера 1570 года о длинном мече. Один источник, изучаемый глубоко и добросовестно — без смешения стилей.',
                    'cs' => 'Jsme specializovaný klub zaměřený výhradně na traktát Joachima Meyera o dlouhém meči z roku 1570 — jeden pramen, studovaný poctivě a do hloubky, nikoliv obecné historické šermy.',
                    'de' => 'Wir sind ein spezialisierter Verein, der sich ausschließlich dem Langschwert-Traktat von Joachim Meyer (1570) widmet — eine Quelle, gründlich und ehrlich studiert, kein allgemeines historisches Fechten.',
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
                'slug' => 'forum-header',
                'title' => [ 'lv' => 'Forums', 'en' => 'Forum', 'ru' => 'Форум', 'cs' => 'Fórum', 'de' => 'Forum' ],
                'body' => [
                    'lv' => 'Ziņas, paziņojumi un noderīgi kluba ieraksti.',
                    'en' => 'News, announcements, and useful posts from the club.',
                    'ru' => 'Новости, объявления и полезные публикации клуба.',
                    'cs' => 'Novinky, oznámení a užitečné příspěvky klubu.',
                    'de' => 'Neuigkeiten, Ankündigungen und nützliche Beiträge des Vereins.',
                ],
            ],
            [
                'slug' => 'schedule-regular',
                'title' => [ 'lv' => 'Pastāvīgais grafiks', 'en' => 'Regular schedule', 'ru' => 'Постоянное расписание', 'cs' => 'Pravidelný rozvrh', 'de' => 'Regelmäßiger Plan' ],
                'body' => [ 'lv' => '', 'en' => '', 'ru' => '', 'cs' => '', 'de' => '' ],
            ],
            [
                'slug' => 'regular-schedule',
                'title' => [ 'lv' => '', 'en' => '', 'ru' => '', 'cs' => '', 'de' => '' ],
                'body' => [
                    'slots' => [
                        ['day' => 'monday', 'start' => '18:00', 'end' => '20:00'],
                        ['day' => 'wednesday', 'start' => '18:00', 'end' => '20:00'],
                        ['day' => 'friday', 'start' => '18:00', 'end' => '20:00'],
                        ['day' => 'saturday', 'start' => '11:00', 'end' => '14:00'],
                        ['day' => 'sunday', 'start' => '11:00', 'end' => '13:00'],
                    ],
                ],
            ],
            [
                'slug' => 'guild-membership',
                'title' => [
                    'lv' => 'Blossfechten Riga ir šīs organizācijas biedrs:',
                    'en' => 'Blossfechten Riga is a member of the',
                    'ru' => 'Blossfechten Riga является членом',
                    'cs' => 'Blossfechten Riga je členem',
                    'de' => 'Blossfechten Riga ist Mitglied der',
                ],
                'body' => [
                    'lv' => 'Mēs trenējamies zem Paukotāju ģildes karoga — starptautiska skolu tīkla, kas veltīts vēsturisko Eiropas cīņas mākslu godīgai izpētei. Mūsu programma, standarti un pārbaudījumi seko Ģildes tradīcijai.',
                    'en' => "We train under the banner of the Fencer's Guild — an international network of schools committed to the honest study of historical European martial arts. Our curriculum, standards, and examinations follow the Guild tradition.",
                    'ru' => 'Мы тренируемся под знаменем Гильдии фехтовальщиков — международной сети школ, посвящённых честному изучению исторических европейских боевых искусств. Наша программа, стандарты и экзамены следуют традиции Гильдии.',
                    'cs' => 'Trénujeme pod hlavičkou Šermířské gildy — mezinárodní sítě škol oddaných poctivému studiu historických evropských bojových umění. Náš program, standardy a zkoušky se řídí tradicí Gildy.',
                    'de' => 'Wir trainieren unter dem Banner der Fechtergilde — einem internationalen Netzwerk von Schulen, die dem ehrlichen Studium historischer europäischer Kampfkünste gewidmet sind. Unser Lehrplan, unsere Standards und Prüfungen folgen der Tradition der Gilde.',
                ],
            ],
            [
                'slug' => 'guild-intro',
                'title' => [
                    'lv' => 'Paukotāju ģilde',
                    'en' => "The Fencer's Guild",
                    'ru' => 'Гильдия фехтовальщиков',
                    'cs' => 'Šermířská gilda',
                    'de' => 'Die Fechtergilde',
                ],
                'body' => [
                    'lv' => "Paukotāju ģilde (Guildam Gladiatorum) tika dibināta 2010. gadā kā Sv. Mihaēla paukošanās ģilde, veltīta cīņas mākslu izpētei un praktizēšanai, balstoties uz oriģinālajiem rakstītajiem avotiem no 14. līdz 17. gadsimtam. Ģilde darbojas kā starptautisks tīkls ar skolām Vācijā, Čehijā, Latvijā un Brazīlijā. Simtiem studentu no Eiropas un Āzijas valstīm ir piedalījušies.",
                    'en' => "The Fencer's Guild (Guildam Gladiatorum) was established in 2010 as St. Michael's Fencing Guild, dedicated to researching and practicing martial arts based on original written sources of the 14th to 17th centuries. The guild operates as an international network with schools across Germany, Czech Republic, Latvia, and Brazil. Hundreds of students from European and Asian countries have participated.",
                    'ru' => 'Гильдия фехтовальщиков (Guildam Gladiatorum) была основана в 2010 году как Гильдия Св. Михаила, посвящённая изучению и практике боевых искусств на основе оригинальных письменных источников XIV–XVII веков. Гильдия действует как международная сеть со школами в Германии, Чехии, Латвии и Бразилии. В ней приняли участие сотни учеников из европейских и азиатских стран.',
                    'cs' => 'Šermířská gilda (Guildam Gladiatorum) byla založena v roce 2010 jako Gilda Sv. Michaela, věnovaná výzkumu a praxi bojových umění na základě původních písemných pramenů 14. až 17. století. Gilda funguje jako mezinárodní síť se školami v Německu, České republice, Lotyšsku a Brazílii. Zúčastnily se jí stovky studentů z evropských a asijských zemí.',
                    'de' => 'Die Fechtergilde (Guildam Gladiatorum) wurde 2010 als St. Michaels Fechtergilde gegründet, gewidmet der Erforschung und Ausübung von Kampfkünsten auf Grundlage originaler schriftlicher Quellen des 14. bis 17. Jahrhunderts. Die Gilde arbeitet als internationales Netzwerk mit Schulen in Deutschland, Tschechien, Lettland und Brasilien. Hunderte Schüler aus europäischen und asiatischen Ländern haben teilgenommen.',
                ],
            ],
            [
                'slug' => 'guild-philosophy',
                'title' => [
                    'lv' => 'Filozofija un treniņi',
                    'en' => 'Philosophy & Training',
                    'ru' => 'Философия и тренировки',
                    'cs' => 'Filozofie a trénink',
                    'de' => 'Philosophie & Training',
                ],
                'body' => [
                    'lv' => 'Ģilde uzsver drošu, kontrolētu paukošanos bez aizsargekipējuma. Praktizētāji sit droši un kontrolēti, izvairoties no bīstamiem dūrieniem sejā. Visiem jāsaglabā cieņa un draudzība, kas ir bruņniecības un mākslas kronis. Cīņa turpinās, līdz viens dalībnieks padodas, un intensitāte tiek pielāgota savstarpējam komfortam.',
                    'en' => 'The guild emphasizes safe, controlled fencing without protective equipment. Practitioners strike in a safe and controlled manner while avoiding dangerous thrusts to the face. All must maintain consideration and friendship, which is the crown of chivalry and art. Combat continues until one participant surrenders, with intensity adjusted to mutual comfort levels.',
                    'ru' => 'Гильдия делает упор на безопасное, контролируемое фехтование без защитного снаряжения. Практикующие наносят удары безопасно и контролируемо, избегая опасных уколов в лицо. Все должны соблюдать уважение и дружбу, которые являются венцом рыцарства и искусства. Бой продолжается до сдачи одного из участников, а интенсивность регулируется по взаимному согласию.',
                    'cs' => 'Gilda klade důraz na bezpečný, kontrolovaný šerm bez ochranného vybavení. Praktikanti vedou údery bezpečně a kontrolovaně, vyhýbají se nebezpečným bodům do obličeje. Všichni musí zachovávat ohleduplnost a přátelství, které jsou korunou rytířství a umění. Boj pokračuje, dokud se jeden z účastníků nevzdá, přičemž intenzita je přizpůsobena vzájemnému komfortu.',
                    'de' => 'Die Gilde betont sicheres, kontrolliertes Fechten ohne Schutzausrüstung. Praktizierende führen Hiebe sicher und kontrolliert aus und vermeiden gefährliche Stöße ins Gesicht. Alle müssen Rücksicht und Freundschaft wahren, die Krone der Ritterlichkeit und Kunst. Der Kampf geht weiter, bis ein Teilnehmer aufgibt, wobei die Intensität dem gegenseitigen Komfort angepasst wird.',
                ],
            ],
            [
                'slug' => 'guild-code',
                'title' => [
                    'lv' => 'Uzvedības kodekss',
                    'en' => 'Code of Conduct',
                    'ru' => 'Кодекс поведения',
                    'cs' => 'Kodex chování',
                    'de' => 'Verhaltenskodex',
                ],
                'body' => [
                    'lv' => 'Biedriem jāiemieso bruņnieciskās vērtības un jāuztur ģildes gods. Dalībai nepieciešams nokārtot ģildes pārbaudījumu un dot zvērestu uz zobena. Pārkāpumi, ieskaitot negodīgumu, zādzību un vielu ļaunprātīgu lietošanu, noved pie dalības izbeigšanas.',
                    'en' => 'Members must embody chivalric virtues and uphold the honour of the guild. Membership requires passing a guild test and taking the sword oath. Violations including dishonesty, theft, and substance abuse result in membership termination.',
                    'ru' => 'Члены гильдии должны воплощать рыцарские добродетели и поддерживать честь гильдии. Для вступления необходимо пройти гильдейское испытание и принести клятву на мече. Нарушения, включая нечестность, кражу и злоупотребление веществами, ведут к исключению.',
                    'cs' => 'Členové musí ztělesňovat rytířské ctnosti a hájit čest gildy. Členství vyžaduje složení gildovní zkoušky a přísahu na meč. Porušení včetně nečestnosti, krádeže a zneužívání návykových látek vede k ukončení členství.',
                    'de' => 'Mitglieder müssen ritterliche Tugenden verkörpern und die Ehre der Gilde wahren. Die Mitgliedschaft erfordert das Bestehen einer Gildenprüfung und den Schwerteid. Verstöße einschließlich Unehrlichkeit, Diebstahl und Substanzmissbrauch führen zur Beendigung der Mitgliedschaft.',
                ],
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
            $p['published'] = true;
            Page::firstOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
