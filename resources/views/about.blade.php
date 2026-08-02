@extends('layouts.app')

@section('title', 'À propos')
@section('meta_description', 'Découvrez qui nous sommes — Hope Media RDC, la plateforme médiatique officielle de l\'Église Adventiste du 7ème Jour, Union du Congo Ouest.')

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
<div class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-slate-900 text-white overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-blue-200 text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
            <i class="fas fa-church"></i> Église Adventiste du 7ème Jour — Union du Congo Ouest
        </div>
        <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight">
            À propos de<br><span class="text-blue-300">Hope Media RDC</span>
        </h1>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
            Une plateforme media au service de la proclamation de l'Évangile en République Démocratique du Congo et au-delà.
        </p>
    </div>
</div>

{{-- ── Qui sommes-nous ──────────────────────────────────────────────────── --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
            <div>
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Qui sommes-nous</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2 mb-6">Une voix pour l'espoir</h2>
                <p class="text-slate-600 leading-relaxed mb-4">
                    <strong>Hope Media RDC</strong> est la plateforme médiatique officielle de l'Église Adventiste du 7ème Jour —
                    Union du Congo Ouest (UCO). Fondée pour répondre à la mission évangélique dans un monde de plus en plus connecté,
                    notre plateforme diffuse des contenus spirituels, bibliques et communautaires sur toutes les grandes plateformes numériques.
                </p>
                <p class="text-slate-600 leading-relaxed mb-4">
                    Nous croyons que la technologie est un outil puissant pour partager la Bonne Nouvelle. C'est pourquoi nous publions
                    chaque semaine des prédications, témoignages, études bibliques et l'Écho de la mission sur YouTube, Facebook,
                    Instagram et TikTok.
                </p>
                <p class="text-slate-600 leading-relaxed">
                    Notre équipe de bénévoles et de techniciens dévoués travaille chaque jour pour produire du contenu de qualité
                    qui édifie, encourage et transforme des vies.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-2xl p-6 text-center">
                    <i class="fas fa-users text-4xl text-blue-600 mb-3"></i>
                    <div class="text-3xl font-extrabold text-slate-900">500K+</div>
                    <div class="text-sm text-slate-500 mt-1">Abonnés cumulés</div>
                </div>
                <div class="bg-red-50 rounded-2xl p-6 text-center">
                    <i class="fa-brands fa-youtube text-4xl text-red-500 mb-3"></i>
                    <div class="text-3xl font-extrabold text-slate-900">4</div>
                    <div class="text-sm text-slate-500 mt-1">Plateformes actives</div>
                </div>
                <div class="bg-green-50 rounded-2xl p-6 text-center">
                    <i class="fas fa-video text-4xl text-green-600 mb-3"></i>
                    <div class="text-3xl font-extrabold text-slate-900">1000+</div>
                    <div class="text-sm text-slate-500 mt-1">Vidéos publiées</div>
                </div>
                <div class="bg-purple-50 rounded-2xl p-6 text-center">
                    <i class="fas fa-globe-africa text-4xl text-purple-600 mb-3"></i>
                    <div class="text-3xl font-extrabold text-slate-900">50+</div>
                    <div class="text-sm text-slate-500 mt-1">Pays touchés</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Notre mission / Vision / Valeurs ───────────────────────────────────── --}}
