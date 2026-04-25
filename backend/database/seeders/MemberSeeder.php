<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'full_name'     => 'Artūrs Serafims Vītiņš',
                'role_title'    => 'Founder & Head Instructor',
                'rank'          => 'Fechtmeister',
                'sort_order'    => 1,
                'is_instructor' => true,
                'bio' => [
                    'lv' => 'Kluba dibinātājs un galvenais instruktors. Vada treniņus pēc Joahima Meijera traktāta, specializējies garajā zobenā un cīniņā bez ieročiem. Piedalījies vairākos HEMA turnīros Baltijā un Skandināvijā.',
                    'en' => 'Founder and head instructor. Leads training based on the treatise of Joachim Meyer, specialising in longsword and unarmed grappling. Has competed at HEMA tournaments across the Baltic and Scandinavia.',
                    'ru' => 'Основатель клуба и главный инструктор. Ведёт тренировки по трактату Иоахима Мейера, специализируется на длинном мече и борьбе. Участвовал в турнирах по HEMA в странах Балтии и Скандинавии.',
                    'cs' => 'Zakladatel klubu a hlavní instruktor. Vede výuku podle traktátu Joachima Meyera se zaměřením na dlouhý meč a zápas. Účastnil se turnajů HEMA po celém Pobaltí a Skandinávii.',
                    'de' => 'Gründer und leitender Ausbilder. Unterrichtet nach dem Traktat Joachim Meyers mit Schwerpunkt auf Langschwert und Ringen. Teilnahme an HEMA-Turnieren im Baltikum und in Skandinavien.',
                    'lineage' => [
                        'en' => 'Independent study of Joachim Meyer (1570), Joachim Mayer (1600), and Ringeck glosses. Seminar training with instructors from GHFS (Sweden) and Schola Gladiatoria (UK).',
                        'lv' => 'Patstāvīgas studijas pēc Joahima Meijera (1570), Joahima Majera (1600) un Ringeka komentāriem. Semināri ar GHFS (Zviedrija) un Schola Gladiatoria (Lielbritānija) instruktoriem.',
                    ],
                    'seminars' => [
                        'en' => 'Nordic HEMA Open (2023, 2024), Swordfish (2023), Baltic Sword (2022–2025)',
                        'lv' => 'Nordic HEMA Open (2023, 2024), Swordfish (2023), Baltic Sword (2022–2025)',
                    ],
                ],
            ],
            [
                'full_name'     => 'Dastins Weich',
                'role_title'    => 'Instructor',
                'rank'          => 'Scholar-Instructor',
                'sort_order'    => 2,
                'is_instructor' => true,
                'bio' => [
                    'lv' => 'Instruktors ar pieredzi dussakā un rapierā; vada tehniskās sesijas iesācējiem. Liela pieredze biedriem paredzētā ekipējuma atlasē.',
                    'en' => 'Instructor with experience in dussack and rapier; leads technical sessions for beginners. Extensive experience advising members on gear selection.',
                    'ru' => 'Инструктор с опытом работы с дюсаком и рапирой; ведёт технические занятия для начинающих. Обширный опыт консультирования по выбору снаряжения.',
                    'cs' => 'Instruktor se zkušenostmi s dusákem a rapírem; vede technické lekce pro začátečníky. Rozsáhlé zkušenosti s poradenstvím při výběru výstroje.',
                    'de' => 'Ausbilder mit Erfahrung an Dussack und Rapier; leitet technische Einheiten für Anfänger. Umfangreiche Erfahrung bei der Ausrüstungsberatung für Mitglieder.',
                    'lineage' => [
                        'en' => 'Trained under Artūrs Vītiņš within the Blossfechten Riga curriculum; additional study of Joachim Meyer dussack and rapier sections.',
                        'lv' => 'Apmācīts Artūra Vītiņa vadībā Blossfechten Riga programmā; papildu studijas Meijera dussack un rapiera nodaļās.',
                    ],
                    'seminars' => [
                        'en' => 'Baltic Sword (2023, 2024)',
                        'lv' => 'Baltic Sword (2023, 2024)',
                    ],
                ],
            ],
            [
                'full_name'     => 'Juris Matkevics',
                'role_title'    => 'Senior Member',
                'rank'          => 'Free Scholar',
                'sort_order'    => 3,
                'is_instructor' => false,
                'bio' => [
                    'lv' => 'Vecākais kluba loceklis, atbildīgs par ekipējumu un sacensību sagatavošanu.',
                    'en' => 'Senior club member, responsible for equipment and tournament preparation.',
                    'ru' => 'Старший член клуба, отвечает за снаряжение и подготовку к турнирам.',
                    'cs' => 'Starší člen klubu, má na starosti vybavení a přípravu na turnaje.',
                    'de' => 'Erfahrenes Vereinsmitglied, verantwortlich für Ausrüstung und Turniervorbereitung.',
                ],
            ],
            [
                'full_name'     => 'Edgars Bērsons',
                'role_title'    => 'Senior Member',
                'rank'          => 'Free Scholar',
                'sort_order'    => 4,
                'is_instructor' => false,
                'bio' => [
                    'lv' => 'Vecākais kluba loceklis, specializējies Ringen un īsajos ieročos.',
                    'en' => 'Senior club member specialising in Ringen (wrestling) and short weapons.',
                    'ru' => 'Старший член клуба, специалист по Ringen (борьбе) и короткому оружию.',
                    'cs' => 'Starší člen klubu se specializací na Ringen (zápas) a krátké zbraně.',
                    'de' => 'Erfahrenes Vereinsmitglied mit Spezialisierung auf Ringen und Kurzwaffen.',
                ],
            ],
        ];

        foreach ($members as $m) {
            Member::updateOrCreate(['full_name' => $m['full_name']], $m);
        }
    }
}
