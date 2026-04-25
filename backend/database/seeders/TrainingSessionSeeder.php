<?php

namespace Database\Seeders;

use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TrainingSessionSeeder extends Seeder
{
    public function run(): void
    {
        // Generate 8 upcoming weeks of Mon/Wed (18:00–20:00) + Saturday (11:00–14:00).
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $slots = [
            ['day' => Carbon::MONDAY,    'from' => '18:00', 'to' => '20:00', 'focus' => 'Longsword'],
            ['day' => Carbon::WEDNESDAY, 'from' => '18:00', 'to' => '20:00', 'focus' => 'Longsword & Drills'],
            ['day' => Carbon::SATURDAY,  'from' => '11:00', 'to' => '14:00', 'focus' => 'Open Training / Ringen'],
        ];

        for ($w = 0; $w < 8; $w++) {
            foreach ($slots as $slot) {
                $day = $start->copy()->addWeeks($w)->next($slot['day']);
                if ($w === 0) {
                    $day = $start->copy()->addDays($slot['day'] - Carbon::MONDAY);
                    if ($day->isPast()) continue;
                }
                [$fh, $fm] = explode(':', $slot['from']);
                [$th, $tm] = explode(':', $slot['to']);

                TrainingSession::updateOrCreate(
                    ['starts_at' => $day->copy()->setTime((int)$fh, (int)$fm)],
                    [
                        'ends_at' => $day->copy()->setTime((int)$th, (int)$tm),
                        'location' => 'Riga',
                        'focus' => $slot['focus'],
                        'title' => [
                            'lv' => $slot['focus'] === 'Longsword' ? 'Garais zobens' : ($slot['focus'] === 'Longsword & Drills' ? 'Garais zobens un vingrinājumi' : 'Atvērtais treniņš / Ringen'),
                            'en' => $slot['focus'],
                            'ru' => $slot['focus'] === 'Longsword' ? 'Длинный меч' : ($slot['focus'] === 'Longsword & Drills' ? 'Длинный меч и упражнения' : 'Открытая тренировка / Ringen'),
                            'cs' => $slot['focus'] === 'Longsword' ? 'Dlouhý meč' : ($slot['focus'] === 'Longsword & Drills' ? 'Dlouhý meč a cvičení' : 'Otevřený trénink / Ringen'),
                            'de' => $slot['focus'] === 'Longsword' ? 'Langschwert' : ($slot['focus'] === 'Longsword & Drills' ? 'Langschwert & Übungen' : 'Offenes Training / Ringen'),
                        ],
                        'description' => [
                            'lv' => 'Treniņš pēc Joahima Meijera metodikas. Iesācēji laipni aicināti.',
                            'en' => 'Training based on the method of Joachim Meyer. Beginners welcome.',
                            'ru' => 'Тренировка по методу Иоахима Мейера. Новички приветствуются.',
                            'cs' => 'Trénink podle metody Joachima Meyera. Začátečníci vítáni.',
                            'de' => 'Training nach der Methode Joachim Meyers. Anfänger willkommen.',
                        ],
                        'capacity' => 20,
                        'members_only' => false,
                        'cancelled' => false,
                    ]
                );
            }
        }
    }
}
