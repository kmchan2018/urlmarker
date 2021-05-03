<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Insert dummy user to the database.
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(
            [
                'name' => 'root',
            ],
            [
                'password' => Hash::make('p@assw0rd!@#$%^&*()', []),
                'role' => User::ADMIN,
                'status' => User::ACTIVE,
            ]
        );

        User::firstOrCreate(
            [
                'name' => 'user',
            ],
            [
                'password' => Hash::make('p@assw0rd!@#$%^&*()', []),
                'role' => User::NORMAL,
                'status' => User::ACTIVE,
            ]
        );

        for ($i = 0; $i < 10; $i++) {
            User::firstOrCreate(
                [
                    'name' => sprintf('dummy%02d', $i),
                ],
                [
                    'password' => Hash::make('p@assw0rd!@#$%^&*()', []),
                    'role' => User::NORMAL,
                    'status' => User::CREATED,
                ]
            );
        }
    }
}