<section class="py-20 bg-slate-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Notre ADN</span>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2">Mission, Vision & Valeurs</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-5">
                    <i class="fas fa-bullseye text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Notre Mission</h3>
                <p class="text-slate-600 leading-relaxed">
                    Proclamer le message des trois anges (Apocalypse 14) à travers les médias numériques modernes,
                    en touchant chaque foyer, chaque cœur et chaque nation avec l'espérance de l'Évangile.
                </p>
            </div>
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-5">
                    <i class="fas fa-eye text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Notre Vision</h3>
                <p class="text-slate-600 leading-relaxed">
                    Être la référence médiatique adventiste en Afrique Centrale, produisant du contenu spirituel
                    de haute qualité qui prépare les gens au retour imminent de Jésus-Christ.
                </p>
            </div>
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-5">
                    <i class="fas fa-heart text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Nos Valeurs</h3>
                <ul class="text-slate-600 space-y-2 text-sm leading-relaxed">
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Fidélité à la Parole de Dieu</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Excellence dans la production</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Service communautaire</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Intégrité et transparence</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Innovation au service de l'Évangile</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- ── Ce que nous faisons ─────────────────────────────────────────────── --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Nos activités</span>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2">Ce que nous faisons</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="flex gap-5 p-6 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-video text-red-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">Productions vidéo</h4>
                    <p class="text-sm text-slate-500">Prédications, études bibliques, témoignages et programmes spéciaux filmés et diffusés chaque semaine.</p>
                </div>
            </div>
            <div class="flex gap-5 p-6 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-broadcast-tower text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">Diffusion en direct</h4>
                    <p class="text-sm text-slate-500">Retransmission en live des cultes du sabbat, campagnes d'évangélisation et événements spéciaux.</p>
                </div>
            </div>
            <div class="flex gap-5 p-6 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-headphones text-green-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">Podcasts & Audio</h4>
                    <p class="text-sm text-slate-500">Sermons et dévotions en format audio pour vous accompagner dans vos déplacements quotidiens.</p>
                </div>
            </div>
            <div class="flex gap-5 p-6 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition">
                <div class="flex-shrink-0 w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-newspaper text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">Écho de la mission</h4>
                    <p class="text-sm text-slate-500">Informations et nouvelles de la communauté adventiste en RDC et dans la région.</p>
                </div>
            </div>
            <div class="flex gap-5 p-6 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition">
                <div class="flex-shrink-0 w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-pray text-pink-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">Intercession & Prière</h4>
                    <p class="text-sm text-slate-500">Service de demandes de prière disponible 24h/24 pour accompagner chaque fidèle dans ses besoins.</p>
                </div>
            </div>
            <div class="flex gap-5 p-6 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition">
                <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">Formation biblique</h4>
                    <p class="text-sm text-slate-500">Cours et séries thématiques pour approfondir votre connaissance des Écritures et grandir spirituellement.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Nos plateformes ─────────────────────────────────────────────────── --}}
<section class="py-20 bg-gradient-to-br from-slate-900 to-blue-950 text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="text-blue-400 font-semibold text-sm uppercase tracking-widest">Rejoignez-nous</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-2 mb-4">Suivez-nous sur les réseaux</h2>
        <p class="text-slate-400 max-w-xl mx-auto mb-12">
            Nos contenus sont disponibles gratuitement sur toutes les grandes plateformes. Abonnez-vous pour ne rien manquer.
        </p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <a href="{{ route('videos.index', ['platform' => 'youtube']) }}"
               class="group bg-white/5 hover:bg-red-600 border border-white/10 hover:border-red-500 rounded-2xl p-8 flex flex-col items-center gap-3 transition-all duration-300">
                <i class="fa-brands fa-youtube text-5xl text-red-400 group-hover:text-white transition"></i>
                <span class="font-bold text-lg">YouTube</span>
                <span class="text-xs text-slate-400 group-hover:text-red-100 transition">Vidéos & Lives</span>
            </a>
            <a href="{{ route('videos.index', ['platform' => 'facebook']) }}"
               class="group bg-white/5 hover:bg-blue-700 border border-white/10 hover:border-blue-500 rounded-2xl p-8 flex flex-col items-center gap-3 transition-all duration-300">
                <i class="fa-brands fa-facebook text-5xl text-blue-400 group-hover:text-white transition"></i>
                <span class="font-bold text-lg">Facebook</span>
                <span class="text-xs text-slate-400 group-hover:text-blue-100 transition">Posts & Stories</span>
            </a>
            <a href="{{ route('videos.index', ['platform' => 'instagram']) }}"
               class="group bg-white/5 hover:bg-pink-600 border border-white/10 hover:border-pink-500 rounded-2xl p-8 flex flex-col items-center gap-3 transition-all duration-300">
                <i class="fa-brands fa-instagram text-5xl text-pink-400 group-hover:text-white transition"></i>
                <span class="font-bold text-lg">Instagram</span>
                <span class="text-xs text-slate-400 group-hover:text-pink-100 transition">Reels & Photos</span>
            </a>
            <a href="{{ route('videos.index', ['platform' => 'tiktok']) }}"
               class="group bg-white/5 hover:bg-slate-700 border border-white/10 hover:border-slate-400 rounded-2xl p-8 flex flex-col items-center gap-3 transition-all duration-300">
                <i class="fa-brands fa-tiktok text-5xl text-slate-300 group-hover:text-white transition"></i>
                <span class="font-bold text-lg">TikTok</span>
                <span class="text-xs text-slate-400 group-hover:text-slate-100 transition">Courtes vidéos</span>
            </a>
        </div>
    </div>
