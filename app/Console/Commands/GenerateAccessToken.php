<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccessToken;

class GenerateAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:new-access-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assignee = $this->ask('Who is the access token for?');
        $email = $this->ask('What is the email address of the user?');
        $scopes = $this->choice('What scopes should be assigned to the access token?', [
            'read' => 'Read',
            'export' => 'Export',
            '*' => 'All',
        ], 'read', null, true);
        $expiresAt = $this->ask('When should the access token expire? (YYYY-MM-DD HH:MM:SS)');
        $expiresAt = $expiresAt ? new \DateTime($expiresAt) : null;

        if (!$expiresAt) {
            $this->error('No date provided- access token will not expire.');
        }

        $accessToken = AccessToken::create([
            'token' => AccessToken::generateNew(),
            'expires_at' => $expiresAt,
            'assigned_to' => $assignee,
            'scope' => $scopes,
            'assignee_email' => $email,
        ]);

        $this->info('Access token generated successfully!');
        $this->info('Token: ' . $accessToken->token);

    }
}