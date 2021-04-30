<?php

namespace App\Console\Commands\Auth;

use App\Models\Invite;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ClearInvites extends Command
{
    /**
     * Name of the command.
     * @var string
     */
    protected $signature = 'auth:clear-invites';

    /**
     * Description of the command.
     * @var string
     */
    protected $description = 'Flush expired invite codes';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        Invite::where('expired_at', '<=', Carbon::now())->delete();
        $this->info('Expired invite codes cleared!');
    }
}
