<?php

namespace App\Console\Commands;

use App\Models\Visitor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('visitors:clean {days=30}')]
#[Description('Supprime les visiteurs inactifs depuis plus de X jours')]
class CleanOldVisitors extends Command
{
    protected $signature = '';

    protected $description = '';

    public function handle()
    {
        $days = $this->argument('days');
        $deleted = Visitor::where('last_visit_at', '<', now()->subDays($days))->delete();
        $this->info("Deleted {$deleted} old visitors.");
    }
}
