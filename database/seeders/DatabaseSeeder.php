<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $user = new User;
        $user->name = "ManuAdmin";
        $user->email = "adminManu@admin.com";
        $user->password = Hash::make("soyeladmin");
        $user->puesto = "directivo";
        $user->salario = "0";
        $user->biografia = "soy geiadmin";
        $user->save();

    }
}
