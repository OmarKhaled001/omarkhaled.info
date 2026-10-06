<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<body style="margin:0;background:#F7F6F2;font-family:-apple-system,Segoe UI,Tahoma,Helvetica,Arial,sans-serif;color:#131316">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 12px">
<tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" dir="{{ $dir }}" style="max-width:560px;width:100%;background:#fff;border:1px solid #E2DFD6;border-radius:12px;text-align:{{ $dir === 'rtl' ? 'right' : 'left' }}">
<tr><td style="padding:32px">
    <p style="margin:0 0 20px;font-size:13px;color:#E8542A">&#9678; {{ $signature }}</p>
    <p style="margin:0 0 16px;font-size:16px;line-height:1.7">{{ __('contact.autoreply.greeting', ['name' => $firstName]) }}</p>
    <p style="margin:0 0 16px;font-size:16px;line-height:1.7">{{ __('contact.autoreply.body', ['hours' => $hours]) }}</p>
    <p style="margin:0 0 24px;font-size:16px;line-height:1.7">{{ __('contact.autoreply.meanwhile') }} <a href="{{ $workUrl }}" style="color:#C03B15">{{ __('contact.autoreply.work_link') }}</a></p>
    <p style="margin:0;font-size:16px;line-height:1.7">{{ __('contact.autoreply.signoff') }}<br>{{ $signature }}</p>
</td></tr>
</table>
<p style="font-size:12px;color:#55555D;margin-top:16px">{{ __('contact.autoreply.footer') }}</p>
</td></tr>
</table>
</body>
</html>
