<?php

namespace Database\Seeders;

use App\Marker;
use App\User;
use Illuminate\Database\Seeder;

class MarkerSeeder extends Seeder
{
    /**
     * Insert dummy markers for user added via the user seeder.
     * @return void
     */
    public function run()
    {
        $owner = User::where('name', 'user')->firstOrFail();

        for ($i = 0; $i < 100; $i++) {
            $owner->markers()->firstOrCreate(
                [
                    'url' => sprintf('http://example.org/%03d', $i),
                ],
                [
                    'description' => sprintf('Dummy URL %03d', $i),
                    'handler' => 'Seeder',
                ]
            );
        }
    }
}
