<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $commentPool = [
            ['author_name' => 'Marie Nkosi',    'content' => 'Merci beaucoup pour cet enseignement ! Que Dieu bénisse notre église.'],
            ['author_name' => 'Jean-Paul Mwamba', 'content' => 'Très enrichissant. J\'ai appris beaucoup de choses sur ce sujet.'],
            ['author_name' => 'Esther Lufwa',    'content' => 'Que Dieu bénisse le pasteur pour ce message inspirant. J\'ai été touché.'],
            ['author_name' => 'David Kabila',    'content' => 'C\'est exactement ce dont j\'avais besoin ce matin. Merci !'],
            ['author_name' => 'Rachel Tshomba',  'content' => 'J\'ai partagé ce message avec ma famille. Nous prions ensemble pour approfondir cette étude.'],
            ['author_name' => 'Samuel Ilunga',   'content' => 'Magnificat ! Ce passage m\'a parlé profondément. Alléluia !'],
            ['author_name' => 'Claudine Mbuyi',  'content' => 'Dieu merci pour cette plateforme qui nous permet d\'accéder aux enseignements bibliques.'],
            ['author_name' => 'Pierre Ntumba',   'content' => 'Un message très puissant. Je reviendrai pour écouter la suite.'],
            ['author_name' => 'Naomi Kasongo',   'content' => 'Je partage cet enseignement avec tous mes amis. Que Dieu vous bénisse !'],
            ['author_name' => 'Emmanuel Diallo', 'content' => 'Bénédictions à toute l\'équipe de Hope Media DRC. Continuez ce beau travail.'],
        ];

        $posts = Post::all();

        foreach ($posts as $index => $post) {
            // Prendre 2 à 4 commentaires différents par post
            $count = rand(2, 4);
            $selected = array_slice($commentPool, ($index * 2) % count($commentPool), $count);

            foreach ($selected as $i => $data) {
                Comment::create([
                    'post_id'      => $post->id,
                    'author_name'  => $data['author_name'],
                    'author_email' => null,
                    'content'      => $data['content'],
                    'is_approved'  => true,
                    'created_at'   => now()->subDays(rand(1, 30))->subMinutes(rand(0, 59)),
                    'updated_at'   => now()->subDays(rand(0, 1)),
                ]);
            }
        }

        $this->command->info('✅ Commentaires de test créés pour tous les posts!');
    }
}
