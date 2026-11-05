@extends('emails.layout')

@section('preheader'){{ collect($items)->pluck('title')->take(3)->implode(' · ') }}@endsection

@section('body')
  <h1 style="margin:0 0 8px;font-size:22px;color:#0B1530;">{{ $firstName ? 'Bonjour ' . $firstName . ',' : 'Bonjour,' }}</h1>
  @if ($site->newsletter_intro)
    <p style="margin:0 0 22px;font-size:15px;line-height:1.6;">{!! nl2br(e($site->newsletter_intro)) !!}</p>
  @endif

  @foreach ($items as $item)
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 18px;border:1px solid #E3E8F4;border-radius:10px;overflow:hidden;">
      @if (! empty($item['image']))
        <tr><td><a href="{{ $item['url'] }}"><img src="{{ $item['image'] }}" width="542" alt="" style="display:block;width:100%;max-width:542px;height:auto;max-height:300px;object-fit:cover;"></a></td></tr>
      @endif
      <tr><td style="padding:14px 16px;">
        <div style="font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#9A5B00;">{{ $item['type'] }}</div>
        <a href="{{ $item['url'] }}" style="display:block;margin:4px 0 6px;font-size:18px;font-weight:bold;color:#0B1530;text-decoration:none;line-height:1.3;">{{ $item['title'] }}</a>
        @if (! empty($item['excerpt']))<p style="margin:0 0 10px;font-size:14px;line-height:1.55;color:#47537A;">{{ $item['excerpt'] }}</p>@endif
        <a href="{{ $item['url'] }}" style="font-size:14px;font-weight:bold;color:#1F3A93;text-decoration:none;">{{ str_starts_with($item['type'], 'Vidéo') ? 'Regarder' : 'Lire' }} →</a>
      </td></tr>
    </table>
  @endforeach

  <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:8px;"><tr>
    <td style="background:#FCA311;border-radius:8px;"><a href="{{ route('home') }}" style="display:inline-block;padding:12px 22px;color:#0B1530;font-weight:bold;text-decoration:none;font-size:15px;">Tout voir sur {{ $site->site_name }}</a></td>
  </tr></table>
@endsection
