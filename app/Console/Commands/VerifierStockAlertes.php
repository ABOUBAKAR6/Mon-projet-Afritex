<?php

namespace App\Console\Commands;

use App\Http\Controllers\AlerteStockController;
use Illuminate\Console\Command;

class VerifierStockAlertes extends Command
{
    protected $signature = 'app:verifier-stock-alertes';
    protected $description = 'Vérifier les stocks et envoyer les alertes aux utilisateurs';

    public function handle()
    {
        $this->info('🔍 Vérification des alertes de stock...');
        
        AlerteStockController::verifierStocksEtAlerter();
        
        $this->info('✅ Vérification terminée');
    }
}
