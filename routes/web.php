<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    $panel = filament()->getCurrentPanel() ?? filament()->getDefaultPanel();

    if (! $panel) {
        abort(404, 'No Filament panel found.');
    }

    return redirect()->route("filament.{$panel->getId()}.auth.login");
})->name('login');
