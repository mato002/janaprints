New storefront quote request

Customer: {{ $quoteRequest->name }}
Email: {{ $quoteRequest->email }}
Phone: {{ $quoteRequest->phone }}
@if ($quoteRequest->company)Company: {{ $quoteRequest->company }}
@endif
Service: {{ $quoteRequest->service_needed }}
@if ($quoteRequest->quantity)Quantity: {{ $quoteRequest->quantity }}
@endif
@if ($quoteRequest->deadline)Deadline: {{ $quoteRequest->deadline }}
@endif
@if ($quoteRequest->artwork_path)Artwork: Attached to this email
@endif

Message:
{{ $quoteRequest->message }}

View in admin: {{ $adminUrl }}
