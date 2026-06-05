New storefront contact message

From: {{ $contactMessage->name }}
Email: {{ $contactMessage->email }}
@if ($contactMessage->phone)Phone: {{ $contactMessage->phone }}
@endif
@if ($contactMessage->company)Company: {{ $contactMessage->company }}
@endif
Subject: {{ $contactMessage->subject }}

Message:
{{ $contactMessage->message }}

View in admin: {{ $adminUrl }}
