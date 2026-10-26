@extends('layouts.app')

@section('title', 'Désabonnement')

@section('content')
<header class="page-head">
    <div class="wrap">
        <div class="eyebrow">Newsletter</div>
        <h1>Désabonnement effectué</h1>
        <p>Vous avez bien été désabonné(e) de notre newsletter. Vous ne recevrez plus nos Échos de la mission.</p>
    </div>
</header>
<div class="wrap page-body">
    <div><a class="btn btn-gold" href="{{ route('news.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Retour aux articles</a></div>
</div>
@endsection
