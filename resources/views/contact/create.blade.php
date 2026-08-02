@extends('layouts.app')

@section('title', 'Nous Contacter')
@section('meta_description', 'Contactez Hope Media RDC pour toute question, demande de prière ou témoignage')

@section('content')
    <!-- En-tête -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-envelope mr-3"></i>
                Nous Contacter
            </h1>
            <p class="text-lg text-blue-100">Vos questions, demandes de prière et commentaires nous sont précieux</p>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Informations de contact -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-blue-600"></i>
                        Adresse
                    </h3>
                    <p class="text-slate-600">
                        01 Lubumbashi, Q. Pigeon, C. Ngaliema, Kinshasa
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-phone text-green-600"></i>
                        Téléphone
                    </h3>
                    <p class="text-slate-600">
                        <a href="tel:+243852283241" class="hover:text-blue-600">+243 852 283 241</a>
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-envelope text-red-600"></i>
                        Email
                    </h3>
                    <p class="text-slate-600">
                        <a href="mailto:hopemediasociaux@gmail.com" class="hover:text-blue-600">hopemediasociaux@gmail.com</a>
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-clock text-yellow-600"></i>
                        Horaires
                    </h3>
                    <p class="text-slate-600 text-sm">
                        Lundi - Vendredi: 9h00 - 17h00<br>
                        Samedi: Fermé<br>
                        Dimanche: 9h00 - 12h00
                    </p>
                </div>
            </div>

            <!-- Formulaire de contact -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">
                        <i class="fas fa-comment-dots mr-2 text-blue-600"></i>
                        Envoyez-nous un message
                    </h2>

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Nom -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-user mr-2"></i>
                                Votre nom *
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 @error('name') border-red-500 @enderror"
                                required
                            >
                            @error('name')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-envelope mr-2"></i>
                                Votre email *
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 @error('email') border-red-500 @enderror"
                                required
                            >
                            @error('email')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Téléphone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-phone mr-2"></i>
                                Téléphone (optionnel)
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value="{{ old('phone') }}"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 @error('phone') border-red-500 @enderror"
                            >
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type de demande -->
                        <div>
                            <label for="request_type" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-list mr-2"></i>
                                Type de demande *
                            </label>
                            <select 
                                id="request_type" 
                                name="request_type"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 @error('request_type') border-red-500 @enderror"
                                required
                            >
                                <option value="">-- Sélectionner --</option>
                                <option value="contact" {{ old('request_type') === 'contact' ? 'selected' : '' }}>Contact général</option>
                                <option value="prayer" {{ old('request_type') === 'prayer' ? 'selected' : '' }}>Demande de prière</option>
                                <option value="question" {{ old('request_type') === 'question' ? 'selected' : '' }}>Question biblique</option>
                                <option value="testimony" {{ old('request_type') === 'testimony' ? 'selected' : '' }}>Témoignage</option>
                                <option value="feedback" {{ old('request_type') === 'feedback' ? 'selected' : '' }}>Commentaire/Feedback</option>
                            </select>
                            @error('request_type')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sujet -->
                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-heading mr-2"></i>
                                Sujet *
                            </label>
                            <input 
                                type="text" 
                                id="subject" 
                                name="subject" 
                                value="{{ old('subject') }}"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 @error('subject') border-red-500 @enderror"
                                required
                            >
                            @error('subject')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-comment mr-2"></i>
                                Votre message *
                            </label>
                            <textarea 
                                id="message" 
                                name="message" 
                                rows="6"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 @error('message') border-red-500 @enderror"
                                required
                            >{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Consentement -->
                        <div class="flex items-start gap-3">
                            <input 
                                type="checkbox" 
                                id="accepted_terms" 
                                name="accepted_terms" 
                                value="1"
                                {{ old('accepted_terms') ? 'checked' : '' }}
                                class="mt-1 @error('accepted_terms') border-red-500 @enderror"
                                required
                            >
                            <label for="accepted_terms" class="text-sm text-slate-600">
                                J'accepte les <a href="#" class="text-blue-600 hover:underline">conditions d'utilisation</a> et je consens au traitement de mes données *
                            </label>
                        </div>
                        @error('accepted_terms')
                            <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror

                        <!-- Newsletter -->
                        <div class="flex items-start gap-3">
                            <input 
                                type="checkbox" 
                                id="subscribed_to_newsletter" 
                                name="subscribed_to_newsletter" 
                                value="1"
                                {{ old('subscribed_to_newsletter') ? 'checked' : '' }}
                                class="mt-1"
                            >
                            <label for="subscribed_to_newsletter" class="text-sm text-slate-600">
                                M'inscrire à la newsletter pour recevoir les derniers enseignements
                            </label>
                        </div>

                        <!-- Bouton Submit -->
                        <div class="pt-4">
                            <button 
                                type="submit" 
                                class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2"
                            >
                                <i class="fas fa-paper-plane"></i>
                                Envoyer le message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
