<?php

use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes pour Progressive Web App (PWA) |-------------------------------------------------------------------------- | | Ces routes sont utilisées par l'application mobile PWA pour scanner | les QR codes et effectuer les inventaires. | | Toutes les routes sont préfixées par /api/v1 | | NOTE IMPORTANTE: Les contrôleurs API doivent être créés dans  | app/Http/Controllers/Api/ avant d'activer ces routes. | */

Route::prefix('v1')->group(function () {

/*
 |----------------------------------------------------------------------
 | Authentification API - DÉSACTIVÉ (contrôleurs supprimés)
 |----------------------------------------------------------------------
 |
 | Routes pour l'authentification via l'API (utilise Sanctum)
 | TODO: Recréer AuthController si l'API est nécessaire
 |
 */
// Route::post('/login', [AuthController::class , 'login'])->name('api.login');
// Route::post('/logout', [AuthController::class , 'logout'])
//     ->middleware('auth:sanctum')
//     ->name('api.logout');

});
