<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Exemple de modèles pour lesquels créer des permissions
        $models = ['Transfert', 'Contact', 'Agence', 'Taux']; 

        // Liste des actions possibles
        $actions = ['afficher', 'créer', 'modifier', 'supprimer'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                // Créer ou récupérer la permission pour l'action et le modèle spécifiés
                Permission::firstOrCreate([
                    'name' => "$action $model",
                    'model_type' => ucfirst(strtolower($model)), // Mettre la première lettre du modèle en majuscule
                ]);
            }
        }

        $this->command->info('Permissions have been seeded successfully!');
    }

    /**
     * Dans le terminal, exécutez la commande suivante pour lancer le seeder :
     *   php artisan db:seed --class=PermissionsSeeder
     */
}
