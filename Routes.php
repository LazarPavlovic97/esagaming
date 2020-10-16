<?php
    use App\Core\Route;

    return [
        Route::get('#^games/([0-9]+)/?$#','Games','gamesGet'),
        Route::post('#^games/([0-9]+)/?$#','Games','gamesPost'),

        # Secret routes
        Route::get('#^tasks/sendNotifications/([A-z0-9]{64})/?$#', 'Task', 'sendNotifications'),

        # Fallback
        Route::get('#^.*$#', 'Main', 'mainGet'),
        Route::post('#^.*$#', 'Main', 'mainPost')
    ];
