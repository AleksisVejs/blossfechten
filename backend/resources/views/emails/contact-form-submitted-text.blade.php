New contact form submission

Name: {{ $senderName }}
Email: {{ $senderEmail }}
@if ($ip)
IP: {{ $ip }}
@endif

Message:
{{ $messageBody }}

Reply directly to this email to respond to {{ $senderName }}.
