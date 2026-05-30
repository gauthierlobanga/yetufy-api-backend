<?php

namespace App\Console\Commands;

use App\Models\Visit;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('visitors:clean {days=30}')]
#[Description('Supprime les visiteurs inactifs depuis plus de X jours')]
class CleanOldVisits extends Command
{
    public function handle()
    {
        $days = $this->option('days');
        $deleted = Visit::where('visited_at', '<', now()->subDays($days))->delete();
        $this->info("{$deleted} visites supprimées.");
    }
}
