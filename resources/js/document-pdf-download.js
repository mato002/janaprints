function parseDownloadFilename(contentDisposition, fallback = 'document.pdf') {
    if (! contentDisposition) {
        return fallback;
    }

    const utf8Match = /filename\*=UTF-8''([^;]+)/i.exec(contentDisposition);

    if (utf8Match?.[1]) {
        try {
            return decodeURIComponent(utf8Match[1]);
        } catch {
            // Fall through to the basic filename parser.
        }
    }

    const basicMatch = /filename="?([^";]+)"?/i.exec(contentDisposition);

    return basicMatch?.[1] ?? fallback;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function triggerBlobDownload(blob, filename) {
    const objectUrl = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = objectUrl;
    anchor.download = filename;
    anchor.style.display = 'none';
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
}

async function downloadDocumentPdf(url, fallbackFilename = 'document.pdf') {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/pdf, application/octet-stream, */*',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
        },
    });

    if (! response.ok) {
        throw new Error(`Download failed (${response.status})`);
    }

    const blob = await response.blob();
    const filename = parseDownloadFilename(response.headers.get('Content-Disposition'), fallbackFilename);

    await triggerBlobDownload(blob, filename.endsWith('.pdf') ? filename : `${filename}.pdf`);
}

function setButtonLoadingState(button, loading) {
    const label = button.querySelector('[data-document-pdf-download-label]');
    const spinner = button.querySelector('[data-document-pdf-download-loading]');

    button.disabled = loading;
    button.setAttribute('aria-busy', loading ? 'true' : 'false');

    if (label) {
        label.classList.toggle('hidden', loading);
        label.classList.toggle('inline-flex', ! loading);
    }

    if (spinner) {
        spinner.classList.toggle('hidden', ! loading);
        spinner.classList.toggle('inline-flex', loading);
    }
}

function bindDocumentPdfDownloadButton(button) {
    if (button.dataset.documentPdfDownloadBound === '1') {
        return;
    }

    button.dataset.documentPdfDownloadBound = '1';

    button.addEventListener('click', async (event) => {
        event.preventDefault();

        if (button.disabled || button.dataset.downloading === '1') {
            return;
        }

        const url = button.dataset.documentPdfDownloadUrl;

        if (! url) {
            return;
        }

        const fallbackFilename = button.dataset.documentPdfDownloadFilename || 'document.pdf';

        button.dataset.downloading = '1';
        setButtonLoadingState(button, true);

        try {
            await downloadDocumentPdf(url, fallbackFilename);
        } catch (error) {
            console.error('documentPdfDownload', error);
            window.alert('PDF download failed. Please try again.');
        } finally {
            button.dataset.downloading = '0';
            setButtonLoadingState(button, false);
        }
    });
}

export function initDocumentPdfDownload(root = document) {
    root.querySelectorAll('[data-document-pdf-download]').forEach(bindDocumentPdfDownloadButton);
}
