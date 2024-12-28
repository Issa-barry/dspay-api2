<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Devise;

class DeviseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ajouter des devises par défaut
        $devises = [
            ['nom' => 'Dollar US', 'tag' => '$'],
            ['nom' => 'Euro', 'tag' => '€'],
            ['nom' => 'Franc-Guinéen', 'tag' => 'GNF'],
            // Ajoutez d'autres devises selon votre besoin
        ];

        // Insertion des devises dans la base de données
        foreach ($devises as $devise) {
            Devise::create($devise);
        }

        $this->command->info('Devises insérées avec succès!');
    }
    
    /**
     * php artisan db:seed --class=DeviseSeeder
     */
}
