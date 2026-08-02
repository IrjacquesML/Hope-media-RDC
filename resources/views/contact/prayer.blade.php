@extends('layouts.app')

@section('title', 'Demande de Prière')
@section('meta_description', 'Soumettez vos demandes de prière à Hope Media RDC. Notre communauté prie pour vous.')

@section('content')
    <!-- En-tête -->
    <div class="bg-gradient-to-r from-red-600 to-red-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <i class="fas fa-hands-praying mr-3"></i>
                Demande de Prière
            </h1>
            <p class="text-lg text-red-100">
                Partagez vos intentions de prière avec notre communauté
            </p>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Message informatif -->
        <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded-lg mb-12">
            <h2 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
                <i class="fas fa-heart text-red-600 text-xl"></i>
                Comment fonctionne notre système de prière?
            </h2>
            <p class="text-blue-800">
                Votre demande de prière sera transmise à nos pasteurs et aux membres de notre communauté. 
                Ensemble, nous prierons pour vous selon vos intentions. Vos informations personnelles restent 
                confidentielles et seront utilisées uniquement à titre de prière et de soutien spirituel.
            </p>
        </div>

        <!-- Grille: Formulaire + Témoignages -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Formulaire de prière (2 colonnes) -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">
                        <i class="fas fa-comment-dots mr-2 text-red-600"></i>
                        Soumettre une demande de prière
                    </h2>

                    <form action="{{ route('contact.prayer.submit') }}" method="POST" id="prayerForm" class="space-y-6">
                        @csrf

                        <!-- Nom -->
                        <div>
                            <label for="prayer_name" class="block text-sm font-medium text-slate-700 mb-2">
                                Votre nom *
                            </label>
                            <input 
                                type="text" 
                                id="prayer_name" 
                                name="name" 
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
                                required
                            >
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="prayer_email" class="block text-sm font-medium text-slate-700 mb-2">
                                Votre email *
                            </label>
                            <input 
                                type="email" 
                                id="prayer_email" 
                                name="email" 
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
                                required
                            >
                        </div>

                        <!-- Demande de prière -->
                        <div>
                            <label for="prayer_message" class="block text-sm font-medium text-slate-700 mb-2">
                                Votre demande de prière *
                            </label>
                            <textarea 
                                id="prayer_message" 
                                name="message" 
                                rows="8"
                                placeholder="Partagez en détail vos intentions de prière, vos défis ou vos demandes..."
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
                                required
                            ></textarea>
                            <p class="text-xs text-slate-500 mt-2">
                                Minimum 10 caractères. Soyez aussi détaillé que vous le souhaitez.
                            </p>
                        </div>

                        <!-- Bouton Submit -->
                        <div class="pt-4">
                            <button 
                                type="submit" 
                                class="w-full bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition flex items-center justify-center gap-2"
                            >
                                <i class="fas fa-paper-plane"></i>
                                Envoyer ma demande de prière
                            </button>
                        </div>

                        <p class="text-xs text-slate-500 text-center">
                            <i class="fas fa-lock mr-1"></i>
                            Vos informations sont sécurisées et traitées de manière confidentielle.
                        </p>
                    </form>
                </div>
            </div>

            <!-- Barre latérale: Info + Catégories -->
            <aside class="lg:col-span-1">
                <!-- Info -->
                <div class="bg-red-50 rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-bold text-red-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-red-600"></i>
                        Information
                    </h3>
                    <p class="text-sm text-red-800 mb-4">
                        Nous croyons à la puissance de la prière communautaire. Vos demandes sont traitées avec respect et compassion.
                    </p>
                    <div class="space-y-3 text-sm text-red-800">
                        <div class="flex gap-2">
                            <i class="fas fa-check-circle text-green-600 flex-shrink-0"></i>
                            <span>Prière confidentielle</span>
                        </div>
                        <div class="flex gap-2">
                            <i class="fas fa-check-circle text-green-600 flex-shrink-0"></i>
                            <span>Soutien communautaire</span>
                        </div>
                        <div class="flex gap-2">
                            <i class="fas fa-check-circle text-green-600 flex-shrink-0"></i>
                            <span>Suivi spirituel</span>
                        </div>
                    </div>
                </div>

                <!-- Catégories de prière -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">
                        <i class="fas fa-tags text-blue-600 mr-2"></i>
                        Catégories
                    </h3>
                    <div class="space-y-2 text-sm">
                        <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                            <input type="checkbox" class="rounded" value="santé">
                            <i class="fas fa-heart text-red-500"></i>
                            <span>Santé</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                            <input type="checkbox" class="rounded" value="famille">
                            <i class="fas fa-people-arrows text-blue-600"></i>
                            <span>Famille</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                            <input type="checkbox" class="rounded" value="travail">
                            <i class="fas fa-briefcase text-purple-600"></i>
                            <span>Travail/Carrière</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                            <input type="checkbox" class="rounded" value="financier">
                            <i class="fas fa-money-bill text-green-600"></i>
                            <span>Besoins financiers</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                            <input type="checkbox" class="rounded" value="spirituel">
                            <i class="fas fa-cross text-blue-700"></i>
                            <span>Croissance spirituelle</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                            <input type="checkbox" class="rounded" value="autre">
                            <i class="fas fa-ellipsis-h"></i>
                            <span>Autre</span>
                        </label>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Témoignages -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-8 mb-12">
            <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                <i class="fas fa-quote-left text-blue-600"></i>
                Témoignages de miracles
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <p class="text-slate-700 mb-4 italic">
                        "J'ai soumis une demande de prière et dans les deux semaines, ma situation s'est transformée. Merci pour vos prières puissantes!"
                    </p>
                    <p class="font-semibold text-slate-900">- Marie D.</p>
                    <p class="text-xs text-slate-500">Août 2024</p>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <p class="text-slate-700 mb-4 italic">
                        "Les prières de cette communauté m'ont soutenu pendant ma maladie. Je me sens entouré d'amour et de foi."
                    </p>
                    <p class="font-semibold text-slate-900">- Jean-Paul K.</p>
                    <p class="text-xs text-slate-500">Juillet 2024</p>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <p class="text-slate-700 mb-4 italic">
                        "Après des mois de difficultés, j'ai trouvé un emploi. La prière collective a vraiment changé ma vie."
                    </p>
                    <p class="font-semibold text-slate-900">- Sylvie M.</p>
                    <p class="text-xs text-slate-500">Septembre 2024</p>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Questions fréquemment posées</h2>
            <div class="space-y-4">
                <details class="border-b pb-4 cursor-pointer">
                    <summary class="font-semibold text-slate-900 flex items-center justify-between">
                        Mes informations sont-elles confidentielles?
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="text-slate-600 mt-2">Oui, vos informations personnelles restent strictement confidentielles et ne seront jamais partagées.</p>
                </details>
                <details class="border-b pb-4 cursor-pointer">
                    <summary class="font-semibold text-slate-900 flex items-center justify-between">
                        Combien de temps faut-il pour que la communauté prie pour moi?
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="text-slate-600 mt-2">Vos demandes sont transmises aux pasteurs immédiatement. La prière commence dès réception.</p>
                </details>
                <details class="border-b pb-4 cursor-pointer">
                    <summary class="font-semibold text-slate-900 flex items-center justify-between">
                        Puis-je soumettre plusieurs demandes?
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <p class="text-slate-600 mt-2">Oui, vous pouvez soumettre autant de demandes que nécessaire. Chacune sera traitée avec soin.</p>
                </details>
            </div>
        </div>
    </div>

    <!-- Script pour afficher les messages de succès -->
    <script>
        document.getElementById('prayerForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            // Ici, vous pouvez ajouter une requête AJAX si vous le souhaitez
            // Pour l'instant, nous soumettons le formulaire normalement
            this.submit();
        });
    </script>
@endsection
