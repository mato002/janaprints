<footer class="client-chat__composer" data-client-chat-composer>
    <form
        method="POST"
        action="{{ route('client.communications.messages.store') }}"
        class="client-chat__form"
        data-client-chat-form
    >
        @csrf
        <label class="sr-only" for="client-chat-body">{{ __('Message') }}</label>
        <input
            id="client-chat-file"
            type="file"
            class="sr-only"
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip"
            data-client-chat-file
        >

        <div class="client-chat__composer-bar">
            <button type="button" class="client-chat__tool" data-client-chat-attach title="{{ __('Attach file') }}" aria-label="{{ __('Attach file') }}">
                <x-client.icon name="paperclip" class="h-5 w-5" />
            </button>

            <div class="client-chat__field">
                <div class="client-chat__reply-preview hidden" data-client-chat-reply-preview hidden>
                    <div class="client-chat__reply-preview-bar" aria-hidden="true"></div>
                    <div class="client-chat__reply-preview-body">
                        <p class="client-chat__reply-preview-author" data-client-chat-reply-author></p>
                        <p class="client-chat__reply-preview-text" data-client-chat-reply-text></p>
                    </div>
                    <button type="button" class="client-chat__reply-preview-cancel" data-client-chat-reply-cancel aria-label="{{ __('Cancel reply') }}">
                        <x-client.icon name="x" class="h-4 w-4" />
                    </button>
                </div>
                <textarea
                    id="client-chat-body"
                    name="body"
                    rows="1"
                    class="client-chat__input"
                    placeholder="{{ __('Write a message…') }}"
                    data-client-chat-body
                ></textarea>
                <div class="client-chat__file-chip hidden" data-client-chat-file-chip hidden>
                    <x-client.icon name="document" class="h-4 w-4 shrink-0" />
                    <span data-client-chat-file-name></span>
                    <button type="button" class="client-chat__file-clear" data-client-chat-file-clear aria-label="{{ __('Remove file') }}">
                        <x-client.icon name="x" class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <button type="submit" class="client-chat__send" title="{{ __('Send') }}" aria-label="{{ __('Send') }}" data-client-chat-send>
                <x-client.icon name="send" class="h-5 w-5" />
            </button>
        </div>
    </form>

    <form
        method="POST"
        action="{{ route('client.communications.attachments.store') }}"
        enctype="multipart/form-data"
        class="hidden"
        data-client-chat-attachment-form
    >
        @csrf
        <input type="hidden" name="caption" data-client-chat-attachment-caption>
        <input type="file" name="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip" data-client-chat-attachment-file>
    </form>
</footer>
