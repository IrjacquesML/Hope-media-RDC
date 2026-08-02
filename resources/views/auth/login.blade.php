<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Hope Media RDC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-600 to-blue-800 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Card de Login -->
        <div class="bg-white rounded-lg shadow-2xl p-8">
            <!-- En-tête -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <i class="fas fa-shield-alt text-blue-600 text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2">Admin Login</h1>
                <p class="text-slate-600">Hope Media RDC - Panneau d'Administration</p>
            </div>

            <!-- Messages d'erreur -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-600 mt-1 flex-shrink-0"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p class="text-red-700 text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Formulaire de login -->
            <form action="{{ route('auth.login') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-blue-600"></i>
                        Adresse Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 transition @error('email') border-red-500 @enderror"
                        placeholder="hopemediasociaux@gmail.com"
                        required
                    >
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-600"></i>
                        Mot de Passe
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:outline-none focus:border-blue-600 transition @error('password') border-red-500 @enderror"
                        placeholder="••••••••"
                        required
                    >
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Se souvenir de moi -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember" 
                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                    >
                    <label for="remember" class="ml-2 text-sm text-slate-600">
                        Se souvenir de moi
                    </label>
                </div>

                <!-- Bouton de connexion -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    Connexion
                </button>
            </form>

            <!-- Divider -->
            <div class="my-6 flex items-center gap-3">
                <div class="flex-1 border-t border-slate-300"></div>
                <span class="text-slate-400 text-sm">ou</span>
                <div class="flex-1 border-t border-slate-300"></div>
            </div>

            <!-- Retour à l'accueil -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center justify-center gap-2 transition">
                    <i class="fas fa-arrow-left"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-white text-sm">
            <p>&copy; 2024 Hope Media RDC. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
