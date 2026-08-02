@extends('layouts.app')

@section('title', 'Désabonnement')

@section('content')
<div class="max-w-lg mx-auto px-4 py-24 text-center">
    <div class="bg-white rounded-2xl shadow-lg p-10">
        <div class="text-5xl text-slate-400 mb-4">
            <i class="fas fa-envelope-open"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-3">Désabonnement effectué</h1>
        <p class="text-slate-600 mb-6">
            Vous avez bien été désabonné(e) de notre newsletter. Vous ne recevrez plus nos Échos de la mission.
        </p>
        <a href="{{ route('news.index') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux articles
        </a>
    </div>
</div>
@endsection
