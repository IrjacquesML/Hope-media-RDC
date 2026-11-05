@extends('emails.layout')

@section('body')
  <h1 style="margin:0 0 12px;font-size:22px;color:#0B1530;">L’envoi d’e-mails fonctionne ✔</h1>
  <p style="margin:0 0 12px;font-size:15px;line-height:1.6;">Ce message de test a été envoyé depuis l’administration de {{ $site->site_name }}, le {{ now()->translatedFormat('d F Y à H:i') }}.</p>
  <p style="margin:0;font-size:15px;line-height:1.6;">La newsletter, les e-mails de bienvenue et les réponses aux messages partiront avec ces mêmes réglages.</p>
@endsection
