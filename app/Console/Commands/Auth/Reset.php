<?php

namespace App\Console\Commands\Auth;

use Exception;
use PDOException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class Reset extends Command
{
    /**
     * Name of the command.
     * @var string
     */
    protected $signature = 'auth:reset';

    /**
     * Description of the command.
     * @var string
     */
    protected $description = 'Reset root user account';

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
        $password = $this->secret('Please enter root user password:');
        $confirm = $this->secret('Please confirm root user password:');

        if (hash_equals($password, $confirm) === false) {
            $this->error("Root user passwords do not match");
        } else {
            try {
                if (($root = User::where('name', 'root')->first()) !== null) {
                    $root->password = Hash::make($password);
                    $root->role = User::ADMIN;
                    $root->status = User::ACTIVE;
                    $root->save();
                    $this->info("Root user is updated");
                } else {
                    $root = new User();
                    $root->password = Hash::make($password);
                    $root->role = User::ADMIN;
                    $root->status = User::ACTIVE;
                    $root->save();
                    $this->info("Root user is created");
                }
            } catch (PDOException $ex) {
                $this->error("Root user cannot be created/updated due to database error");
            } catch (Exception $ex) {
                $this->error("Root user cannot be created/updated due to other errors");
            }
        }
    }
}
