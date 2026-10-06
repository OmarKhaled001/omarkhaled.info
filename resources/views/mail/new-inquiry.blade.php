<!DOCTYPE html>
<html lang="en">
<body style="margin:0;background:#F7F6F2;font-family:-apple-system,Segoe UI,Helvetica,Arial,sans-serif;color:#131316">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 12px">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border:1px solid #E2DFD6;border-radius:12px">
<tr><td style="padding:28px 32px;border-bottom:1px solid #E2DFD6">
    <p style="margin:0;font:12px/1 ui-monospace,Menlo,monospace;letter-spacing:.08em;text-transform:uppercase;color:#C03B15">New inquiry · {{ strtoupper($submission->locale) }}</p>
    <h1 style="margin:12px 0 0;font-size:22px">{{ $submission->name }}@if ($submission->company) <span style="color:#55555D;font-weight:400">· {{ $submission->company }}</span>@endif</h1>
</td></tr>
<tr><td style="padding:24px 32px">
    <table role="presentation" width="100%" style="font-size:14px">
        <tr><td style="color:#55555D;padding:4px 0;width:120px">Email</td><td><a href="mailto:{{ $submission->email }}" style="color:#131316">{{ $submission->email }}</a></td></tr>
        <tr><td style="color:#55555D;padding:4px 0">Project</td><td>{{ $type }}</td></tr>
        <tr><td style="color:#55555D;padding:4px 0">Budget</td><td>{{ $budget }}</td></tr>
    </table>
    <div style="margin-top:20px;padding:16px;background:#F7F6F2;border-radius:8px;white-space:pre-wrap;font-size:15px;line-height:1.6" @if ($submission->locale === 'ar') dir="rtl" @endif>{{ $submission->message }}</div>
    <p style="margin:24px 0 0;font-size:13px;color:#55555D">Reply to this email to answer {{ $submission->name }} directly · <a href="{{ $adminUrl }}" style="color:#C03B15">Open in admin</a></p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
