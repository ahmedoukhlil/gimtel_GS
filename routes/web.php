<?php

use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | Routes Publiques |-------------------------------------------------------------------------- */

// Redirection de la page d'accueil vers le login
Route::get('/', function () {
    return redirect()->route('login');
});

/* |-------------------------------------------------------------------------- | Routes d'Authentification |-------------------------------------------------------------------------- */

// Inclusion des routes d'authentification
if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}

/* |-------------------------------------------------------------------------- | Routes Authentifiées |-------------------------------------------------------------------------- | | Toutes les routes ci-dessous nécessitent que l'utilisateur soit authentifié. | */

Route::middleware(['auth', 'session.timeout'])->group(function () {

    /*
     |----------------------------------------------------------------------
     | Dashboard Principal (Stock)
     |----------------------------------------------------------------------
     */
    Route::get('/dashboard', \App\Livewire\Stock\DashboardStock::class)->name('dashboard');

    /*
     |----------------------------------------------------------------------
     | Espace Client (Rôle: client)
     |----------------------------------------------------------------------
     */
    Route::middleware(['client'])->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', \App\Livewire\Client\ReservedStock::class)->name('dashboard');
        Route::get('/commande/{commande}', \App\Livewire\Client\ClientCommandeDetail::class)->name('commande.show')->where('commande', '[0-9]+');
        Route::get('/commande', \App\Livewire\Client\ClientCommande::class)->name('commande');
    });

        /*
     |----------------------------------------------------------------------
     | Espace Production (Rôle: direction_production)
     |----------------------------------------------------------------------
     */
    Route::middleware(['production'])->prefix('production')->name('production.')->group(function () {
        Route::get('/commandes', \App\Livewire\Production\OrdersManagement::class)->name('orders');
        Route::get('/commandes/{order}/bon-livraison', \App\Http\Controllers\Production\BonLivraisonController::class)->name('orders.bon-livraison.download');
        Route::get('/commandes/{order}', \App\Livewire\Production\OrderDetail::class)->name('orders.show');
        Route::get('/commander-pour-client', \App\Livewire\Production\CommandePourClient::class)->name('commander-pour-client');
        Route::get('/reservations', \App\Livewire\Production\GestionReservations::class)->name('reservations');
    });

    /*
     |----------------------------------------------------------------------
     | Demandes d'approvisionnement - Demandeur interne (services/directions)
     |----------------------------------------------------------------------
     */
    Route::middleware(['demandeur_interne'])->prefix('demandes-appro')->name('demandes-appro.')->group(function () {
        Route::get('/', \App\Livewire\DemandesAppro\ListeDemandes::class)->name('index');
        Route::get('/create', \App\Livewire\DemandesAppro\FormDemande::class)->name('create');
        Route::get('/{demande}', \App\Livewire\DemandesAppro\DetailDemande::class)->name('show')->where('demande', '[0-9]+');
    });

    /*
     |----------------------------------------------------------------------
     | Demandes d'approvisionnement - Direction moyens généraux (DMG)
     |----------------------------------------------------------------------
     */
    Route::middleware(['dmg'])->prefix('dmg')->name('dmg.')->group(function () {
        Route::get('/demandes', \App\Livewire\Dmg\ListeDemandesDmg::class)->name('demandes.index');
        Route::get('/demandes/create', \App\Livewire\Dmg\CreerDemandePourDemandeur::class)->name('demandes.create');
        Route::get('/demandes/{demande}', \App\Livewire\Dmg\DetailDemandeDmg::class)->name('demandes.show')->where('demande', '[0-9]+');
    });

        /*
     |----------------------------------------------------------------------
     | Gestion des Utilisateurs (Admin uniquement)
     |----------------------------------------------------------------------
     */
        Route::middleware(['admin'])->group(function () {
            Route::get('/settings/mail', \App\Livewire\Admin\ConfigMail::class)->name('settings.mail');
            Route::prefix('users')->name('users.')->group(function () {
                    Route::get('/', \App\Livewire\Users\ListeUsers::class)->name('index');
                    Route::get('/create', \App\Livewire\Users\FormUser::class)->name('create');
                    Route::get('/{user}/edit', \App\Livewire\Users\FormUser::class)->name('edit');
                    Route::get('/roles', \App\Livewire\Users\GestionRoles::class)->name('roles');
                }
                );
            }
            );

            /*
         |----------------------------------------------------------------------
         | Gestion de Stock - Paramètres (Admin, Admin_stock, Direction production)
         |----------------------------------------------------------------------
         |
         | Magasins, Catégories, Fournisseurs, Demandeurs, Entrées
         |
         */
            Route::middleware(['stock'])->group(function () {
            Route::prefix('stock')->name('stock.')->group(function () {

                    // Magasins (commandes / cartes)
                    Route::prefix('magasins')->name('magasins.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Magasins\ListeMagasins::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Magasins\FormMagasin::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Magasins\FormMagasin::class)->name('edit');
                        }
                        );
                    Route::prefix('magasins-appro')->name('magasins-appro.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Magasins\ListeMagasins::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Magasins\FormMagasin::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Magasins\FormMagasin::class)->name('edit');
                        }
                        );

                        // Catégories (commandes / cartes)
                        Route::prefix('categories')->name('categories.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Categories\ListeCategories::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Categories\FormCategorie::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Categories\FormCategorie::class)->name('edit');
                        }
                        );
                    Route::prefix('categories-appro')->name('categories-appro.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Categories\ListeCategories::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Categories\FormCategorie::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Categories\FormCategorie::class)->name('edit');
                        }
                        );

                        // Fournisseurs (commandes / cartes)
                        Route::prefix('fournisseurs')->name('fournisseurs.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Fournisseurs\ListeFournisseurs::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Fournisseurs\FormFournisseur::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Fournisseurs\FormFournisseur::class)->name('edit');
                        }
                        );
                    Route::prefix('fournisseurs-appro')->name('fournisseurs-appro.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Fournisseurs\ListeFournisseurs::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Fournisseurs\FormFournisseur::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Fournisseurs\FormFournisseur::class)->name('edit');
                        }
                        );

                        // Demandeurs
                        Route::prefix('demandeurs')->name('demandeurs.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Demandeurs\ListeDemandeurs::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Demandeurs\FormDemandeur::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Demandeurs\FormDemandeur::class)->name('edit');
                        }
                        );

                        // Clients
                        Route::prefix('clients')->name('clients.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Clients\ListeClients::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Clients\FormClient::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Clients\FormClient::class)->name('edit');
                        }
                        );

                        // Services (demandes d'approvisionnement)
                        Route::prefix('services')->name('services.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Services\ListeServices::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Services\FormService::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Services\FormService::class)->name('edit');
                        }
                        );

                        // Entrées
                        Route::prefix('entrees')->name('entrees.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Entrees\ListeEntrees::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Entrees\FormEntree::class)->name('create');
                        }
                        );
                    }
                    );
                }
                );

                /*
             |----------------------------------------------------------------------
             | Gestion de Stock - Opérations (Admin + Admin_stock + Agent)
             |----------------------------------------------------------------------
             |
             | Dashboard Stock, Produits, Sorties
             |
             */
                Route::middleware(['inventory'])->group(function () {
            Route::prefix('stock')->name('stock.')->group(function () {

                    // Dashboard Stock
                    Route::get('/', \App\Livewire\Stock\DashboardStock::class)->name('dashboard');

                    // Dashboard Approvisionnement
                    Route::get('dashboard-appro', \App\Livewire\Stock\DashboardAppro::class)->name('dashboard-appro');

                    // Produits (commandes / cartes clients uniquement)
                    Route::prefix('produits')->name('produits.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Produits\ListeProduits::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Produits\FormProduit::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Produits\FormProduit::class)->name('edit');
                            Route::get('/{id}', \App\Livewire\Stock\Produits\DetailProduit::class)->name('show');
                        }
                        );

                        // Produits d'approvisionnement (demandes appro uniquement)
                        Route::prefix('produits-appro')->name('produits-appro.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Produits\ListeProduits::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Produits\FormProduit::class)->name('create');
                            Route::get('/{id}/edit', \App\Livewire\Stock\Produits\FormProduit::class)->name('edit');
                            Route::get('/{id}', \App\Livewire\Stock\Produits\DetailProduit::class)->name('show');
                        }
                        );

                        // Sorties
                        Route::prefix('sorties')->name('sorties.')->group(function () {
                            Route::get('/', \App\Livewire\Stock\Sorties\ListeSorties::class)->name('index');
                            Route::get('/create', \App\Livewire\Stock\Sorties\FormSortie::class)->name('create');
                        }
                        );
                    }
                    );
                }
                );
            });
