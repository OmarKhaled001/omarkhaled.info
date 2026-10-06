New inquiry ({{ strtoupper($submission->locale) }})

Name: {{ $submission->name }}
Company: {{ $submission->company ?: '—' }}
Email: {{ $submission->email }}
Project: {{ $type }}
Budget: {{ $budget }}

{{ $submission->message }}

Reply to this email to answer directly. Admin: {{ $adminUrl }}
