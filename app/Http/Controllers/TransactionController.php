<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Psr\Log\LoggerInterface;
use Spatie\WebhookClient\Models\WebhookCall;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TransactionController extends Controller
{

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Validate an incoming github webhook
     *
     * @param string $known_token Our known token that we've defined
     * @param \Illuminate\Http\Request $request
     *
     * @throws \BadRequestHttpException, \UnauthorizedException
     * @return void
     */
    protected function validateGithubWebhook($known_token, Request $request)
    {
        if (($signature = $_SERVER['HTTP_STRIPE_SIGNATURE']) == null) {
            throw new BadRequestHttpException('Header not set');
        }

        $known_signature = bcrypt($known_token);
        if (! Hash::check($signature, $known_signature)) {
            throw new UnauthorizedException('Could not verify request signature ' . $signature);
        }

    }



    public function handle(Request $request)
    {
        // return "i am working";
        $this->validateGithubWebhook(config('app.webhook_client_secret'), $request);

        $this->logger->info('Hello World. The webhook is validated');
        $this->logger->info($request->getContent());

        Transaction::create([
            'data'=>json_encode($request->all),
        ]);
    }
}