</section>

{{-- ── L'Église Adventiste ─────────────────────────────────────────────── --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-start">
            <div>
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Notre famille</span>
                <h2 class="text-3xl font-bold text-slate-900 mt-2 mb-6">L'Église Adventiste du 7ème Jour</h2>
                <p class="text-slate-600 leading-relaxed mb-4">
                    L'Église Adventiste du 7ème Jour est un mouvement chrétien mondial présent dans plus de <strong>200 pays</strong>
                    avec plus de <strong>21 millions de membres</strong>. En RDC, l'Église est organisée en plusieurs unions,
                    dont l'<strong>Union du Congo Ouest (UCO)</strong> à laquelle Hope Media RDC est rattachée.
                </p>
                <p class="text-slate-600 leading-relaxed mb-4">
                    Les adventistes du 7ème jour accordent une importance particulière à l'étude des Écritures, à la santé holistique,
                    au sabbat (le 7ème jour) et à la préparation au retour de Christ.
                </p>
                <p class="text-slate-600 leading-relaxed">
                    Hope Media RDC est fière de porter haut les valeurs de cette église et de les partager avec le monde entier
                    à travers les outils numériques modernes.
                </p>
            </div>
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-5 rounded-xl bg-blue-50 border border-blue-100">
                    <i class="fas fa-book-bible text-blue-600 text-xl mt-0.5 flex-shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Fondée sur la Bible</h4>
                        <p class="text-sm text-slate-600">La Sainte Bible est notre seule règle de foi et de pratique.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-5 rounded-xl bg-purple-50 border border-purple-100">
                    <i class="fas fa-calendar-week text-purple-600 text-xl mt-0.5 flex-shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Le Sabbat du 7ème jour</h4>
                        <p class="text-sm text-slate-600">Nous observons le samedi (sabbat) comme jour saint selon le 4ème commandement.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-5 rounded-xl bg-green-50 border border-green-100">
                    <i class="fas fa-heart-pulse text-green-600 text-xl mt-0.5 flex-shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Santé & Bien-être</h4>
                        <p class="text-sm text-slate-600">Nous promouvons un mode de vie sain comme témoignage de notre foi.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-5 rounded-xl bg-yellow-50 border border-yellow-100">
                    <i class="fas fa-globe text-yellow-600 text-xl mt-0.5 flex-shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Mission mondiale</h4>
                        <p class="text-sm text-slate-600">Présents dans plus de 200 pays, nous œuvrons pour une évangélisation globale.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── CTA Contact ─────────────────────────────────────────────────────── --}}
<section class="py-16 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Vous avez des questions ?</h2>
        <p class="text-blue-100 text-lg mb-8 max-w-xl mx-auto">
            Notre équipe est disponible pour répondre à toutes vos questions spirituelles, techniques ou communautaires.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('contact.create') }}"
               class="bg-white text-blue-700 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
                <i class="fas fa-envelope mr-2"></i> Nous contacter
            </a>
            <a href="{{ route('contact.prayer') }}"
               class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition">
                <i class="fas fa-pray mr-2"></i> Demande de prière
            </a>
        </div>
    </div>
</section>

@endsection
