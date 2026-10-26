@extends('layouts.app')

@section('title', 'Écrire à la rédaction')
@section('meta_description', 'Contactez Hope Media RDC pour toute question, demande de prière ou témoignage')

@section('content')
<header class="page-head">
    <div class="wrap">
        <div class="eyebrow">La rédaction</div>
        <h1>Écrire à la rédaction</h1>
        <p>Une question biblique, un témoignage à partager, une remarque sur nos programmes ? L’équipe de Hope Media vous lit et vous répond.</p>
    </div>
</header>

<div class="wrap page-body">
    <div class="split">
        <form class="form-card" action="{{ route('contact.store') }}" method="POST" novalidate>
            @csrf
            <h2>Votre message</h2>
            @if ($errors->any())
                <div class="alert err" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>Vérifiez les champs signalés ci-dessous.</span></div>
            @endif

            <div class="form-grid two">
                <div class="field">
                    <label for="name">Votre nom <span class="req">*</span></label>
                    <input class="input" type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" @error('name') aria-invalid="true" @enderror>
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="email">Votre email <span class="req">*</span></label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-invalid="true" @enderror>
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="phone">Téléphone <span class="hint" style="display:inline">(optionnel)</span></label>
                    <input class="input" type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+243 …" @error('phone') aria-invalid="true" @enderror>
                    @error('phone')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="request_type">Type de demande <span class="req">*</span></label>
                    <select class="select" id="request_type" name="request_type" required @error('request_type') aria-invalid="true" @enderror>
                        <option value="">-- Sélectionner --</option>
                        <option value="contact" @selected(old('request_type') === 'contact')>Contact général</option>
                        <option value="prayer" @selected(old('request_type') === 'prayer')>Demande de prière</option>
                        <option value="question" @selected(old('request_type') === 'question')>Question biblique</option>
                        <option value="testimony" @selected(old('request_type') === 'testimony')>Témoignage</option>
                        <option value="feedback" @selected(old('request_type') === 'feedback')>Commentaire/Feedback</option>
                    </select>
                    @error('request_type')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="field">
                <label for="subject">Sujet <span class="req">*</span></label>
                <input class="input" type="text" id="subject" name="subject" value="{{ old('subject') }}" required @error('subject') aria-invalid="true" @enderror>
                @error('subject')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="message">Votre message <span class="req">*</span></label>
                <textarea class="textarea" id="message" name="message" rows="6" required @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                @error('message')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label class="check" for="accepted_terms">
                    <input type="checkbox" id="accepted_terms" name="accepted_terms" value="1" @checked(old('accepted_terms')) required>
                    <span>J'accepte les conditions d'utilisation et je consens au traitement de mes données <span class="req">*</span></span>
                </label>
                @error('accepted_terms')<p class="error">{{ $message }}</p>@enderror
            </div>
            <label class="check" for="subscribed_to_newsletter">
                <input type="checkbox" id="subscribed_to_newsletter" name="subscribed_to_newsletter" value="1" @checked(old('subscribed_to_newsletter'))>
                <span>M'inscrire à la newsletter pour recevoir les derniers enseignements</span>
            </label>
            <div><button class="btn btn-gold" type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i>Envoyer le message</button></div>
        </form>

        <aside class="info-card" aria-labelledby="coordTitle">
            <h2 id="coordTitle">La rédaction</h2>
            <ul>
                <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><small>Adresse</small>01 Lubumbashi, Q. Pigeon, C. Ngaliema, Kinshasa</span></li>
                <li><i class="fa-solid fa-phone" aria-hidden="true"></i><span><small>Téléphone</small><a href="tel:+243852283241">+243 852 283 241</a></span></li>
                <li><i class="fa-solid fa-envelope" aria-hidden="true"></i><span><small>Email</small><a href="mailto:hopemediasociaux@gmail.com">hopemediasociaux@gmail.com</a></span></li>
                <li><i class="fa-regular fa-clock" aria-hidden="true"></i><span><small>Horaires de la rédaction</small>Lundi - Vendredi : 9h00 - 17h00<br>Samedi : Fermé<br>Dimanche : 9h00 - 12h00</span></li>
            </ul>
            <a class="btn btn-line" href="{{ route('contact.prayer') }}"><i class="fa-solid fa-hands-praying" aria-hidden="true"></i>Ligne de prière</a>
        </aside>
    </div>
</div>
@endsection
