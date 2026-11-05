@extends('emails.layout')

@section('preheader'){{ \Illuminate\Support\Str::limit($site->newsletter_welcome_text, 90) }}@endsection

@section('body')
  <h1 style="margin:0 0 12px;font-size:22px;color:#0B1530;">Bienvenue{{ $subscriber->name ? ', ' . strtok($subscriber->name, ' ') : '' }} !</h1>
  @if ($site->newsletter_welcome_text)
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;">{!! nl2br(e($site->newsletter_welcome_text)) !!}</p>
  @endif
  <table role="presentation" cellspacing="0" cellpadding="0"><tr>
    <td style="background:#FCA311;border-radius:8px;"><a href="{{ route('videos.index') }}" style="display:inline-block;padding:12px 22px;color:#0B1530;font-weight:bold;text-decoration:none;font-size:15px;">Regarder nos vidéos</a></td>
    <td width="10"></td>
    <td style="border:2px solid #1F3A93;border-radius:8px;"><a href="{{ route('posts.index') }}" style="display:inline-block;padding:10px 20px;color:#1F3A93;font-weight:bold;text-decoration:none;font-size:15px;">Les enseignements</a></td>
  </tr></table>
@endsection
