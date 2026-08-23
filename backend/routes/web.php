<?php

use Illuminate\Support\Facades\Route;

/*
 * The API host has no front page of its own. It used to answer with Laravel's
 * welcome view, which Google indexed and ranked against the club's brand — a
 * second, contentless "Blossfechten" result competing with the real site.
 *
 * A permanent redirect to the public site removes the duplicate and hands any
 * accumulated signal to the page that should have it.
 */
Route::get('/', function () {
    $frontend = rtrim((string) config('app.frontend_url', ''), '/');

    if ($frontend === '') {
        $frontend = 'https://blossfechtenriga.com';
    }

    return redirect()->away($frontend, 301);
});
