<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder pour les données initiales
 * Crée des catégories, posts et médias de test
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Initialisation de la base de données...');

        // Créer l'utilisateur administrateur par défaut
        $this->command->info('👤 Création de l\'administrateur...');
        User::firstOrCreate(
            ['email' => 'hopemediasociaux@gmail.com'],
            [
                'name' => 'Administrateur Hope Media',
                'password' => Hash::make('hopemediauco@sda'),
            ]
        );
        $this->command->info('✅ Administrateur créé!');
        $this->command->warn('📧 Email: hopemediasociaux@gmail.com');
        $this->command->warn('🔑 Mot de passe: hopemediauco@sda');

        // Créer les catégories de base (vides - sans posts de test)
        $this->command->info('📁 Création des catégories...');
        $categories = [
            [
                'name' => 'Sermons du sabbat',
                'slug' => 'sermons-sabbat',
                'description' => 'Les prédications hebdomadaires de nos pasteurs',
                'type' => 'enseignement',
                'icon' => 'microphone',
                'order' => 1,
            ],
            [
                'name' => 'Études bibliques',
                'slug' => 'etudes-bibliques',
                'description' => 'Approfondissez votre connaissance des Écritures',
                'type' => 'enseignement',
                'icon' => 'book',
                'order' => 2,
            ],
            [
                'name' => 'Enseignements vidéo',
                'slug' => 'enseignements-video',
                'description' => 'Vidéos éducatives et spirituelles',
                'type' => 'enseignement',
                'icon' => 'video',
                'order' => 3,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(['slug' => $categoryData['slug']], $categoryData);
        }

        $this->command->info('✅ Catégories créées!');
        $this->command->info('✨ Base de données initialisée!');
        $this->command->warn('ℹ️  La base de données est prête. Vous pouvez ajouter des posts via l\'interface admin.');
    }
}
