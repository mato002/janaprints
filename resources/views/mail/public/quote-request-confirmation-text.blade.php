Dear {{ $quoteRequest->name }},

Thank you for reaching out to Jana Prints. We have received your quote request and our commercial team is reviewing your requirements.

YOUR REQUEST SUMMARY
--------------------
Service: {{ $quoteRequest->service_needed }}
@if ($quoteRequest->quantity)Quantity: {{ $quoteRequest->quantity }}
@endif
@if ($quoteRequest->deadline)Deadline: {{ $quoteRequest->deadline }}
@endif
@if ($quoteRequest->company)Company: {{ $quoteRequest->company }}
@endif
@if ($quoteRequest->artwork_path)Artwork: Uploaded — our team will review your files
@endif

WHAT HAPPENS NEXT?
1. Our team reviews your project requirements
2. Artwork is checked if you uploaded files
3. Pricing and production guidance are prepared
4. A Jana Prints representative contacts you directly

NEED TO REACH US SOONER?
Email: {{ $contact['email'] }}
Phone: {{ $contact['phone'] }}
WhatsApp: https://wa.me/{{ $whatsapp['number'] }}

Jana Prints
Commercial Printing • Branding • Packaging
