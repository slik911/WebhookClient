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

        $signature_part = explode(',', $signature);
        $signature_parts = explode('=', $signature_part[1]);


        if (count($signature_parts) != 2) {
            throw new BadRequestHttpException('signature has invalid format');
        }

        $known_signature = hash_hmac('sha1', $request->getContent(), $known_token);

        // return $known_token;

        // if (! hash_equals($known_signature, $signature_parts[1])) {
        //     throw new UnauthorizedException('Could not verify request signature ' . $signature_parts[1]);
        // }
        if (! hash_equals($signature_parts[1], $known_signature)) {
            throw new UnauthorizedException('Could not verify request signature ' . $signature_parts[1]);
        }

        // $computedSignature = hash_hmac('sha256', $request->getContent(), $signingSecret);

        // return hash_equals($signature, $computedSignature);

        // $computedSignature = hash_hmac('sha256', $request->getContent(), $signingSecret);

        // if (($signature = $_SERVER['HTTP_STRIPE_SIGNATURE']) == null) {
        //     throw new BadRequestHttpException('Header not set');
        // }

        // $known_signature = bcrypt($known_token);
        // if (! Hash::check($signature, $known_signature)) {
        //     throw new UnauthorizedException('Could not verify request signature ' . $signature);
        // }

    }



    public function handle(Request $request)
    {

        return $this->validateGithubWebhook(config('app.webhook_client_secret'), $request);

        $this->logger->info('Hello World. The webhook is validated');
        $this->logger->info($request->getContent());

        Transaction::create([
            'data'=>json_encode($request->all),
        ]);
    }
}
