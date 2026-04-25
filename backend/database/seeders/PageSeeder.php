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
        ];

        foreach ($pages as $p) {
            Page::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
