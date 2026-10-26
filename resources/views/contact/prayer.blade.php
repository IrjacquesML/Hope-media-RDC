@extends('layouts.app')

@section('title', 'Demande de Prière')
@section('meta_description', 'Soumettez vos demandes de prière à Hope Media RDC. Notre communauté prie pour vous.')

@section('content')
<section class="wrap" style="padding-top:calc(env(safe-area-inset-top,0px) + 92px)">
    <div class="prayer">
        <x-site.art theme="t-dawn" icon="fa-hands-praying" style="--gx:80%;--gy:10%" />
        <div class="prayer-in">
            <span class="hero-slogan" style="font-size:.95rem">Hope Media RDC</span>
            <h1>Ligne de prière</h1>
            <p>Envoyez votre sujet de prière : l’équipe d’intercession prie pour vous, en toute confidentialité.</p>
            <p class="verse">« Déchargez-vous sur lui de tous vos soucis, car lui-même prend soin de vous. » — 1 Pierre 5:7</p>
        </div>
    </div>
</section>

<div class="wrap page-body">
    <div class="alert info"><i class="fa-solid fa-heart" aria-hidden="true"></i>
        <span><strong>Comment fonctionne notre système de prière ?</strong><br>
        <span style="font-weight:400">Votre demande de prière sera transmise à nos pasteurs et aux membres de notre communauté. Ensemble, nous prierons pour vous selon vos intentions. Vos informations personnelles restent confidentielles et seront utilisées uniquement à titre de prière et de soutien spirituel.</span></span>
    </div>

    <div class="split">
        <form class="form-card" action="{{ route('contact.prayer.submit') }}" method="POST" id="prayerForm" novalidate>
            @csrf
            <h2>Votre sujet de prière</h2>
            <div class="alert" data-form-msg role="status" hidden></div>
            <div class="form-grid two">
                <div class="field">
                    <label for="prayer_name">Votre nom <span class="req">*</span></label>
                    <input class="input" type="text" id="prayer_name" name="name" required autocomplete="name">
                </div>
                <div class="field">
                    <label for="prayer_email">Votre email <span class="req">*</span></label>
                    <input class="input" type="email" id="prayer_email" name="email" required autocomplete="email">
                </div>
            </div>
            <div class="field">
                <label for="prayer_message">Votre demande de prière <span class="req">*</span></label>
                <textarea class="textarea" id="prayer_message" name="message" rows="8" required minlength="10" placeholder="Partagez en détail vos intentions de prière, vos défis ou vos demandes..."></textarea>
                <p class="hint">Minimum 10 caractères. Soyez aussi détaillé que vous le souhaitez.</p>
            </div>
            <div class="field">
                <span class="label">Catégories</span>
                <div class="chips">
                    @foreach ([['santé', 'Santé', 'fa-heart'], ['famille', 'Famille', 'fa-people-roof'], ['travail', 'Travail/Carrière', 'fa-briefcase'], ['financier', 'Besoins financiers', 'fa-money-bill'], ['spirituel', 'Croissance spirituelle', 'fa-cross'], ['autre', 'Autre', 'fa-ellipsis']] as [$val, $label, $icon])
                        <label><input type="checkbox" value="{{ $val }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}</label>
                    @endforeach
                </div>
            </div>
            <button class="btn btn-gold btn-block" type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i>Envoyer ma demande de prière</button>
            <p class="hint" style="text-align:center"><i class="fa-solid fa-lock" aria-hidden="true"></i> Vos informations sont sécurisées et traitées de manière confidentielle.</p>
        </form>

        <aside class="info-card">
            <h2>Information</h2>
            <p class="muted" style="margin:0">Nous croyons à la puissance de la prière communautaire. Vos demandes sont traitées avec respect et compassion.</p>
            <ul>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Prière confidentielle</li>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Soutien communautaire</li>
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Suivi spirituel</li>
            </ul>
        </aside>
    </div>

    <section aria-labelledby="testiTitle">
        <div class="row-head"><h2 id="testiTitle"><small>Ils témoignent</small>Témoignages de miracles</h2></div>
        <div class="quotes">
            <blockquote class="quote"><p>« J'ai soumis une demande de prière et dans les deux semaines, ma situation s'est transformée. Merci pour vos prières puissantes ! »</p><footer>— Marie D. · Août 2024</footer></blockquote>
            <blockquote class="quote"><p>« Les prières de cette communauté m'ont soutenu pendant ma maladie. Je me sens entouré d'amour et de foi. »</p><footer>— Jean-Paul K. · Juillet 2024</footer></blockquote>
            <blockquote class="quote"><p>« Après des mois de difficultés, j'ai trouvé un emploi. La prière collective a vraiment changé ma vie. »</p><footer>— Sylvie M. · Septembre 2024</footer></blockquote>
        </div>
    </section>

    <section aria-labelledby="faqTitle">
        <div class="row-head"><h2 id="faqTitle"><small>Vos questions</small>Questions fréquemment posées</h2></div>
        <div class="faq">
            <details><summary>Mes informations sont-elles confidentielles ? <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><p>Oui, vos informations personnelles restent strictement confidentielles et ne seront jamais partagées.</p></details>
            <details><summary>Combien de temps faut-il pour que la communauté prie pour moi ? <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><p>Vos demandes sont transmises aux pasteurs immédiatement. La prière commence dès réception.</p></details>
            <details><summary>Puis-je soumettre plusieurs demandes ? <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><p>Oui, vous pouvez soumettre autant de demandes que nécessaire. Chacune sera traitée avec soin.</p></details>
        </div>
    </section>
</div>
@endsection
