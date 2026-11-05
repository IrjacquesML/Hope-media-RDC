@php
    use App\Http\Controllers\AdminReportController as R;
    $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');
    $d = fn ($date) => $date ? $date->translatedFormat('d/m/Y') : '—';
    $num = 0;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $title }} — {{ $site->site_full_name }}</title>
<style>
    @page { margin: 118px 42px 70px 42px; }
    * { font-family: "DejaVu Sans", sans-serif; }
    body { font-size: 9.5pt; color: #1b2440; line-height: 1.45; }

    /* En-tête et pied répétés sur chaque page */
    header { position: fixed; top: -96px; left: 0; right: 0; height: 82px; border-bottom: 2.5px solid #FCA311; }
    header table { width: 100%; border-collapse: collapse; }
    header td { vertical-align: middle; padding: 0; }
    header .logo { width: 82px; }
    header .logo img { height: 70px; }
    header .org { font-size: 15pt; font-weight: bold; color: #0B1530; letter-spacing: .5px; }
    header .sub { font-size: 7.8pt; color: #47537A; margin-top: 2px; }
    header .ref { text-align: right; font-size: 7.5pt; color: #47537A; line-height: 1.5; }
    footer { position: fixed; bottom: -48px; left: 0; right: 0; height: 30px; border-top: 1px solid #d5dbea; font-size: 7.5pt; color: #5a6585; padding-top: 6px; }

    h1 { font-size: 17pt; color: #0B1530; margin: 0 0 4px; text-transform: uppercase; letter-spacing: .5px; }
    h2 { font-size: 11.5pt; color: #fff; background: #1F3A93; padding: 6px 10px; margin: 22px 0 10px; page-break-after: avoid; }
    h3 { font-size: 10pt; color: #1F3A93; margin: 14px 0 6px; page-break-after: avoid; }
    p { margin: 0 0 6px; }
    .muted { color: #5a6585; }
    .small { font-size: 8pt; }

    .cover { border: 1px solid #d5dbea; background: #f4f6fb; padding: 12px 14px; margin-bottom: 6px; }
    .cover table { width: 100%; border-collapse: collapse; }
    .cover td { padding: 3px 0; font-size: 9pt; vertical-align: top; }
    .cover td.k { width: 150px; color: #47537A; }

    table.data { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    table.data th { background: #0B1530; color: #fff; font-size: 8pt; text-align: left; padding: 5px 6px; }
    table.data td { border-bottom: 1px solid #e3e8f4; padding: 4px 6px; font-size: 8.3pt; vertical-align: top; }
    table.data tr:nth-child(even) td { background: #f6f8fc; }
    table.data .r { text-align: right; white-space: nowrap; }
    table.data tfoot td { font-weight: bold; background: #fff4dc; border-top: 1.5px solid #FCA311; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }

    table.kpi { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 8px; }
    table.kpi td { width: 25%; border: 1px solid #d5dbea; border-top: 3px solid #FCA311; padding: 8px 8px 7px; text-align: center; }
    table.kpi .v { font-size: 15pt; font-weight: bold; color: #0B1530; }
    table.kpi .l { font-size: 7.5pt; color: #47537A; }

    .two { width: 100%; border-collapse: collapse; }
    .two > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }
    .two > tbody > tr > td:first-child { padding-right: 8px; }
    .two > tbody > tr > td:last-child { padding-left: 8px; }

    .note { font-size: 7.8pt; color: #5a6585; font-style: italic; }
    .empty { color: #5a6585; font-style: italic; padding: 6px 0; }
    .obs { border-left: 3px solid #FCA311; background: #fffaf0; padding: 8px 12px; white-space: pre-line; }

    .sign { margin-top: 30px; page-break-inside: avoid; }
    .sign table { width: 100%; border-collapse: collapse; }
    .sign td { vertical-align: top; }
    .sign .box { width: 46%; text-align: center; }
    .sign .role { font-weight: bold; color: #0B1530; text-transform: uppercase; font-size: 9pt; }
    .sign .space { height: 70px; }
    .sign .space img { max-height: 66px; max-width: 200px; }
    .sign .name { font-weight: bold; border-top: 1px solid #1b2440; padding-top: 4px; margin: 0 30px; }
</style>
</head>
<body>

<header>
    <table>
        <tr>
            <td class="logo">@if ($logo)<img src="{{ $logo }}" alt="{{ $site->site_full_name }}">@endif</td>
            <td>
                <div class="org">{{ mb_strtoupper($site->site_full_name) }}</div>
                @if ($site->organization)<div class="sub">{{ $site->organization }}</div>@endif
                @if ($site->city_country)<div class="sub">{{ $site->city_country }}</div>@endif
            </td>
            <td class="ref">
                Réf. : {{ $reference }}<br>
                Période : {{ $d($from) }} – {{ $d($to) }}<br>
                Émis le {{ $generatedAt->translatedFormat('d/m/Y à H:i') }}
            </td>
        </tr>
    </table>
</header>

<footer>
    {{ $site->site_full_name }} — {{ $title }} du {{ $d($from) }} au {{ $d($to) }} · Document interne
</footer>

<main>
    <h1>{{ $title }}</h1>
    <p class="muted" style="margin-bottom:12px">Période du <strong>{{ $from->translatedFormat('d F Y') }}</strong> au <strong>{{ $to->translatedFormat('d F Y') }}</strong> ({{ $days }} jour{{ $days > 1 ? 's' : '' }})</p>

    <div class="cover">
        <table>
            <tr><td class="k">Référence</td><td>{{ $reference }}</td></tr>
            <tr><td class="k">Date d'émission</td><td>{{ $generatedAt->translatedFormat('l d F Y à H:i') }}</td></tr>
            @if ($generatedBy)<tr><td class="k">Généré par</td><td>{{ $generatedBy }}</td></tr>@endif
            <tr><td class="k">Signataire</td><td>{{ $signatory['name'] }} — {{ $signatory['role'] }}</td></tr>
            <tr><td class="k">Sections couvertes</td><td>{{ collect($sections)->map(fn ($s) => $sectionNames[$s])->implode(', ') }}</td></tr>
        </table>
    </div>

    {{-- 1. Synthèse --}}
    <h2>{{ ++$num }}. Synthèse de la période</h2>
    @php $kpis = array_chunk($summary, 4); @endphp
    @foreach ($kpis as $row)
        <table class="kpi"><tr>
            @foreach ($row as [$label, $value])
                <td><div class="v">{{ $fmt($value) }}</div><div class="l">{{ $label }}</div></td>
            @endforeach
            @for ($i = count($row); $i < 4; $i++)<td style="border:0"></td>@endfor
        </tr></table>
    @endforeach
    @php $opt = fn ($n) => $n === null ? '—' : $fmt($n); @endphp
    @if (!empty($totals['rows']))
        <h3>Récapitulatif des totaux</h3>
        <table class="data">
            <thead><tr><th>Domaine</th><th class="r">Publications</th><th class="r">Vues</th><th class="r">J'aime</th><th class="r">Commentaires</th><th class="r">Partages / téléch.</th></tr></thead>
            <tbody>
            @foreach ($totals['rows'] as $row)
                <tr>
                    <td>{{ $row['label'] }}@unless ($row['inTotal'])<span class="small muted"> (déjà comptées dans les réseaux sociaux)</span>@endunless</td>
                    <td class="r">{{ $fmt($row['items']) }}</td><td class="r">{{ $opt($row['views']) }}</td><td class="r">{{ $opt($row['likes']) }}</td>
                    <td class="r">{{ $opt($row['comments']) }}</td><td class="r">{{ $opt($row['other']) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot><tr>
                <td>TOTAL GÉNÉRAL</td>
                <td class="r">{{ $fmt($totals['total']['items']) }}</td><td class="r">{{ $fmt($totals['total']['views']) }}</td><td class="r">{{ $fmt($totals['total']['likes']) }}</td>
                <td class="r">{{ $fmt($totals['total']['comments']) }}</td><td class="r">{{ $fmt($totals['total']['other']) }}</td>
            </tr></tfoot>
        </table>
    @endif
    @if (!empty($totals['audience']))
        <h3>Interactions directes du public</h3>
        <table class="data">
            <thead><tr><th>Type</th><th class="r">Nombre</th></tr></thead>
            <tbody>
            @foreach ($totals['audience'] as [$label, $value])<tr><td>{{ $label }}</td><td class="r">{{ $fmt($value) }}</td></tr>@endforeach
            </tbody>
            <tfoot><tr><td>TOTAL</td><td class="r">{{ $fmt($totals['audienceTotal']) }}</td></tr></tfoot>
        </table>
    @endif
    <p class="note">Les contenus sont comptés selon leur date de publication ; les commentaires, messages et abonnements selon leur date de réception. Les vues et « j'aime » sont les totaux cumulés à la date d'émission pour les contenus publiés pendant la période.</p>

    {{-- Écho de la mission --}}
    @isset($news)
        <h2>{{ ++$num }}. Écho de la mission</h2>
        <table class="kpi"><tr>
            <td><div class="v">{{ $fmt($news['count']) }}</div><div class="l">Articles publiés</div></td>
            <td><div class="v">{{ $fmt($news['views']) }}</div><div class="l">Vues</div></td>
            <td><div class="v">{{ $fmt($news['likes']) }}</div><div class="l">J'aime</div></td>
            <td><div class="v">{{ $fmt($news['drafts']) }}</div><div class="l">Brouillons créés</div></td>
        </tr></table>

        @if ($news['byCategory']->isNotEmpty())
            <h3>Répartition par catégorie</h3>
            <table class="data">
                <thead><tr><th>Catégorie</th><th class="r">Articles</th><th class="r">Vues</th><th class="r">Part</th></tr></thead>
                <tbody>
                @foreach ($news['byCategory'] as $c)
                    <tr><td>{{ $c->label }}</td><td class="r">{{ $fmt($c->total) }}</td><td class="r">{{ $fmt($c->views) }}</td><td class="r">{{ $news['count'] ? round($c->total * 100 / $news['count']) : 0 }} %</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <h3>Liste des articles publiés</h3>
        @if ($news['items']->isEmpty())
            <p class="empty">Aucun article publié sur la période.</p>
        @else
            <table class="data">
                <thead><tr><th style="width:18px">#</th><th style="width:58px">Date</th><th>Titre</th><th>Catégorie</th><th>Auteur</th><th class="r">Vues</th><th class="r">J'aime</th><th class="r">Com.</th></tr></thead>
                <tbody>
                @foreach ($news['items'] as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td><td>{{ $d($a->published_at ?? $a->created_at) }}</td><td>{{ R::clean($a->title) }}</td>
                        <td>{{ $a->category ?: '—' }}</td><td>{{ $a->author ?: '—' }}</td>
                        <td class="r">{{ $fmt($a->views) }}</td><td class="r">{{ $fmt($a->likes) }}</td><td class="r">{{ $fmt($a->comments_count) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot><tr><td colspan="5">Total ({{ $fmt($news['count']) }} articles)</td><td class="r">{{ $fmt($news['views']) }}</td><td class="r">{{ $fmt($news['likes']) }}</td><td class="r">{{ $fmt($news['comments']) }}</td></tr></tfoot>
            </table>
            @if ($news['count'] > $maxRows)<p class="note">Seuls les {{ $maxRows }} premiers articles sont détaillés ; les totaux portent sur l'ensemble.</p>@endif
        @endif
    @endisset

    {{-- Enseignements --}}
    @isset($posts)
        <h2>{{ ++$num }}. Enseignements</h2>
        <table class="kpi"><tr>
            <td><div class="v">{{ $fmt($posts['count']) }}</div><div class="l">Enseignements publiés</div></td>
            <td><div class="v">{{ $fmt($posts['views']) }}</div><div class="l">Vues</div></td>
            <td><div class="v">{{ $fmt($posts['likes']) }}</div><div class="l">J'aime</div></td>
            <td><div class="v">{{ $fmt($posts['comments']) }}</div><div class="l">Commentaires</div></td>
        </tr></table>

        @if ($posts['byType']->isNotEmpty() || $posts['byCategory']->isNotEmpty())
            <table class="two"><tr>
                <td>
                    <h3>Par type de contenu</h3>
                    <table class="data"><thead><tr><th>Type</th><th class="r">Nombre</th></tr></thead><tbody>
                        @foreach ($posts['byType'] as $t)<tr><td>{{ R::label($t->label) }}</td><td class="r">{{ $fmt($t->total) }}</td></tr>@endforeach
                    </tbody></table>
                </td>
                <td>
                    <h3>Par catégorie</h3>
                    <table class="data"><thead><tr><th>Catégorie</th><th class="r">Nombre</th></tr></thead><tbody>
                        @foreach ($posts['byCategory'] as $c)<tr><td>{{ $c->label }}</td><td class="r">{{ $fmt($c->total) }}</td></tr>@endforeach
                    </tbody></table>
                </td>
            </tr></table>
        @endif

        <h3>Liste des enseignements publiés</h3>
        @if ($posts['items']->isEmpty())
            <p class="empty">Aucun enseignement publié sur la période.</p>
        @else
            <table class="data">
                <thead><tr><th style="width:18px">#</th><th style="width:58px">Date</th><th>Titre</th><th>Catégorie</th><th>Type</th><th>Auteur</th><th class="r">Vues</th><th class="r">J'aime</th></tr></thead>
                <tbody>
                @foreach ($posts['items'] as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td><td>{{ $d($p->published_at ?? $p->created_at) }}</td><td>{{ R::clean($p->title) }}</td>
                        <td>{{ $p->category?->name ?? '—' }}</td><td>{{ R::label($p->content_type) }}</td><td>{{ $p->author ?: '—' }}</td>
                        <td class="r">{{ $fmt($p->views) }}</td><td class="r">{{ $fmt($p->likes) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot><tr><td colspan="6">Total ({{ $fmt($posts['count']) }} enseignements)</td><td class="r">{{ $fmt($posts['views']) }}</td><td class="r">{{ $fmt($posts['likes']) }}</td></tr></tfoot>
            </table>
            @if ($posts['count'] > $maxRows)<p class="note">Seuls les {{ $maxRows }} premiers enseignements sont détaillés ; les totaux portent sur l'ensemble.</p>@endif
        @endif
    @endisset

    {{-- Vidéos --}}
    @isset($videos)
        <h2>{{ ++$num }}. Vidéos</h2>
        <table class="kpi"><tr>
            <td><div class="v">{{ $fmt($videos['count']) }}</div><div class="l">Vidéos publiées</div></td>
            <td><div class="v">{{ $fmt($videos['views']) }}</div><div class="l">Vues (plateformes)</div></td>
            <td><div class="v">{{ $fmt($videos['likes']) }}</div><div class="l">J'aime</div></td>
            <td><div class="v">{{ $fmt($videos['comments']) }}</div><div class="l">Commentaires</div></td>
        </tr></table>

        @if ($videos['byPlatform']->isNotEmpty())
            <h3>Répartition par plateforme</h3>
            <table class="data">
                <thead><tr><th>Plateforme</th><th class="r">Vidéos</th><th class="r">Vues</th></tr></thead>
                <tbody>
                @foreach ($videos['byPlatform'] as $pl)
                    <tr><td>{{ R::label($pl->label) }}</td><td class="r">{{ $fmt($pl->total) }}</td><td class="r">{{ $fmt($pl->views) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <h3>Liste des vidéos publiées</h3>
        @if ($videos['items']->isEmpty())
            <p class="empty">Aucune vidéo publiée sur la période.</p>
        @else
            <table class="data">
                <thead><tr><th style="width:18px">#</th><th style="width:58px">Date</th><th>Titre</th><th>Plateforme</th><th class="r">Vues</th><th class="r">J'aime</th><th class="r">Com.</th></tr></thead>
                <tbody>
                @foreach ($videos['items'] as $i => $v)
                    <tr>
                        <td>{{ $i + 1 }}</td><td>{{ $d($v->published_at ?? $v->created_at) }}</td><td>{{ R::clean($v->title) }}</td><td>{{ R::label($v->platform) }}</td>
                        <td class="r">{{ $fmt($v->view_count) }}</td><td class="r">{{ $fmt($v->likes_count) }}</td><td class="r">{{ $fmt($v->comments_count) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot><tr><td colspan="4">Total ({{ $fmt($videos['count']) }} vidéos)</td><td class="r">{{ $fmt($videos['views']) }}</td><td class="r">{{ $fmt($videos['likes']) }}</td><td class="r">{{ $fmt($videos['comments']) }}</td></tr></tfoot>
            </table>
            @if ($videos['count'] > $maxRows)<p class="note">Seules les {{ $maxRows }} premières vidéos sont détaillées ; les totaux portent sur l'ensemble.</p>@endif
            <p class="note">Les statistiques des vidéos proviennent de la dernière synchronisation avec chaque plateforme.</p>
        @endif
    @endisset

    {{-- Réseaux sociaux --}}
    @isset($social)
        <h2>{{ ++$num }}. Réseaux sociaux</h2>
        <table class="kpi"><tr>
            <td><div class="v">{{ $fmt($social['count']) }}</div><div class="l">Publications</div></td>
            <td><div class="v">{{ $fmt($social['views']) }}</div><div class="l">Vues</div></td>
            <td><div class="v">{{ $fmt($social['likes']) }}</div><div class="l">J'aime</div></td>
            <td><div class="v">{{ $fmt($social['comments'] + $social['shares']) }}</div><div class="l">Commentaires et partages</div></td>
        </tr></table>

        @if ($social['byPlatform']->isNotEmpty())
            <h3>Répartition par réseau</h3>
            <table class="data">
                <thead><tr><th>Réseau</th><th class="r">Publications</th><th class="r">Vues</th><th class="r">J'aime</th><th class="r">Com.</th><th class="r">Partages</th></tr></thead>
                <tbody>
                @foreach ($social['byPlatform'] as $pl)
                    <tr><td>{{ R::label($pl->label) }}</td><td class="r">{{ $fmt($pl->total) }}</td><td class="r">{{ $fmt($pl->views) }}</td><td class="r">{{ $fmt($pl->likes) }}</td><td class="r">{{ $fmt($pl->comments) }}</td><td class="r">{{ $fmt($pl->shares) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot><tr><td>Total</td><td class="r">{{ $fmt($social['count']) }}</td><td class="r">{{ $fmt($social['views']) }}</td><td class="r">{{ $fmt($social['likes']) }}</td><td class="r">{{ $fmt($social['comments']) }}</td><td class="r">{{ $fmt($social['shares']) }}</td></tr></tfoot>
            </table>
            <p class="small muted">Par format : {{ $social['byType']->map(fn ($t) => R::label($t->label) . ' ' . $fmt($t->total))->implode(' · ') }}</p>
        @endif

        <h3>Liste des publications</h3>
        @if ($social['items']->isEmpty())
            <p class="empty">Aucune publication synchronisée sur la période. Vérifiez la synchronisation dans Admin → Réseaux sociaux.</p>
        @else
            <table class="data">
                <thead><tr><th style="width:18px">#</th><th style="width:58px">Date</th><th>Publication</th><th>Réseau</th><th class="r">Vues</th><th class="r">J'aime</th><th class="r">Com.</th></tr></thead>
                <tbody>
                @foreach ($social['items'] as $i => $f)
                    <tr>
                        <td>{{ $i + 1 }}</td><td>{{ $d($f->published_at ?? $f->created_at) }}</td>
                        <td>{{ Str::limit(R::clean($f->title ?: $f->body) ?: '(sans texte)', 90) }}</td><td>{{ R::label($f->platform) }}</td>
                        <td class="r">{{ $fmt($f->view_count) }}</td><td class="r">{{ $fmt($f->likes_count) }}</td><td class="r">{{ $fmt($f->comments_count) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if ($social['count'] > $maxRows)<p class="note">Seules les {{ $maxRows }} premières publications sont détaillées ; les totaux portent sur l'ensemble.</p>@endif
            <p class="note">Statistiques issues de la dernière synchronisation avec chaque réseau.</p>
        @endif
    @endisset

    {{-- Ressources --}}
    @isset($resources)
        <h2>{{ ++$num }}. Ressources</h2>
        <table class="kpi"><tr>
            <td><div class="v">{{ $fmt($resources['count']) }}</div><div class="l">Ressources publiées</div></td>
            <td><div class="v">{{ $fmt($resources['downloads']) }}</div><div class="l">Téléchargements</div></td>
            <td style="border:0"></td><td style="border:0"></td>
        </tr></table>
        @if ($resources['items']->isEmpty())
            <p class="empty">Aucune ressource publiée sur la période.</p>
        @else
            <table class="data">
                <thead><tr><th style="width:18px">#</th><th style="width:58px">Date</th><th>Titre</th><th>Auteur</th><th>Catégorie</th><th class="r">Téléch.</th></tr></thead>
                <tbody>
                @foreach ($resources['items'] as $i => $r)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $d($r->published_at ?? $r->created_at) }}</td><td>{{ R::clean($r->title) }}</td><td>{{ $r->author ?: '—' }}</td><td>{{ $r->category ?: '—' }}</td><td class="r">{{ $fmt($r->downloads) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot><tr><td colspan="5">Total ({{ $fmt($resources['count']) }} ressources)</td><td class="r">{{ $fmt($resources['downloads']) }}</td></tr></tfoot>
            </table>
        @endif
    @endisset

    {{-- Engagement --}}
    @isset($engagement)
        <h2>{{ ++$num }}. Engagement et commentaires</h2>
        <table class="kpi"><tr>
            <td><div class="v">{{ $fmt($engagement['total']) }}</div><div class="l">Commentaires reçus</div></td>
            <td><div class="v">{{ $fmt($engagement['approved']) }}</div><div class="l">Approuvés</div></td>
            <td><div class="v">{{ $fmt($engagement['pending']) }}</div><div class="l">En attente de modération</div></td>
            <td><div class="v">{{ $fmt($engagement['adminReplies']) }}</div><div class="l">Réponses de l'équipe</div></td>
        </tr></table>
        <p>Répartition : <strong>{{ $fmt($engagement['onNews']) }}</strong> sur l'Écho de la mission, <strong>{{ $fmt($engagement['onPosts']) }}</strong> sur les enseignements.</p>

        <table class="two"><tr>
            <td>
                <h3>Articles les plus lus (cumul)</h3>
                <table class="data"><thead><tr><th>Titre</th><th class="r">Vues</th></tr></thead><tbody>
                    @forelse ($engagement['topNews'] as $t)<tr><td>{{ Str::limit(R::clean($t->title), 60) }}</td><td class="r">{{ $fmt($t->views) }}</td></tr>
                    @empty<tr><td colspan="2" class="empty">—</td></tr>@endforelse
                </tbody></table>
            </td>
            <td>
                <h3>Enseignements les plus vus (cumul)</h3>
                <table class="data"><thead><tr><th>Titre</th><th class="r">Vues</th></tr></thead><tbody>
                    @forelse ($engagement['topPosts'] as $t)<tr><td>{{ Str::limit(R::clean($t->title), 60) }}</td><td class="r">{{ $fmt($t->views) }}</td></tr>
                    @empty<tr><td colspan="2" class="empty">—</td></tr>@endforelse
                </tbody></table>
            </td>
        </tr></table>
    @endisset

    {{-- Messages et prière --}}
    @isset($contacts)
        <h2>{{ ++$num }}. Messages et demandes de prière</h2>
        <table class="kpi"><tr>
            <td><div class="v">{{ $fmt($contacts['total']) }}</div><div class="l">Messages reçus</div></td>
            <td><div class="v">{{ $fmt($contacts['replied']) }}</div><div class="l">Messages répondus</div></td>
            <td><div class="v">{{ $contacts['rate'] }} %</div><div class="l">Taux de réponse</div></td>
            <td style="border:0"></td>
        </tr></table>
        @if ($contacts['total'])
            <table class="two"><tr>
                <td>
                    <h3>Par type de demande</h3>
                    <table class="data"><thead><tr><th>Type</th><th class="r">Nombre</th></tr></thead><tbody>
                        @foreach ($contacts['byType'] as $t)<tr><td>{{ R::label($t->label) }}</td><td class="r">{{ $fmt($t->total) }}</td></tr>@endforeach
                    </tbody></table>
                </td>
                <td>
                    <h3>Par statut de traitement</h3>
                    <table class="data"><thead><tr><th>Statut</th><th class="r">Nombre</th></tr></thead><tbody>
                        @foreach ($contacts['byStatus'] as $s)<tr><td>{{ R::label($s->label) }}</td><td class="r">{{ $fmt($s->total) }}</td></tr>@endforeach
                    </tbody></table>
                </td>
            </tr></table>
            <p class="note">Par respect de la confidentialité, le contenu des messages et l'identité des expéditeurs ne figurent pas dans ce rapport.</p>
        @else
            <p class="empty">Aucun message reçu sur la période.</p>
        @endif
    @endisset

    {{-- Newsletter --}}
    @isset($newsletter)
        <h2>{{ ++$num }}. Newsletter</h2>
        <table class="kpi"><tr>
            <td><div class="v">{{ $fmt($newsletter['new']) }}</div><div class="l">Nouvelles inscriptions</div></td>
            <td><div class="v">{{ $fmt($newsletter['newActive']) }}</div><div class="l">Dont encore actives</div></td>
            <td><div class="v">{{ $fmt($newsletter['totalActive']) }}</div><div class="l">Abonnés actifs (total)</div></td>
            <td style="border:0"></td>
        </tr></table>
    @endisset

    {{-- Observations --}}
    <h2>{{ ++$num }}. Observations et recommandations</h2>
    @if ($observations)
        <div class="obs">{{ $observations }}</div>
    @else
        <p class="empty">Aucune observation formulée.</p>
    @endif

    {{-- Signature --}}
    <div class="sign">
        <table>
            <tr>
                <td></td>
                <td class="box">
                    <p>Fait à {{ $signatory['place'] }}, le {{ $generatedAt->translatedFormat('d F Y') }}</p>
                    <p class="role">{{ $signatory['role'] }}</p>
                    <div class="space">@if ($signatory['signature'])<img src="{{ $signatory['signature'] }}" alt="Signature">@endif</div>
                    <p class="name">{{ $signatory['name'] }}</p>
                </td>
            </tr>
        </table>
    </div>
</main>
</body>
</html>
