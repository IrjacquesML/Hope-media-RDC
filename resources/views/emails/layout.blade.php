{{-- Gabarit des e-mails (tableaux et styles en ligne : compatibles Gmail, Outlook, téléphones). --}}
@php $logo = $site->logo_light ? asset($site->logo_light) : null; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title>{{ $site->site_name }}</title>
</head>
<body style="margin:0;padding:0;background:#EEF1F8;font-family:Arial,Helvetica,sans-serif;color:#1b2440;">
@hasSection('preheader')<div style="display:none;max-height:0;overflow:hidden;opacity:0;">@yield('preheader')</div>@endif
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#EEF1F8;">
  <tr><td align="center" style="padding:24px 12px;">
    <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">
      <tr><td style="background:#0B1530;padding:22px 28px;border-bottom:4px solid #FCA311;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>
          @if ($logo)<td width="64" style="padding-right:14px;"><img src="{{ $logo }}" width="56" alt="" style="display:block;width:56px;height:auto;background:#fff;border-radius:8px;padding:3px;"></td>@endif
          <td>
            <div style="font-size:20px;font-weight:bold;color:#ffffff;">{{ $site->site_name }}</div>
            @if ($site->slogan)<div style="font-size:13px;color:#FCA311;letter-spacing:1px;text-transform:uppercase;">{{ $site->slogan }}</div>@endif
          </td>
        </tr></table>
      </td></tr>
      <tr><td style="padding:28px;">
        @yield('body')
      </td></tr>
      <tr><td style="background:#F4F6FB;padding:20px 28px;font-size:12px;line-height:1.6;color:#5a6585;">
        @php $socials = \App\Support\Site::socialLinks(); @endphp
        @if ($socials)
          <p style="margin:0 0 8px;">Suivez-nous :
            @foreach ($socials as [$label, $icon, $url])
              <a href="{{ $url }}" style="color:#1F3A93;text-decoration:none;font-weight:bold;">{{ $label }}</a>@if (! $loop->last) · @endif
            @endforeach
          </p>
        @endif
        <p style="margin:0 0 8px;">
          {{ $site->site_full_name }}@if ($site->contact_address) — {{ $site->contact_address }}@endif
          @if ($site->contact_email)<br><a href="mailto:{{ $site->contact_email }}" style="color:#1F3A93;">{{ $site->contact_email }}</a>@endif
        </p>
        @isset($unsubscribeUrl)
          <p style="margin:0;">Vous recevez cet e-mail car vous êtes abonné(e) à la newsletter. <a href="{{ $unsubscribeUrl }}" style="color:#5a6585;">Se désabonner</a></p>
        @endisset
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
