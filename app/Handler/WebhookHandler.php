<?php
namespace App\Handler;

use App\Models\Transaction;
use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;

class WebhookHandler extends SpatieProcessWebhookJob
{
    public function handle()
    {
        logger('i was here');
        Transaction::create([
            'data'=>$this->webhookCall,
        ]);
    }
}

