<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\Contact;
use App\Models\News;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\Resource;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Supprime les données de test (articles « TEST », commentaires d'exemple, adresses fictives…)
 * sans toucher au contenu réel. Sans --force, la commande liste seulement ce qui serait supprimé.
 *
 *   php artisan content:purge-test          → aperçu
 *   php artisan content:purge-test --force  → suppression
 */
class PurgeTestData extends Command
{
    protected $signature = 'content:purge-test {--force : Supprimer réellement (sinon simple aperçu)}';

    protected $description = 'Supprime les données de test (titres « test/essai/démo », commentaires d\'exemple, e-mails fictifs)';

    /** Titre entièrement composé d'un mot de test, suivi au plus de chiffres ou de ponctuation : « TEST », « test 2 », « Essai !! ». */
    public const TEST_TITLE = '/^\s*(tests?|teste?r?|essais?|d[ée]mo|exemple|example|sample|dummy|lorem ipsum.*|azerty|qwerty|xxx+|aaa+)[\s\d\p{P}]*$/iu';

    /** Domaines réservés aux exemples et aux tests. */
    public const TEST_EMAIL = '/@(example\.(com|org|net)|test\.com|[^@\s]+\.(test|example|invalid|localhost|local))$/i';

    /** Commentaires créés par l'ancien CommentSeeder. */
    private const SEEDED_COMMENTS = [
        'Marie Nkosi', 'Jean-Paul Mwamba', 'Esther Lufwa', 'David Kabila', 'Rachel Tshomba',
        'Samuel Ilunga', 'Claudine Mbuyi', 'Pierre Ntumba', 'Naomi Kasongo', 'Emmanuel Diallo',
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $isTitle = fn (Model $m) => preg_match(self::TEST_TITLE, (string) $m->title) === 1;
        $isEmail = fn (Model $m) => preg_match(self::TEST_EMAIL, (string) $m->email) === 1;

        $groups = [
            'Articles (Écho de la mission)' => News::all(['id', 'title', 'featured_image'])->filter($isTitle),
            'Enseignements'                 => Post::all(['id', 'title', 'featured_image'])->filter($isTitle),
            'Ressources'                    => Resource::all(['id', 'title'])->filter($isTitle),
            'Vidéos'                        => Video::all(['id', 'title'])->filter($isTitle),
            'Messages de contact'           => Contact::all()->filter(fn ($c) => $isEmail($c) || preg_match(self::TEST_TITLE, (string) $c->name) === 1),
            'Abonnés newsletter'            => NewsletterSubscription::all(['id', 'email'])->filter($isEmail),
        ];

        // Commentaires : ceux du seeder, ceux dont le texte est un simple « test », et ceux des contenus supprimés
        $deletedContents = $groups['Articles (Écho de la mission)']->map(fn ($n) => [News::class, $n->id])
            ->merge($groups['Enseignements']->map(fn ($p) => [Post::class, $p->id]));
        $groups['Commentaires'] = Comment::all()->filter(function (Comment $c) use ($deletedContents) {
            return in_array($c->author_name, self::SEEDED_COMMENTS, true) && $c->author_email === null
                || preg_match(self::TEST_TITLE, (string) $c->content) === 1
                || $deletedContents->contains(fn ($k) => $k[0] === $c->commentable_type && (int) $k[1] === (int) $c->commentable_id);
        });
        // Réponses aux commentaires supprimés
        $commentIds = $groups['Commentaires']->pluck('id');
        $groups['Commentaires'] = $groups['Commentaires']->merge(
            Comment::whereIn('parent_id', $commentIds)->whereNotIn('id', $commentIds)->get()
        );

        $total = array_sum(array_map(fn (Collection $g) => $g->count(), $groups));
        if ($total === 0) {
            $this->info('Aucune donnée de test trouvée.');
            return self::SUCCESS;
        }

        foreach ($groups as $label => $items) {
            if ($items->isEmpty()) {
                continue;
            }
            $this->line("<comment>{$label}</comment> : {$items->count()}");
            foreach ($items->take(15) as $item) {
                $this->line('   - #' . $item->id . ' ' . ($item->title ?? $item->email ?? $item->author_name ?? ''));
            }
        }

        if (!$force) {
            $this->warn("Aperçu uniquement : {$total} élément(s) seraient supprimés. Relancez avec --force pour supprimer.");
            return self::SUCCESS;
        }

        foreach ($groups['Commentaires'] as $c) {
            $c->delete();
        }
        foreach ($groups as $label => $items) {
            if ($label === 'Commentaires') {
                continue;
            }
            foreach ($items as $item) {
                $this->deleteFiles($item);
                $item->delete();
            }
        }

        $this->info("{$total} élément(s) de test supprimé(s).");

        return self::SUCCESS;
    }

    /** Supprime les images envoyées pour un article ou un enseignement de test. */
    private function deleteFiles(Model $item): void
    {
        $paths = [$item->featured_image ?? null];
        if ($item instanceof News) {
            $paths = array_merge($paths, $item->images()->pluck('image')->all());
        }
        foreach (array_filter($paths) as $path) {
            $file = public_path($path);
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
