<?php

namespace App\Console\Commands;

use App\Services\NewsletterService;
use Illuminate\Console\Command;

/**
 * Envoie la newsletter hebdomadaire quand elle est due, puis continue l'envoi par lots.
 * Lancé par le planificateur (routes/console.php) ou à la main : php artisan newsletter:send
 */
class SendNewsletter extends Command
{
    protected $signature = 'newsletter:send';

    protected $description = 'Envoie la newsletter automatique si elle est due (par lots)';

    public function handle(NewsletterService $newsletter): int
    {
        $this->info($newsletter->runDue());

        return self::SUCCESS;
    }
}
