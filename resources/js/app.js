import Alpine from 'alpinejs';
import * as Turbo from '@hotwired/turbo';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { initDocumentPdfDownload } from './document-pdf-download';

const ErpToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4500,
    timerProgressBar: true,
    heightAuto: false,
    customClass: {
        container: 'erp-swal-container',
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

function erpFormActionUrl(form) {
    if (! form) {
        return '';
    }

    // Prefer an explicit snapshot — named controls like name="action" shadow the URL property
    // and can even leave a corrupted action attribute ("[object RadioNodeList]").
    const snapshot = form.dataset?.erpFormAction;

    if (snapshot && snapshot.trim() !== '' && ! snapshot.includes('[object ')) {
        return snapshot;
    }

    const attr = form.getAttribute('action');

    if (attr && attr.trim() !== '' && ! attr.includes('[object ')) {
        try {
            return new URL(attr, window.location.href).toString();
        } catch {
            return attr;
        }
    }

    return window.location.href;
}

function showErpSweetAlert(message, variant = 'success', options = {}) {
    if (! message) {
        return;
    }

    const icon = ['success', 'error', 'warning', 'info'].includes(variant) ? variant : 'info';
    const { timer: optionTimer, ...restOptions } = options ?? {};

    ErpToast.fire({
        icon,
        title: String(message),
        timer: optionTimer ?? 4500,
        ...restOptions,
    });
}

function extractValidationErrorsFromDoc(root) {
    if (! root?.querySelectorAll) {
        return [];
    }

    const fromHidden = [...root.querySelectorAll('[data-erp-validation-errors] [data-erp-validation-message]')]
        .map((element) => element.textContent?.trim())
        .filter(Boolean);

    if (fromHidden.length > 0) {
        return [...new Set(fromHidden)];
    }

    const fromList = [...root.querySelectorAll('[data-erp-validation-errors] li')]
        .map((element) => element.textContent?.trim())
        .filter(Boolean);

    if (fromList.length > 0) {
        return [...new Set(fromList)];
    }

    const fromSpans = [...root.querySelectorAll('[data-erp-validation-errors] > span, [data-erp-validation-errors] span[data-erp-validation-message]')]
        .map((element) => element.textContent?.trim())
        .filter(Boolean);

    if (fromSpans.length > 0) {
        return [...new Set(fromSpans)];
    }

    const fromFields = [...root.querySelectorAll('[data-erp-field-error]')]
        .map((element) => element.textContent?.trim())
        .filter(Boolean);

    if (fromFields.length > 0) {
        return [...new Set(fromFields)];
    }

    return [];
}

function extractValidationPresentationFromDoc(root) {
    const marker = root?.querySelector?.('[data-erp-validation-errors]');

    if (! marker) {
        return null;
    }

    const category = marker.dataset.erpValidationCategory?.trim();
    const categoryLabel = marker.dataset.erpValidationCategoryLabel?.trim();

    if (! category && ! categoryLabel) {
        return null;
    }

    return {
        category: categoryLabel || category || null,
        category_label: categoryLabel || category || null,
    };
}

function extractAnyFormErrorsFromHtml(html) {
    if (! html || typeof html !== 'string') {
        return { messages: [], presentation: null, detail: null };
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');
    const validationErrors = extractValidationErrorsFromDoc(doc);

    if (validationErrors.length > 0) {
        return {
            messages: validationErrors,
            presentation: extractValidationPresentationFromDoc(doc),
            detail: null,
        };
    }

    const governed = doc.querySelector('.rounded-lg.border-rose-200.bg-rose-50, [data-erp-form-modal-panel] .rounded-lg.border-rose-200');
    if (governed) {
        const message = governed.querySelector('p.font-medium')?.textContent?.trim()
            || governed.textContent?.trim();
        const detail = governed.querySelector('p.text-xs')?.textContent?.trim() || null;

        if (message) {
            return {
                messages: [message],
                presentation: {
                    category_label: doc.querySelector('.erp-form-modal__title')?.textContent?.trim() || 'Unable to save',
                },
                detail,
            };
        }
    }

    const flashError = doc.querySelector('[data-erp-flash-error]')?.textContent?.trim()
        || doc.querySelector('[data-erp-modal-error]')?.textContent?.trim();
    if (flashError) {
        return { messages: [flashError], presentation: null, detail: null };
    }

    const laravelList = [...doc.querySelectorAll('.text-red-600 li, .text-rose-600 li, #erp-form-modal .text-sm.text-red-600')]
        .map((element) => element.textContent?.trim())
        .filter(Boolean);
    if (laravelList.length > 0) {
        return { messages: [...new Set(laravelList)], presentation: { category_label: 'Validation Errors' }, detail: null };
    }

    const exceptionMessage = doc.querySelector('.exception-message, .message-body, #exception-message')?.textContent?.trim();
    if (exceptionMessage) {
        return { messages: [exceptionMessage], presentation: { category_label: 'System Errors' }, detail: null };
    }

    return { messages: [], presentation: null, detail: null };
}

function humanizeFormSaveFailure({ status = null, html = '', payload = null, networkError = null } = {}) {
    if (payload && typeof payload === 'object') {
        const fromPayload = Object.values(payload.errors ?? {}).flat().filter(Boolean);
        const message = payload.message || fromPayload[0] || null;
        if (message) {
            return {
                messages: fromPayload.length > 1 ? fromPayload : [message],
                presentation: {
                    category_label: payload.category_label || payload.category || null,
                },
                detail: payload.detail || null,
            };
        }
    }

    const extracted = extractAnyFormErrorsFromHtml(html);
    if (extracted.messages.length > 0) {
        return extracted;
    }

    if (networkError?.message) {
        return {
            messages: [networkError.message],
            presentation: { category_label: 'Network Error' },
            detail: null,
        };
    }

    if (status === 0) {
        return {
            messages: [
                'The server redirected without returning error details. Check required fields and try again. If it keeps failing, open the browser Network tab for the failed request.',
            ],
            presentation: { category_label: 'Unable to save' },
            detail: 'HTTP status 0 (opaque redirect or interrupted request)',
        };
    }

    if (status === 419) {
        return {
            messages: ['Your session expired. Refresh the page and try again.'],
            presentation: { category_label: 'Session Expired' },
            detail: null,
        };
    }

    if (status === 403) {
        return {
            messages: ['You do not have permission to complete this action.'],
            presentation: { category_label: 'Access Denied' },
            detail: null,
        };
    }

    if (status === 404) {
        return {
            messages: ['The requested record or form action was not found.'],
            presentation: { category_label: 'Not Found' },
            detail: null,
        };
    }

    if (status === 422) {
        return {
            messages: ['Please fix the highlighted fields and try again.'],
            presentation: { category_label: 'Validation Errors' },
            detail: null,
        };
    }

    if (status >= 500) {
        return {
            messages: ['A server error prevented this form from saving. Check logs or try again.'],
            presentation: { category_label: 'Server Error' },
            detail: status ? `HTTP ${status}` : null,
        };
    }

    return {
        messages: [
            status
                ? `Unable to save form (HTTP ${status}). Please try again.`
                : 'Unable to save form. Please try again.',
        ],
        presentation: { category_label: 'Unable to save' },
        detail: status ? `HTTP ${status}` : null,
    };
}

function reportFormSaveFailure(messagesOrOptions, errorDetails = null) {
    let messages = messagesOrOptions;
    let details = errorDetails;

    if (messagesOrOptions && typeof messagesOrOptions === 'object' && ! Array.isArray(messagesOrOptions) && messagesOrOptions.messages) {
        const resolved = messagesOrOptions;
        messages = resolved.messages;
        details = {
            ...(errorDetails || {}),
            category: resolved.presentation?.category_label || resolved.presentation?.category || errorDetails?.category || null,
            detail: resolved.detail || errorDetails?.detail || null,
        };
    }

    showErpFormErrorAlert(messages, details);
}

function extractModalLoadErrorsFromHtml(html, status = null) {
    if (! html) {
        if (status === 403) {
            return {
                messages: ['You do not have permission to open this form.'],
                presentation: { category_label: 'Access Denied' },
            };
        }

        if (status === 404) {
            return {
                messages: ['The requested form or record could not be found.'],
                presentation: { category_label: 'Not Found' },
            };
        }

        return {
            messages: [`Unable to open form${status ? ` (${status})` : ''}. Please try again.`],
            presentation: { category_label: 'Unable to open form' },
        };
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');
    const validationErrors = extractValidationErrorsFromDoc(doc);

    if (validationErrors.length > 0) {
        return {
            messages: validationErrors,
            presentation: extractValidationPresentationFromDoc(doc),
        };
    }

    const errorBox = doc.querySelector('[data-erp-form-modal-panel] .rounded-lg.border-rose-200')
        ?? doc.querySelector('.rounded-lg.border-rose-200.bg-rose-50');

    if (errorBox) {
        const message = errorBox.querySelector('p.font-medium')?.textContent?.trim();
        const detail = errorBox.querySelector('p.text-xs')?.textContent?.trim();
        const categoryLabel = doc.querySelector('.erp-form-modal__title')?.textContent?.trim();

        if (message) {
            return {
                messages: [message],
                presentation: {
                    category: categoryLabel || 'System Errors',
                    category_label: categoryLabel || 'System Errors',
                    detail: detail || null,
                },
            };
        }
    }

    const modalTitle = doc.querySelector('.erp-form-modal__title')?.textContent?.trim();

    if (status === 403) {
        return {
            messages: ['You do not have permission to open this form.'],
            presentation: { category_label: modalTitle || 'Access Denied' },
        };
    }

    if (status === 404) {
        return {
            messages: ['The requested form or record could not be found.'],
            presentation: { category_label: modalTitle || 'Not Found' },
        };
    }

    const exceptionMessage = doc.querySelector('.exception-message')?.textContent?.trim()
        ?? doc.querySelector('.break-all')?.textContent?.trim();

    if (exceptionMessage) {
        return {
            messages: [exceptionMessage],
            presentation: {
                category_label: modalTitle || 'System Errors',
                detail: doc.querySelector('title')?.textContent?.trim() || null,
            },
        };
    }

    return {
        messages: [`Unable to open form${status ? ` (${status})` : ''}. Please try again.`],
        presentation: { category_label: modalTitle || 'Unable to open form' },
    };
}

function showErpFormErrorAlert(messages, errorDetails = null) {
    const items = (Array.isArray(messages) ? messages : [messages])
        .map((message) => String(message ?? '').trim())
        .filter(Boolean);

    if (items.length === 0) {
        return;
    }

    const escapeHtml = (value) => value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    // Build detailed error information
    let detailsHtml = '';
    if (errorDetails) {
        const details = [];
        if (errorDetails.detail) {
            details.push(`Error: ${escapeHtml(String(errorDetails.detail))}`);
        }
        if (errorDetails.status) {
            details.push(`Status: ${errorDetails.status}`);
        }
        if (errorDetails.url) {
            details.push(`URL: ${escapeHtml(errorDetails.url)}`);
        }
        if (errorDetails.method) {
            details.push(`Method: ${errorDetails.method}`);
        }
        if (errorDetails.route) {
            details.push(`Route: ${escapeHtml(errorDetails.route)}`);
        }
        if (errorDetails.timestamp) {
            details.push(`Time: ${errorDetails.timestamp}`);
        }
        
        if (details.length > 0) {
            detailsHtml = `<div class="mt-4 p-3 bg-gray-100 rounded text-xs text-gray-600 font-mono">
                <div class="font-semibold mb-1">Technical Details:</div>
                ${details.map(d => `<div>${escapeHtml(d)}</div>`).join('')}
            </div>`;
        }
    }

    const errorListHtml = `<ul class="mt-2 list-disc space-y-1 pl-5 text-left text-sm">${items.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`;

    if (items.length === 1 && ! errorDetails) {
        Swal.fire({
            icon: 'error',
            title: 'Unable to save',
            text: items[0],
            confirmButtonText: 'OK',
            heightAuto: false,
            customClass: {
                container: 'erp-swal-container',
            },
        });

        return;
    }

    const title = errorDetails?.category
        ? String(errorDetails.category)
        : (items.length === 1 ? 'Unable to save' : 'Please fix the following');

    Swal.fire({
        icon: 'error',
        title,
        html: (items.length === 1
            ? `<p class="text-left text-sm">${escapeHtml(items[0])}</p>`
            : errorListHtml) + detailsHtml,
        confirmButtonText: 'OK',
        heightAuto: false,
        width: items.length > 3 || errorDetails ? '600px' : '500px',
        customClass: {
            container: 'erp-swal-container',
        },
    });
}

function showErpDeskErrorAlert(messages, options = {}) {
    const items = (Array.isArray(messages) ? messages : [messages])
        .map((message) => String(message ?? '').trim())
        .filter(Boolean);

    if (items.length === 0) {
        return;
    }

    showErpFormErrorAlert(items, {
        category: options.categoryLabel || options.category || null,
        detail: options.detail || null,
        status: options.status || null,
        url: options.url || null,
        method: options.method || null,
        timestamp: options.timestamp || null,
    });
}

function erpCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function erpXsrfToken() {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

function erpJsonHeaders(extra = {}) {
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...extra,
    };

    const csrf = erpCsrfToken();
    const xsrf = erpXsrfToken();

    if (csrf) {
        headers['X-CSRF-TOKEN'] = csrf;
    }

    if (xsrf) {
        headers['X-XSRF-TOKEN'] = xsrf;
    }

    return headers;
}

function showErpNotificationAlert(notification) {
    if (! notification?.title) {
        return;
    }

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: notification.title,
        text: notification.body ?? '',
        timer: 10000,
        timerProgressBar: true,
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);

            if (! notification.action_url) {
                return;
            }

            toast.style.cursor = 'pointer';
            toast.addEventListener('click', () => {
                Swal.close();

                if (window.Turbo?.visit) {
                    window.Turbo.visit(notification.action_url, { frame: 'erp-main', action: 'advance' });
                } else {
                    window.location.href = notification.action_url;
                }
            });
        },
    });
}

window.showErpSweetAlert = showErpSweetAlert;
window.showErpFormErrorAlert = showErpFormErrorAlert;
window.showErpNotificationAlert = showErpNotificationAlert;

window.Alpine = Alpine;
window.Turbo = Turbo;

Turbo.session.drive = true;

const WORKSPACE_STATE_KEY = 'erp.workspace.tableState';

const erpModalManager = {
    pendingModalLoad: false,
    pendingDrawerLoad: false,
    modalLoadSeq: 0,
    modalAbortController: null,
    modalStack: [],
    currentModalUrl: null,
    /** @type {{ url: string|null, html: string }|null} */
    lastPanelSnapshot: null,

    isModalVisible() {
        const overlay = this.overlay();

        return Boolean(overlay && ! overlay.hidden);
    },

    normalizeModalUrl(url) {
        if (! url) {
            return '';
        }

        try {
            const parsed = new URL(url, window.location.origin);

            return `${parsed.pathname}${parsed.search}`;
        } catch {
            return String(url);
        }
    },

    sameModalUrl(left, right) {
        return this.normalizeModalUrl(left) === this.normalizeModalUrl(right);
    },

    captureModalState() {
        const frame = this.modalFrame();
        const panel = frame?.querySelector('[data-erp-form-modal-panel]');

        if (! panel) {
            return this.lastPanelSnapshot
                ? { ...this.lastPanelSnapshot }
                : null;
        }

        return {
            url: this.currentModalUrl,
            html: panel.outerHTML,
        };
    },

    rememberPanelSnapshot(panel, url = null) {
        if (! panel) {
            return;
        }

        this.lastPanelSnapshot = {
            url: url ?? this.currentModalUrl,
            html: panel.outerHTML,
        };
    },

    pushModalState() {
        const state = this.captureModalState();

        if (state?.html) {
            this.modalStack.push(state);
        }
    },

    restoreModalState(state) {
        const frame = this.modalFrame();

        if (! frame || ! state?.html) {
            return;
        }

        frame.innerHTML = state.html;
        this.currentModalUrl = state.url ?? null;
        this.lastPanelSnapshot = state.html
            ? { url: state.url ?? null, html: state.html }
            : this.lastPanelSnapshot;

        const panel = frame.querySelector('[data-erp-form-modal-panel]');

        if (panel) {
            this.prepareModalFormContent(panel, state.url ?? undefined);
            Alpine.initTree(frame);
        }

        this.syncModalNavigation();
        this.showOverlay();
    },

    popModal() {
        const state = this.modalStack.pop();

        if (state) {
            this.restoreModalState(state);

            return true;
        }

        return false;
    },

    dismissModal() {
        if (this.popModal()) {
            return;
        }

        this.closeModal();
    },

    /**
     * After a nested create/edit succeeds, return to the parent task instead of closing.
     */
    returnToParentModal({ message = '', refresh = true, redirect = '' } = {}) {
        if (message) {
            this.showToast(message);
        }

        document.dispatchEvent(new CustomEvent('erp-modal-nested-return', {
            detail: {
                redirect,
                message,
                parentUrl: this.currentModalUrl,
            },
        }));

        if (refresh) {
            this.refreshTable().then(() => {
                this.restoreWorkspaceState();
            });
        }
    },

    syncModalNavigation() {
        const frame = this.modalFrame();
        const panel = frame?.querySelector('[data-erp-form-modal-panel]');
        const header = panel?.querySelector('.erp-form-modal__header');

        if (! header) {
            return;
        }

        const title = header.querySelector('.erp-form-modal__title');
        let backButton = header.querySelector('[data-erp-form-modal-back]');
        const canGoBack = this.modalStack.length > 0;
        const backLabel = this.modalConfig().backLabel ?? 'Back';

        if (canGoBack) {
            if (! backButton) {
                backButton = document.createElement('button');
                backButton.type = 'button';
                backButton.className = 'erp-form-modal__back erp-btn-secondary shrink-0 !px-2.5 !py-1.5 text-xs';
                backButton.setAttribute('data-erp-form-modal-back', '');
                backButton.setAttribute('aria-label', backLabel);
            }

            backButton.textContent = backLabel;

            if (title) {
                header.insertBefore(backButton, title);
            } else {
                header.prepend(backButton);
            }
        } else if (backButton) {
            backButton.remove();
        }
    },

    overlay() {
        return document.getElementById('erp-modal-overlay');
    },

    drawerOverlay() {
        return document.getElementById('erp-drawer-overlay');
    },

    modalFrame() {
        return document.getElementById('erp-form-modal');
    },

    drawerFrame() {
        return document.getElementById('erp-preview-drawer');
    },

    toastHost() {
        return document.getElementById('erp-toast-host');
    },

    openModal(url) {
        return this.loadForm(url);
    },

    modalConfig() {
        return window.__erpModalForm ?? { blockedPathFragments: [] };
    },

    isModalFormUrl(url) {
        try {
            const parsed = new URL(url, window.location.origin);

            if (parsed.origin !== window.location.origin) {
                return false;
            }

            const path = parsed.pathname.toLowerCase();
            const blocked = this.modalConfig().blockedPathFragments ?? [];

            if (blocked.some((fragment) => path.includes(String(fragment).toLowerCase()))) {
                return false;
            }

            if (path.includes('/quick-create')) {
                return false;
            }

            return /\/(create|edit)(\/|$)/.test(path)
                || path.includes('/invoices/from/sales-order')
                || path.includes('/invoices/from/job-card');
        } catch {
            return false;
        }
    },

    shouldOpenLinkAsModal(link) {
        if (! link?.href) {
            return false;
        }

        if (link.hasAttribute('data-erp-modal-open') || link.hasAttribute('data-no-modal')) {
            return false;
        }

        if (link.getAttribute('target') === '_blank' || link.getAttribute('data-turbo') === 'false') {
            return false;
        }

        if (link.closest('#erp-form-modal') || link.closest('[data-turbo-frame="_top"]')) {
            return false;
        }

        return this.isModalFormUrl(link.href);
    },

    shouldOpenFormModal(link) {
        if (! link?.href) {
            return false;
        }

        if (link.hasAttribute('data-no-modal')) {
            return false;
        }

        if (link.getAttribute('target') === '_blank') {
            return false;
        }

        if (link.hasAttribute('data-erp-modal-open')) {
            return true;
        }

        if (link.closest('#erp-form-modal') || link.closest('#erp-lookup-modal-overlay')) {
            return false;
        }

        if (link.getAttribute('data-turbo') === 'false') {
            return false;
        }

        return this.shouldOpenLinkAsModal(link);
    },

    prepareModalFormContent(root, sourceUrl = null) {
        const parentUrl = this.modalStack.length > 0
            ? this.modalStack[this.modalStack.length - 1]?.url
            : null;
        const formUrl = this.currentModalUrl || sourceUrl || parentUrl;
        const returnUrl = window.location.href;

        root.querySelectorAll('form').forEach((form) => {
            form.setAttribute('data-turbo', 'false');
            form.removeAttribute('data-turbo-frame');

            // Snapshot the real action URL before any name="action" controls can shadow form.action.
            if (! form.dataset.erpFormAction) {
                const actionAttr = form.getAttribute('action');

                if (actionAttr && actionAttr.trim() !== '' && ! actionAttr.includes('[object ')) {
                    form.dataset.erpFormAction = new URL(actionAttr, window.location.href).toString();
                }
            }

            if (this.isDeskShellForm(form)) {
                return;
            }

            if (! form.querySelector('[name="_erp_modal"]')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_erp_modal';
                input.value = '1';
                form.appendChild(input);
            }

            let formUrlInput = form.querySelector('[name="_erp_modal_form_url"]');
            const resolvedFormUrl = formUrl
                || form.querySelector('[name="_erp_modal_form_url"]')?.value
                || form.dataset.erpModalFormUrl
                || null;

            if (resolvedFormUrl) {
                if (! formUrlInput) {
                    formUrlInput = document.createElement('input');
                    formUrlInput.type = 'hidden';
                    formUrlInput.name = '_erp_modal_form_url';
                    form.appendChild(formUrlInput);
                }

                formUrlInput.value = resolvedFormUrl;
                form.dataset.erpModalFormUrl = resolvedFormUrl;
            }

            let returnInput = form.querySelector('[name="_erp_modal_return"]');

            if (returnUrl) {
                if (! returnInput) {
                    returnInput = document.createElement('input');
                    returnInput.type = 'hidden';
                    returnInput.name = '_erp_modal_return';
                    form.appendChild(returnInput);
                }

                returnInput.value = returnUrl;
            }

            if (returnUrl) {
                form.dataset.erpModalReturn = returnUrl;
            }
        });
    },

    hasValidationErrors(doc) {
        return Boolean(
            doc?.querySelector('[data-erp-validation-errors]')
            || doc?.querySelector('[data-erp-modal-error]')
            || doc?.querySelector('[data-erp-field-error]'),
        );
    },

    highlightInvalidFields(panel, doc) {
        if (! panel || ! doc) {
            return;
        }

        panel.querySelectorAll('.erp-input--invalid, .erp-select--invalid').forEach((element) => {
            element.classList.remove('erp-input--invalid', 'erp-select--invalid');
        });

        const invalidNames = new Set(
            [...doc.querySelectorAll('[data-erp-field-error]')]
                .map((element) => element.dataset.erpFieldError)
                .filter(Boolean),
        );

        doc.querySelectorAll('input:invalid, select:invalid, textarea:invalid').forEach((element) => {
            if (element.name) {
                invalidNames.add(element.name);
            }
        });

        // Laravel reports nested keys as dotted paths; form fields use bracket names.
        [...invalidNames].forEach((name) => {
            if (name.includes('.') && ! name.includes('[')) {
                invalidNames.add(name.replace(/\.(\d+)/g, '[$1]').replace(/\.([^[.\]]+)/g, '[$1]'));
            }
        });

        const fieldAliases = {
            sales_order: 'sales_order_id',
            artwork: 'sales_order_id',
        };

        Object.entries(fieldAliases).forEach(([from, to]) => {
            if (invalidNames.has(from)) {
                invalidNames.add(to);
            }
        });

        // Pull field names from the session error bag echoed as list items when possible.
        [...doc.querySelectorAll('[data-erp-validation-errors] li')].forEach((element) => {
            const text = element.textContent ?? '';
            const match = text.match(/material_requirements(?:\.|\[)\d+/i);
            if (match) {
                invalidNames.add('material_requirements[0][inventory_item_id]');
            }
        });

        invalidNames.forEach((name) => {
            const field = panel.querySelector(`[name="${CSS.escape(name)}"]`)
                ?? panel.querySelector(`[name="${name}"]`);

            if (! field) {
                return;
            }

            if (field.matches('select')) {
                field.classList.add('erp-select--invalid');
            } else {
                field.classList.add('erp-input--invalid');
            }
        });

        if ([...invalidNames].some((name) => name.includes('material_requirements'))) {
            panel.querySelector('[x-data*="materials"]')?.scrollIntoView?.({ block: 'center', behavior: 'smooth' });
        }
    },

    ensureValidationSummary() {
        // Validation messages are shown via SweetAlert; field highlighting stays inline.
    },

    buildModalPanel(title, bodyHtml) {
        const wrapper = document.createElement('div');
        wrapper.className = 'erp-form-modal w-full';
        wrapper.setAttribute('data-erp-form-modal-panel', '');

        const header = document.createElement('div');
        header.className = 'erp-form-modal__header';

        const heading = document.createElement('h2');
        heading.id = 'erp-form-modal-title';
        heading.className = 'erp-form-modal__title';
        heading.textContent = title || 'Form';

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'erp-form-modal__close';
        closeButton.setAttribute('data-erp-form-modal-close', '');
        closeButton.setAttribute('aria-label', 'Close');
        closeButton.innerHTML = '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>';

        header.append(heading, closeButton);

        const body = document.createElement('div');
        body.className = 'erp-form-modal__body';
        body.innerHTML = bodyHtml;
        this.prepareModalFormContent(body);

        wrapper.append(header, body);

        return wrapper;
    },

    extractModalPanel(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const nativePanel = doc.querySelector('[data-erp-form-modal-panel]')
            ?? doc.querySelector('#erp-form-modal [data-erp-form-modal-panel]')
            ?? doc.querySelector('#erp-form-modal .erp-form-modal')
            ?? doc.querySelector('.erp-form-modal');

        if (nativePanel) {
            return nativePanel;
        }

        const main = doc.querySelector('#erp-main main') ?? doc.querySelector('main');
        const form = main?.querySelector('form');

        if (! form) {
            return null;
        }

        const title = doc.querySelector('#erp-page-title')?.textContent?.trim()
            ?? doc.querySelector('h1')?.textContent?.trim()
            ?? doc.title.split('—')[0]?.trim()
            ?? 'Form';

        // Prefer a wrapper around the form. If the form itself matches a card
        // selector (e.g. class="erp-card"), use form.outerHTML so the modal
        // keeps a real <form> — using the form's innerHTML alone drops the tag
        // and leaves submit buttons that do nothing.
        const container = form.closest('.bg-white:not(form)')
            ?? form.closest('.erp-data-grid')?.parentElement
            ?? form.parentElement;

        const bodyHtml = (container && container !== form && container.querySelector('form') !== form)
            ? container.innerHTML
            : form.outerHTML;
        const panel = this.buildModalPanel(title, bodyHtml);
        this.prepareModalFormContent(panel, doc.baseURI || window.location.href);

        return panel;
    },

    abortModalLoad() {
        if (this.modalAbortController) {
            this.modalAbortController.abort();
            this.modalAbortController = null;
        }
    },

    renderModalLoading() {
        const frame = this.modalFrame();

        if (! frame) {
            return;
        }

        frame.innerHTML = `
            <div class="erp-form-modal w-full" data-erp-form-modal-loading>
                <div class="erp-form-modal__header">
                    <h2 id="erp-form-modal-title" class="erp-form-modal__title">Loading…</h2>
                    <button type="button" class="erp-form-modal__close" data-erp-form-modal-close aria-label="Close">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="erp-form-modal__body erp-form-modal__body--loading">
                    <div class="erp-modal-spinner" role="status" aria-label="Loading form">
                        <span class="sr-only">Loading form</span>
                    </div>
                </div>
            </div>
        `;

        this.showOverlay();
    },

    async loadForm(url) {
        const frame = this.modalFrame();

        if (! frame || ! url) {
            return;
        }

        const hasOpenPanel = Boolean(frame.querySelector('[data-erp-form-modal-panel]'));
        const openingNested = this.isModalVisible()
            && this.currentModalUrl
            && ! this.sameModalUrl(url, this.currentModalUrl);

        if (openingNested && (hasOpenPanel || this.lastPanelSnapshot)) {
            this.pushModalState();
        } else if (! this.isModalVisible()) {
            this.modalStack = [];
            this.currentModalUrl = null;
            this.lastPanelSnapshot = null;
        }

        this.abortModalLoad();
        const loadId = ++this.modalLoadSeq;
        this.modalAbortController = new AbortController();
        this.pendingModalLoad = true;
        this.renderModalLoading();

        try {
            const response = await fetch(url, {
                signal: this.modalAbortController.signal,
                headers: {
                    'Turbo-Frame': 'erp-form-modal',
                    'Accept': 'text/html, application/xhtml+xml',
                },
                credentials: 'same-origin',
            });

            if (loadId !== this.modalLoadSeq) {
                return;
            }

            const responseText = await response.text();
            const responseUrl = response.url || url;

            if (! response.ok) {
                this.handleModalLoadFailure({
                    url: responseUrl,
                    status: response.status,
                    html: responseText,
                    reason: `HTTP ${response.status}`,
                });

                return;
            }

            const panel = this.extractModalPanel(responseText);

            if (! panel) {
                this.handleModalLoadFailure({
                    url: responseUrl,
                    status: response.status,
                    html: responseText,
                    reason: 'Modal form markup was not found in the response.',
                });

                return;
            }

            this.prepareModalFormContent(panel, url);
            frame.replaceChildren(panel);
            this.currentModalUrl = url;
            this.rememberPanelSnapshot(panel, url);
            this.pendingModalLoad = false;
            this.modalAbortController = null;
            this.showOverlay();
            this.syncModalNavigation();

            await new Promise((resolve) => window.requestAnimationFrame(resolve));
            Alpine.initTree(frame);
        } catch (error) {
            if (error?.name === 'AbortError' || loadId !== this.modalLoadSeq) {
                return;
            }

            console.error('erpModalManager.loadForm', error);
            this.pendingModalLoad = false;
            this.modalAbortController = null;

            this.handleModalLoadFailure({
                url,
                status: null,
                html: '',
                reason: error?.message || 'Unable to open form. Please try again.',
            });
        }
    },

    handleModalLoadFailure({ url, status, html, reason }) {
        const { messages, presentation } = extractModalLoadErrorsFromHtml(html, status);

        if (this.modalStack.length > 0) {
            this.popModal();
        } else {
            this.closeModal();
        }

        showErpFormErrorAlert(
            messages.length > 0 ? messages : [reason || 'Unable to open form. Please try again.'],
            {
                status,
                url,
                method: 'GET',
                detail: presentation?.detail
                    ?? (reason && ! messages.includes(reason) ? reason : null),
                category: presentation?.category_label ?? presentation?.category ?? 'Unable to open form',
                timestamp: new Date().toISOString(),
            },
        );
    },

    closeModal() {
        if (this.isLookupOverlayOpen()) {
            return;
        }

        this.modalLoadSeq += 1;
        this.abortModalLoad();
        this.pendingModalLoad = false;
        this.modalStack = [];
        this.currentModalUrl = null;
        this.lastPanelSnapshot = null;

        const frame = this.modalFrame();

        if (frame) {
            frame.removeAttribute('src');
            frame.innerHTML = '';
        }

        this.hideOverlay();
    },

    openDrawer(url) {
        const frame = this.drawerFrame();

        if (! frame || ! url) {
            return;
        }

        frame.src = url;
        this.showDrawer();
    },

    closeDrawer() {
        this.pendingDrawerLoad = false;

        const frame = this.drawerFrame();

        if (frame) {
            frame.removeAttribute('src');
            frame.innerHTML = '';
        }

        this.hideDrawer();
    },

    showOverlay() {
        const overlay = this.overlay();

        if (overlay) {
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        }
    },

    hideOverlay() {
        const overlay = this.overlay();

        if (overlay) {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        }
    },

    showDrawer() {
        const overlay = this.drawerOverlay();

        if (overlay) {
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
        }
    },

    hideDrawer() {
        const overlay = this.drawerOverlay();

        if (overlay) {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
        }
    },

    showToast(message, variant = 'success') {
        if (variant === 'error' && this.overlay() && ! this.overlay().hidden) {
            showErpFormErrorAlert([message]);

            return;
        }

        showErpSweetAlert(message, variant);
    },

    saveWorkspaceState() {
        const frame = document.getElementById('erp-main');

        if (! frame) {
            return;
        }

        const tables = frame.querySelectorAll('[x-data]');
        const states = [];

        tables.forEach((element) => {
            const data = element._x_dataStack?.[0];

            if (! data || typeof data.rowVisible !== 'function') {
                return;
            }

            states.push({
                query: data.query ?? '',
                activeChip: data.activeChip ?? 'all',
                filterOpen: data.filterOpen ?? false,
            });
        });

        sessionStorage.setItem(WORKSPACE_STATE_KEY, JSON.stringify({
            scrollY: shellScrollY(),
            tables: states,
        }));
    },

    restoreWorkspaceState() {
        const raw = sessionStorage.getItem(WORKSPACE_STATE_KEY);

        if (! raw) {
            return;
        }

        sessionStorage.removeItem(WORKSPACE_STATE_KEY);

        let saved;

        try {
            saved = JSON.parse(raw);
        } catch {
            return;
        }

        const frame = document.getElementById('erp-main');

        if (! frame) {
            return;
        }

        const tables = [...frame.querySelectorAll('[x-data]')].filter((element) => {
            const data = element._x_dataStack?.[0];

            return data && typeof data.rowVisible === 'function';
        });

        saved.tables?.forEach((state, index) => {
            const data = tables[index]?._x_dataStack?.[0];

            if (! data) {
                return;
            }

            if ('query' in state) {
                data.query = state.query;
            }

            if ('activeChip' in state) {
                data.activeChip = state.activeChip;
            }

            if ('filterOpen' in state) {
                data.filterOpen = state.filterOpen;
            }
        });

        if (typeof saved.scrollY === 'number') {
            window.requestAnimationFrame(() => {
                setShellScrollY(saved.scrollY);
            });
        }
    },

    resolveWorkspaceRefreshUrl() {
        const workspaceFrame = document.getElementById('module-workspace-content');
        const frameSrc = workspaceFrame?.src || workspaceFrame?.getAttribute('src') || '';

        if (frameSrc) {
            return frameSrc;
        }

        const activeTab = document.querySelector(
            '.module-workspace-switcher--secondary [data-workspace-tab][href].workspace-pill--active',
        );

        return activeTab?.getAttribute('href') || null;
    },

    refreshTable() {
        if (! window.Turbo) {
            return Promise.resolve();
        }

        this.saveWorkspaceState();

        const workspaceRefreshUrl = this.resolveWorkspaceRefreshUrl();

        if (workspaceRefreshUrl) {
            return window.Turbo.visit(workspaceRefreshUrl, {
                frame: 'module-workspace-content',
                action: 'replace',
            }).then(() => {
                this.restoreWorkspaceState();
            });
        }

        const frame = document.getElementById('erp-main');

        if (! frame) {
            return Promise.resolve();
        }

        return window.Turbo.visit(window.location.href, {
            frame: 'erp-main',
            action: 'replace',
        }).then(() => {
            this.restoreWorkspaceState();
        });
    },

    safeHandleSuccess(options = {}) {
        try {
            this.handleSuccess(options);
        } catch (error) {
            console.error('erpModalManager.handleSuccess', error);
            this.closeModal();

            if (options.message) {
                this.showToast(options.message);
            }
        }
    },

    isDeskShellForm(form) {
        return form instanceof HTMLFormElement
            && form.hasAttribute('data-erp-desk-form')
            && Boolean(form.closest('.store-desk-shell, .sales-desk-shell, .designer-desk-shell, .production-floor-shell'));
    },

    completeDeskFormRedirect(form, { redirect = '', html = '', message = '', returnUrl = '', variant = 'success' } = {}) {
        const validationMessage = extractValidationMessageFromHtml(html);
        const defaultMessage = form?.dataset?.erpDeskSuccessMessage?.trim() ?? '';
        const finalMessage = validationMessage || message || (! validationMessage && redirect ? defaultMessage : '');
        const finalVariant = validationMessage ? 'error' : variant;

        if (finalMessage) {
            if (finalVariant === 'error') {
                showErpDeskErrorAlert([finalMessage]);
            } else {
                sessionStorage.setItem('erp.pendingDeskToast', JSON.stringify({
                    message: finalMessage,
                    variant: finalVariant,
                }));
                this.showToast(finalMessage, finalVariant);
            }
        }

        if (redirect) {
            this.visitDeskRedirect(redirect);
        } else if (this.isDeskShellForm(form)) {
            this.visitDeskRedirect(window.location.href);
        } else {
            this.safeHandleSuccess({
                refresh: false,
                redirect,
                message: finalMessage,
                returnUrl,
            });
        }
    },

    visitDeskRedirect(url) {
        if (! url) {
            return;
        }

        let target;

        try {
            target = new URL(url, window.location.origin);
        } catch {
            return;
        }

        if (target.origin !== window.location.origin) {
            window.location.assign(target.href);

            return;
        }

        const frame = document.getElementById('erp-main') ? 'erp-main' : '_top';
        const samePage = target.href === window.location.href;

        if (window.Turbo) {
            window.Turbo.visit(target.href, {
                frame,
                action: samePage ? 'replace' : 'advance',
            });

            return;
        }

        if (samePage) {
            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }

            return;
        }

        window.location.assign(target.href);
    },

    async submitFormRequest(form, submitter = null) {
        if (! form) {
            return;
        }

        if (form.dataset.erpModalSubmitting === '1') {
            return;
        }

        form.dataset.erpModalSubmitting = '1';

        const formData = new FormData(form);
        // FormData(form) omits the clicked submit button — restore intent / action values.
        const activeSubmitter = submitter
            || form.querySelector('button[type="submit"]:focus, input[type="submit"]:focus')
            || null;

        if (activeSubmitter instanceof HTMLButtonElement || activeSubmitter instanceof HTMLInputElement) {
            if (activeSubmitter.name) {
                formData.set(activeSubmitter.name, activeSubmitter.value ?? '');
            }
        }

        const method = (formData.get('_method') || form.method || 'POST').toString().toUpperCase();
        const submitButton = activeSubmitter
            || form.querySelector('[type="submit"]');
        const modalReturnUrl = formData.get('_erp_modal_return')?.toString()
            || form.dataset.erpModalReturn
            || null;
        const modalFormUrl = formData.get('_erp_modal_form_url')?.toString()
            || form.dataset.erpModalFormUrl
            || this.currentModalUrl
            || null;

        if (submitButton) {
            submitButton.disabled = true;
        }

        const deskForm = this.isDeskShellForm(form);
        const fetchHeaders = {
            Accept: deskForm
                ? 'application/json, text/html, application/xhtml+xml'
                : 'text/html, application/xhtml+xml',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (! deskForm) {
            fetchHeaders['Turbo-Frame'] = 'erp-form-modal';
        }

        if (deskForm) {
            showErpSweetAlert(
                form.dataset.erpDeskSubmittingMessage?.trim() || 'Submitting…',
                'info',
                { timer: 2000 },
            );
        }

        try {
            let response = await fetch(erpFormActionUrl(form), {
                method: method === 'GET' ? 'GET' : 'POST',
                body: method === 'GET' ? null : formData,
                headers: fetchHeaders,
                credentials: 'same-origin',
                redirect: 'manual',
            });

            // Opaque redirects (status 0) hide Location + body. Follow once so we can
            // recover success markers or the real validation/exception message.
            if (response.status === 0 || response.type === 'opaqueredirect') {
                response = await fetch(erpFormActionUrl(form), {
                    method: method === 'GET' ? 'GET' : 'POST',
                    body: method === 'GET' ? null : formData,
                    headers: fetchHeaders,
                    credentials: 'same-origin',
                    redirect: 'follow',
                });
            }

            if (deskForm) {
                const contentType = response.headers.get('content-type') ?? '';

                if (contentType.includes('application/json')) {
                    const payload = await response.json().catch(() => ({}));

                    if (response.ok && payload.ok !== false) {
                        this.completeDeskFormRedirect(form, {
                            redirect: payload.redirect ?? window.location.href,
                            message: payload.message ?? '',
                        });
                    } else {
                        const failure = humanizeFormSaveFailure({
                            status: response.status,
                            payload,
                        });

                        reportFormSaveFailure(failure, {
                            status: response.status,
                            url: erpFormActionUrl(form),
                            method,
                            timestamp: new Date().toISOString(),
                        });
                    }

                    return;
                }
            }

            if ((response.status === 301 || response.status === 302 || response.status === 303 || response.status === 307 || response.status === 308) && response.headers.get('Location')) {
                const location = new URL(response.headers.get('Location'), window.location.href).toString();

                if (location && ! this.isModalFormUrl(location)) {
                    const followUp = await fetch(location, {
                        method: 'GET',
                        headers: {
                            'Accept': 'text/html, application/xhtml+xml',
                        },
                        credentials: 'same-origin',
                    });
                    const redirectHtml = await followUp.text();

                    if (this.isDeskShellForm(form)) {
                        this.completeDeskFormRedirect(form, {
                            redirect: location,
                            html: redirectHtml,
                            message: extractFlashMessageFromHtml(redirectHtml),
                            returnUrl: modalReturnUrl,
                        });
                    } else {
                        this.safeHandleSuccess({
                            redirect: location,
                            message: extractFlashMessageFromHtml(redirectHtml),
                            refresh: false,
                            returnUrl: modalReturnUrl,
                        });
                    }

                    return;
                }

                const redirectTarget = location || modalReturnUrl;

                response = await fetch(redirectTarget, {
                    method: 'GET',
                    headers: {
                        'Turbo-Frame': 'erp-form-modal',
                        'Accept': 'text/html, application/xhtml+xml',
                    },
                    credentials: 'same-origin',
                });

                const redirectHtml = await response.text();
                const redirectMessage = extractFlashMessageFromHtml(redirectHtml);

                if (redirectTarget && ! this.isModalFormUrl(redirectTarget)) {
                    if (this.isDeskShellForm(form)) {
                        this.completeDeskFormRedirect(form, {
                            redirect: redirectTarget,
                            html: redirectHtml,
                            message: redirectMessage,
                            returnUrl: modalReturnUrl,
                        });
                    } else {
                        this.safeHandleSuccess({
                            redirect: redirectTarget,
                            message: redirectMessage,
                            refresh: false,
                            returnUrl: modalReturnUrl,
                        });
                    }

                    return;
                }
            }

            const contentType = response.headers.get('content-type') ?? '';
            const responseBody = await response.text();

            if (contentType.includes('application/json')) {
                let payload = {};

                try {
                    payload = JSON.parse(responseBody);
                } catch {
                    payload = {};
                }

                if (payload.ok === false || (! response.ok && Object.keys(payload).length > 0)) {
                    const failure = humanizeFormSaveFailure({
                        status: response.status,
                        payload,
                        html: responseBody,
                    });

                    reportFormSaveFailure(failure, {
                        status: response.status,
                        url: erpFormActionUrl(form),
                        method,
                        timestamp: new Date().toISOString(),
                    });

                    return;
                }

                if (payload.ok !== false) {
                    if (deskForm) {
                        this.completeDeskFormRedirect(form, {
                            redirect: payload.redirect ?? window.location.href,
                            message: payload.message ?? '',
                            returnUrl: modalReturnUrl,
                        });
                    } else {
                        this.safeHandleSuccess({
                            message: payload.message ?? '',
                            redirect: payload.redirect ?? '',
                            refresh: true,
                            returnUrl: modalReturnUrl,
                        });
                    }

                    return;
                }
            }

            const html = responseBody;

            if (html.includes('data-erp-modal-success')) {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const successMarker = doc.querySelector('[data-erp-modal-success]');

                if (deskForm) {
                    this.completeDeskFormRedirect(form, {
                        redirect: successMarker?.dataset.redirect
                            ?? html.match(/data-redirect="([^"]*)"/)?.[1]
                            ?? window.location.href,
                        message: successMarker?.dataset.message
                            ?? html.match(/data-message="([^"]*)"/)?.[1]
                            ?? '',
                        returnUrl: modalReturnUrl,
                    });

                    return;
                }

                this.safeHandleSuccess({
                    message: successMarker?.dataset.message
                        ?? html.match(/data-message="([^"]*)"/)?.[1]
                        ?? '',
                    refresh: (successMarker?.dataset.refresh
                        ?? html.match(/data-refresh="([^"]*)"/)?.[1]
                        ?? '1') !== '0',
                    redirect: successMarker?.dataset.redirect
                        ?? html.match(/data-redirect="([^"]*)"/)?.[1]
                        ?? '',
                    returnUrl: modalReturnUrl,
                });

                return;
            }

            const doc = new DOMParser().parseFromString(html, 'text/html');

            const finalUrl = response.url || '';
            const submittedTo = erpFormActionUrl(form);

            if (
                response.ok
                && method !== 'GET'
                && finalUrl !== ''
                && finalUrl !== submittedTo
                && ! this.isModalFormUrl(finalUrl)
            ) {
                if (this.isDeskShellForm(form)) {
                    this.completeDeskFormRedirect(form, {
                        redirect: finalUrl,
                        html,
                        message: extractFlashMessageFromHtml(html),
                        returnUrl: modalReturnUrl,
                    });
                } else {
                    this.safeHandleSuccess({
                        refresh: false,
                        redirect: finalUrl,
                        message: extractFlashMessageFromHtml(html),
                        returnUrl: modalReturnUrl,
                    });
                }

                return;
            }

            const errorPanel = doc.querySelector('[data-erp-form-modal-panel]')
                ?? doc.querySelector('#erp-form-modal [data-erp-form-modal-panel]');

            if (! response.ok && errorPanel) {
                // Extract before replaceChildren — moving the panel empties these nodes from `doc`.
                let validationErrors = extractValidationErrorsFromDoc(errorPanel);
                if (validationErrors.length === 0) {
                    validationErrors = extractValidationErrorsFromDoc(doc);
                }
                const validationPresentation = extractValidationPresentationFromDoc(errorPanel)
                    ?? extractValidationPresentationFromDoc(doc);
                const frame = this.modalFrame();

                if (frame) {
                    this.prepareModalFormContent(errorPanel, modalFormUrl || modalReturnUrl || response.url);
                    frame.replaceChildren(errorPanel);
                    this.showOverlay();
                    Alpine.initTree(frame);
                    this.ensureValidationSummary(errorPanel, errorPanel);
                    this.highlightInvalidFields(errorPanel, errorPanel);

                    const firstInvalid = errorPanel.querySelector('.erp-input--invalid, .erp-select--invalid, [data-erp-field-error]');
                    firstInvalid?.scrollIntoView?.({ block: 'center', behavior: 'smooth' });
                }

                const failure = humanizeFormSaveFailure({
                    status: response.status,
                    html,
                });
                const alertMessages = validationErrors.length > 0
                    ? validationErrors
                    : failure.messages;

                reportFormSaveFailure({
                    messages: alertMessages,
                    presentation: validationPresentation || failure.presentation,
                    detail: failure.detail,
                }, {
                    status: response.status,
                    url: erpFormActionUrl(form),
                    method,
                    timestamp: new Date().toISOString(),
                });

                return;
            }

            const panel = this.extractModalPanel(html);
            const frame = this.modalFrame();

            if (panel && frame) {
                const panelHasValidation = this.hasValidationErrors(panel);
                const validationErrors = panelHasValidation
                    ? extractValidationErrorsFromDoc(panel)
                    : [];
                const validationPresentation = panelHasValidation
                    ? extractValidationPresentationFromDoc(panel)
                    : null;

                this.prepareModalFormContent(panel, modalFormUrl || modalReturnUrl || response.url);
                frame.replaceChildren(panel);
                this.showOverlay();
                Alpine.initTree(frame);

                if (panelHasValidation) {
                    this.ensureValidationSummary(panel, panel);
                    this.highlightInvalidFields(panel, panel);

                    const failure = humanizeFormSaveFailure({ status: response.status, html });
                    reportFormSaveFailure({
                        messages: validationErrors.length > 0 ? validationErrors : failure.messages,
                        presentation: validationPresentation || failure.presentation,
                        detail: failure.detail,
                    }, {
                        status: response.status,
                        url: erpFormActionUrl(form),
                        method,
                        timestamp: new Date().toISOString(),
                    });
                }

                return;
            }

            if (! response.ok) {
                console.error('erpModalManager.submitFormRequest', {
                    status: response.status,
                    url: erpFormActionUrl(form),
                });

                const failure = humanizeFormSaveFailure({
                    status: response.status,
                    html,
                });

                reportFormSaveFailure(failure, {
                    status: response.status,
                    url: erpFormActionUrl(form),
                    method,
                    timestamp: new Date().toISOString(),
                });

                return;
            }

            if (method !== 'GET' && ! this.hasValidationErrors(doc)) {
                console.error('erpModalManager.submitFormRequest unexpected modal response', {
                    status: response.status,
                    url: erpFormActionUrl(form),
                });

                if (this.isDeskShellForm(form)) {
                    this.completeDeskFormRedirect(form, {
                        redirect: window.location.href,
                        html,
                        message: extractFlashMessageFromHtml(html),
                        returnUrl: modalReturnUrl,
                    });
                } else {
                    const failure = humanizeFormSaveFailure({ status: response.status, html });
                    reportFormSaveFailure({
                        messages: [
                            failure.messages[0] || 'Unable to save form. The server response was unexpected. Please try again.',
                        ],
                        presentation: failure.presentation,
                        detail: failure.detail || html.slice(0, 280),
                    }, {
                        status: response.status,
                        url: erpFormActionUrl(form),
                        method,
                        timestamp: new Date().toISOString(),
                    });
                }

                return;
            }

            if (this.isDeskShellForm(form)) {
                const failure = humanizeFormSaveFailure({ status: response.status, html });
                reportFormSaveFailure(failure, {
                    status: response.status,
                    url: erpFormActionUrl(form),
                    method,
                    timestamp: new Date().toISOString(),
                });

                return;
            }

            reportFormSaveFailure(humanizeFormSaveFailure({ status: response.status, html }), {
                status: response.status,
                url: erpFormActionUrl(form),
                method,
                timestamp: new Date().toISOString(),
            });
        } catch (error) {
            console.error('erpModalManager.submitFormRequest', error);

            reportFormSaveFailure(humanizeFormSaveFailure({ networkError: error }), {
                url: erpFormActionUrl(form),
                method,
                timestamp: new Date().toISOString(),
                detail: error?.stack || null,
            });
        } finally {
            delete form.dataset.erpModalSubmitting;

            const overlay = this.overlay();

            if (submitButton && (deskForm || (overlay && ! overlay.hidden))) {
                submitButton.disabled = false;
            }
        }
    },

    submitForm(form) {
        if (! form) {
            return;
        }

        if (form.closest('#erp-form-modal')) {
            this.submitFormRequest(form);

            return;
        }

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    },

    handleSuccess({ message = '', refresh = true, redirect = '', returnUrl = '' } = {}) {
        const target = typeof redirect === 'string' ? redirect.trim() : '';
        const parentReturn = typeof returnUrl === 'string' ? returnUrl.trim() : '';

        if (target && this.isModalFormUrl(target)) {
            if (message) {
                this.showToast(message);
            }

            if (window.erpLookupManager) {
                window.erpLookupManager.close();
            }

            // Nested success that points at another form: still prefer returning to parent.
            if (this.modalStack.length > 0) {
                this.popModal();
                this.returnToParentModal({ message: '', refresh, redirect: target });

                return;
            }

            this.modalStack = [];
            this.loadForm(target);

            return;
        }

        // Zoho-style continuous work: nested create/edit returns to the parent task.
        if (this.modalStack.length > 0) {
            this.popModal();
            this.returnToParentModal({ message, refresh, redirect: target });

            return;
        }

        // Recovery when the stack was lost but the nested form still points at its parent.
        if (
            parentReturn
            && this.isModalFormUrl(parentReturn)
            && ! this.sameModalUrl(parentReturn, this.currentModalUrl)
        ) {
            if (message) {
                this.showToast(message);
            }

            this.loadForm(parentReturn);

            if (refresh) {
                this.refreshTable().then(() => {
                    this.restoreWorkspaceState();
                });
            }

            document.dispatchEvent(new CustomEvent('erp-modal-nested-return', {
                detail: {
                    redirect: target,
                    message,
                    parentUrl: parentReturn,
                },
            }));

            return;
        }

        this.closeModal();

        if (message) {
            this.showToast(message);
        }

        if (target && ! this.isModalFormUrl(target)) {
            if (typeof window.erpVisitUrl === 'function') {
                window.erpVisitUrl(target);
            } else {
                window.Turbo.visit(target, { frame: 'erp-main', action: 'advance' });
            }

            return;
        }

        if (refresh) {
            this.refreshTable().then(() => {
                this.restoreWorkspaceState();
            });
        }
    },

    isLookupOverlayOpen() {
        const lookupOverlay = document.getElementById('erp-lookup-modal-overlay');

        return Boolean(lookupOverlay && ! lookupOverlay.hidden);
    },

    handleInModalLink(event, link) {
        if (! link?.href || link.hasAttribute('data-no-modal') || link.getAttribute('target') === '_blank') {
            return false;
        }

        try {
            const parsed = new URL(link.href, window.location.origin);

            if (parsed.origin !== window.location.origin) {
                return false;
            }
        } catch {
            return false;
        }

        if (link.hasAttribute('data-erp-modal-open') || this.isModalFormUrl(link.href)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            this.loadForm(link.href);

            return true;
        }

        const turboFrame = link.getAttribute('data-turbo-frame');

        if (turboFrame === 'erp-main' || turboFrame === '_top') {
            event.preventDefault();
            event.stopImmediatePropagation();
            this.closeModal();

            if (typeof window.erpVisitUrl === 'function') {
                window.erpVisitUrl(link.href);
            } else {
                window.Turbo.visit(link.href, {
                    frame: turboFrame === '_top' ? '_top' : 'erp-main',
                    action: 'advance',
                });
            }

            return true;
        }

        return false;
    },

    handleDocumentClick(event) {
        if (this.isLookupOverlayOpen()) {
            if (event.target.closest('#erp-lookup-modal-overlay')) {
                return;
            }
        }

        const modalLink = event.target.closest('a[href]');

        if (modalLink && this.shouldOpenFormModal(modalLink)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            this.loadForm(modalLink.href);

            return;
        }

        const inModalLink = event.target.closest('#erp-form-modal a[href]');

        if (this.handleInModalLink(event, inModalLink)) {
            return;
        }

        const drawerLink = event.target.closest('a[data-turbo-frame="erp-preview-drawer"]');

        if (drawerLink?.href) {
            this.pendingDrawerLoad = true;
            this.showDrawer();
        }

        const closeTrigger = event.target.closest('[data-erp-form-modal-close]');
        const backTrigger = event.target.closest('[data-erp-form-modal-back]');

        if (backTrigger) {
            event.preventDefault();
            event.stopPropagation();
            this.popModal();

            return;
        }

        if (closeTrigger) {
            event.preventDefault();
            event.stopPropagation();

            if (! this.isLookupOverlayOpen()) {
                this.dismissModal();
            }
        }

        const drawerClose = event.target.closest('[data-erp-drawer-close]');

        if (drawerClose) {
            event.preventDefault();
            this.closeDrawer();
        }
    },

    interceptModalNavigation(url) {
        if (! url || ! this.isModalFormUrl(url)) {
            return false;
        }

        this.loadForm(url);

        return true;
    },

    isPrefetchFetch(event) {
        const headers = event.detail?.fetchOptions?.headers;

        if (! headers) {
            return false;
        }

        const read = (name) => (typeof headers.get === 'function' ? headers.get(name) : headers[name]);
        const purpose = (read('Purpose') || read('Sec-Purpose') || read('X-Sec-Purpose') || '').toLowerCase();

        return purpose === 'prefetch';
    },

    bind() {
        document.addEventListener('turbo:before-prefetch', (event) => {
            const link = event.target?.closest?.('a[href]');

            if (link && this.shouldOpenFormModal(link)) {
                event.preventDefault();
            }
        });

        document.addEventListener('turbo:before-visit', (event) => {
            const url = event.detail?.url?.toString();

            if (! url || event.detail?.action === 'restore') {
                return;
            }

            if (this.isModalFormUrl(url)) {
                event.preventDefault();
                this.loadForm(url);
            }
        });

        document.addEventListener('turbo:before-fetch-request', (event) => {
            if (this.isPrefetchFetch(event)) {
                return;
            }

            const url = event.detail?.url?.toString();
            const frame = event.target;

            if (! url || ! (frame instanceof Element)) {
                return;
            }

            const frameId = frame.id ?? '';
            const modalFrames = ['erp-form-modal', 'erp-preview-drawer', 'erp-lookup-modal'];

            if (modalFrames.includes(frameId)) {
                return;
            }

            if (this.isModalFormUrl(url)) {
                event.preventDefault();
                this.loadForm(url);
            }
        });

        document.addEventListener('turbo:click', (event) => {
            const { url, originalEvent } = event.detail ?? {};
            const link = originalEvent?.target?.closest?.('a[href]');

            if (! url || ! link || ! this.shouldOpenFormModal(link)) {
                return;
            }

            event.preventDefault();
            originalEvent?.preventDefault?.();
            originalEvent?.stopImmediatePropagation?.();
            this.loadForm(url);
        });

        // Capture phase for drawer/modal chrome controls and non-Turbo links.
        document.addEventListener('click', (event) => this.handleDocumentClick(event), true);

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (! (form instanceof HTMLFormElement)) {
                return;
            }

            const method = (form.getAttribute('method') ?? 'POST').toUpperCase();

            if (form.closest('#erp-form-modal')) {
                event.preventDefault();
                event.stopImmediatePropagation();

                if (method === 'GET') {
                    const url = new URL(erpFormActionUrl(form), window.location.href);
                    new FormData(form).forEach((value, key) => {
                        if (typeof value === 'string' && value !== '') {
                            url.searchParams.set(key, value);
                        }
                    });
                    this.loadForm(url.toString());

                    return;
                }

                this.prepareModalFormContent(form);
                this.submitFormRequest(form, event.submitter);

                return;
            }

            const deskShell = form.closest('.store-desk-shell, .sales-desk-shell, .designer-desk-shell, .production-floor-shell');

            if (
                deskShell
                && form.hasAttribute('data-erp-desk-form')
                && method !== 'GET'
            ) {
                event.preventDefault();
                event.stopImmediatePropagation();
                this.prepareModalFormContent(form, window.location.href);
                this.submitFormRequest(form, event.submitter);

                return;
            }

            const workspaceFrame = form.closest('#module-workspace-content');

            if (
                workspaceFrame
                && method !== 'GET'
                && form.closest('.erp-form-grid, .erp-form-shell, [data-erp-form-modal-panel]')
            ) {
                event.preventDefault();
                event.stopImmediatePropagation();
                this.prepareModalFormContent(form.closest('[data-erp-form-modal-panel]') ?? form, window.location.href);
                this.submitFormRequest(form, event.submitter);
            }
        }, true);

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            const lookupOverlay = document.getElementById('erp-lookup-modal-overlay');

            if (lookupOverlay && ! lookupOverlay.hidden) {
                return;
            }

            const overlay = this.overlay();

            if (overlay && ! overlay.hidden) {
                event.preventDefault();
                this.dismissModal();
            }

            const drawer = this.drawerOverlay();

            if (drawer && ! drawer.hidden) {
                event.preventDefault();
                this.closeDrawer();
            }
        });
    },

    initFrame(frame) {
        if (! frame) {
            return;
        }

        if (frame.id === 'erp-form-modal') {
            const successMarker = frame.querySelector('[data-erp-modal-success]');

            if (successMarker) {
                this.safeHandleSuccess({
                    message: successMarker.dataset.message ?? '',
                    refresh: successMarker.dataset.refresh !== '0',
                    redirect: successMarker.dataset.redirect ?? '',
                });

                return;
            }

            if (frame.innerHTML.trim() !== '') {
                this.pendingModalLoad = false;
                this.showOverlay();
                Alpine.initTree(frame);
            } else if (! this.pendingModalLoad) {
                this.hideOverlay();
            }
        }

        if (frame.id === 'erp-preview-drawer') {
            if (frame.innerHTML.trim() !== '') {
                this.pendingDrawerLoad = false;
                this.showDrawer();
                Alpine.initTree(frame);
            } else if (! this.pendingDrawerLoad) {
                this.hideDrawer();
            }
        }
    },
};

window.erpModalManager = erpModalManager;
erpModalManager.bind();
erpModalManager.hideOverlay();
erpModalManager.hideDrawer();

const erpLookupManager = {
    pendingLoad: false,
    loadSeq: 0,
    abortController: null,
    onSuccess: null,
    suppressBackdropCloseUntil: 0,
    /** @type {{ panelHTML: string, onSuccess: (() => void)|null }[]} */
    stack: [],

    overlay() {
        return document.getElementById('erp-lookup-modal-overlay');
    },

    frame() {
        return document.getElementById('erp-lookup-modal');
    },

    abortLoad() {
        if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
        }
    },

    renderLoading(title = 'Loading…') {
        const frame = this.frame();

        if (! frame) {
            return;
        }

        frame.innerHTML = `
            <div class="erp-form-modal erp-lookup-modal erp-lookup-modal--w-4xl mx-auto w-full shrink-0" data-erp-lookup-modal-loading>
                <div class="erp-form-modal__header">
                    <h2 id="erp-lookup-modal-title" class="erp-form-modal__title">${title}</h2>
                    <button type="button" class="erp-form-modal__close" data-erp-lookup-modal-close aria-label="Close">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="erp-form-modal__body erp-form-modal__body--loading">
                    <div class="erp-modal-spinner" role="status" aria-label="Loading form"><span class="sr-only">Loading form</span></div>
                </div>
            </div>
        `;

        this.showOverlay();
    },

    extractPanel(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');

        return doc.querySelector('[data-erp-lookup-modal-panel]')
            ?? doc.querySelector('.erp-lookup-modal');
    },

    pushStack() {
        const frame = this.frame();

        if (! frame || this.pendingLoad) {
            return;
        }

        const html = frame.innerHTML.trim();

        if (html === '' || frame.querySelector('[data-erp-lookup-modal-loading]')) {
            return;
        }

        this.stack.push({
            panelHTML: html,
            onSuccess: this.onSuccess,
        });
    },

    restoreStackedPanel(entry) {
        const frame = this.frame();

        if (! frame || ! entry) {
            return;
        }

        frame.innerHTML = entry.panelHTML;
        this.onSuccess = entry.onSuccess ?? null;
        this.pendingLoad = false;
        this.showOverlay();
        Alpine.initTree(frame);
    },

    async open(url, { onSuccess = null, title = 'Loading…' } = {}) {
        const frame = this.frame();

        if (! frame || ! url) {
            return;
        }

        let requestUrl = url;

        try {
            const parsed = new URL(url, window.location.origin);
            parsed.searchParams.set('_erp_lookup_create', '1');
            requestUrl = parsed.toString();
        } catch (error) {
            requestUrl = url;
        }

        this.pushStack();
        this.onSuccess = onSuccess;
        this.abortLoad();
        const loadId = ++this.loadSeq;
        this.abortController = new AbortController();
        this.pendingLoad = true;
        this.suppressBackdropCloseUntil = Date.now() + 400;
        this.renderLoading(title);

        try {
            const response = await fetch(requestUrl, {
                signal: this.abortController.signal,
                headers: {
                    'Accept': 'text/html, application/xhtml+xml',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Erp-Lookup-Create': '1',
                },
                credentials: 'same-origin',
            });

            if (loadId !== this.loadSeq) {
                return;
            }

            if (! response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const panel = this.extractPanel(await response.text());

            if (! panel) {
                throw new Error('Lookup form markup was not found in the response.');
            }

            frame.replaceChildren(panel);
            this.pendingLoad = false;
            this.abortController = null;
            this.showOverlay();
            await new Promise((resolve) => window.requestAnimationFrame(resolve));

            try {
                Alpine.initTree(frame);
            } catch (initError) {
                console.error('erpLookupManager.open initTree', initError);
            }
        } catch (error) {
            if (error?.name === 'AbortError' || loadId !== this.loadSeq) {
                return;
            }

            console.error('erpLookupManager.open', error);
            this.pendingLoad = false;
            this.abortController = null;
            this.close();
            erpModalManager.showToast('Unable to open quick create form. Please try again.', 'error');
        }
    },

    close() {
        if (this.stack.length > 0) {
            this.loadSeq += 1;
            this.abortLoad();
            this.pendingLoad = false;
            this.restoreStackedPanel(this.stack.pop());

            return;
        }

        this.loadSeq += 1;
        this.abortLoad();
        this.pendingLoad = false;
        this.onSuccess = null;
        this.stack = [];

        const frame = this.frame();

        if (frame) {
            frame.innerHTML = '';
        }

        this.hideOverlay();
    },

    showOverlay() {
        const overlay = this.overlay();

        if (overlay) {
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
            overlay.classList.add('erp-lookup-modal-overlay--open');
            document.body.classList.add('overflow-hidden');
        }
    },

    hideOverlay() {
        const overlay = this.overlay();

        if (overlay) {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
            overlay.classList.remove('erp-lookup-modal-overlay--open');

            const formOverlay = document.getElementById('erp-modal-overlay');

            if (! formOverlay || formOverlay.hidden) {
                document.body.classList.remove('overflow-hidden');
            }
        }
    },

    async submitForm(form) {
        if (! form) {
            return;
        }

        const formData = new FormData(form);
        const method = (formData.get('_method') || form.method || 'POST').toString().toUpperCase();

        try {
            const response = await fetch(erpFormActionUrl(form), {
                method: method === 'GET' ? 'GET' : 'POST',
                body: method === 'GET' ? null : formData,
                headers: {
                    'Accept': 'application/json, text/html',
                    'X-Erp-Lookup-Create': '1',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const contentType = response.headers.get('content-type') ?? '';

            if (contentType.includes('application/json')) {
                const payload = await response.json();

                if (! response.ok) {
                    reportFormSaveFailure(humanizeFormSaveFailure({
                        status: response.status,
                        payload,
                    }), {
                        status: response.status,
                        url: erpFormActionUrl(form),
                        method,
                        timestamp: new Date().toISOString(),
                    });

                    return;
                }

                this.handleSuccess(payload);

                return;
            }

            const html = await response.text();
            const panel = this.extractPanel(html);
            const frame = this.frame();

            if (panel && frame) {
                frame.replaceChildren(panel);
                this.showOverlay();
                Alpine.initTree(frame);

                return;
            }

            if (response.ok) {
                this.handleSuccess({ message: 'Saved successfully.' });
            } else {
                reportFormSaveFailure(humanizeFormSaveFailure({
                    status: response.status,
                    html,
                }), {
                    status: response.status,
                    url: erpFormActionUrl(form),
                    method,
                    timestamp: new Date().toISOString(),
                });
            }
        } catch (error) {
            console.error('erpLookupManager.submitForm', error);
            reportFormSaveFailure(humanizeFormSaveFailure({ networkError: error }), {
                url: erpFormActionUrl(form),
                timestamp: new Date().toISOString(),
            });
        }
    },

    handleSuccess(payload = {}) {
        const callback = this.onSuccess;
        const message = payload.message ?? '';

        if (typeof callback === 'function') {
            callback({
                id: payload.id ?? payload.value,
                value: payload.value ?? payload.id,
                label: payload.label ?? '',
            });
        }

        if (this.stack.length > 0) {
            this.loadSeq += 1;
            this.abortLoad();
            this.pendingLoad = false;
            this.restoreStackedPanel(this.stack.pop());
        } else {
            this.loadSeq += 1;
            this.abortLoad();
            this.pendingLoad = false;
            this.onSuccess = null;
            this.stack = [];

            const frame = this.frame();

            if (frame) {
                frame.innerHTML = '';
            }

            this.hideOverlay();
        }

        if (message) {
            erpModalManager.showToast(message);
        }
    },

    bind() {
        document.addEventListener('click', (event) => {
            const closeTrigger = event.target.closest('[data-erp-lookup-modal-close]');

            if (closeTrigger) {
                if (Date.now() < this.suppressBackdropCloseUntil) {
                    event.preventDefault();
                    event.stopPropagation();

                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                this.close();

                return;
            }
        }, true);

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (! (form instanceof HTMLFormElement) || ! form.matches('[data-erp-lookup-form]')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            this.submitForm(form);
        }, true);

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            const overlay = this.overlay();

            if (overlay && ! overlay.hidden) {
                event.preventDefault();
                event.stopPropagation();
                this.close();
            }
        }, true);
    },
};

window.erpLookupManager = erpLookupManager;
erpLookupManager.bind();
erpLookupManager.hideOverlay();

const progressBar = () => document.getElementById('turbo-progress');
const NAVIGATION_FRAME_IDS = new Set(['erp-main', 'module-workspace-content']);
const PROGRESS_FRAME_IDS = new Set(['erp-main']);
const PROGRESS_SAFETY_MS = 12000;
let navigationDepth = 0;
let progressPulseTimer = null;
let progressHideTimer = null;
let progressSafetyTimer = null;

function headerValue(headers, name) {
    if (! headers) {
        return null;
    }

    if (typeof headers.get === 'function') {
        return headers.get(name);
    }

    return headers[name] ?? headers[name.toLowerCase()] ?? null;
}

function isPrefetchFetchEvent(event) {
    const headers = event.detail?.fetchOptions?.headers;
    const purpose = (
        headerValue(headers, 'Purpose')
        || headerValue(headers, 'Sec-Purpose')
        || headerValue(headers, 'X-Sec-Purpose')
        || ''
    ).toLowerCase();

    return purpose === 'prefetch';
}

function navigationFrameFromEvent(event) {
    const target = event.target;

    if (target instanceof Element && NAVIGATION_FRAME_IDS.has(target.id)) {
        return target;
    }

    return null;
}

function frameCountsForProgress(frame) {
    return ! frame || PROGRESS_FRAME_IDS.has(frame.id);
}

function clearProgressSafetyTimer() {
    window.clearTimeout(progressSafetyTimer);
    progressSafetyTimer = null;
}

function armProgressSafetyTimer() {
    clearProgressSafetyTimer();
    progressSafetyTimer = window.setTimeout(() => {
        navigationDepth = 0;
        document.body.classList.remove('erp-navigating');
        hideTurboProgress();
        document.querySelectorAll('.erp-frame--busy').forEach((busyFrame) => {
            markFrameBusy(busyFrame, false);
        });
    }, PROGRESS_SAFETY_MS);
}

function showTurboProgress(startWidth = 28) {
    const bar = progressBar();

    if (! bar) {
        return;
    }

    window.clearTimeout(progressHideTimer);
    bar.classList.add('turbo-progress--visible');
    bar.setAttribute('aria-hidden', 'false');

    const current = Number.parseFloat(bar.style.width || '0') || 0;
    bar.style.width = `${Math.max(current, startWidth)}%`;

    window.clearInterval(progressPulseTimer);
    progressPulseTimer = window.setInterval(() => {
        const width = Number.parseFloat(bar.style.width || '0') || 0;

        if (width >= 86) {
            return;
        }

        bar.style.width = `${width + Math.max(1.2, (86 - width) * 0.08)}%`;
    }, 180);

    armProgressSafetyTimer();
}

function hideTurboProgress() {
    const bar = progressBar();

    window.clearInterval(progressPulseTimer);
    progressPulseTimer = null;
    clearProgressSafetyTimer();

    if (! bar) {
        return;
    }

    bar.style.width = '100%';
    progressHideTimer = window.setTimeout(() => {
        bar.classList.remove('turbo-progress--visible');
        bar.style.width = '0';
        bar.setAttribute('aria-hidden', 'true');
    }, 180);
}

function markFrameBusy(frame, busy) {
    if (! (frame instanceof Element)) {
        return;
    }

    frame.classList.toggle('erp-frame--busy', busy);
    frame.setAttribute('aria-busy', busy ? 'true' : 'false');
}

function beginErpNavigation(frame = null) {
    // Nested workspace content already shows a skeleton — do not hold the global
    // magenta progress bar across the second round-trip (that felt like a long wait).
    if (frameCountsForProgress(frame)) {
        navigationDepth += 1;
        document.body.classList.add('erp-navigating');
        showTurboProgress(navigationDepth === 1 ? 28 : 48);
    }

    if (frame) {
        markFrameBusy(frame, true);
    }
}

function endErpNavigation(frame = null) {
    if (frameCountsForProgress(frame)) {
        navigationDepth = Math.max(0, navigationDepth - 1);
    }

    if (frame) {
        markFrameBusy(frame, false);
    }

    if (navigationDepth === 0) {
        document.body.classList.remove('erp-navigating');
        hideTurboProgress();
        document.querySelectorAll('.erp-frame--busy').forEach((busyFrame) => {
            markFrameBusy(busyFrame, false);
        });
    }
}

function ensureWorkspaceContentFrameLoads(root = document) {
    const frame = root.querySelector?.('#module-workspace-content')
        ?? (root?.id === 'module-workspace-content' ? root : null);

    if (! (frame instanceof HTMLElement)) {
        return;
    }

    // Legacy shells may still ship with loading="lazy", which delays nested content
    // until an intersection observer fires — making sidebar navigation look broken.
    if (frame.getAttribute('loading') === 'lazy') {
        frame.removeAttribute('loading');
    }
}

document.addEventListener('turbo:before-fetch-request', (event) => {
    if (isPrefetchFetchEvent(event)) {
        return;
    }

    const frame = navigationFrameFromEvent(event);

    if (frame) {
        beginErpNavigation(frame);

        return;
    }

    if (event.target === document || event.target === document.documentElement) {
        beginErpNavigation(document.getElementById('erp-main'));
    }
});

document.addEventListener('turbo:fetch-request-error', (event) => {
    const frame = navigationFrameFromEvent(event);

    if (frame || navigationDepth > 0) {
        endErpNavigation(frame);
    }
});

document.addEventListener('turbo:frame-render', (event) => {
    const frame = event.target instanceof Element ? event.target : null;

    if (frame && NAVIGATION_FRAME_IDS.has(frame.id)) {
        endErpNavigation(frame);
    }

    // Promote flash as soon as frame HTML is in the DOM (before frame-load handlers).
    if (frame?.id === 'erp-main' || frame?.id === 'module-workspace-content') {
        promoteFlashAlertsToToast(frame);
        flushPendingDeskToast();
    }

    if (event.target?.id === 'erp-main') {
        syncShellFromFrame();
        window.requestAnimationFrame(() => ensureWorkspaceContentFrameLoads(event.target));
    }

    if (event.target?.id === 'erp-form-modal' || event.target?.id === 'erp-preview-drawer') {
        erpModalManager.initFrame(event.target);
    }

    if (event.target?.id === 'module-workspace-content' || event.target?.querySelector?.('#inbox-messages')) {
        initSharedInboxPoll();
    }

    if (event.target?.querySelector?.('[data-inbox-list-panel]')) {
        initSharedInboxListPoll(event.target);
    }

    if (frame?.id === 'erp-main' || frame?.querySelector?.('[data-open-dialog]')) {
        initNativeDialogs(frame ?? event.target);
    }
});

document.addEventListener('turbo:load', () => {
    navigationDepth = 0;
    document.body.classList.remove('erp-navigating');
    hideTurboProgress();
    document.querySelectorAll('.erp-frame--busy').forEach((busyFrame) => {
        markFrameBusy(busyFrame, false);
    });
    promoteFlashAlertsToToast(document);
    flushPendingDeskToast();
});

function discoveryTokenize(value) {
    return String(value ?? '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .trim()
        .split(/\s+/)
        .filter(Boolean);
}

function discoveryEntryMatches(entry, tokens) {
    if (! entry || entry.coming_soon || ! entry.url) {
        return false;
    }

    const haystack = String(entry.search_text ?? `${entry.label} ${entry.path}`).toLowerCase();

    return tokens.every((token) => haystack.includes(token));
}

function discoveryResolveUrl(entry) {
    if (entry?.url) {
        return entry.url;
    }

    const routeName = entry?.route;

    if (routeName && window.__erpRoutes && window.__erpRoutes[routeName]) {
        return window.__erpRoutes[routeName];
    }

    return routeName ? `/admin?nav=${encodeURIComponent(routeName)}` : '#';
}

function discoverySearchUrl() {
    return window.__erpFeatureDiscovery?.searchUrl ?? '';
}

function discoveryEntryId(entry) {
    return typeof entry === 'string' ? entry : entry?.id;
}

function discoveryNormalizeStoredEntries(entries) {
    if (! Array.isArray(entries)) {
        return [];
    }

    return entries
        .map((entry) => {
            if (typeof entry === 'string') {
                return null;
            }

            if (! entry?.url) {
                return null;
            }

            return {
                id: entry.id,
                label: entry.label ?? '',
                path: entry.path ?? entry.label ?? '',
                url: entry.url,
                turbo_frame: entry.turbo_frame ?? 'module-workspace-content',
            };
        })
        .filter(Boolean);
}

function parseExportFilename(contentDisposition, fallback = 'export') {
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

window.erpExport = {
    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    exportHeaders() {
        return {
            Accept: 'application/octet-stream, application/pdf, text/csv, application/vnd.ms-excel, */*',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfToken(),
        };
    },

    async downloadResponse(response, fallbackFilename = 'export') {
        if (! response.ok) {
            throw new Error(`Export failed (${response.status})`);
        }

        const blob = await response.blob();
        const filename = parseExportFilename(response.headers.get('Content-Disposition'), fallbackFilename);
        await triggerBlobDownload(blob, filename);
    },

    async downloadUrl(url, fallbackFilename = 'export') {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: this.exportHeaders(),
        });

        await this.downloadResponse(response, fallbackFilename);
    },

    async downloadFormPost(url, fields, fallbackFilename = 'export') {
        const body = new FormData();

        Object.entries(fields).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                body.append(key, value);
            }
        });

        if (! body.has('_token')) {
            body.append('_token', this.csrfToken());
        }

        if (document.getElementById('module-workspace-content')) {
            body.append('embedded', '1');
        }

        const response = await fetch(url, {
            method: 'POST',
            body,
            credentials: 'same-origin',
            redirect: 'follow',
            headers: {
                ...this.exportHeaders(),
                Accept: 'text/html,application/xhtml+xml,application/octet-stream,application/pdf,text/csv,application/vnd.ms-excel,*/*',
            },
        });

        const disposition = response.headers.get('Content-Disposition') || '';
        const contentType = response.headers.get('Content-Type') || '';
        const exportMode = response.headers.get('X-Erp-Export') || '';
        const isFileDownload = exportMode === 'direct'
            || disposition.includes('attachment')
            || disposition.includes('filename=')
            || (
                response.ok
                && ! contentType.includes('text/html')
                && ! contentType.includes('text/plain')
                && (
                    contentType.includes('octet-stream')
                    || contentType.includes('pdf')
                    || contentType.includes('csv')
                    || contentType.includes('spreadsheet')
                    || contentType.includes('excel')
                )
            );

        if (isFileDownload) {
            await this.downloadResponse(response, fallbackFilename);

            return;
        }

        await this.handleUnexpectedExportResponse(response);
    },

    async handleUnexpectedExportResponse(response) {
        let detail = '';

        try {
            const preview = await response.clone().text();
            const trimmed = preview.trim();

            if (trimmed.startsWith('<!') || trimmed.startsWith('<html')) {
                detail = 'The server returned a page instead of a file.';
            } else if (trimmed !== '') {
                detail = trimmed.slice(0, 180);
            }
        } catch {
            // Ignore preview failures.
        }

        const message = detail
            ? `Export failed. ${detail}`
            : `Export failed (${response.status || 'unknown error'}). Please try again.`;

        showErpSweetAlert(message, 'error');
    },
};

async function discoveryFetchResults(query, moduleKey = null, limit = 24) {
    const searchUrl = discoverySearchUrl();

    if (! searchUrl || ! String(query ?? '').trim()) {
        return [];
    }

    const params = new URLSearchParams({ q: String(query).trim() });

    if (moduleKey) {
        params.set('module', moduleKey);
    }

    if (limit !== 24) {
        params.set('limit', String(limit));
    }

    const response = await fetch(`${searchUrl}?${params.toString()}`, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (! response.ok) {
        return [];
    }

    const payload = await response.json();

    return Array.isArray(payload.results) ? payload.results : [];
}

function ensureIndexFilterFormContext(form) {
    if (! form) {
        return;
    }

    const workspaceFrame = document.getElementById('module-workspace-content');
    const inWorkspaceFrame = Boolean(form.closest('#module-workspace-content') && workspaceFrame);

    if (! inWorkspaceFrame) {
        return;
    }

    form.setAttribute('data-turbo-frame', 'module-workspace-content');

    try {
        const action = new URL(form.getAttribute('action'), window.location.origin);

        if (! action.searchParams.has('embedded')) {
            action.searchParams.set('embedded', '1');
            form.setAttribute('action', `${action.pathname}${action.search}`);
        }
    } catch {
        // Keep the original form action when it cannot be parsed.
    }
}

const indexFilterFormControllers = new WeakMap();
let indexFilterFormListenersBound = false;

function isProductionFloorLiveFilterForm(form) {
    return Boolean(
        form?.hasAttribute?.('data-production-floor-live-filters')
        && form?.closest?.('.production-floor'),
    );
}

function createIndexFilterFormController(form) {
    return {
        form,
        debounceTimer: null,

        setFilterValue(name, value) {
            const field = this.form.elements[name];

            if (field) {
                field.value = value ?? '';
            }
        },

        resetFilterFormAction() {
            try {
                const action = new URL(this.form.getAttribute('action') ?? window.location.href, window.location.origin);
                const embedded = action.searchParams.get('embedded') === '1';
                action.search = embedded ? 'embedded=1' : '';

                this.form.setAttribute('action', `${action.pathname}${action.search ? `?${action.search}` : ''}`);
            } catch {
                // Keep the original form action when it cannot be parsed.
            }

            ensureIndexFilterFormContext(this.form);
        },

        submitFilterForm() {
            ensureIndexFilterFormContext(this.form);

            const temporarilyDisabled = [];
            this.form.querySelectorAll('input[type="hidden"][name]').forEach((field) => {
                if (field.value === '') {
                    field.disabled = true;
                    temporarilyDisabled.push(field);
                } else {
                    field.disabled = false;
                }
            });

            if (typeof this.form.requestSubmit === 'function') {
                this.form.requestSubmit();
            } else {
                this.form.submit();
            }

            setTimeout(() => {
                temporarilyDisabled.forEach((field) => {
                    field.disabled = false;
                });
            }, 0);
        },

        resetFilters() {
            this.form.querySelectorAll('input[name], select[name], textarea[name]').forEach((field) => {
                field.disabled = false;

                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = false;

                    return;
                }

                if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;

                    return;
                }

                field.value = '';
            });

            this.resetFilterFormAction();
            this.submitFilterForm();
        },

        onFieldChange(event) {
            const target = event.target;

            if (! target?.name || target.hasAttribute('data-erp-auto-search')) {
                return;
            }

            if (
                target.tagName === 'SELECT'
                || target.type === 'date'
                || target.type === 'number'
                || target.type === 'checkbox'
            ) {
                this.submitFilterForm();
            }
        },
    };
}

function getIndexFilterFormController(form) {
    if (! (form instanceof HTMLFormElement)) {
        return null;
    }

    if (! indexFilterFormControllers.has(form)) {
        indexFilterFormControllers.set(form, createIndexFilterFormController(form));
    }

    return indexFilterFormControllers.get(form);
}

function bindIndexFilterForms(root = document) {
    root.querySelectorAll('form.erp-index-toolbar-form').forEach((form) => {
        ensureIndexFilterFormContext(form);
        getIndexFilterFormController(form);
    });
}

function bindIndexFilterFormListeners() {
    if (indexFilterFormListenersBound) {
        return;
    }

    indexFilterFormListenersBound = true;

    document.addEventListener('click', (event) => {
        const pill = event.target.closest('[data-erp-filter-pill]');

        if (pill) {
            const form = pill.closest('form.erp-index-toolbar-form');

            if (! form) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const controller = getIndexFilterFormController(form);
            controller.setFilterValue(
                pill.dataset.erpFilterParam ?? '',
                pill.dataset.erpFilterValue ?? '',
            );
            controller.submitFilterForm();

            return;
        }

        const resetButton = event.target.closest('[data-erp-filter-reset]');

        if (resetButton) {
            const form = resetButton.closest('form.erp-index-toolbar-form');

            if (! form) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            getIndexFilterFormController(form).resetFilters();
        }
    }, true);

    document.addEventListener('change', (event) => {
        const form = event.target?.closest?.('form.erp-index-toolbar-form');

        if (! form) {
            return;
        }

        getIndexFilterFormController(form).onFieldChange(event);
    }, true);

    document.addEventListener('input', (event) => {
        if (! event.target?.hasAttribute?.('data-erp-auto-search')) {
            return;
        }

        const form = event.target.closest('form.erp-index-toolbar-form');

        if (! form) {
            return;
        }

        const controller = getIndexFilterFormController(form);
        clearTimeout(controller.debounceTimer);
        controller.debounceTimer = setTimeout(() => controller.submitFilterForm(), 300);
    }, true);
}

function ensureWebsiteSettingsFormContext(form) {
    if (! form) {
        return;
    }

    const inWorkspaceFrame = Boolean(form.closest('#module-workspace-content'));

    if (! inWorkspaceFrame) {
        return;
    }

    form.setAttribute('data-turbo-frame', 'module-workspace-content');
}

function nextJsonSettingsRowIndex(list) {
    let maxIndex = -1;

    list.querySelectorAll('[data-json-row] input[name]').forEach((input) => {
        const match = input.name.match(/\[(\d+)\]/);

        if (match) {
            maxIndex = Math.max(maxIndex, Number.parseInt(match[1], 10));
        }
    });

    return maxIndex + 1;
}

function appendJsonSettingsRow(editor) {
    const field = editor.dataset.jsonField;
    const list = editor.querySelector('[data-json-rows-list]');

    if (! field || ! list) {
        return;
    }

    editor.querySelector('[data-json-rows-empty]')?.remove();

    const index = nextJsonSettingsRowIndex(list);
    const row = document.createElement('div');
    row.className = 'flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3';
    row.dataset.jsonRow = '';
    const isNav = field === 'footer_nav';

    if (isNav) {
        row.innerHTML = `
            <input type="text" name="${field}[${index}][label]" class="erp-input min-w-[8rem] flex-1" placeholder="Link label" required>
            <input type="text" name="${field}[${index}][href]" class="erp-input min-w-[12rem] flex-[2]" placeholder="URL or path" required>
            <button type="button" class="erp-btn-secondary text-xs" data-json-row-remove>Remove</button>
        `;
    } else {
        row.innerHTML = `
            <input type="text" name="${field}[${index}]" class="erp-input flex-1" placeholder="Badge label" required>
            <button type="button" class="erp-btn-secondary text-xs" data-json-row-remove>Remove</button>
        `;
    }

    list.appendChild(row);
}

let websiteSettingsListenersBound = false;

function bindWebsiteSettingsListeners() {
    if (websiteSettingsListenersBound) {
        return;
    }

    websiteSettingsListenersBound = true;

    document.addEventListener('click', (event) => {
        const tabTrigger = event.target.closest('[data-settings-tab-trigger]');

        if (tabTrigger) {
            const form = tabTrigger.closest('[data-settings-tabs]');

            if (! form) {
                return;
            }

            event.preventDefault();

            const tab = tabTrigger.getAttribute('data-settings-tab-trigger');

            form.querySelectorAll('[data-settings-tab-trigger]').forEach((button) => {
                button.removeAttribute('data-settings-tab-active');
            });
            tabTrigger.setAttribute('data-settings-tab-active', '');

            form.querySelectorAll('[data-settings-tab-panel]').forEach((panel) => {
                panel.hidden = panel.getAttribute('data-settings-tab-panel') !== tab;
            });

            return;
        }

        const addButton = event.target.closest('[data-json-row-add]');

        if (addButton) {
            const editor = addButton.closest('[data-json-rows-editor]');

            if (! editor) {
                return;
            }

            event.preventDefault();
            appendJsonSettingsRow(editor);

            return;
        }

        const removeButton = event.target.closest('[data-json-row-remove]');

        if (removeButton) {
            const row = removeButton.closest('[data-json-row]');
            const editor = removeButton.closest('[data-json-rows-editor]');
            const list = editor?.querySelector('[data-json-rows-list]');

            if (! row || ! list) {
                return;
            }

            event.preventDefault();
            row.remove();

            if (list.querySelectorAll('[data-json-row]').length === 0) {
                const empty = document.createElement('p');
                empty.className = 'text-xs text-slate-500';
                empty.dataset.jsonRowsEmpty = '';
                empty.textContent = list.dataset.emptyLabel || 'No rows yet. Add one below.';
                list.appendChild(empty);
            }

            return;
        }

        const resetTrigger = event.target.closest('[data-website-settings-reset-trigger], [data-document-settings-reset-trigger]');

        if (resetTrigger) {
            const resetUrl = resetTrigger.dataset.resetUrl;
            const confirmMessage = resetTrigger.dataset.resetConfirm || 'Reset this setting to the config fallback?';

            if (! resetUrl) {
                return;
            }

            event.preventDefault();

            if (! window.confirm(confirmMessage)) {
                return;
            }

            const resetForm = document.createElement('form');
            resetForm.method = 'POST';
            resetForm.action = resetUrl;
            resetForm.hidden = true;
            resetForm.dataset.websiteSettingsReset = '';

            const inWorkspace = Boolean(resetTrigger.closest('#module-workspace-content'));
            resetForm.dataset.turboFrame = inWorkspace ? 'module-workspace-content' : 'erp-main';

            if (inWorkspace) {
                try {
                    const url = new URL(resetUrl, window.location.origin);

                    if (! url.searchParams.has('embedded')) {
                        url.searchParams.set('embedded', '1');
                        resetForm.action = `${url.pathname}${url.search}`;
                    }
                } catch {
                    // Keep the original reset URL when it cannot be parsed.
                }
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                ?? resetTrigger.closest('form')?.querySelector('input[name="_token"]')?.value;

            if (csrf) {
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrf;
                resetForm.appendChild(tokenInput);
            }

            document.body.appendChild(resetForm);
            resetForm.requestSubmit();
            resetForm.remove();
        }
    }, true);

    document.addEventListener('input', (event) => {
        if (! event.target?.hasAttribute?.('data-settings-search')) {
            return;
        }

        const settingsSearch = event.target;
        const scope = settingsSearch.closest('form') ?? settingsSearch.closest('.module-workspace-embedded') ?? document;
        const needle = settingsSearch.value.trim().toLowerCase();

        scope.querySelectorAll('[data-settings-field], [data-json-rows-editor]').forEach((field) => {
            const label = field.dataset.settingsLabel || '';
            const key = field.dataset.settingsKey || field.dataset.jsonField || '';
            const haystack = `${label} ${key}`.toLowerCase();
            field.style.display = needle === '' || haystack.includes(needle) ? '' : 'none';
        });
    }, true);
}

function bindWebsiteSettingsForms(root = document) {
    root.querySelectorAll('[data-website-settings-form]').forEach((form) => {
        ensureWebsiteSettingsFormContext(form);
    });
}

bindWebsiteSettingsListeners();

bindIndexFilterFormListeners();

document.addEventListener('alpine:init', () => {
    Alpine.data('erpLookupCreate', (config = {}) => ({
        name: config.name ?? '',
        selected: config.selected ?? '',
        options: Array.isArray(config.options) ? config.options : [],
        createUrl: config.createUrl ?? null,
        refreshUrl: config.refreshUrl ?? null,
        modalTitle: config.modalTitle ?? 'Create',
        scopeCompanyField: config.scopeCompanyField ?? null,
        scopeBranchField: config.scopeBranchField ?? null,
        scopeCustomerField: config.scopeCustomerField ?? null,
        scopeCategoryField: config.scopeCategoryField ?? null,
        scopeFormKey: config.scopeFormKey ?? null,
        _onNestedReturn: null,

        init() {
            if (this.options.length === 0) {
                const select = this.$root.querySelector('select');

                if (select) {
                    this.options = [...select.options]
                        .filter((option) => option.value !== '')
                        .map((option) => ({
                            value: option.value,
                            label: option.textContent?.trim() ?? option.value,
                        }));
                }
            }

            this.syncSelectOptions();
            this.$watch('options', () => this.syncSelectOptions());
            this.$watch('selected', (value, oldValue) => {
                const select = this.$root.querySelector('select');

                if (! select) {
                    return;
                }

                const next = value ?? '';

                if (select.value !== next) {
                    select.value = next;
                }

                if (String(oldValue ?? '') !== String(next)) {
                    this.$dispatch('erp-lookup-changed', {
                        name: this.name,
                        value: next,
                    });
                }
            });

            this.bindScopeFields();

            if (this.refreshUrl && this.options.length === 0) {
                this.refreshOptions(this.selected || null);
            } else if (this.refreshUrl && this.scopeCategoryField && this.scopedFieldValue(this.scopeCategoryField)) {
                this.refreshOptions(this.selected || null);
            }

            this._onNestedReturn = () => this.onNestedReturn();
            document.addEventListener('erp-modal-nested-return', this._onNestedReturn);
        },

        destroy() {
            if (this._onNestedReturn) {
                document.removeEventListener('erp-modal-nested-return', this._onNestedReturn);
            }
        },

        bindScopeFields() {
            const rootForm = this.$root.closest('form');
            const scopedFields = [
                this.scopeCompanyField,
                this.scopeBranchField,
                this.scopeCustomerField,
                this.scopeCategoryField,
            ].filter(Boolean);

            if (! rootForm || scopedFields.length === 0) {
                return;
            }

            const refreshFromScopeChange = () => {
                this.selected = '';

                if (this.refreshUrl) {
                    this.refreshOptions();
                } else {
                    this.options = [];
                }
            };

            scopedFields.forEach((fieldName) => {
                const field = rootForm.querySelector(`[name="${fieldName}"]`);

                if (field) {
                    field.addEventListener('change', refreshFromScopeChange);
                }
            });

            rootForm.addEventListener('erp-lookup-changed', (event) => {
                if (scopedFields.includes(event.detail?.name)) {
                    refreshFromScopeChange();
                }
            });
        },

        scopedFieldValue(fieldName) {
            const rootForm = this.$root.closest('form');
            const field = rootForm?.querySelector(`[name="${fieldName}"]`);

            return field?.value ?? '';
        },

        syncSelectOptions() {
            const select = this.$root.querySelector('select');

            if (! select) {
                return;
            }

            const current = this.selected ?? '';
            const emptyOption = select.dataset.emptyOption !== '0';
            select.innerHTML = '';

            if (emptyOption) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = select.dataset.placeholder || 'Select';
                select.appendChild(placeholder);
            }

            this.options.forEach((option) => {
                const node = document.createElement('option');
                node.value = String(option.value);
                node.textContent = option.label;
                select.appendChild(node);
            });

            select.value = current;
            this.selected = select.value;
        },

        scopeParams() {
            const params = new URLSearchParams();
            const rootForm = this.$root.closest('form');

            if (this.scopeCompanyField && rootForm) {
                const companyField = rootForm.querySelector(`[name="${this.scopeCompanyField}"]`);

                if (companyField?.value) {
                    params.set('company_id', companyField.value);
                }
            }

            if (this.scopeBranchField && rootForm) {
                const branchField = rootForm.querySelector(`[name="${this.scopeBranchField}"]`);

                if (branchField?.value) {
                    params.set('branch_id', branchField.value);
                }
            }

            if (this.scopeCustomerField && rootForm) {
                const customerField = rootForm.querySelector(`[name="${this.scopeCustomerField}"]`);

                if (customerField?.value) {
                    params.set('customer_id', customerField.value);
                }
            }

            if (this.scopeCategoryField && rootForm) {
                const categoryField = rootForm.querySelector(`[name="${this.scopeCategoryField}"]`);

                if (categoryField?.value) {
                    params.set('category_id', categoryField.value);
                }
            }

            if (this.scopeFormKey) {
                params.set('form_key', this.scopeFormKey);
            }

            return params;
        },

        scopedUrl(baseUrl) {
            if (! baseUrl) {
                return '';
            }

            try {
                const url = new URL(baseUrl, window.location.origin);

                this.scopeParams().forEach((value, key) => {
                    if (value !== '') {
                        url.searchParams.set(key, value);
                    }
                });

                return url.toString();
            } catch (error) {
                console.error('erpLookupCreate.scopedUrl', error);

                const query = this.scopeParams().toString();

                return query ? `${baseUrl}${baseUrl.includes('?') ? '&' : '?'}${query}` : baseUrl;
            }
        },

        async refreshOptions(selectValue = null) {
            if (! this.refreshUrl) {
                return;
            }

            try {
                const response = await fetch(this.scopedUrl(this.refreshUrl), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                this.options = await response.json();

                if (selectValue !== null && selectValue !== undefined && selectValue !== '') {
                    this.selected = String(selectValue);
                }
            } catch (error) {
                console.error('erpLookupCreate.refreshOptions', error);
            }
        },

        openCreate(event) {
            event?.preventDefault?.();
            event?.stopPropagation?.();

            if (! this.createUrl) {
                return;
            }

            if (this.scopeCustomerField && ! this.scopedFieldValue(this.scopeCustomerField)) {
                window.erpModalManager?.showToast?.('Select a customer first.', 'error');

                return;
            }

            if (this.scopeCategoryField && ! this.scopedFieldValue(this.scopeCategoryField)) {
                window.erpModalManager?.showToast?.('Select a category first.', 'error');

                return;
            }

            const url = this.scopedUrl(this.createUrl);

            // Full create/edit forms nest in the form modal so work continues on return.
            if (window.erpModalManager?.isModalFormUrl?.(url)) {
                window.erpModalManager.loadForm(url);

                return;
            }

            if (! window.erpLookupManager) {
                return;
            }

            window.erpLookupManager.open(url, {
                title: this.modalTitle,
                onSuccess: async (record) => {
                    await this.refreshOptions(record.value);
                    this.selected = String(record.value ?? '');
                },
            });
        },

        onNestedReturn() {
            // Parent continuous workspaces reload the whole form after nested success.
            if (this.$root.closest('[data-erp-continuous-workspace]')) {
                return;
            }

            if (this.refreshUrl) {
                this.refreshOptions(this.selected || null);
            }
        },
    }));

    /**
     * Keep a parent create/edit workspace live after nested blocker resolution
     * (approve artwork, create order, open related record, then continue here).
     */
    Alpine.data('erpContinuousWorkspace', (config = {}) => ({
        reloadOnReturn: config.reloadOnReturn === true,
        _onNestedReturn: null,

        init() {
            this.$root.setAttribute('data-erp-continuous-workspace', '1');

            this._onNestedReturn = () => {
                // Soft-refresh lookups so the parent modal stays mounted.
                this.$root.querySelectorAll('.erp-lookup-select').forEach((element) => {
                    const data = element._x_dataStack?.[0];

                    if (data && typeof data.refreshOptions === 'function') {
                        data.refreshOptions(data.selected || null);
                    }
                });

                if (! this.reloadOnReturn) {
                    return;
                }

                const manager = window.erpModalManager;
                const url = manager?.currentModalUrl;

                if (! url || ! manager?.loadForm) {
                    return;
                }

                manager.loadForm(url);
            };

            document.addEventListener('erp-modal-nested-return', this._onNestedReturn);
        },

        destroy() {
            if (this._onNestedReturn) {
                document.removeEventListener('erp-modal-nested-return', this._onNestedReturn);
            }
        },

        checkAgain() {
            const manager = window.erpModalManager;
            const url = manager?.currentModalUrl;

            if (url && manager?.loadForm) {
                manager.loadForm(url);
            }
        },
    }));

    Alpine.data('bomFormLines', (initialLines = [], materials = []) => ({
        lines: Array.isArray(initialLines) && initialLines.length
            ? initialLines.map((line) => ({
                inventory_item_id: line.inventory_item_id != null ? String(line.inventory_item_id) : '',
                quantity_per_unit: line.quantity_per_unit ?? '',
                waste_factor_percent: line.waste_factor_percent ?? 0,
                notes: line.notes ?? '',
            }))
            : [{ inventory_item_id: '', quantity_per_unit: '', waste_factor_percent: 0, notes: '' }],
        materials: Array.isArray(materials) ? materials : [],
        addLine() {
            this.lines.push({ inventory_item_id: '', quantity_per_unit: '', waste_factor_percent: 0, notes: '' });
        },
        removeLine(index) {
            if (this.lines.length <= 1) {
                return;
            }

            this.lines.splice(index, 1);
        },
    }));

    Alpine.data('erpShell', (searchIndex = [], discoveryIndex = []) => ({
        sidebarCollapsed: localStorage.getItem('erp.sidebarCollapsed') === '1',
        mobileNavOpen: false,
        query: '',
        searchOpen: false,
        sidebarResults: [],
        sidebarLoading: false,
        sidebarSearchTimer: null,
        discoveryFavorites: discoveryNormalizeStoredEntries(JSON.parse(localStorage.getItem('erp.discovery.favorites') || '[]')),
        discoveryRecent: discoveryNormalizeStoredEntries(JSON.parse(localStorage.getItem('erp.discovery.recent') || '[]')),
        paletteOpen: false,
        paletteQuery: '',
        paletteHighlightIndex: 0,
        paletteResults: [],
        paletteLoading: false,
        paletteSearchTimer: null,
        favorites: JSON.parse(localStorage.getItem('erp.nav.favorites') || '[]'),

        init() {
            this.$watch('sidebarCollapsed', (value) => {
                localStorage.setItem('erp.sidebarCollapsed', value ? '1' : '0');
            });

            this.$watch('favorites', (value) => {
                localStorage.setItem('erp.nav.favorites', JSON.stringify(value));
            });

            this.$watch('discoveryFavorites', (value) => {
                localStorage.setItem('erp.discovery.favorites', JSON.stringify(value));
            });

            this.$watch('discoveryRecent', (value) => {
                localStorage.setItem('erp.discovery.recent', JSON.stringify(value));
            });

            this.$watch('paletteQuery', () => {
                this.paletteHighlightIndex = 0;
                this.schedulePaletteSearch();
            });

            this.$watch('query', () => {
                this.scheduleSidebarSearch();
            });

            document.addEventListener('erp:page-loaded', () => {
                this.trackRecentVisit();
            });

            this.trackRecentVisit();
        },

        scheduleSidebarSearch() {
            clearTimeout(this.sidebarSearchTimer);

            if (! this.query.trim()) {
                this.sidebarResults = [];
                this.sidebarLoading = false;

                return;
            }

            this.sidebarSearchTimer = setTimeout(() => this.runSidebarSearch(), 150);
        },

        async runSidebarSearch() {
            const tokens = this.query.trim();

            if (! tokens) {
                this.sidebarResults = [];
                this.sidebarLoading = false;

                return;
            }

            this.sidebarLoading = true;

            try {
                this.sidebarResults = await discoveryFetchResults(tokens, null, 12);
            } catch {
                this.sidebarResults = [];
            } finally {
                this.sidebarLoading = false;
            }
        },

        schedulePaletteSearch() {
            clearTimeout(this.paletteSearchTimer);

            if (! this.paletteQuery.trim()) {
                this.paletteResults = [];
                this.paletteLoading = false;

                return;
            }

            this.paletteSearchTimer = setTimeout(() => this.runPaletteSearch(), 150);
        },

        async runPaletteSearch() {
            const tokens = this.paletteQuery.trim();

            if (! tokens) {
                this.paletteResults = [];
                this.paletteLoading = false;

                return;
            }

            this.paletteLoading = true;

            try {
                this.paletteResults = await discoveryFetchResults(tokens, null, 24);
            } catch {
                this.paletteResults = [];
            } finally {
                this.paletteLoading = false;
            }
        },

        get searchHits() {
            return this.sidebarResults;
        },

        get favoriteItems() {
            return this.discoveryFavorites;
        },

        get favoriteDiscoveryItems() {
            return this.discoveryFavorites;
        },

        get recentItems() {
            return this.discoveryRecent;
        },

        get paletteFlatResults() {
            return this.paletteResults;
        },

        get paletteSections() {
            if (! this.paletteQuery.trim()) {
                return [];
            }

            const buckets = {
                features: [],
                reports: [],
                settings: [],
                workflows: [],
                workspaces: [],
            };

            for (const item of this.paletteResults) {
                const key = buckets[item.category] ? item.category : 'features';
                buckets[key].push(item);
            }

            const labels = {
                features: 'Features',
                reports: 'Reports',
                settings: 'Settings',
                workflows: 'Workflows',
                workspaces: 'Workspaces',
            };

            return Object.entries(buckets)
                .filter(([, items]) => items.length > 0)
                .map(([key, items]) => ({
                    key,
                    label: labels[key] ?? key,
                    items,
                }));
        },

        get paletteSelectableItems() {
            if (this.paletteQuery.trim()) {
                return this.paletteSections.flatMap((section) => section.items);
            }

            return [...this.recentItems, ...this.favoriteDiscoveryItems];
        },

        paletteSectionOffset(sectionKey, index) {
            let offset = this.paletteQuery.trim() ? 0 : this.recentItems.length;

            for (const section of this.paletteSections) {
                if (section.key === sectionKey) {
                    return offset + index;
                }

                offset += section.items.length;
            }

            if (! this.paletteQuery.trim()) {
                return this.recentItems.length + index;
            }

            return index;
        },

        openPalette() {
            this.paletteOpen = true;
            this.paletteQuery = '';
            this.paletteHighlightIndex = 0;

            this.$nextTick(() => {
                this.$refs.paletteInput?.focus();
            });
        },

        closePalette() {
            this.paletteOpen = false;
            this.paletteQuery = '';
            this.paletteHighlightIndex = 0;
        },

        movePaletteSelection(step) {
            const total = this.paletteSelectableItems.length;

            if (total === 0) {
                return;
            }

            this.paletteHighlightIndex = (this.paletteHighlightIndex + step + total) % total;
        },

        openPaletteSelection() {
            const item = this.paletteSelectableItems[this.paletteHighlightIndex];

            if (item) {
                this.navigatePaletteItem(item);
            }
        },

        navigatePaletteItem(item) {
            if (! item?.url) {
                return;
            }

            this.recordRecent(item);
            this.closePalette();
            window.Turbo.visit(item.url, { frame: 'erp-main', action: 'advance' });
        },

        openPaletteItemNewTab(item) {
            if (! item?.url) {
                return;
            }

            this.recordRecent(item);
            window.open(item.url, '_blank', 'noopener,noreferrer');
        },

        async copyPaletteItemLink(item) {
            if (! item?.url) {
                return;
            }

            try {
                await navigator.clipboard.writeText(new URL(item.url, window.location.origin).href);
            } catch {
                // Clipboard may be unavailable in non-secure contexts.
            }
        },

        isDiscoveryFavorite(id) {
            return this.discoveryFavorites.some((entry) => discoveryEntryId(entry) === id);
        },

        toggleDiscoveryFavorite(idOrItem) {
            const id = discoveryEntryId(idOrItem);
            const item = typeof idOrItem === 'object' && idOrItem !== null
                ? idOrItem
                : this.paletteResults.find((entry) => entry.id === id)
                    ?? this.sidebarResults.find((entry) => entry.id === id);

            if (! id) {
                return;
            }

            if (this.isDiscoveryFavorite(id)) {
                this.discoveryFavorites = this.discoveryFavorites.filter((entry) => discoveryEntryId(entry) !== id);

                return;
            }

            if (! item?.url) {
                return;
            }

            this.discoveryFavorites = [
                {
                    id: item.id,
                    label: item.label,
                    path: item.path ?? item.label,
                    url: item.url,
                    turbo_frame: item.turbo_frame ?? 'module-workspace-content',
                },
                ...this.discoveryFavorites,
            ].slice(0, 12);
        },

        recordRecent(item) {
            if (! item?.url) {
                return;
            }

            const entry = {
                id: item.id ?? item.route ?? item.url,
                label: item.label ?? '',
                path: item.path ?? item.label ?? '',
                url: item.url,
                turbo_frame: item.turbo_frame ?? 'module-workspace-content',
            };

            this.discoveryRecent = [
                entry,
                ...this.discoveryRecent.filter((existing) => discoveryEntryId(existing) !== entry.id),
            ].slice(0, 20);
        },

        trackRecentVisit() {
            const meta = document.getElementById('erp-route-meta');

            if (! meta) {
                return;
            }

            const route = meta.dataset.route ?? '';
            const title = meta.dataset.title ?? '';

            if (! route || ! title) {
                return;
            }

            this.recordRecent({
                id: route,
                label: title,
                path: title,
                url: `${window.location.pathname}${window.location.search}`,
            });
        },

        routeUrl(routeName) {
            if (window.__erpRoutes && window.__erpRoutes[routeName]) {
                return window.__erpRoutes[routeName];
            }

            return `/admin?nav=${encodeURIComponent(routeName)}`;
        },

        isFavorite(route) {
            return this.favorites.includes(route);
        },

        toggleFavorite(route) {
            if (this.isFavorite(route)) {
                this.favorites = this.favorites.filter((entry) => entry !== route);
            } else {
                this.favorites = [...this.favorites, route].slice(0, 8);
            }
        },

        clearSearch() {
            this.query = '';
            this.searchOpen = false;
        },

        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        },

        toggleMobileNav() {
            this.mobileNavOpen = !this.mobileNavOpen;
        },

        closeMobileNav() {
            this.mobileNavOpen = false;
        },
    }));

    Alpine.data('moduleWorkspaceSearch', (moduleKey = null) => ({
        query: '',
        open: false,
        hits: [],
        loading: false,
        searchTimer: null,
        moduleKey: moduleKey || null,

        scheduleSearch() {
            clearTimeout(this.searchTimer);
            this.$dispatch('module-workspace-search', { query: this.query });

            if (! this.query.trim()) {
                this.hits = [];
                this.loading = false;

                return;
            }

            this.searchTimer = setTimeout(() => this.runSearch(), 150);
        },

        async runSearch() {
            const tokens = this.query.trim();

            if (! tokens) {
                this.hits = [];
                this.loading = false;

                return;
            }

            this.loading = true;

            try {
                this.hits = await discoveryFetchResults(tokens, this.moduleKey, 16);
            } catch {
                this.hits = [];
            } finally {
                this.loading = false;
            }
        },

        clear() {
            this.query = '';
            this.hits = [];
            this.loading = false;
            this.open = false;
            this.$dispatch('module-workspace-search', { query: '' });
        },
    }));

    Alpine.data('moduleWorkspaceShell', () => ({
        query: '',

        init() {
            this.$watch('query', () => this.applyTabFilter());
            this.applyTabFilter();
        },

        applyTabFilter() {
            const tokens = discoveryTokenize(this.query);
            const tabs = this.$root.querySelectorAll('[data-workspace-tab]');

            tabs.forEach((tab) => {
                const label = tab.dataset.searchLabel ?? '';

                if (tokens.length === 0) {
                    tab.removeAttribute('data-workspace-tab-hidden');
                    return;
                }

                const matches = tokens.every((token) => label.includes(token));

                if (matches) {
                    tab.removeAttribute('data-workspace-tab-hidden');
                } else {
                    tab.setAttribute('data-workspace-tab-hidden', 'true');
                }
            });
        },
    }));

    Alpine.data('navGroup', (groupId, initiallyOpen = false) => ({
        open: initiallyOpen,

        init() {
            if (this.$el.dataset.navGroupOpen === '1') {
                this.open = true;
            } else {
                const stored = localStorage.getItem(`erp.nav.${groupId}`);

                if (stored !== null) {
                    this.open = stored === '1';
                }
            }

            document.addEventListener('erp:page-loaded', () => {
                if (this.$el.dataset.navGroupOpen === '1') {
                    this.open = true;
                }
            });
        },

        toggle() {
            this.open = !this.open;
            localStorage.setItem(`erp.nav.${groupId}`, this.open ? '1' : '0');
        },
    }));

    Alpine.data('erpExportDropdown', () => ({
        exportOpen: false,
        exporting: false,

        async downloadUrl(url, fallbackFilename = 'export') {
            if (this.exporting) {
                return;
            }

            this.exporting = true;
            this.exportOpen = false;

            try {
                await window.erpExport.downloadUrl(url, fallbackFilename);
            } catch (error) {
                console.error('erpExportDropdown.downloadUrl', error);
                showErpSweetAlert('Export failed. Please try again.', 'error');
            } finally {
                this.exporting = false;
            }
        },

        async submitPost(action, fields, fallbackFilename = 'export') {
            if (this.exporting) {
                return;
            }

            this.exporting = true;
            this.exportOpen = false;

            try {
                await window.erpExport.downloadFormPost(action, fields, fallbackFilename);
            } catch (error) {
                console.error('erpExportDropdown.submitPost', error);
                showErpSweetAlert('Export failed. Please try again.', 'error');
            } finally {
                this.exporting = false;
            }
        },
    }));

    Alpine.data('erpDataTable', (config = {}) => ({
        query: '',
        debouncedQuery: '',
        debounceTimer: null,
        activeChip: config.chips?.[0]?.id ?? 'all',
        filterValues: {},
        filterOpen: false,
        pageSize: Number(localStorage.getItem(`erp.table.${config.tableId ?? 'default'}.pageSize`) || 25),
        currentPage: 1,
        exportOpen: false,
        exportLoading: false,
        selectable: config.selectable ?? false,
        selected: new Set(),
        tableId: config.tableId ?? null,
        exportFilename: config.exportFilename ?? 'export',
        tableExportUrl: config.tableExportUrl ?? window.__erpTableExportUrl ?? null,

        _tableRevision: 0,

        init() {
            const bumpTableRevision = () => {
                this._tableRevision += 1;
            };

            this.$watch('query', (value) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.debouncedQuery = value;
                    this.currentPage = 1;
                    bumpTableRevision();
                }, 250);
            });

            this.$watch('filterValues', () => {
                this.currentPage = 1;
                bumpTableRevision();
            });

            this.$watch('currentPage', bumpTableRevision);
            this.$watch('activeChip', bumpTableRevision);
        },

        get selectedCount() {
            return this.selected.size;
        },

        matches(text) {
            if (!this.debouncedQuery.trim()) {
                return true;
            }

            return String(text).toLowerCase().includes(this.debouncedQuery.trim().toLowerCase());
        },

        hasActiveFilters() {
            return Object.entries(this.filterValues).some(([, value]) => {
                if (value === null || value === undefined || value === '' || value === 'all') {
                    return false;
                }

                return true;
            });
        },

        usesClientPagination(rowIndex = null) {
            if (rowIndex === null) {
                return false;
            }

            if (this.debouncedQuery.trim() !== '' || this.hasActiveFilters() || this.activeChip !== 'all') {
                return false;
            }

            return true;
        },

        rowVisible(searchText, chipValue = null, filters = {}, rowIndex = null) {
            if (this.usesClientPagination(rowIndex)) {
                const index = Number(rowIndex);
                const start = (this.currentPage - 1) * this.pageSize + 1;
                const end = this.currentPage * this.pageSize;

                if (index < start || index > end) {
                    return false;
                }
            }

            if (this.activeChip !== 'all' && chipValue !== null && chipValue !== '' && String(chipValue) !== String(this.activeChip)) {
                return false;
            }

            return this.matches(searchText) && this.matchesFilters(filters);
        },

        resetFilters() {
            this.filterValues = {};
            this.currentPage = 1;
        },

        countVisibleRows() {
            if (! this.tableId) {
                return 0;
            }

            const table = document.getElementById(this.tableId);

            if (! table) {
                return 0;
            }

            return [...table.querySelectorAll('tbody tr')].filter((row) => this.isExportableRow(row)).length;
        },

        get showNoResults() {
            void this._tableRevision;

            const searching = this.debouncedQuery.trim() !== '' || this.hasActiveFilters() || this.activeChip !== 'all';

            if (! searching) {
                return false;
            }

            return this.countVisibleRows() === 0;
        },

        matchesFilters(filters = {}) {
            return Object.entries(this.filterValues).every(([key, expected]) => {
                if (expected === null || expected === undefined || expected === '' || expected === 'all') {
                    return true;
                }

                if (key.endsWith('_from')) {
                    const actual = filters[key.replace(/_from$/, '')];

                    return !actual || String(actual) >= String(expected);
                }

                if (key.endsWith('_to')) {
                    const actual = filters[key.replace(/_to$/, '')];

                    return !actual || String(actual) <= String(expected);
                }

                const actual = filters[key];

                if (Array.isArray(actual)) {
                    return actual.map(String).includes(String(expected));
                }

                return String(actual ?? '') === String(expected);
            });
        },

        setPageSize(size) {
            this.pageSize = Number(size);
            this.currentPage = 1;
            localStorage.setItem(`erp.table.${this.tableId ?? 'default'}.pageSize`, String(this.pageSize));
        },

        nextPage() {
            this.currentPage += 1;
        },

        previousPage() {
            this.currentPage = Math.max(1, this.currentPage - 1);
        },

        setChip(id) {
            this.activeChip = id;
        },

        isSelected(id) {
            return this.selected.has(String(id));
        },

        toggleRow(id, event) {
            const key = String(id);

            if (event.target.checked) {
                this.selected.add(key);
            } else {
                this.selected.delete(key);
            }

            this.syncSelectAllCheckbox();
        },

        toggleAll(event) {
            const table = document.getElementById(this.tableId);

            if (!table) {
                return;
            }

            table.querySelectorAll('tbody tr[data-row-id]').forEach((row) => {
                const id = row.dataset.rowId;
                const checkbox = row.querySelector('input[type="checkbox"]');
                const visible = this.isExportableRow(row);

                if (!visible || !checkbox) {
                    return;
                }

                checkbox.checked = event.target.checked;

                if (event.target.checked) {
                    this.selected.add(id);
                    row.dataset.selected = 'true';
                } else {
                    this.selected.delete(id);
                    delete row.dataset.selected;
                }
            });

            this.syncSelectAllCheckbox(event.target);
        },

        syncSelectAllCheckbox(source = null) {
            const table = document.getElementById(this.tableId);

            if (!table) {
                return;
            }

            const selectAll = source ?? table.querySelector('thead input[type="checkbox"]');

            if (!selectAll) {
                return;
            }

            const checkboxes = [...table.querySelectorAll('tbody tr[data-row-id]')]
                .filter((row) => this.isExportableRow(row))
                .map((row) => row.querySelector('input[type="checkbox"]'))
                .filter(Boolean);

            if (checkboxes.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;

                return;
            }

            const checkedCount = checkboxes.filter((checkbox) => checkbox.checked).length;

            selectAll.checked = checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        },

        exportTable(format = 'csv') {
            if (this.exportLoading) {
                return;
            }

            this.exportOpen = false;

            const table = this.tableId
                ? this.$el.querySelector(`#${CSS.escape(this.tableId)}`)
                : this.$el.querySelector('table');

            if (!table) {
                return;
            }

            const headers = [...table.querySelectorAll('thead th')]
                .filter((th) => !th.classList.contains('erp-table-checkbox-col'))
                .filter((th) => !th.classList.contains('erp-table-actions-col'))
                .map((th) => th.textContent.trim());

            const rows = [];

            table.querySelectorAll('tbody tr').forEach((row) => {
                if (!this.isExportableRow(row)) {
                    return;
                }

                const cells = [...row.querySelectorAll('td')]
                    .filter((td) => !td.classList.contains('erp-table-checkbox-col'))
                    .filter((td) => !td.classList.contains('erp-table-actions-col'))
                    .map((td) => td.textContent.trim().replace(/\s+/g, ' '));

                if (cells.length) {
                    rows.push(cells);
                }
            });

            if (rows.length === 0 && headers.length === 0) {
                showErpSweetAlert(this.$el?.dataset?.exportEmptyMessage ?? 'No rows to export.', 'warning');

                return;
            }

            const exportUrl = this.tableExportUrl ?? window.__erpTableExportUrl ?? null;

            if (!exportUrl) {
                showErpSweetAlert('Export is not configured for this table.', 'error');

                return;
            }

            this.submitTableExport(exportUrl, format, headers, rows);
        },

        async submitTableExport(exportUrl, format, headers, rows) {
            this.exportLoading = true;

            try {
                await window.erpExport.downloadFormPost(exportUrl, {
                    _token: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    format,
                    basename: this.exportFilename,
                    title: this.exportFilename,
                    headers: JSON.stringify(headers),
                    rows: JSON.stringify(rows),
                }, this.exportFilename);
            } catch (error) {
                console.error('erpDataTable.submitTableExport', error);
                showErpSweetAlert('Export failed. Please try again.', 'error');
            } finally {
                this.exportLoading = false;
            }
        },

        isExportableRow(row) {
            if (! row || row.hidden) {
                return false;
            }

            if (row.querySelector('[data-export-skip], [data-empty-state], .erp-empty-state')) {
                return false;
            }

            if (row.querySelectorAll('td').length === 0) {
                return false;
            }

            const style = window.getComputedStyle(row);

            return style.display !== 'none' && style.visibility !== 'hidden';
        },

        exportSelected() {
            this.exportTable('csv');
        },
    }));

    Alpine.data('jobCardsRegister', (config = {}) => ({
        columns: config.columns ?? [],
        presets: config.presets ?? [],
        indexUrl: config.indexUrl ?? '',
        columnsOpen: false,
        visibleColumns: {},
        customViews: [],
        storageKeyColumns: 'erp.job-cards.register.columns',
        storageKeyViews: 'erp.job-cards.register.savedViews',

        init() {
            const storedColumns = JSON.parse(localStorage.getItem(this.storageKeyColumns) || 'null');

            this.columns.forEach((column) => {
                this.visibleColumns[column.key] = storedColumns?.[column.key] ?? column.default ?? true;
            });

            this.customViews = JSON.parse(localStorage.getItem(this.storageKeyViews) || '[]');
        },

        isColumnVisible(key) {
            return this.visibleColumns[key] !== false;
        },

        toggleColumn(key) {
            this.visibleColumns[key] = !this.isColumnVisible(key);
            localStorage.setItem(this.storageKeyColumns, JSON.stringify(this.visibleColumns));
        },

        buildUrl(query = {}) {
            const params = new URLSearchParams();

            Object.entries(query).forEach(([name, value]) => {
                if (value !== null && value !== undefined && value !== '' && value !== false) {
                    params.set(name, String(value));
                }
            });

            const qs = params.toString();

            return qs ? `${this.indexUrl}?${qs}` : this.indexUrl;
        },

        applyPreset(key) {
            if (!key) {
                return;
            }

            const preset = this.presets.find((item) => item.key === key);

            if (preset) {
                if (typeof window.erpVisitUrl === 'function') {
                    window.erpVisitUrl(this.buildUrl(preset.query ?? {}));
                } else {
                    window.location.href = this.buildUrl(preset.query ?? {});
                }

                return;
            }

            const custom = this.customViews.find((view) => view.id === key);

            if (custom) {
                if (typeof window.erpVisitUrl === 'function') {
                    window.erpVisitUrl(this.buildUrl(custom.query ?? {}));
                } else {
                    window.location.href = this.buildUrl(custom.query ?? {});
                }
            }
        },

        saveCurrentView() {
            const label = window.prompt('Name this view');

            if (!label || !label.trim()) {
                return;
            }

            const query = Object.fromEntries(new URLSearchParams(window.location.search));
            delete query.page;

            const view = {
                id: `custom-${Date.now()}`,
                label: label.trim(),
                query,
            };

            this.customViews = [...this.customViews, view];
            localStorage.setItem(this.storageKeyViews, JSON.stringify(this.customViews));
        },
    }));

    Alpine.data('productionFloor', (config = {}) => ({
        panelOpen: false,
        panelLoading: false,
        panel: null,
        panelBase: config.panelBase ?? '',
        csrf: config.csrf ?? document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        assignMachineUrl: config.assignMachineUrl ?? '',
        labelUrl: config.labelUrl ?? '',
        jobCardUrl: config.jobCardUrl ?? '',
        machines: config.machines ?? [],
        selectedJobs: [],
        groupBy: '',
        batchMachineOpen: false,
        batchMachineId: '',
        batchSubmitting: false,
        actionModalOpen: false,
        actionModalLoading: false,
        actionModalPanel: null,
        actionModalTarget: '',
        actionModalMachineId: '',
        actionModalAssignSubmitting: false,
        selectedOperatorId: '',
        operatorCreateUrl: config.operatorCreateUrl ?? null,
        operatorsRefreshUrl: config.operatorsRefreshUrl ?? null,
        qcDecision: 'passed',
        modalTitles: config.modalTitles ?? {},
        stickyObserver: null,
        serverFilterTimer: null,

        get visibleJobKeys() {
            const tbody = this.$refs.queueBody;

            if (! tbody) {
                return [];
            }

            return Array.from(tbody.querySelectorAll('tr[data-floor-row]'))
                .filter((row) => ! row.hidden)
                .map((row) => row.dataset.jobKey)
                .filter(Boolean);
        },

        get allVisibleSelected() {
            const visible = this.visibleJobKeys;

            return visible.length > 0 && visible.every((key) => this.selectedJobs.includes(key));
        },

        get someVisibleSelected() {
            return this.visibleJobKeys.some((key) => this.selectedJobs.includes(key));
        },

        init() {
            if (config.initialJobKey) {
                this.openPanel(config.initialJobKey);
            }

            this.initStickyOffset();
            this.initLiveFilters();

            this.$watch('selectedJobs', () => {
                this.$nextTick(() => this.updateStickyOffset());
            });
        },

        initLiveFilters() {
            this.$nextTick(() => {
                const form = this.getFilterForm();

                if (form && ! form.dataset.productionFloorLiveFiltersBound) {
                    form.dataset.productionFloorLiveFiltersBound = '1';

                    form.addEventListener('change', (event) => {
                        if (event.target?.name) {
                            this.applyLiveFilters();
                        }
                    });

                    form.addEventListener('input', (event) => {
                        if (event.target?.name) {
                            this.applyLiveFilters();
                        }
                    });
                }

                this.applyLiveFilters();
            });
        },

        getFilterForm() {
            return this.$el?.querySelector('form[data-production-floor-live-filters]') ?? null;
        },

        readLiveFilterState() {
            const form = this.getFilterForm();

            if (! form) {
                return null;
            }

            const vendorSelect = form.elements.vendor_id;
            let vendorName = '';

            if (vendorSelect?.value && vendorSelect.selectedIndex >= 0) {
                vendorName = vendorSelect.options[vendorSelect.selectedIndex].text.trim().toLowerCase();
            }

            return {
                search: String(form.elements.search?.value ?? '').trim().toLowerCase(),
                stage: String(form.elements.stage?.value ?? ''),
                machineId: String(form.elements.machine_id?.value ?? ''),
                vendorName,
                priority: String(form.elements.priority?.value ?? ''),
                overdueOnly: Boolean(form.elements.overdue?.checked),
            };
        },

        rowMatchesLiveFilters(row, filters) {
            if (! filters) {
                return true;
            }

            if (filters.search) {
                const haystack = row.dataset.filterSearch ?? '';

                if (! haystack.includes(filters.search)) {
                    return false;
                }
            }

            if (filters.stage && row.dataset.filterStage !== filters.stage) {
                return false;
            }

            if (filters.machineId && row.dataset.filterMachineId !== filters.machineId) {
                return false;
            }

            if (filters.vendorName) {
                const rowVendor = row.dataset.filterVendor ?? '';

                if (rowVendor !== filters.vendorName) {
                    return false;
                }
            }

            if (filters.priority && row.dataset.filterPriority !== filters.priority) {
                return false;
            }

            if (filters.overdueOnly && row.dataset.filterOverdue !== '1') {
                return false;
            }

            return true;
        },

        applyLiveFilters() {
            const tbody = this.$refs.queueBody;

            if (! tbody) {
                return;
            }

            const filters = this.readLiveFilterState();
            const rows = tbody.querySelectorAll('tr[data-floor-row]');
            let visibleCount = 0;

            rows.forEach((row) => {
                const visible = this.rowMatchesLiveFilters(row, filters);
                row.hidden = ! visible;

                if (visible) {
                    visibleCount += 1;
                }
            });

            tbody.querySelectorAll('.production-floor-group-header').forEach((header) => {
                let sibling = header.nextElementSibling;
                let groupVisible = false;

                while (sibling && ! sibling.classList.contains('production-floor-group-header')) {
                    if (sibling.hasAttribute('data-floor-row') && ! sibling.hidden) {
                        groupVisible = true;
                        break;
                    }

                    sibling = sibling.nextElementSibling;
                }

                header.hidden = ! groupVisible;
            });

            const emptyRow = this.$refs.liveFilterEmpty;

            if (emptyRow) {
                emptyRow.hidden = visibleCount > 0 || rows.length === 0;
            }
        },

        onLiveFilterInput(event) {
            if (! event.target?.name) {
                return;
            }

            this.applyLiveFilters();

            const delay = event.target.hasAttribute('data-erp-auto-search') ? 400 : 150;
            this.scheduleServerFilter(delay);
        },

        onLiveFilterChange(event) {
            if (! event.target?.name) {
                return;
            }

            this.applyLiveFilters();
            this.scheduleServerFilter(120);
        },

        scheduleServerFilter(delay = 200) {
            clearTimeout(this.serverFilterTimer);
            this.serverFilterTimer = setTimeout(() => {
                const form = this.getFilterForm();

                if (! form) {
                    return;
                }

                getIndexFilterFormController(form)?.submitFilterForm();
            }, delay);
        },

        initStickyOffset() {
            this.$nextTick(() => {
                this.updateStickyOffset();

                if (typeof ResizeObserver === 'undefined' || ! this.$refs.commandBar) {
                    return;
                }

                this.stickyObserver?.disconnect();
                this.stickyObserver = new ResizeObserver(() => this.updateStickyOffset());
                this.stickyObserver.observe(this.$refs.commandBar);
            });
        },

        updateStickyOffset() {
            const bar = this.$refs.commandBar;
            const batchBar = this.$refs.batchBar;
            const shell = this.$el?.closest('.production-floor-shell');

            if (! bar || ! shell) {
                return;
            }

            shell.style.setProperty('--production-floor-sticky-offset', `${bar.offsetHeight}px`);

            const batchHeight = batchBar && this.selectedJobs.length > 0 && batchBar.offsetHeight
                ? batchBar.offsetHeight
                : 0;

            shell.style.setProperty('--production-floor-batch-height', `${batchHeight}px`);
            shell.style.setProperty(
                '--production-floor-table-sticky-offset',
                `${bar.offsetHeight + batchHeight}px`,
            );
        },

        toggleJobSelection(jobKey, checked) {
            if (checked) {
                if (! this.selectedJobs.includes(jobKey)) {
                    this.selectedJobs = [...this.selectedJobs, jobKey];
                }
            } else {
                this.selectedJobs = this.selectedJobs.filter((key) => key !== jobKey);
            }
        },

        toggleSelectAll(checked) {
            const visible = this.visibleJobKeys;

            if (checked) {
                this.selectedJobs = Array.from(new Set([...this.selectedJobs, ...visible]));
            } else {
                this.selectedJobs = this.selectedJobs.filter((key) => ! visible.includes(key));
            }
        },

        clearSelection() {
            this.selectedJobs = [];
        },

        openBatchMachineAssign() {
            if (this.selectedJobs.length === 0) {
                return;
            }

            this.batchMachineId = '';
            this.batchMachineOpen = true;
        },

        refreshProductionFloor() {
            if (window.Turbo) {
                const workspaceFrame = document.getElementById('module-workspace-content');

                if (workspaceFrame) {
                    return window.Turbo.visit(window.location.href, {
                        frame: 'module-workspace-content',
                        action: 'replace',
                    });
                }
            }

            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }
        },

        async assignMachineInline(jobKey, event) {
            const select = event?.target;

            if (! select || select.dataset.assigning === '1') {
                return;
            }

            const machineId = select.value;
            const previousValue = select.dataset.currentValue ?? '';

            if (machineId === previousValue) {
                return;
            }

            select.dataset.assigning = '1';
            select.disabled = true;

            try {
                const body = new FormData();
                body.append('_token', this.csrf);
                body.append('assigned_machine_asset_id', machineId);

                const response = await fetch(`${this.assignMachineUrl}/${jobKey}/assign-machine`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body,
                });

                const payload = await response.json().catch(() => null);

                if (! response.ok || ! payload?.ok) {
                    select.value = previousValue;

                    return;
                }

                select.dataset.currentValue = machineId;
                await this.refreshProductionFloor();
            } catch (error) {
                console.error('productionFloor.assignMachineInline', error);
                select.value = previousValue;
            } finally {
                delete select.dataset.assigning;
                select.disabled = false;
            }
        },

        async submitBatchMachineAssign() {
            if (this.selectedJobs.length === 0 || this.batchSubmitting) {
                return;
            }

            this.batchSubmitting = true;

            try {
                for (const jobKey of this.selectedJobs) {
                    const body = new FormData();
                    body.append('_token', this.csrf);
                    body.append('assigned_machine_asset_id', this.batchMachineId);
                    body.append('from', 'production-floor');

                    await fetch(`${this.assignMachineUrl}/${jobKey}/assign-machine`, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body,
                    });
                }

                this.batchMachineOpen = false;
                await this.refreshProductionFloor();
            } finally {
                this.batchSubmitting = false;
            }
        },

        batchPrintLabels() {
            this.selectedJobs.forEach((jobKey) => {
                window.open(`${this.labelUrl}/${jobKey}/label`, '_blank', 'noopener');
            });
        },

        batchPrintJobCards() {
            this.selectedJobs.forEach((jobKey) => {
                const row = this.$refs.queueBody?.querySelector(`tr[data-job-key="${jobKey}"]`);
                const printPath = row?.dataset.printPath ?? 'job-sheet';

                window.open(`${this.jobCardUrl}/${jobKey}/${printPath}`, '_blank', 'noopener');
            });
        },

        applyGrouping() {
            const tbody = this.$refs.queueBody;

            if (! tbody) {
                return;
            }

            tbody.querySelectorAll('.production-floor-group-header').forEach((row) => row.remove());

            const rows = Array.from(tbody.querySelectorAll('tr[data-floor-row]'));

            if (! this.groupBy) {
                rows.sort((a, b) => Number(a.dataset.originalIndex ?? 0) - Number(b.dataset.originalIndex ?? 0))
                    .forEach((row) => tbody.appendChild(row));

                this.applyLiveFilters();

                return;
            }

            const attribute = `group${this.groupBy.charAt(0).toUpperCase()}${this.groupBy.slice(1)}`;

            rows.forEach((row, index) => {
                if (! row.dataset.originalIndex) {
                    row.dataset.originalIndex = String(index);
                }
            });

            const groups = rows.reduce((carry, row) => {
                const key = row.dataset[attribute] || '—';
                carry[key] = carry[key] || [];
                carry[key].push(row);

                return carry;
            }, {});

            Object.keys(groups)
                .sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' }))
                .forEach((groupLabel) => {
                    const header = document.createElement('tr');
                    header.className = 'production-floor-group-header';
                    header.innerHTML = `<td colspan="10">${groupLabel}</td>`;
                    tbody.appendChild(header);
                    groups[groupLabel].forEach((row) => tbody.appendChild(row));
                });

            this.applyLiveFilters();
        },

        actionModalSubtitle() {
            const titles = this.modalTitles ?? {};

            return titles[this.actionModalTarget] ?? titles.default ?? 'Next step';
        },

        async openActionModal(jobKey, target) {
            this.closePanel();
            this.actionModalTarget = target;
            this.qcDecision = 'passed';
            this.actionModalMachineId = '';
            this.selectedOperatorId = '';
            this.actionModalOpen = true;
            this.actionModalLoading = true;
            this.actionModalPanel = null;

            try {
                const response = await fetch(`${this.panelBase}/${jobKey}/panel`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (response.ok) {
                    this.actionModalPanel = await response.json();

                    if (target === 'machine') {
                        this.actionModalMachineId = String(this.actionModalPanel?.job?.machine_id ?? '');
                    }

                    if (target === 'outsource-send'
                        && this.actionModalPanel?.outsource?.can_return
                        && ! this.actionModalPanel?.outsource?.can_outsource) {
                        this.actionModalTarget = 'outsource-return';
                    }
                }
            } finally {
                this.actionModalLoading = false;
            }
        },

        async openCreateOperator() {
            if (! this.operatorCreateUrl || ! window.erpLookupManager) {
                return;
            }

            window.erpLookupManager.open(this.operatorCreateUrl, {
                title: 'Create operator',
                onSuccess: async (record) => {
                    const selected = String(record?.value ?? record?.id ?? '');

                    await this.refreshActionModalOperators(selected);

                    if (selected) {
                        this.selectedOperatorId = selected;
                    }
                },
            });
        },

        async refreshActionModalOperators(selectValue = null) {
            const jobKey = this.actionModalPanel?.job?.public_id;

            if (jobKey) {
                try {
                    const response = await fetch(`${this.panelBase}/${jobKey}/panel`, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (response.ok) {
                        const panel = await response.json();
                        this.actionModalPanel = {
                            ...this.actionModalPanel,
                            operators: panel.operators ?? [],
                            execution: panel.execution ?? this.actionModalPanel?.execution,
                        };
                    }
                } catch (error) {
                    console.error('productionFloor.refreshActionModalOperators', error);
                }
            } else if (this.operatorsRefreshUrl) {
                try {
                    const response = await fetch(this.operatorsRefreshUrl, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (response.ok && this.actionModalPanel) {
                        const options = await response.json();
                        this.actionModalPanel = {
                            ...this.actionModalPanel,
                            operators: (options ?? []).map((option) => ({
                                id: option.value,
                                name: option.label,
                            })),
                        };
                    }
                } catch (error) {
                    console.error('productionFloor.refreshActionModalOperators', error);
                }
            }

            if (selectValue) {
                this.selectedOperatorId = String(selectValue);
            }
        },

        openQcModal(jobKey) {
            return this.openActionModal(jobKey, 'qc');
        },

        closeActionModal() {
            this.actionModalOpen = false;
            this.actionModalPanel = null;
            this.actionModalTarget = '';
            this.actionModalMachineId = '';
            this.actionModalAssignSubmitting = false;
            this.selectedOperatorId = '';
            this.qcDecision = 'passed';
        },

        async submitActionModalAssignMachine(event) {
            const jobKey = this.actionModalPanel?.job?.public_id;
            const machineId = this.actionModalMachineId;

            if (! jobKey || ! machineId || this.actionModalAssignSubmitting) {
                return;
            }

            this.actionModalAssignSubmitting = true;

            try {
                const body = new FormData();
                body.append('_token', this.csrf);
                body.append('assigned_machine_asset_id', machineId);
                body.append('from', 'production-floor');

                const response = await fetch(`${this.assignMachineUrl}/${jobKey}/assign-machine`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body,
                });

                const payload = await response.json().catch(() => null);

                if (! response.ok || ! payload?.ok) {
                    window.erpModalManager?.showToast?.(
                        payload?.message ?? 'Unable to assign machine. Please try again.',
                        'error',
                    );

                    return;
                }

                this.closeActionModal();
                window.erpModalManager?.showToast?.(payload?.message ?? 'Machine assignment updated.');
                await this.refreshProductionFloor();
            } catch (error) {
                console.error('productionFloor.submitActionModalAssignMachine', error);
                window.erpModalManager?.showToast?.('Unable to assign machine. Please try again.', 'error');
            } finally {
                this.actionModalAssignSubmitting = false;
            }
        },

        closeQcModal() {
            this.closeActionModal();
        },

        async openPanel(jobKey, hash = '') {
            const modalTarget = {
                quality: 'qc',
                fulfilment: 'fulfilment',
                outsource: 'outsource-send',
            }[hash] ?? null;

            if (modalTarget) {
                await this.openActionModal(jobKey, modalTarget);

                return;
            }

            this.panelOpen = true;
            this.panelLoading = true;
            this.panel = null;

            try {
                const response = await fetch(`${this.panelBase}/${jobKey}/panel`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (response.ok) {
                    this.panel = await response.json();
                    if (hash) {
                        this.$nextTick(() => this.scrollToPanelSection(`#${hash}`));
                    }
                }
            } finally {
                this.panelLoading = false;
            }
        },

        scrollToPanelSection(urlOrHash) {
            const hash = String(urlOrHash || '').includes('#')
                ? String(urlOrHash).split('#').pop()
                : String(urlOrHash || '').replace(/^#/, '');

            if (! hash) {
                return;
            }

            const section = document.getElementById(hash);

            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        closePanel() {
            this.panelOpen = false;
            this.panel = null;
        },
    }));

    Alpine.data('designerDesk', (config = {}) => ({
        selectedKey: null,
        panelLoading: false,
        panel: null,
        panelBase: config.panelBase ?? '',
        csrf: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        activeFilter: null,

        init() {
            if (config.initialFilter) {
                this.activeFilter = config.initialFilter;
            } else {
                const urlFilter = new URLSearchParams(window.location.search).get('filter');

                if (urlFilter && urlFilter !== 'all') {
                    this.activeFilter = urlFilter;
                }
            }

            if (config.initialRequestKey) {
                this.selectRequest(config.initialRequestKey);

                return;
            }

            if (config.autoSelectFirst && config.firstKey && window.matchMedia('(min-width: 1024px)').matches) {
                this.selectRequest(config.firstKey);
            }
        },

        async selectRequest(requestKey, scrollTarget = '') {
            if (! requestKey) {
                return;
            }

            this.selectedKey = requestKey;
            this.panelLoading = true;
            this.panel = null;

            try {
                const response = await fetch(`${this.panelBase}/${requestKey}/panel`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (response.ok) {
                    this.panel = await response.json();

                    this.$nextTick(() => {
                        if (window.matchMedia('(max-width: 1023px)').matches) {
                            const workspace = document.querySelector('.designer-desk-workspace');

                            if (workspace) {
                                workspace.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }

                        if (scrollTarget) {
                            this.scrollToSection(scrollTarget);
                        }
                    });
                }
            } finally {
                this.panelLoading = false;
            }
        },

        clearSelection() {
            this.selectedKey = null;
            this.panel = null;
        },

        scrollToSection(id) {
            const section = document.getElementById(id);

            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        setFilter(key) {
            if (key === 'all') {
                this.activeFilter = null;

                return;
            }

            this.activeFilter = this.activeFilter === key ? null : key;
        },

        filterUrgent(key) {
            this.setFilter(key);
        },

        clearFilter() {
            this.activeFilter = null;
        },

        rowVisible(rowElement) {
            if (! this.activeFilter) {
                return true;
            }

            const map = {
                due_today: 'data-urgency-due-today',
                overdue: 'data-urgency-overdue',
                waiting_customer: 'data-urgency-waiting',
                new_assignment: 'data-urgency-new',
                working: 'data-filter-working',
                review: 'data-filter-review',
                late: 'data-filter-late',
                high: 'data-filter-high',
                today: 'data-filter-today',
                mine: 'data-filter-mine',
                available: 'data-filter-available',
                all: null,
            };

            const attribute = map[this.activeFilter];

            if (! attribute) {
                return true;
            }

            return rowElement.getAttribute(attribute) === '1';
        },
    }));

    Alpine.data('storeDeskLookup', (config = {}) => ({
        searchUrl: config.searchUrl ?? '',
        query: '',
        results: [],
        selected: null,
        open: false,
        loading: false,
        searchTimer: null,

        formatQty(value) {
            if (value === null || value === undefined) {
                return '—';
            }

            const number = Number(value);

            return Number.isInteger(number) ? String(number) : number.toFixed(2);
        },

        async openDropdown() {
            this.open = true;
            await this.fetchResults();
        },

        closeDropdown() {
            this.open = false;
        },

        onInput() {
            clearTimeout(this.searchTimer);
            this.open = true;
            this.searchTimer = setTimeout(() => this.fetchResults(), 200);
        },

        selectItem(row) {
            this.selected = row;
            this.open = false;
        },

        clearSelection() {
            this.selected = null;
        },

        async fetchResults() {
            if (! this.searchUrl) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(
                    `${this.searchUrl}?q=${encodeURIComponent(this.query.trim())}`,
                    {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    },
                );

                if (! response.ok) {
                    this.results = [];

                    return;
                }

                const payload = await response.json();
                this.results = payload.results ?? [];

                if (this.results.length === 1 && this.query.trim() !== '') {
                    this.selected = this.results[0];
                }
            } catch (e) {
                this.results = [];
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('invoiceOrderPicker', (orders = []) => ({
        orders: Array.isArray(orders) ? orders : [],
        query: '',
        selected: null,

        get filtered() {
            const needle = this.query.trim().toLowerCase();

            if (! needle) {
                return this.orders;
            }

            return this.orders.filter((order) => (order.search ?? '').includes(needle));
        },

        select(order) {
            this.selected = order;
        },
    }));

    Alpine.data('salesDeskSearch', (config = {}) => ({
        searchUrl: config.searchUrl ?? '',
        deskUrl: config.deskUrl ?? '',
        query: '',
        results: [],
        open: false,
        loading: false,
        searchTimer: null,

        resultKindLabel(row) {
            const labels = {
                customer: 'Customer',
                quotation: 'Quote',
                order: 'Order',
                job: 'Job',
            };

            return labels[row.kind] ?? row.kind ?? 'Record';
        },

        resultHref(row) {
            if (row.url) {
                return row.url;
            }

            if (row.kind === 'customer') {
                return `${this.deskUrl}?customer=${encodeURIComponent(row.key || row.id)}&step=2`;
            }

            return '#';
        },

        async openDropdown() {
            this.open = true;
            await this.fetchResults();
        },

        closeDropdown() {
            this.open = false;
        },

        onInput() {
            clearTimeout(this.searchTimer);
            this.open = true;
            this.searchTimer = setTimeout(() => this.fetchResults(), 200);
        },

        async fetchResults() {
            if (! this.searchUrl) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(
                    `${this.searchUrl}?q=${encodeURIComponent(this.query.trim())}`,
                    {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    },
                );

                if (! response.ok) {
                    this.results = [];

                    return;
                }

                const payload = await response.json();
                this.results = payload.results ?? [];
            } catch (e) {
                this.results = [];
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('salesDeskInlineSpec', (config = {}) => ({
        storeUrl: config.storeUrl ?? '',
        continueUrl: config.continueUrl ?? '',
        customerId: config.customerId ?? null,
        csrf: config.csrf ?? '',
        saving: false,
        error: '',

        async submit(form) {
            if (! form || this.saving) {
                return;
            }

            this.saving = true;
            this.error = '';

            try {
                const body = new FormData(form);
                if (! body.get('customer_id') && this.customerId) {
                    body.set('customer_id', String(this.customerId));
                }

                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Erp-Lookup-Create': '1',
                    },
                    body,
                });

                const payload = await response.json().catch(() => ({}));

                if (! response.ok) {
                    const messages = payload.errors
                        ? Object.values(payload.errors).flat()
                        : [payload.message || 'Unable to save specification.'];
                    this.error = messages.join(' ');

                    return;
                }

                const specId = payload.value ?? payload.id ?? null;
                if (! specId) {
                    this.error = 'Specification saved but no id was returned.';

                    return;
                }

                window.location.href = this.continueUrl.replace('__SPEC__', encodeURIComponent(String(specId)));
            } catch (e) {
                this.error = 'Unable to save specification.';
            } finally {
                this.saving = false;
            }
        },
    }));

    Alpine.data('tableSearch', () => ({
        query: '',
        debouncedQuery: '',
        debounceTimer: null,
        activeChip: 'all',

        init() {
            this.$watch('query', (value) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.debouncedQuery = value;
                }, 250);
            });
        },

        matches(text) {
            if (!this.debouncedQuery.trim()) {
                return true;
            }

            return String(text).toLowerCase().includes(this.debouncedQuery.trim().toLowerCase());
        },

        rowVisible(searchText, chipValue = null) {
            if (this.activeChip !== 'all' && chipValue !== null && chipValue !== '' && String(chipValue) !== String(this.activeChip)) {
                return false;
            }

            return this.matches(searchText);
        },
    }));

    Alpine.data('erpCategorySubcategoryFilter', (config = {}) => ({
        refreshUrl: config.refreshUrl ?? '',
        categoryId: config.categoryId ?? '',
        subcategoryId: config.subcategoryId ?? '',
        subcategories: [],

        init() {
            if (this.categoryId) {
                this.loadSubcategories(this.subcategoryId);
            }
        },

        async onCategoryChange() {
            this.subcategoryId = '';
            await this.loadSubcategories();
        },

        async loadSubcategories(preserveId = null) {
            if (! this.categoryId) {
                this.subcategories = [];

                return;
            }

            if (! this.refreshUrl) {
                return;
            }

            try {
                const url = new URL(this.refreshUrl, window.location.origin);
                url.searchParams.set('category_id', this.categoryId);

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                this.subcategories = await response.json();

                if (preserveId !== null && preserveId !== '' && this.subcategories.some((option) => String(option.value) === String(preserveId))) {
                    this.subcategoryId = String(preserveId);
                }
            } catch (error) {
                console.error('erpCategorySubcategoryFilter.loadSubcategories', error);
                this.subcategories = [];
            }
        },
    }));

    Alpine.data('erpIndexFilterForm', () => ({
        init() {
            bindIndexFilterForms(this.$el);
        },

        onFieldChange(event) {
            getIndexFilterFormController(this.$el)?.onFieldChange(event);
        },

        resetFilters() {
            getIndexFilterFormController(this.$el)?.resetFilters();
        },

        setFilterValue(name, value) {
            getIndexFilterFormController(this.$el)?.setFilterValue(name, value);
        },

        submitFilterForm() {
            getIndexFilterFormController(this.$el)?.submitFilterForm();
        },
    }));

    Alpine.data('permissionMatrixWorkspace', (config) => ({
        activeModule: '',
        search: '',
        modules: config.modules ?? [],
        granted: [...(config.granted ?? [])],
        grantedSet: new Set(config.granted ?? []),
        uncatalogued: [...(config.uncatalogued ?? [])],
        editable: config.editable ?? false,
        storageKey: config.storageKey ?? null,
        lastUpdated: config.lastUpdated ?? null,
        renderLimit: 30,
        renderBatch: 20,

        init() {
            const stored = this.storageKey ? localStorage.getItem(this.storageKey) : null;

            if (stored === 'all') {
                localStorage.removeItem(this.storageKey);
            }

            const storedIsValid = stored && stored !== 'all' && this.modules.some((module) => module.key === stored);

            this.activeModule = storedIsValid ? stored : (this.modules[0]?.key ?? '');

            this.$watch('activeModule', (value) => {
                if (this.storageKey && value) {
                    localStorage.setItem(this.storageKey, value);
                }

                this.resetRenderWindow();
            });

            this.$watch('search', () => this.resetRenderWindow());
        },

        get activeModuleData() {
            return this.modules.find((module) => module.key === this.activeModule) ?? null;
        },

        get activeModuleLabel() {
            return this.activeModuleData?.label ?? '';
        },

        get activeColumns() {
            return this.activeModuleData?.columns ?? [];
        },

        get gridTemplateColumns() {
            const count = Math.max(this.activeColumns.length, 1);

            return `grid-template-columns: 10rem repeat(${count}, 3.25rem)`;
        },

        get visibleRows() {
            const rows = (this.activeModuleData?.rows ?? []).map((row) => ({
                ...row,
                moduleKey: this.activeModule,
                moduleLabel: this.activeModuleLabel,
            }));

            const query = this.search.trim().toLowerCase();

            if (! query) {
                return rows;
            }

            return rows.filter((row) => String(row.label).toLowerCase().includes(query));
        },

        get pagedRows() {
            return this.visibleRows.slice(0, this.renderLimit);
        },

        get hasMoreRows() {
            return this.pagedRows.length < this.visibleRows.length;
        },

        get bulkColumnActions() {
            return this.activeColumns.filter((column) => this.visibleRows.some((row) => row.cells[column.key]));
        },

        get activeModuleStats() {
            const module = this.activeModuleData;

            if (! module) {
                return { capabilities: 0, permissionsEnabled: 0, totalPermissions: 0 };
            }

            const permissions = [];

            module.rows.forEach((row) => {
                Object.values(row.cells).forEach((permission) => {
                    if (permission) {
                        permissions.push(permission);
                    }
                });
            });

            return {
                capabilities: module.rows.length,
                permissionsEnabled: permissions.filter((permission) => this.grantedSet.has(permission)).length,
                totalPermissions: permissions.length,
            };
        },

        isGranted(permission) {
            return permission ? this.grantedSet.has(permission) : false;
        },

        setGranted(permission, enabled) {
            if (! this.editable || ! permission) {
                return;
            }

            if (enabled) {
                this.grantedSet.add(permission);
            } else {
                this.grantedSet.delete(permission);
            }

            this.granted = Array.from(this.grantedSet);
        },

        toggle(permission) {
            this.setGranted(permission, ! this.isGranted(permission));
        },

        modulePermissions() {
            const permissions = [];

            this.visibleRows.forEach((row) => {
                this.activeColumns.forEach((column) => {
                    const permission = row.cells[column.key];

                    if (permission) {
                        permissions.push(permission);
                    }
                });
            });

            return permissions;
        },

        bulkEnableModule() {
            this.modulePermissions().forEach((permission) => this.setGranted(permission, true));
        },

        bulkDisableModule() {
            this.modulePermissions().forEach((permission) => this.setGranted(permission, false));
        },

        bulkEnableColumn(columnKey) {
            this.visibleRows.forEach((row) => {
                const permission = row.cells[columnKey];

                if (permission) {
                    this.setGranted(permission, true);
                }
            });
        },

        setModule(moduleKey) {
            this.activeModule = moduleKey;
        },

        resetRenderWindow() {
            this.renderLimit = 30;
        },

        onTableScroll(event) {
            const element = event.target;

            if (element.scrollTop + element.clientHeight >= element.scrollHeight - 96) {
                this.renderLimit = Math.min(this.visibleRows.length, this.renderLimit + this.renderBatch);
            }
        },
    }));

    function workspaceHubSearchText(card) {
        const label = String(card.label ?? '').toLowerCase();
        const aliases = [];

        if (label.includes('general ledger') || label === 'gl report') {
            aliases.push('gl ledger');
        }

        if (label.includes('profit') || label.includes('p&l')) {
            aliases.push('pnl income statement');
        }

        if (label.includes('trial balance')) {
            aliases.push('tb');
        }

        if (label.includes('chart of accounts')) {
            aliases.push('coa accounts');
        }

        return [
            card.search_text,
            card.label,
            card.description,
            card.group_label,
            ...aliases,
            ...(Array.isArray(card.keywords) ? card.keywords : []),
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();
    }

    Alpine.data('workspaceHub', (cards = []) => ({
        query: '',
        normalizedQuery: '',
        visibleCardIds: [],
        cards: cards.map((card) => ({
            ...card,
            search_text: workspaceHubSearchText(card),
        })),

        init() {
            this.syncVisibleCards();

            this.$watch('query', (value) => {
                this.normalizedQuery = this.normalizeQuery(value);
                this.syncVisibleCards();
            });
        },

        normalizeQuery(value) {
            return String(value ?? '')
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ' ');
        },

        syncVisibleCards() {
            this.normalizedQuery = this.normalizeQuery(this.query);

            if (! this.normalizedQuery) {
                this.visibleCardIds = this.cards.map((card) => card.id);

                return;
            }

            const tokens = this.normalizedQuery.split(' ').filter(Boolean);

            this.visibleCardIds = this.cards
                .filter((card) => tokens.every((token) => card.search_text.includes(token)))
                .map((card) => card.id);
        },

        get visibleCount() {
            return this.visibleCardIds.length;
        },

        cardVisible(cardId) {
            return this.visibleCardIds.includes(cardId);
        },

        groupVisible(groupLabel) {
            return this.cards.some(
                (card) => card.group_label === groupLabel && this.visibleCardIds.includes(card.id),
            );
        },
    }));

    Alpine.data('settingsControlCenter', (cards = []) => ({
        query: '',
        activeFilter: localStorage.getItem('erp.settingsControlCenter.filter') || 'all',
        viewMode: localStorage.getItem('erp.settingsControlCenter.viewMode') || 'grid',
        cards,

        init() {
            this.$watch('activeFilter', (value) => {
                localStorage.setItem('erp.settingsControlCenter.filter', value);
            });

            this.$watch('viewMode', (value) => {
                localStorage.setItem('erp.settingsControlCenter.viewMode', value);
            });
        },

        get visibleCount() {
            return this.cards.filter((card) => this.cardVisible(card.id)).length;
        },

        matches(searchText) {
            if (! this.query.trim()) {
                return true;
            }

            return String(searchText).toLowerCase().includes(this.query.trim().toLowerCase());
        },

        cardVisible(cardId) {
            const card = this.cards.find((entry) => entry.id === cardId);

            if (! card) {
                return false;
            }

            if (! this.matches(card.search_text)) {
                return false;
            }

            if (this.query.trim()) {
                return true;
            }

            if (this.activeFilter !== 'all' && card.domain_slug !== this.activeFilter) {
                return false;
            }

            return true;
        },

        setFilter(slug) {
            this.activeFilter = slug;
        },

        setViewMode(mode) {
            this.viewMode = mode;
        },
    }));

    Alpine.data('formsControlCenter', (config = {}) => ({
        query: '',
        activeCategory: localStorage.getItem('erp.formsControlCenter.category') || 'all',
        auditMode: false,
        importModalOpen: false,
        importMessage: '',
        cards: config.cards ?? [],
        exportPayload: config.exportPayload ?? {},
        auditUrl: config.auditUrl ?? null,

        init() {
            this.$watch('activeCategory', (value) => {
                localStorage.setItem('erp.formsControlCenter.category', value);
            });
        },

        get visibleCount() {
            return this.cards.filter((card) => this.cardVisible(card.id)).length;
        },

        normalizeQuery(value) {
            return String(value ?? '')
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ' ');
        },

        matchesSearch(card) {
            const normalized = this.normalizeQuery(this.query);

            if (! normalized) {
                return true;
            }

            const tokens = normalized.split(' ').filter(Boolean);
            const haystack = String(card.search_text ?? '').toLowerCase();

            return tokens.every((token) => haystack.includes(token));
        },

        cardVisible(cardId) {
            const card = this.cards.find((entry) => entry.id === cardId);

            if (! card) {
                return false;
            }

            if (this.auditMode && ! card.has_governance_issues) {
                return false;
            }

            if (! this.matchesSearch(card)) {
                return false;
            }

            if (this.activeCategory === 'all') {
                return true;
            }

            return card.category_slug === this.activeCategory;
        },

        sectionVisible(categorySlug) {
            if (this.activeCategory !== 'all' && this.activeCategory !== categorySlug) {
                return false;
            }

            return this.cards.some(
                (card) => ! card.comingSoon
                    && card.category_slug === categorySlug
                    && this.cardVisible(card.id),
            );
        },

        plannedSectionVisible() {
            if (this.auditMode) {
                return false;
            }

            return this.cards.some(
                (card) => card.comingSoon && this.cardVisible(card.id),
            );
        },

        setCategory(slug) {
            this.activeCategory = slug;
        },

        exportConfiguration() {
            const payload = JSON.stringify(this.exportPayload, null, 2);
            const blob = new Blob([payload], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const stamp = new Date().toISOString().slice(0, 10);
            const anchor = document.createElement('a');

            anchor.href = url;
            anchor.download = `jana-forms-configuration-${stamp}.json`;
            anchor.click();

            URL.revokeObjectURL(url);
        },

        handleImportSelect(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            this.importMessage = 'Import applies server-side configuration changes and is not yet enabled from this screen. Use Export to download a snapshot for review.';
            event.target.value = '';
        },

        auditForms() {
            this.auditMode = true;
            this.activeCategory = 'all';
            this.query = '';

            this.$nextTick(() => {
                document.getElementById('forms-health-widget')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                });
            });

            if (this.auditUrl && this.visibleCount === 0) {
                if (typeof window.erpVisitUrl === 'function') {
                    window.erpVisitUrl(this.auditUrl);
                } else {
                    window.location.href = this.auditUrl;
                }
            }
        },
    }));

    Alpine.data('roleGovernanceDashboard', () => ({
        query: '',
        drawerOpen: false,
        drawerRole: null,
        previewRole: null,
        previewTop: 0,
        previewLeft: 0,

        matches(text) {
            if (! this.query.trim()) {
                return true;
            }

            return String(text).toLowerCase().includes(this.query.trim().toLowerCase());
        },

        openDrawer(role) {
            this.drawerRole = role;
            this.drawerOpen = true;
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.drawerRole = null;
        },

        setPreview(role, event) {
            this.previewRole = role;
            this._previewAnchor = event?.currentTarget?.querySelector('[data-role-preview-anchor]')
                ?? event?.currentTarget
                ?? null;
            this.$nextTick(() => this.placePreview());
        },

        clearPreview() {
            this.previewRole = null;
            this._previewAnchor = null;
        },

        placePreview() {
            const anchor = this._previewAnchor;

            if (! anchor || ! this.previewRole) {
                return;
            }

            const rect = anchor.getBoundingClientRect();
            const gap = 6;
            const cardWidth = 256;
            const card = this.$refs.rolePreview;
            const cardHeight = card?.offsetHeight ?? 180;
            const spaceBelow = window.innerHeight - rect.bottom;
            let top = rect.bottom + gap;

            if (spaceBelow < cardHeight + gap && rect.top > cardHeight + gap) {
                top = rect.top - cardHeight - gap;
            }

            let left = rect.left;

            if (left + cardWidth > window.innerWidth - 8) {
                left = window.innerWidth - cardWidth - 8;
            }

            this.previewTop = Math.max(8, top);
            this.previewLeft = Math.max(8, left);
        },

        get previewStyle() {
            return {
                top: `${this.previewTop}px`,
                left: `${this.previewLeft}px`,
            };
        },

        openRole(url) {
            if (typeof window.erpVisitUrl === 'function') {
                window.erpVisitUrl(url);
            } else if (window.Turbo) {
                window.Turbo.visit(url, { frame: 'erp-main', action: 'advance' });
            } else {
                window.location.href = url;
            }
        },
    }));

    Alpine.data('erpRowActionsMenu', (align = 'right') => ({
        open: false,
        align,
        menuTop: 0,
        menuLeft: 0,
        menuTransform: 'translateX(-100%)',
        _outsideLockedUntil: 0,
        _menuHost: null,

        init() {
            this.close();
        },

        toggle(event) {
            event?.stopPropagation?.();

            if (this.open) {
                this.close();

                return;
            }

            const menu = this.$refs.menu;

            if (menu && ! menu.querySelector('a, button, form')) {
                return;
            }

            if (window.__erpOpenRowMenu && window.__erpOpenRowMenu !== this) {
                window.__erpOpenRowMenu.close();
            }

            window.__erpOpenRowMenu = this;
            this._outsideLockedUntil = Date.now() + 250;
            this.open = true;
            this.mountMenu();

            if (menu) {
                menu.classList.add('erp-row-actions-menu--open');
            }

            this.$nextTick(() => {
                this.placeMenu();
                requestAnimationFrame(() => this.placeMenu());
            });
        },

        close() {
            this.open = false;

            const menu = this.$refs.menu;

            if (menu) {
                menu.classList.remove('erp-row-actions-menu--open');
            }

            this.restoreMenu();

            if (window.__erpOpenRowMenu === this) {
                window.__erpOpenRowMenu = null;
            }
        },

        closeFromOutside() {
            if (Date.now() < this._outsideLockedUntil) {
                return;
            }

            this.close();
        },

        mountMenu() {
            const menu = this.$refs.menu;

            if (! menu || menu.parentElement === document.body) {
                return;
            }

            this._menuHost = this.$el;
            menu.__erpMenuHost = this._menuHost;
            document.body.appendChild(menu);
        },

        restoreMenu() {
            const menu = this.$refs.menu;
            const host = this._menuHost ?? menu?.__erpMenuHost;

            if (! menu || ! host || menu.parentElement !== document.body) {
                return;
            }

            host.appendChild(menu);
            this._menuHost = null;
        },

        placeMenu() {
            const trigger = this.$refs.trigger;

            if (! trigger || ! this.open) {
                return;
            }

            const menu = this.$refs.menu;

            if (menu) {
                menu.classList.add('erp-row-actions-menu--open');
            }

            const rect = trigger.getBoundingClientRect();
            const gap = 4;
            const menuWidth = menu?.offsetWidth ?? 192;
            const menuHeight = menu?.offsetHeight ?? 0;
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            this.menuTop = spaceBelow < menuHeight + gap && spaceAbove > menuHeight + gap
                ? rect.top - menuHeight - gap
                : rect.bottom + gap;

            if (this.align === 'left') {
                this.menuLeft = Math.max(8, rect.left);
                this.menuTransform = 'none';
            } else {
                this.menuLeft = Math.min(window.innerWidth - 8, rect.right);
                this.menuTransform = 'translateX(-100%)';
            }

            if (this.menuLeft - menuWidth < 8) {
                this.menuLeft = Math.min(rect.left, window.innerWidth - menuWidth - 8);
                this.menuTransform = 'none';
            }
        },

        get menuStyle() {
            if (! this.open) {
                return {};
            }

            return {
                position: 'fixed',
                top: `${this.menuTop}px`,
                left: `${this.menuLeft}px`,
                transform: this.menuTransform,
                zIndex: 99999,
            };
        },
    }));

    Alpine.data('chartOfAccountsExplorer', (bootstrap = {}) => ({
        stats: bootstrap.stats ?? { total: 0, active: 0, locked: 0, groups: 0 },
        types: bootstrap.types ?? [],
        routes: bootstrap.routes ?? {},
        permissions: bootstrap.permissions ?? {},
        selectedTypeId: bootstrap.initialTypeId ?? null,
        selectedGroupId: bootstrap.initialGroupId ?? null,
        selectedAccountId: null,
        groups: [],
        accounts: [],
        groupsLoading: false,
        accountsLoading: false,
        query: '',
        searchResults: [],
        searchOpen: false,
        searchLoading: false,
        drawerOpen: false,
        panel: null,
        panelLoading: false,
        mobileStack: 'types',

        init() {
            if (this.selectedTypeId) {
                this.loadGroups(this.selectedTypeId, this.selectedGroupId);
            }

            this.$watch('selectedTypeId', () => this.syncUrl());
            this.$watch('selectedGroupId', () => this.syncUrl());
        },

        async loadGroups(typeId, selectGroupId = null) {
            this.selectedTypeId = typeId;
            this.groupsLoading = true;
            this.groups = [];
            this.accounts = [];
            this.selectedGroupId = null;

            try {
                const response = await fetch(`${this.routes.groups}?type_id=${typeId}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (! response.ok) {
                    return;
                }

                const data = await response.json();
                this.groups = data.groups ?? [];

                const groupId = selectGroupId ?? this.groups[0]?.id ?? null;

                if (groupId) {
                    await this.loadAccounts(groupId);
                }
            } finally {
                this.groupsLoading = false;
            }

            if (window.innerWidth < 1024) {
                this.mobileStack = 'groups';
            }
        },

        async loadAccounts(groupId) {
            this.selectedGroupId = groupId;
            this.accountsLoading = true;
            this.accounts = [];

            try {
                const response = await fetch(`${this.routes.accounts}?group_id=${groupId}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (! response.ok) {
                    return;
                }

                const data = await response.json();
                this.accounts = data.accounts ?? [];
            } finally {
                this.accountsLoading = false;
            }

            if (window.innerWidth < 1024) {
                this.mobileStack = 'accounts';
            }
        },

        selectType(typeId) {
            this.loadGroups(typeId);
        },

        selectGroup(groupId) {
            this.loadAccounts(groupId);
        },

        async runSearch() {
            const term = this.query.trim();
            this.searchOpen = term !== '';

            if (! term) {
                this.searchResults = [];

                return;
            }

            this.searchLoading = true;

            try {
                const response = await fetch(`${this.routes.search}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.searchResults = data.results ?? [];
                }
            } finally {
                this.searchLoading = false;
            }
        },

        async goToSearchResult(hit) {
            this.query = '';
            this.searchOpen = false;
            this.searchResults = [];

            if (hit.type_id) {
                await this.loadGroups(hit.type_id, hit.group_id ?? null);
            }

            if (hit.account_id) {
                this.openDrawer(hit.account_id);
            }
        },

        panelUrl(accountId) {
            return (this.routes.panel ?? '').replace('__ID__', String(accountId));
        },

        deactivateUrl(accountId) {
            return (this.routes.deactivate ?? '').replace('__ID__', String(accountId));
        },

        async openDrawer(accountId) {
            this.selectedAccountId = accountId;
            this.drawerOpen = true;
            this.panelLoading = true;
            this.panel = null;

            try {
                const response = await fetch(this.panelUrl(accountId), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.panel = data.panel ?? null;
                }
            } finally {
                this.panelLoading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.panel = null;
            this.selectedAccountId = null;
        },

        async deactivateAccount(accountId = null) {
            const id = accountId ?? this.panel?.id;

            if (! id || ! this.permissions.edit) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch(this.deactivateUrl(id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token ?? '',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            if (this.panel?.id === id) {
                this.panel.status = data.status;
                this.panel.status_label = data.status_label;
            }

            const row = this.accounts.find((item) => item.id === id);

            if (row) {
                row.status = data.status;
                row.status_label = data.status_label;
            }

            if (data.status === 'inactive') {
                this.stats.active = Math.max(0, this.stats.active - 1);
            }
        },

        mobileBack() {
            if (this.mobileStack === 'accounts') {
                this.mobileStack = 'groups';

                return;
            }

            if (this.mobileStack === 'groups') {
                this.mobileStack = 'types';
            }
        },

        mobileTitle() {
            if (this.mobileStack === 'groups') {
                const type = this.types.find((item) => item.id === this.selectedTypeId);

                return type?.name ?? '';
            }

            if (this.mobileStack === 'accounts') {
                const group = this.groups.find((item) => item.id === this.selectedGroupId);

                return group?.name ?? '';
            }

            return '';
        },

        syncUrl() {
            if (! window.history?.replaceState) {
                return;
            }

            const params = new URLSearchParams();

            if (this.selectedTypeId) {
                params.set('type_id', String(this.selectedTypeId));
            }

            if (this.selectedGroupId) {
                params.set('group_id', String(this.selectedGroupId));
            }

            const query = params.toString();
            const url = query ? `${window.location.pathname}?${query}` : window.location.pathname;

            window.history.replaceState({}, '', url);
        },
    }));

    Alpine.data('whatsappComposeForm', (bootstrap = {}) => ({
        contactType: bootstrap.contactType || 'customers',
        selectedId: bootstrap.selectedId ? String(bootstrap.selectedId) : '',
        phone: bootstrap.phone || '',
        contactSearch: '',
        pickerOptions: bootstrap.pickerOptions || {},
        filters: {
            branch_id: bootstrap.filters?.branch_id || '',
            customer_type: bootstrap.filters?.customer_type || '',
            status: bootstrap.filters?.status || '',
            has_outstanding: bootstrap.filters?.has_outstanding || '',
            department_id: bootstrap.filters?.department_id || '',
            employment_status: bootstrap.filters?.employment_status || '',
            vendor_type: bootstrap.filters?.vendor_type || '',
        },

        get currentPickerOptions() {
            return this.pickerOptions[this.contactType] || [];
        },

        get visibleContacts() {
            const query = this.contactSearch.trim().toLowerCase();

            return this.currentPickerOptions.filter((person) => {
                if (! this.matchesActiveFilters(person)) {
                    return false;
                }

                if (! query) {
                    return true;
                }

                const haystack = `${person.label || ''} ${person.phone || ''}`.toLowerCase();

                return haystack.includes(query);
            });
        },

        matchesActiveFilters(person) {
            const f = this.filters;
            const source = this.contactType;

            const eq = (expected, actual) => {
                if (expected === undefined || expected === null || expected === '') {
                    return true;
                }

                return String(actual ?? '') === String(expected);
            };

            if (source === 'customers') {
                const outstandingOk = ! f.has_outstanding
                    || Boolean(person.has_outstanding) === ['1', 'true', 'yes'].includes(String(f.has_outstanding));

                return eq(f.branch_id, person.branch_id)
                    && eq(f.customer_type, person.customer_type)
                    && eq(f.status, person.status)
                    && outstandingOk;
            }

            if (source === 'leads') {
                return eq(f.branch_id, person.branch_id) && eq(f.status, person.status);
            }

            if (source === 'employees') {
                return eq(f.department_id, person.department_id)
                    && eq(f.employment_status, person.employment_status);
            }

            if (source === 'suppliers') {
                return eq(f.vendor_type, person.vendor_type) && eq(f.status, person.status);
            }

            return true;
        },

        resetFilters() {
            this.filters = {
                branch_id: '',
                customer_type: '',
                status: '',
                has_outstanding: '',
                department_id: '',
                employment_status: '',
                vendor_type: '',
            };
        },

        setContactType(type) {
            this.contactType = type;
            this.contactSearch = '';
            this.selectedId = '';
            this.phone = type === 'phone' ? this.phone : '';
            this.resetFilters();
        },

        onFiltersChanged() {
            if (! this.selectedId) {
                return;
            }

            const stillVisible = this.visibleContacts.some(
                (person) => String(person.id) === String(this.selectedId),
            );

            if (! stillVisible) {
                this.selectedId = '';
                this.phone = '';
            }
        },

        selectContact(person) {
            this.selectedId = String(person.id);
            this.phone = person.phone || '';
        },
    }));

    Alpine.data('smsCampaignForm', (bootstrap = {}) => ({
        previewUrl: bootstrap.previewUrl,
        estimateUrl: bootstrap.estimateUrl || null,
        sendMode: bootstrap.sendMode || 'immediate',
        recipientSource: bootstrap.recipientSource || 'customers',
        messageTemplate: bootstrap.messageTemplate || '',
        manualPhones: bootstrap.manualPhones || '',
        preview: null,
        pickerOptions: bootstrap.pickerOptions || {},
        selectedRecipientIds: (bootstrap.selectedRecipientIds || []).map(String),
        recipientSearch: '',
        audienceEstimate: null,
        estimatingAudience: false,
        importSummary: null,
        filters: {
            branch_id: bootstrap.filters?.branch_id || '',
            customer_type: bootstrap.filters?.customer_type || '',
            status: bootstrap.filters?.status || '',
            has_outstanding: bootstrap.filters?.has_outstanding || '',
            department_id: bootstrap.filters?.department_id || '',
            employment_status: bootstrap.filters?.employment_status || '',
            vendor_type: bootstrap.filters?.vendor_type || '',
        },

        get currentPickerOptions() {
            return this.pickerOptions[this.recipientSource] || [];
        },

        get visibleRecipients() {
            const query = this.recipientSearch.trim().toLowerCase();

            return this.currentPickerOptions.filter((person) => {
                if (! this.matchesActiveFilters(person)) {
                    return false;
                }

                if (! query) {
                    return true;
                }

                const haystack = `${person.label || ''} ${person.phone || ''}`.toLowerCase();

                return haystack.includes(query);
            });
        },

        get selectedRecipientCount() {
            return this.selectedRecipientIds.length;
        },

        matchesActiveFilters(person) {
            const f = this.filters;
            const source = this.recipientSource;

            const eq = (expected, actual) => {
                if (expected === undefined || expected === null || expected === '') {
                    return true;
                }

                return String(actual ?? '') === String(expected);
            };

            if (['customers', 'dynamic'].includes(source)) {
                const outstandingOk = ! f.has_outstanding
                    || Boolean(person.has_outstanding) === ['1', 'true', 'yes'].includes(String(f.has_outstanding));

                return eq(f.branch_id, person.branch_id)
                    && eq(f.customer_type, person.customer_type)
                    && eq(f.status, person.status)
                    && outstandingOk;
            }

            if (source === 'leads') {
                return eq(f.branch_id, person.branch_id) && eq(f.status, person.status);
            }

            if (source === 'employees') {
                return eq(f.department_id, person.department_id)
                    && eq(f.employment_status, person.employment_status);
            }

            if (source === 'suppliers') {
                return eq(f.vendor_type, person.vendor_type) && eq(f.status, person.status);
            }

            return true;
        },

        onFiltersChanged() {
            const visibleIds = new Set(this.visibleRecipients.map((person) => String(person.id)));
            this.selectedRecipientIds = this.selectedRecipientIds.filter((id) => visibleIds.has(String(id)));
            this.audienceEstimate = null;
        },

        resetFilters() {
            this.filters = {
                branch_id: '',
                customer_type: '',
                status: '',
                has_outstanding: '',
                department_id: '',
                employment_status: '',
                vendor_type: '',
            };
        },

        onRecipientSourceChange() {
            this.recipientSearch = '';
            this.selectedRecipientIds = [];
            this.audienceEstimate = null;
            this.importSummary = null;
            this.resetFilters();
        },

        toggleRecipient(id, checked) {
            const key = String(id);

            if (checked) {
                if (! this.selectedRecipientIds.includes(key)) {
                    this.selectedRecipientIds.push(key);
                }

                return;
            }

            this.selectedRecipientIds = this.selectedRecipientIds.filter((value) => value !== key);
        },

        selectAllVisibleRecipients() {
            const ids = new Set(this.selectedRecipientIds.map(String));

            this.visibleRecipients.forEach((person) => ids.add(String(person.id)));
            this.selectedRecipientIds = Array.from(ids);
            this.audienceEstimate = null;
        },

        clearSelectedRecipients() {
            this.selectedRecipientIds = [];
            this.audienceEstimate = null;
        },

        onTemplateChange(event) {
            const option = event.target.selectedOptions[0];
            const body = option?.dataset?.body;

            if (body) {
                this.messageTemplate = body;
            }
        },

        parsePhoneList(raw) {
            const lines = String(raw || '').split(/\r\n|\r|\n/);
            const recipients = [];
            const invalid = [];
            const seen = new Set();

            lines.forEach((line, index) => {
                const trimmed = line.trim();

                if (! trimmed) {
                    return;
                }

                if (index === 0 && /^(name|phone|mobile|number)\b/i.test(trimmed)) {
                    return;
                }

                const parts = trimmed.split(/[,;\t]+/).map((part) => part.trim()).filter(Boolean);
                let phone = null;
                let name = null;

                parts.forEach((part) => {
                    const digits = part.replace(/\D+/g, '');

                    if (digits.length >= 9) {
                        phone = digits;
                    } else if (! name) {
                        name = part;
                    }
                });

                if (! phone && parts.length === 1) {
                    const digits = parts[0].replace(/\D+/g, '');
                    phone = digits.length >= 9 ? digits : null;
                }

                if (! phone) {
                    invalid.push(trimmed);

                    return;
                }

                if (seen.has(phone)) {
                    return;
                }

                seen.add(phone);
                recipients.push(name ? `${name},${phone}` : phone);
            });

            return { recipients, invalid };
        },

        applyImportedList() {
            const parsed = this.parsePhoneList(this.manualPhones);

            this.manualPhones = parsed.recipients.join('\n');
            this.importSummary = {
                valid: parsed.recipients.length,
                invalid: parsed.invalid.length,
                invalidSamples: parsed.invalid.slice(0, 5),
            };
            this.audienceEstimate = parsed.recipients.length;
        },

        clearImportedList() {
            this.manualPhones = '';
            this.importSummary = null;
            this.audienceEstimate = null;
        },

        async importFromFile(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            const text = await file.text();
            this.manualPhones = text;
            this.applyImportedList();
            event.target.value = '';
        },

        collectRecipientFilters() {
            const source = this.recipientSource;
            const f = this.filters;
            const filters = {};

            const put = (key, value) => {
                if (value !== undefined && value !== null && value !== '') {
                    filters[key] = value;
                }
            };

            if (['customers', 'dynamic', 'leads'].includes(source)) {
                put('branch_id', f.branch_id);
            }

            if (['customers', 'dynamic'].includes(source)) {
                put('customer_type', f.customer_type);
                put('status', f.status);
                put('has_outstanding', f.has_outstanding);
            }

            if (source === 'leads') {
                put('status', f.status);
            }

            if (source === 'employees') {
                put('department_id', f.department_id);
                put('employment_status', f.employment_status);
            }

            if (source === 'suppliers') {
                put('vendor_type', f.vendor_type);
                put('status', f.status);
            }

            if (this.selectedRecipientIds.length && source !== 'dynamic') {
                filters.ids = this.selectedRecipientIds.map((id) => Number(id));
            }

            return filters;
        },

        async estimateAudience() {
            if (! this.estimateUrl) {
                return;
            }

            this.estimatingAudience = true;

            try {
                const response = await fetch(this.estimateUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        recipient_source: this.recipientSource,
                        recipient_filters: this.collectRecipientFilters(),
                        manual_phones: this.manualPhones,
                    }),
                });

                if (response.ok) {
                    const data = await response.json();
                    this.audienceEstimate = data.count ?? 0;
                }
            } finally {
                this.estimatingAudience = false;
            }
        },

        async runPreview() {
            const templateId = document.querySelector('[name=communication_template_id]')?.value || null;

            const response = await fetch(this.previewUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    communication_template_id: templateId || null,
                    message_template: this.messageTemplate,
                }),
            });

            if (response.ok) {
                this.preview = await response.json();
            }
        },
    }));

    function erpVisitUrl(url) {
        if (! url) {
            return;
        }

        let target;

        try {
            target = new URL(url, window.location.origin);
        } catch {
            return;
        }

        if (target.origin !== window.location.origin) {
            window.open(target.href, '_blank', 'noopener');

            return;
        }

        if (window.Turbo) {
            const frame = document.getElementById('erp-main') ? 'erp-main' : '_top';
            window.Turbo.visit(target.href, { frame, action: 'advance' });

            return;
        }

        window.location.assign(target.href);
    }

    function erpRefreshCurrentView() {
        if (! window.Turbo) {
            window.location.reload();

            return;
        }

        const workspaceFrame = document.getElementById('module-workspace-content');

        if (workspaceFrame && document.activeElement?.closest?.('#module-workspace-content')) {
            window.Turbo.visit(window.location.href, {
                frame: 'module-workspace-content',
                action: 'replace',
            });

            return;
        }

        if (document.getElementById('erp-main')) {
            window.Turbo.visit(window.location.href, {
                frame: 'erp-main',
                action: 'replace',
            });

            return;
        }

        window.location.reload();
    }

    window.erpVisitUrl = erpVisitUrl;
    window.erpRefreshCurrentView = erpRefreshCurrentView;

    Alpine.data('erpNotificationBell', (bootstrap = {}) => ({
        routes: bootstrap.routes ?? {},
        unreadCount: bootstrap.unreadCount ?? 0,
        lastKnownCount: bootstrap.unreadCount ?? 0,
        lastSeenNotificationId: bootstrap.latestNotificationId ?? null,
        pollTimer: null,
        open: false,
        loading: false,
        items: [],
        panelTop: 0,
        panelLeft: 0,
        panelTransform: 'translateX(-100%)',
        _panelHost: null,
        _outsideLockedUntil: 0,
        _onDocumentPointerDown: null,

        init() {
            window.addEventListener('erp:notifications-updated', (event) => {
                if (event.detail?.unreadCount !== undefined) {
                    this.unreadCount = event.detail.unreadCount;
                    this.lastKnownCount = event.detail.unreadCount;
                }
            });

            this.startPolling();

            this._onDocumentPointerDown = (event) => {
                if (! this.open) {
                    return;
                }

                if (Date.now() < this._outsideLockedUntil) {
                    return;
                }

                const panel = this.$refs.panel;
                const trigger = this.$refs.trigger;

                if (panel?.contains(event.target) || trigger?.contains(event.target)) {
                    return;
                }

                this.close();
            };

            document.addEventListener('pointerdown', this._onDocumentPointerDown, true);
        },

        startPolling() {
            const poll = async () => {
                if (document.hidden || ! this.routes.unread) {
                    return;
                }

                try {
                    const response = await fetch(this.routes.unread, {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });

                    if (! response.ok) {
                        return;
                    }

                    const data = await response.json();
                    const nextCount = data.unread_count ?? 0;
                    const latest = data.latest ?? null;

                    if (
                        latest?.id
                        && latest.id !== this.lastSeenNotificationId
                        && nextCount > this.lastKnownCount
                    ) {
                        this.lastSeenNotificationId = latest.id;
                        showErpNotificationAlert(latest);
                    }

                    this.unreadCount = nextCount;
                    this.lastKnownCount = nextCount;
                    this.broadcastUnread();
                } catch {
                    // ignore transient network errors
                }
            };

            poll();
            this.pollTimer = window.setInterval(poll, 15000);
        },

        destroy() {
            if (this.pollTimer) {
                window.clearInterval(this.pollTimer);
                this.pollTimer = null;
            }

            if (this._onDocumentPointerDown) {
                document.removeEventListener('pointerdown', this._onDocumentPointerDown, true);
            }
        },

        async toggle() {
            if (this.open) {
                this.close();

                return;
            }

            this._outsideLockedUntil = Date.now() + 250;
            this.open = true;

            this.$nextTick(() => {
                this.mountPanel();
                this.placePanel();
                requestAnimationFrame(() => this.placePanel());
            });

            await this.fetchPanel();
        },

        close() {
            this.open = false;
            this.restorePanel();
        },

        closeFromOutside() {
            if (Date.now() < this._outsideLockedUntil) {
                return;
            }

            this.close();
        },

        mountPanel() {
            const panel = this.$refs.panel;

            if (! panel || panel.parentElement === document.body) {
                return;
            }

            this._panelHost = this.$el;
            panel.__erpPanelHost = this._panelHost;
            document.body.appendChild(panel);
        },

        restorePanel() {
            const panel = this.$refs.panel;
            const host = this._panelHost ?? panel?.__erpPanelHost;

            if (! panel || ! host || panel.parentElement !== document.body) {
                return;
            }

            host.appendChild(panel);
            this._panelHost = null;
        },

        placePanel() {
            const trigger = this.$refs.trigger;
            const panel = this.$refs.panel;

            if (! trigger || ! panel || ! this.open) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const gap = 8;
            const panelWidth = panel.offsetWidth || 384;
            const panelHeight = panel.offsetHeight || 320;
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            this.panelTop = spaceBelow < panelHeight + gap && spaceAbove > panelHeight + gap
                ? rect.top - panelHeight - gap
                : rect.bottom + gap;

            this.panelLeft = Math.min(window.innerWidth - 8, rect.right);
            this.panelTransform = 'translateX(-100%)';

            if (this.panelLeft - panelWidth < 8) {
                this.panelLeft = Math.min(rect.left, window.innerWidth - panelWidth - 8);
                this.panelTransform = 'none';
            }
        },

        get panelStyle() {
            if (! this.open) {
                return {};
            }

            return {
                position: 'fixed',
                top: `${this.panelTop}px`,
                left: `${this.panelLeft}px`,
                transform: this.panelTransform,
                zIndex: 99999,
            };
        },

        async fetchPanel() {
            this.loading = true;

            try {
                const response = await fetch(this.routes.panel, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.items = data.notifications ?? [];
                    this.unreadCount = data.unread_count ?? 0;
                }
            } finally {
                this.loading = false;
                this.$nextTick(() => this.placePanel());
            }
        },

        async markRead(item) {
            const response = await fetch(this.routes.markRead.replace('__ID__', String(item.id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });

            if (response.ok) {
                const data = await response.json();
                this.unreadCount = data.unread_count ?? this.unreadCount;
                item.is_unread = false;
                item.status = 'read';
                this.broadcastUnread();
            }
        },

        async markAllRead() {
            const response = await fetch(this.routes.markAllRead, {
                method: 'POST',
                headers: this.jsonHeaders(),
            });

            if (response.ok) {
                this.unreadCount = 0;
                this.items = this.items.map((item) => ({ ...item, is_unread: false, status: 'read' }));
                this.broadcastUnread();
            }
        },

        async openNotification(item, event) {
            if (event?.defaultPrevented) {
                return;
            }

            const href = this.notificationHref(item);

            this.close();
            this.recordNotificationOpen(item);

            if (event?.currentTarget?.tagName === 'A') {
                return;
            }

            erpVisitUrl(href);
        },

        notificationHref(item) {
            return item.action_url || this.routes.center;
        },

        recordNotificationOpen(item) {
            fetch(this.routes.open.replace('__ID__', String(item.id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            })
                .then((response) => (response.ok ? response.json() : null))
                .then((data) => {
                    if (! data) {
                        return;
                    }

                    this.unreadCount = data.unread_count ?? this.unreadCount;
                    item.is_unread = false;
                    item.status = 'read';
                    this.broadcastUnread();
                })
                .catch(() => {});
        },

        broadcastUnread() {
            window.dispatchEvent(new CustomEvent('erp:notifications-updated', {
                detail: { unreadCount: this.unreadCount },
            }));
        },

        jsonHeaders() {
            return {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            };
        },

        formatDate(iso) {
            if (! iso) {
                return '—';
            }

            try {
                return new Date(iso).toLocaleString();
            } catch {
                return iso;
            }
        },
    }));

    Alpine.data('notificationCenterWorkspace', (bootstrap = {}) => ({
        routes: bootstrap.routes ?? {},
        can: bootstrap.can ?? {},
        types: bootstrap.types ?? [],
        preferences: bootstrap.preferences ?? {
            commercial_alerts: true,
            production_alerts: true,
            accounting_alerts: true,
            hr_alerts: true,
            system_alerts: true,
        },
        prefsSaving: false,
        selectedIds: [],
        testForm: {
            recipient_user_id: bootstrap.recipientId,
            type: 'quotation_approved',
            title: 'Test notification',
            body: 'This is an internal ERP test alert.',
        },

        async markRead(id) {
            await fetch(this.routes.mark_read.replace('__ID__', String(id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });
            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }
        },

        async dismiss(id) {
            await fetch(this.routes.dismiss.replace('__ID__', String(id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });
            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }
        },

        async archive(id) {
            await fetch(this.routes.archive.replace('__ID__', String(id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            });
            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }
        },

        async openNotification(id, event, href = null) {
            const targetUrl = href || this.routes.center;

            fetch(this.routes.open.replace('__ID__', String(id)), {
                method: 'POST',
                headers: this.jsonHeaders(),
            }).catch(() => {});

            if (event?.currentTarget?.tagName === 'A') {
                return;
            }

            erpVisitUrl(targetUrl);
        },

        toggleAll(event) {
            const checked = event.target.checked;
            this.selectedIds = checked
                ? [...document.querySelectorAll('tbody input[type=checkbox][value]')].map((el) => Number(el.value))
                : [];
        },

        async bulkRead() {
            if (this.selectedIds.length === 0) {
                return;
            }

            await fetch(this.routes.bulk_read, {
                method: 'POST',
                headers: this.jsonHeaders(),
                body: JSON.stringify({ ids: this.selectedIds }),
            });
            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }
        },

        async bulkDismiss() {
            if (this.selectedIds.length === 0) {
                return;
            }

            await fetch(this.routes.bulk_dismiss, {
                method: 'POST',
                headers: this.jsonHeaders(),
                body: JSON.stringify({ ids: this.selectedIds }),
            });
            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }
        },

        async savePreferences() {
            this.prefsSaving = true;

            try {
                await fetch(this.routes.preferences, {
                    method: 'PUT',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify(this.preferences),
                });
            } finally {
                this.prefsSaving = false;
            }
        },

        async sendTest() {
            await fetch(this.routes.store, {
                method: 'POST',
                headers: this.jsonHeaders(),
                body: JSON.stringify(this.testForm),
            });
            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }
        },

        jsonHeaders() {
            return {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            };
        },
    }));

    Alpine.data('smsCrmTopupModal', (bootstrap = {}) => ({
        isOpen: Boolean(bootstrap.open),
        phase: Boolean(bootstrap.polling) && bootstrap.reference ? 'waiting' : 'form',
        reference: bootstrap.reference || null,
        statusUrlTemplate: bootstrap.statusUrlTemplate || '',
        topupUrl: bootstrap.topupUrl || '',
        enabled: Boolean(bootstrap.enabled),
        currency: bootstrap.currency || 'KES',
        minAmount: Number(bootstrap.minAmount || 10),
        maxAmount: Number(bootstrap.maxAmount || 50000),
        phone: '',
        amount: '500',
        statusMessage: '',
        formError: '',
        pollTimer: null,
        submitting: false,

        get busy() {
            return this.submitting || this.phase === 'waiting';
        },

        get polling() {
            return this.phase === 'waiting' || this.phase === 'submitting';
        },

        get statusTitle() {
            if (this.phase === 'submitting') {
                return 'Sending M-Pesa prompt…';
            }

            if (this.phase === 'waiting') {
                return 'Waiting for M-Pesa confirmation…';
            }

            if (this.phase === 'completed') {
                return 'Payment successful';
            }

            if (this.phase === 'failed') {
                return 'Payment not completed';
            }

            return '';
        },

        get amountLabel() {
            if (! this.amount) {
                return '';
            }

            return `${this.currency} ${Number(this.amount).toLocaleString()}`;
        },

        init() {
            if (this.phase === 'waiting' && this.reference) {
                this.statusMessage = 'Approve the STK prompt on your phone.';
                this.startPolling();
            }
        },

        open() {
            if (this.phase === 'form') {
                this.formError = '';
            }

            this.isOpen = true;
        },

        close() {
            if (this.busy) {
                return;
            }

            this.isOpen = false;
        },

        resetToForm() {
            window.clearTimeout(this.pollTimer);
            this.phase = 'form';
            this.reference = null;
            this.statusMessage = '';
            this.formError = '';
            this.submitting = false;
        },

        closeAndRefresh() {
            window.clearTimeout(this.pollTimer);
            this.isOpen = false;
            if (typeof window.erpRefreshCurrentView === 'function') { window.erpRefreshCurrentView(); } else { window.location.reload(); }
        },

        statusUrl() {
            return String(this.statusUrlTemplate).replace('__REF__', encodeURIComponent(String(this.reference || '')));
        },

        async submitTopup() {
            this.formError = '';

            const phone = String(this.phone || '').trim();
            const amount = Number(this.amount);

            if (! phone) {
                this.formError = 'Enter the M-Pesa phone number.';

                return;
            }

            if (! Number.isFinite(amount) || amount < this.minAmount || amount > this.maxAmount) {
                this.formError = `Amount must be between ${this.minAmount} and ${this.maxAmount} ${this.currency}.`;

                return;
            }

            this.submitting = true;
            this.phase = 'submitting';
            this.statusMessage = 'Contacting payment provider…';

            try {
                const response = await fetch(this.topupUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        phone,
                        amount,
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok || data.ok === false) {
                    const firstError = data.errors
                        ? Object.values(data.errors).flat()[0]
                        : null;
                    this.formError = firstError || data.message || 'Could not send the M-Pesa prompt.';
                    this.phase = 'form';
                    this.submitting = false;

                    return;
                }

                this.reference = data.reference;
                this.phone = data.phone || phone;
                this.amount = String(data.amount ?? amount);
                this.statusMessage = data.message || 'Check your phone and enter your M-Pesa PIN.';
                this.phase = 'waiting';
                this.submitting = false;
                this.startPolling(data.next_poll_seconds || 3);
            } catch {
                this.formError = 'Network error while sending the M-Pesa prompt. Try again.';
                this.phase = 'form';
                this.submitting = false;
            }
        },

        startPolling(delaySeconds = 0) {
            window.clearTimeout(this.pollTimer);

            if (delaySeconds > 0) {
                this.scheduleNext(delaySeconds);

                return;
            }

            this.pollOnce();
        },

        async pollOnce() {
            if (! this.reference || this.phase !== 'waiting') {
                return;
            }

            try {
                const response = await fetch(this.statusUrl(), {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json().catch(() => ({}));

                if (data.message) {
                    this.statusMessage = data.message;
                }

                if (data.amount) {
                    this.amount = String(data.amount);
                }

                if (! response.ok && ! data.terminal) {
                    this.scheduleNext(data.next_poll_seconds || 5);

                    return;
                }

                if (data.terminal) {
                    this.finish(data.status || 'failed', data.message || this.statusMessage);

                    return;
                }

                this.scheduleNext(data.next_poll_seconds || 4);
            } catch {
                this.scheduleNext(5);
            }
        },

        scheduleNext(seconds) {
            window.clearTimeout(this.pollTimer);
            this.pollTimer = window.setTimeout(() => this.pollOnce(), Math.max(2, Number(seconds) || 4) * 1000);
        },

        finish(status, message) {
            window.clearTimeout(this.pollTimer);
            this.submitting = false;
            this.statusMessage = message || '';

            if (status === 'completed') {
                this.phase = 'completed';

                if (typeof showErpSweetAlert === 'function') {
                    showErpSweetAlert(message || 'SMS credits topped up.', 'success');
                }

                return;
            }

            this.phase = 'failed';

            if (typeof showErpSweetAlert === 'function') {
                showErpSweetAlert(message || 'Payment was not completed.', 'error');
            }
        },
    }));

    Alpine.data('communicationTemplatesWorkspace', (bootstrap = {}) => ({
        routes: bootstrap.routes ?? {},
        can: bootstrap.can ?? {},
        options: bootstrap.options ?? {},
        categoryGroups: bootstrap.categoryGroups ?? [],
        templates: bootstrap.templates ?? [],
        viewMode: bootstrap.activeFilters?.view === 'category' ? 'category' : 'list',
        filters: {
            channel: bootstrap.activeFilters?.channel ?? '',
            status: bootstrap.activeFilters?.status ?? '',
            group: bootstrap.activeFilters?.group ?? '',
        },
        selectedId: null,
        editorOpen: false,
        editorMode: 'create',
        editorSaving: false,
        editorError: null,
        form: {},
        versionsOpen: false,
        versionsLoading: false,
        versions: [],
        compareLeft: null,
        compareRight: null,
        compareResult: null,

        get hasActiveFilters() {
            return Boolean(this.filters.channel || this.filters.status || this.filters.group);
        },

        get filteredTemplates() {
            return this.templates.filter((template) => {
                if (this.filters.channel && template.channel !== this.filters.channel) {
                    return false;
                }

                if (this.filters.status && template.status !== this.filters.status) {
                    return false;
                }

                if (this.filters.group) {
                    const group = this.categoryGroups.find((item) => item.key === this.filters.group);
                    const categories = group?.categories ?? [];

                    if (categories.length > 0 && ! categories.includes(template.category)) {
                        return false;
                    }
                }

                return true;
            });
        },

        get categoryGroupCards() {
            return this.categoryGroups.map((group) => ({
                ...group,
                count: this.templates.filter((template) => (group.categories ?? []).includes(template.category)).length,
            }));
        },

        clearFilters() {
            this.filters.channel = '';
            this.filters.status = '';
            this.filters.group = '';
        },

        toggleGroup(key) {
            this.filters.group = this.filters.group === key ? '' : key;
        },

        openEditor(template = null) {
            this.editorMode = template ? 'edit' : 'create';
            this.editorError = null;
            this.selectedId = template?.id ?? null;
            this.form = template
                ? {
                    name: template.name,
                    channel: template.channel,
                    template_type: template.template_type,
                    category: template.category,
                    status: template.status,
                    subject: template.subject ?? '',
                    body: template.body,
                    description: template.description ?? '',
                    change_notes: '',
                }
                : {
                    name: '',
                    channel: 'sms',
                    template_type: 'transactional',
                    category: 'quotation_ready',
                    subject: '',
                    body: 'Dear {{customer_name}}, your quotation {{quotation_number}} is ready.',
                };
            this.editorOpen = true;
        },

        closeEditor() {
            this.editorOpen = false;
        },

        async saveTemplate() {
            this.editorSaving = true;
            this.editorError = null;

            const isCreate = this.editorMode === 'create';
            const url = isCreate
                ? this.routes.store
                : this.routes.update.replace('__ID__', String(this.selectedId));
            const method = isCreate ? 'POST' : 'PUT';

            try {
                const response = await fetch(url, {
                    method,
                    credentials: 'same-origin',
                    headers: erpJsonHeaders(),
                    body: JSON.stringify({
                        ...this.form,
                        _token: erpCsrfToken(),
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    const message = data.message
                        ?? Object.values(data.errors ?? {}).flat().join(' ')
                        ?? 'Unable to save template.';
                    this.editorError = message;

                    return;
                }

                if (isCreate) {
                    this.templates.push(data.template);
                } else {
                    const index = this.templates.findIndex((t) => t.id === data.template.id);

                    if (index >= 0) {
                        this.templates[index] = data.template;
                    }
                }

                this.selectedId = data.template.id;
                this.closeEditor();
            } finally {
                this.editorSaving = false;
            }
        },

        async openVersions(template = null) {
            if (template) {
                this.selectedId = template.id;
            }

            if (! this.selectedId || ! this.can.versionView) {
                return;
            }

            this.versionsOpen = true;
            this.versionsLoading = true;
            this.compareLeft = null;
            this.compareRight = null;
            this.compareResult = null;

            try {
                const response = await fetch(this.routes.versions.replace('__ID__', String(this.selectedId)), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.versions = data.versions ?? [];
                }
            } finally {
                this.versionsLoading = false;
            }
        },

        comparePick(version, side) {
            if (side === 'left') {
                this.compareLeft = version;
            } else {
                this.compareRight = version;
            }

            if (this.compareLeft && this.compareRight) {
                this.runCompare();
            }
        },

        async runCompare() {
            const params = new URLSearchParams({
                left_id: String(this.compareLeft.id),
                right_id: String(this.compareRight.id),
            });

            const response = await fetch(
                `${this.routes.compare.replace('__ID__', String(this.selectedId))}?${params}`,
                { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } },
            );

            if (response.ok) {
                this.compareResult = await response.json();
            }
        },

        async restoreVersion(version) {
            if (! this.can.restore || ! confirm('Restore this version? A new version will be created.')) {
                return;
            }

            const response = await fetch(this.routes.restore.replace('__ID__', String(this.selectedId)), {
                method: 'POST',
                credentials: 'same-origin',
                headers: erpJsonHeaders(),
                body: JSON.stringify({
                    _token: erpCsrfToken(),
                    version_id: version.id,
                }),
            });

            if (response.ok) {
                const data = await response.json();
                const index = this.templates.findIndex((t) => t.id === data.template.id);

                if (index >= 0) {
                    this.templates[index] = data.template;
                }

                this.openVersions({ id: data.template.id });
            }
        },

        closePanels() {
            this.closeEditor();
            this.versionsOpen = false;
        },

        formatDate(iso) {
            if (! iso) {
                return '—';
            }

            try {
                return new Date(iso).toLocaleString();
            } catch {
                return iso;
            }
        },
    }));

    Alpine.data('postingRulesWorkspace', (bootstrap = {}) => ({
        routes: bootstrap.routes ?? {},
        canAudit: bootstrap.canAudit ?? false,
        drawerOpen: false,
        drawerLoading: false,
        rule: null,

        showUrl(ruleId) {
            return (this.routes.show ?? '').replace('__ID__', String(ruleId));
        },

        async openDrawer(ruleId) {
            this.drawerOpen = true;
            this.drawerLoading = true;
            this.rule = null;

            try {
                const response = await fetch(this.showUrl(ruleId), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    const data = await response.json();
                    this.rule = data.rule ?? null;
                }
            } finally {
                this.drawerLoading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.rule = null;
        },

        formatDate(iso) {
            if (! iso) {
                return '—';
            }

            try {
                return new Date(iso).toLocaleString();
            } catch {
                return iso;
            }
        },

        validationBadgeClass(status) {
            if (status === 'valid') {
                return 'bg-emerald-100 text-emerald-800';
            }

            if (status === 'warning') {
                return 'bg-amber-100 text-amber-900';
            }

            if (status === 'broken') {
                return 'bg-red-100 text-red-800';
            }

            return 'bg-slate-100 text-slate-700';
        },
    }));

    Alpine.data('accessAuditWorkspace', (bootstrap = {}) => ({
        showRoute: bootstrap.showRoute ?? '',
        exportRoute: bootstrap.exportRoute ?? '',
        activeFilters: bootstrap.activeFilters ?? {},
        drawerOpen: false,
        loading: false,
        detail: null,

        async openDrawer(id) {
            this.drawerOpen = true;
            this.loading = true;
            this.detail = null;

            try {
                const response = await fetch(this.showRoute.replace('__ID__', String(id)), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    this.detail = await response.json();
                }
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.detail = null;
        },

        exportUrl(format) {
            const params = new URLSearchParams({ ...this.activeFilters, format });
            return `${this.exportRoute}?${params.toString()}`;
        },
    }));

    Alpine.data('backgroundJobsWorkspace', (bootstrap = {}) => ({
        showRoute: bootstrap.showRoute ?? '',
        drawerOpen: false,
        loading: false,
        detail: null,

        async openDrawer(reference) {
            this.drawerOpen = true;
            this.loading = true;
            this.detail = null;

            try {
                const response = await fetch(this.showRoute.replace('__REF__', encodeURIComponent(String(reference))), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    this.detail = await response.json();
                }
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.detail = null;
        },
    }));

    Alpine.data('auditLogsWorkspace', (bootstrap = {}) => ({
        showRoute: bootstrap.showRoute ?? '',
        exportRoute: bootstrap.exportRoute ?? '',
        activeFilters: bootstrap.activeFilters ?? {},
        drawerOpen: false,
        loading: false,
        detail: null,

        async openDrawer(id) {
            this.drawerOpen = true;
            this.loading = true;
            this.detail = null;

            try {
                const response = await fetch(this.showRoute.replace('__ID__', String(id)), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    this.detail = await response.json();
                }
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.detail = null;
        },

        exportUrl(format) {
            const params = new URLSearchParams({ ...this.activeFilters, format });
            return `${this.exportRoute}?${params.toString()}`;
        },
    }));

    Alpine.data('backupManagementWorkspace', (bootstrap = {}) => ({
        readinessRoute: bootstrap.readinessRoute ?? '',
        drawerOpen: false,
        loading: false,
        report: null,

        async openReadiness(id) {
            this.drawerOpen = true;
            this.loading = true;
            this.report = null;

            try {
                const response = await fetch(this.readinessRoute.replace('__ID__', String(id)), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (response.ok) {
                    this.report = await response.json();
                }
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.report = null;
        },
    }));

    Alpine.data('qr360PrintingIntelligence', (config = {}) => ({
        artworkOpen: false,
        activeArtwork: config.activeArtwork ?? 'primary',
        timelineOpen: true,
        piAnalysisOpen: false,
        piAnalysisLoading: false,
        piSummary: config.summary ?? null,
        piWarnings: [],
        piStatusMessage: null,
        piModalUrl: config.modalUrl ?? '',
        piRunUrl: config.runUrl ?? '',
        piRerunUrl: config.rerunUrl ?? '',
        piApplyUrl: config.applyUrl ?? '',
        piActiveTab: 'overview',
        piShowCmykBreakdown: true,
        piShowInkCmykMl: false,

        setPiTab(tab) {
            this.piActiveTab = tab;
        },

        resolvePiTabFromAction(action = '') {
            if (action.includes('/colour')) {
                return 'colour';
            }

            if (action.includes('/ink')) {
                return 'ink';
            }

            if (action.includes('/production')) {
                return 'production';
            }

            if (action.includes('/quotation')) {
                return 'quotation';
            }

            if (action.includes('/metadata')) {
                return 'overview';
            }

            return 'overview';
        },

        async openPiModal() {
            this.piAnalysisOpen = true;

            if (! this.piSummary) {
                await this.refreshPiSummary();
            }
        },

        async refreshPiSummary() {
            if (! this.piModalUrl) {
                return;
            }

            const response = await fetch(this.piModalUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            this.piSummary = data.summary ?? null;
            if (data.environment && this.piSummary) {
                this.piSummary.environment = data.environment;
            } else if (data.environment) {
                this.piSummary = { ...(this.piSummary ?? {}), environment: data.environment };
            }
        },

        async submitPiForm(event) {
            event.preventDefault();

            const form = event.target;
            this.piAnalysisOpen = true;
            this.piAnalysisLoading = true;
            this.piStatusMessage = null;

            try {
                const response = await fetch(erpFormActionUrl(form), {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (! response.ok) {
                    const message = data.message || data.error || 'Analysis request failed.';

                    throw new Error(message);
                }

                this.piSummary = data.summary ?? null;
                if (data.environment && this.piSummary) {
                    this.piSummary.environment = data.environment;
                }
                this.piWarnings = data.warnings ?? [];
                this.piStatusMessage = data.message ?? null;
                this.piActiveTab = this.resolvePiTabFromAction(form.action);

                if (data.queued && this.piModalUrl) {
                    window.setTimeout(() => this.refreshPiSummary(), 3000);
                }
            } catch (error) {
                this.piWarnings = [error?.message || 'Unable to run analysis.'];
            } finally {
                this.piAnalysisLoading = false;
            }
        },
    }));
});

Alpine.start();

const navActiveClasses = ['erp-nav-link--active', 'text-white', 'border-l-3', 'border-erp-accent', 'bg-erp-primary'];
const navInactiveClasses = ['text-white/80', 'text-slate-400', 'hover:text-white'];

function navRouteIsActive(linkRoute, currentRoute) {
    if (! linkRoute || ! currentRoute) {
        return false;
    }

    if (linkRoute.includes('*')) {
        const pattern = linkRoute.replace(/\./g, '\\.').replace(/\*/g, '.*');
        const regex = new RegExp(`^${pattern}$`);

        return regex.test(currentRoute);
    }

    if (linkRoute.endsWith('.index')) {
        const prefix = linkRoute.replace(/\.index$/, '');

        return currentRoute === linkRoute || currentRoute.startsWith(`${prefix}.`);
    }

    return currentRoute === linkRoute;
}

function navTokenIsActive(token, currentRoute) {
    if (! token || ! currentRoute) {
        return false;
    }

    if (token.includes('*')) {
        return navRouteIsActive(token, currentRoute);
    }

    if (token.includes('.')) {
        return navRouteIsActive(token, currentRoute);
    }

    return currentRoute.startsWith(`${token}.`) || currentRoute === token;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function parseQuickCreatePayload(meta) {
    if (! meta?.dataset?.quickCreate) {
        return [];
    }

    try {
        const parsed = JSON.parse(meta.dataset.quickCreate);

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function quickCreateMarkup(items, labels = {}) {
    if (! Array.isArray(items) || items.length === 0) {
        return '';
    }

    const createLabel = escapeHtml(labels.create ?? 'Create');
    const soonLabel = escapeHtml(labels.soon ?? 'Soon');

    const links = items.map((item) => {
        const label = escapeHtml(item.label);

        if (item.coming_soon) {
            return `<span class="block w-full px-4 py-2 text-start text-sm leading-5 text-slate-400 cursor-not-allowed">${label} <span class="text-xs">(${soonLabel})</span></span>`;
        }

        if (! item.href) {
            return '';
        }

        const href = escapeHtml(item.href);
        const modalAttr = item.modal ? ' data-erp-modal-open' : '';
        const turboAttr = item.modal ? '' : ' data-turbo-frame="erp-main"';

        return `<a href="${href}"${modalAttr}${turboAttr} class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">${label}</a>`;
    }).join('');

    return `
        <div
            class="relative z-20 inline-block text-left"
            x-data="erpRowActionsMenu('right')"
            @click.outside="closeFromOutside()"
            @keydown.escape.window="close()"
            @scroll.window="close()"
            @resize.window="close()"
        >
            <div x-ref="trigger" @click.stop="toggle($event)">
                <button type="button" class="erp-btn-primary py-2">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span class="hidden sm:inline">${createLabel}</span>
                </button>
            </div>
            <div
                x-ref="menu"
                :style="open ? menuStyle : null"
                :class="open ? 'erp-row-actions-menu--open' : ''"
                class="erp-row-actions-menu w-48 rounded-xl shadow-card-hover"
            >
                <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white border border-erp-border">
                    ${links}
                </div>
            </div>
        </div>
    `;
}

function syncQuickCreateFromFrame(meta) {
    const root = document.getElementById('erp-quick-create');

    if (! root) {
        return;
    }

    const items = parseQuickCreatePayload(meta);
    const markup = quickCreateMarkup(items, {
        create: meta.dataset.i18nCreate,
        soon: meta.dataset.i18nSoon,
    });

    if (window.__erpOpenRowMenu?.close) {
        window.__erpOpenRowMenu.close();
    }

    root.innerHTML = markup;

    if (markup) {
        Alpine.initTree(root);
    }
}

function shellScrollContainer() {
    const embedded = document.querySelector('.module-workspace-embedded');

    if (embedded instanceof HTMLElement) {
        const style = window.getComputedStyle(embedded);

        if (/(auto|scroll)/.test(style.overflowY)) {
            return embedded;
        }
    }

    const workspaceFrame = document.getElementById('module-workspace-content');

    if (workspaceFrame instanceof HTMLElement) {
        const style = window.getComputedStyle(workspaceFrame);

        if (/(auto|scroll)/.test(style.overflowY)) {
            return workspaceFrame;
        }
    }

    const frame = document.getElementById('erp-main');

    if (frame instanceof HTMLElement) {
        const style = window.getComputedStyle(frame);

        if (/(auto|scroll)/.test(style.overflowY)) {
            return frame;
        }
    }

    return null;
}

function shellScrollY() {
    const container = shellScrollContainer();

    return container ? container.scrollTop : window.scrollY;
}

function setShellScrollY(y) {
    const container = shellScrollContainer();

    if (container) {
        container.scrollTop = y;

        return;
    }

    window.scrollTo(0, y);
}

function applyShellLayout(compact) {
    const shell = document.getElementById('erp-app-shell');
    const frame = document.getElementById('erp-main');

    document.body.classList.add('overflow-hidden');

    if (shell) {
        shell.classList.add('h-screen', 'max-h-screen', 'overflow-hidden');
        shell.classList.remove('min-h-screen');
    }

    if (frame) {
        frame.classList.toggle('overflow-hidden', compact);
        frame.classList.add('overflow-x-hidden', 'min-w-0');
        frame.classList.toggle('overflow-y-auto', ! compact);
    }
}

function syncShellFromFrame() {
    const meta = document.getElementById('erp-route-meta');

    if (! meta) {
        return;
    }

    const compact = meta.dataset.compactPage === '1' || meta.dataset.compactWorkspace === '1';

    applyShellLayout(compact);

    const currentRoute = meta.dataset.route ?? '';
    const pageTitle = meta.dataset.title ?? '';
    const appName = meta.dataset.appName ?? 'Janaprints';

    const titleEl = document.getElementById('erp-page-title');

    if (titleEl && pageTitle) {
        titleEl.textContent = pageTitle;
    }

    if (pageTitle) {
        document.title = `${pageTitle} — ${appName}`;
    }

    document.querySelectorAll('#erp-sidebar [data-nav-route]').forEach((link) => {
        const activePatterns = (link.dataset.navActiveRoutes ?? '').split(',').filter(Boolean);
        const active = activePatterns.some((pattern) => navTokenIsActive(pattern, currentRoute))
            || navRouteIsActive(link.dataset.navRoute, currentRoute);
        const isChild = link.classList.contains('pl-9') || link.dataset.navDepth === 'child';

        link.classList.remove(...navActiveClasses, ...navInactiveClasses, 'hover:bg-white/5', 'bg-erp-accent', 'bg-erp-accent/90', 'bg-erp-primary', 'border-l-3', 'border-erp-accent');

        if (active) {
            link.classList.add('erp-nav-link--active', 'border-l-3', 'border-erp-accent', 'bg-erp-primary', 'text-white');
        } else {
            link.classList.add(...(isChild ? navInactiveClasses : ['text-white/80', 'hover:text-white']));
            link.style.backgroundColor = '';
        }
    });

    document.querySelectorAll('#erp-sidebar [data-nav-group], #erp-sidebar [data-nav-subgroup]').forEach((group) => {
        const routes = (group.dataset.navGroupRoutes ?? '').split(',').filter(Boolean);
        const open = routes.some((route) => navTokenIsActive(route, currentRoute));
        group.dataset.navGroupOpen = open ? '1' : '0';

        const alpine = group.__x?.$data;

        if (alpine && typeof alpine.open !== 'undefined') {
            alpine.open = open;
        }
    });

    syncQuickCreateFromFrame(meta);

    document.dispatchEvent(new CustomEvent('erp:page-loaded'));
}

function cleanupRowActionMenus(root = document) {
    root.querySelectorAll('[data-erp-row-actions-menu]').forEach((menu) => {
        menu.classList.remove('erp-row-actions-menu--open');
        menu.removeAttribute('hidden');
        menu.style.removeProperty('top');
        menu.style.removeProperty('left');
        menu.style.removeProperty('transform');
        menu.style.removeProperty('position');
        menu.style.removeProperty('z-index');

        const host = menu.__erpMenuHost ?? menu.closest('[data-erp-row-actions]');

        if (host && menu.parentElement === document.body) {
            host.appendChild(menu);
        }
    });

    window.__erpOpenRowMenu = null;
}

function extractFlashMessageFromHtml(html) {
    if (! html) {
        return '';
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');

    return doc.querySelector('[data-erp-flash-status]')?.textContent?.trim()
        ?? doc.querySelector('[hidden][data-erp-flash-status]')?.textContent?.trim()
        ?? doc.querySelector('[data-erp-flash-error]')?.textContent?.trim()
        ?? '';
}

function extractValidationMessageFromHtml(html) {
    if (! html) {
        return '';
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');
    const items = extractValidationErrorsFromDoc(doc);

    if (items.length > 0) {
        return items.join('\n');
    }

    return doc.querySelector('[data-erp-modal-error] p:last-child')?.textContent?.trim()
        ?? '';
}

function flushPendingDeskToast() {
    const raw = sessionStorage.getItem('erp.pendingDeskToast');

    if (! raw) {
        return;
    }

    sessionStorage.removeItem('erp.pendingDeskToast');

    try {
        const payload = JSON.parse(raw);

        if (payload?.message) {
            showErpSweetAlert(payload.message, payload.variant ?? 'success');
        }
    } catch {
        // Ignore malformed session payloads.
    }
}

function promoteFlashAlertsToToast(root = document) {
    if (! root?.querySelectorAll) {
        return;
    }

    const promote = (selector, variant, { preferListItems = false } = {}) => {
        root.querySelectorAll(selector).forEach((alert) => {
            let message = alert.dataset.erpFlashMessage?.trim() ?? '';

            if (! message && preferListItems) {
                const items = [...alert.querySelectorAll('li')]
                    .map((item) => item.textContent?.trim())
                    .filter(Boolean);
                message = items.length > 0 ? items.join('\n') : '';
            }

            if (! message) {
                message = alert.textContent?.trim() ?? '';
            }

            if (message) {
                if (variant === 'error' && root.closest?.('.designer-desk-shell, .sales-desk-shell, .store-desk-shell, .production-floor-shell')) {
                    showErpDeskErrorAlert([message]);
                } else {
                    showErpSweetAlert(message, variant);
                }
            }

            alert.remove();
        });
    };

    promote('[data-erp-flash-status], [data-erp-flash-success]', 'success');
    promote('[data-erp-flash-error], [data-erp-flash-danger]', 'error');
    promote('[data-erp-flash-warning]', 'warning');
    promote('[data-erp-flash-info]', 'info');

    root.querySelectorAll('[data-erp-validation-errors]').forEach((alert) => {
        const messages = [...alert.querySelectorAll('[data-erp-validation-message]')]
            .map((item) => item.textContent?.trim())
            .filter(Boolean);

        const items = messages.length > 0
            ? messages
            : [...alert.querySelectorAll('li')]
                .map((item) => item.textContent?.trim())
                .filter(Boolean);

        if (items.length > 0) {
            if (window.erpModalManager?.overlay?.() && ! window.erpModalManager.overlay().hidden) {
                showErpFormErrorAlert(items);
            } else if (root.closest?.('.designer-desk-shell, .sales-desk-shell, .store-desk-shell, .production-floor-shell')) {
                showErpDeskErrorAlert(items);
            } else {
                showErpSweetAlert(items.join('\n'), 'error');
            }
        }

        alert.remove();
    });
}

const FORM_SETTINGS_SCROLL_KEY = 'erp.formSettings.scrollTop';

function saveFormSettingsScrollPosition() {
    const scrollContainer = shellScrollContainer();

    if (scrollContainer) {
        sessionStorage.setItem(FORM_SETTINGS_SCROLL_KEY, String(scrollContainer.scrollTop));
    }
}

function restoreFormSettingsScrollPosition() {
    const savedScroll = sessionStorage.getItem(FORM_SETTINGS_SCROLL_KEY);

    if (savedScroll === null) {
        return false;
    }

    const scrollContainer = shellScrollContainer();

    if (scrollContainer) {
        scrollContainer.scrollTop = Number.parseInt(savedScroll, 10) || 0;
    }

    sessionStorage.removeItem(FORM_SETTINGS_SCROLL_KEY);

    return true;
}

function setFormSettingsSaveState(form, isSaving) {
    const button = form?.querySelector('[data-erp-form-settings-save]');

    if (! button) {
        return;
    }

    if (isSaving) {
        if (! button.dataset.defaultLabel) {
            button.dataset.defaultLabel = button.textContent?.trim() ?? '';
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = button.dataset.savingLabel || 'Saving…';
        button.classList.add('opacity-70', 'pointer-events-none');
    } else {
        button.disabled = false;
        button.removeAttribute('aria-busy');

        if (button.dataset.defaultLabel) {
            button.textContent = button.dataset.defaultLabel;
        }

        button.classList.remove('opacity-70', 'pointer-events-none');
    }
}

function resolveFormSettingsFrameContext(form) {
    const workspaceFrame = document.getElementById('module-workspace-content');
    const inWorkspaceFrame = Boolean(form?.closest('#module-workspace-content') && workspaceFrame);

    return {
        frameId: inWorkspaceFrame ? 'module-workspace-content' : 'erp-main',
        targetFrame: inWorkspaceFrame
            ? workspaceFrame
            : document.getElementById('erp-main'),
        embedded: inWorkspaceFrame,
    };
}

function extractFormSettingsFrameContent(doc, frameId) {
    if (! doc) {
        return null;
    }

    return doc.querySelector(`turbo-frame#${frameId}`)
        ?? doc.getElementById(frameId);
}

function extractFormSettingsFlashMessage(root) {
    if (! root) {
        return null;
    }

    const status = root.querySelector('[data-erp-flash-status]');
    const error = root.querySelector('[data-erp-flash-error]');

    if (status) {
        return { message: status.textContent.trim(), variant: 'success' };
    }

    if (error) {
        return { message: error.textContent.trim(), variant: 'error' };
    }

    return null;
}

function showFormSettingsSweetAlert(message, variant = 'success') {
    showErpSweetAlert(message, variant);
}

function bindFormSettingsForms(root = document) {
    root.querySelectorAll('form[data-erp-form-settings]').forEach((form) => {
        if (form.dataset.erpFormSettingsBound === '1') {
            return;
        }

        form.dataset.erpFormSettingsBound = '1';

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            if (typeof window.__erpSubmitFormSettings === 'function') {
                window.__erpSubmitFormSettings(form);
            }
        }, true);
    });
}

function applyFormSettingsFrameHtml(html, responseUrl = null, turboLocation = null, form = null) {
    const context = form ? resolveFormSettingsFrameContext(form) : {
        frameId: 'erp-main',
        targetFrame: document.getElementById('erp-main'),
        embedded: false,
    };
    const doc = new DOMParser().parseFromString(html, 'text/html');
    let sourceFrame = extractFormSettingsFrameContent(doc, context.frameId);
    const targetFrame = context.targetFrame;

    if (! sourceFrame) {
        const alternateFrameId = context.frameId === 'erp-main'
            ? 'module-workspace-content'
            : 'erp-main';
        sourceFrame = extractFormSettingsFrameContent(doc, alternateFrameId);
    }

    if (! sourceFrame || ! targetFrame) {
        return false;
    }

    targetFrame.innerHTML = sourceFrame.innerHTML;

    const nextUrl = turboLocation || responseUrl;

    if (nextUrl && ! context.embedded) {
        try {
            window.history.pushState({}, '', new URL(nextUrl, window.location.origin).toString());
        } catch {
            // Keep the current URL when the response location cannot be parsed.
        }
    }

    if (context.frameId === 'module-workspace-content') {
        refreshEmbeddedWorkspaceFrame(targetFrame);
    } else {
        refreshFrameAlpine(targetFrame);
        restoreFormSettingsScrollPosition();
    }

    const flash = extractFormSettingsFlashMessage(targetFrame);

    promoteFlashAlertsToToast(targetFrame);
    bindFormSettingsForms(targetFrame);

    if (flash) {
        showFormSettingsSweetAlert(flash.message, flash.variant);
    } else if (form?.dataset?.erpFormLabel) {
        showFormSettingsSweetAlert(`${form.dataset.erpFormLabel} settings saved successfully.`, 'success');
    }

    return true;
}

window.__erpSubmitFormSettings = async function submitFormSettingsRequest(form) {
    if (! form) {
        return;
    }

    const context = resolveFormSettingsFrameContext(form);
    const formData = new FormData(form);
    formData.set('_turbo_frame', '1');

    if (context.embedded) {
        formData.set('_embedded_workspace', '1');
    }

    const method = (formData.get('_method') || form.method || 'POST').toString().toUpperCase();

    saveFormSettingsScrollPosition();
    setFormSettingsSaveState(form, true);

    try {
        const response = await fetch(erpFormActionUrl(form), {
            method: method === 'GET' ? 'GET' : 'POST',
            body: method === 'GET' ? null : formData,
            headers: {
                'Turbo-Frame': context.frameId,
                'Accept': 'text/html, application/xhtml+xml',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            redirect: 'follow',
        });

        const html = await response.text();
        const turboLocation = response.headers.get('Turbo-Location');

        if (applyFormSettingsFrameHtml(html, response.url, turboLocation, form)) {
            return;
        }

        if (! response.ok) {
            showFormSettingsSweetAlert(`Unable to save form settings (${response.status}). Please try again.`, 'error');
            sessionStorage.removeItem(FORM_SETTINGS_SCROLL_KEY);

            return;
        }

        showFormSettingsSweetAlert('Unable to save form settings. Please try again.', 'error');
        sessionStorage.removeItem(FORM_SETTINGS_SCROLL_KEY);
    } catch (error) {
        console.error('submitFormSettingsRequest', error);
        showFormSettingsSweetAlert('Unable to save form settings. Please try again.', 'error');
        sessionStorage.removeItem(FORM_SETTINGS_SCROLL_KEY);
    } finally {
        setFormSettingsSaveState(form, false);
    }
};

function setSubmitFeedbackState(form, loading) {
    const button = form?.querySelector('[data-erp-submit-feedback-button]');

    if (! button) {
        return;
    }

    const label = button.querySelector('[data-erp-submit-feedback-label]');
    const loadingEl = button.querySelector('[data-erp-submit-feedback-loading]');

    button.disabled = loading;
    button.setAttribute('aria-busy', loading ? 'true' : 'false');
    label?.classList.toggle('hidden', loading);
    loadingEl?.classList.toggle('hidden', ! loading);
    loadingEl?.classList.toggle('inline-flex', loading);
}

function resetSubmitFeedbackForm(form) {
    if (! form?.hasAttribute?.('data-erp-submit-feedback')) {
        return;
    }

    delete form.dataset.erpSubmitFeedbackActive;
    setSubmitFeedbackState(form, false);
}

function initSubmitFeedbackForms(root = document) {
    root.querySelectorAll('form[data-erp-submit-feedback]').forEach((form) => {
        if (form.dataset.erpSubmitFeedbackBound === '1') {
            return;
        }

        form.dataset.erpSubmitFeedbackBound = '1';

        form.addEventListener('submit', () => {
            if (form.dataset.erpSubmitFeedbackActive === '1') {
                return;
            }

            form.dataset.erpSubmitFeedbackActive = '1';
            setSubmitFeedbackState(form, true);

            const message = form.dataset.erpSubmittingMessage?.trim();

            if (message) {
                showErpSweetAlert(message, 'info', { timer: 15000 });
            }
        });
    });
}

function refreshFrameAlpine(frame) {
    if (! frame) {
        return;
    }

    cleanupRowActionMenus(frame);
    Alpine.destroyTree(frame);
    Alpine.initTree(frame);
    wireMainFrameLinks(frame);
    promoteFlashAlertsToToast(frame);
    initSubmitFeedbackForms(frame);
    syncShellFromFrame();
    bindFormSettingsForms(frame);
    bindIndexFilterForms(frame);
    bindWebsiteSettingsForms(frame);
}

function workspaceTabSlug(value) {
    return String(value ?? '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function workspaceFeaturePathsMatch(framePath, contentPath) {
    if (! framePath || ! contentPath) {
        return false;
    }

    if (framePath === contentPath) {
        return true;
    }

    return contentPath !== '/' && framePath.startsWith(`${contentPath}/`);
}

function syncWorkspaceDeskUrlToTab(tab) {
    if (! tab || ! window.history?.replaceState) {
        return;
    }

    const deskHref = tab.getAttribute('href');

    if (! deskHref) {
        return;
    }

    try {
        const deskUrl = new URL(deskHref, window.location.origin);
        const currentUrl = new URL(window.location.href);

        if (! deskUrl.pathname.startsWith('/admin/workspaces/')) {
            return;
        }

        if (
            currentUrl.pathname === deskUrl.pathname
            && currentUrl.search === deskUrl.search
        ) {
            return;
        }

        // Keep the module desk URL in sync with the visible secondary tab.
        if (
            currentUrl.pathname.startsWith('/admin/workspaces/')
            || currentUrl.searchParams.get('embedded') === '1'
        ) {
            window.history.replaceState({}, '', `${deskUrl.pathname}${deskUrl.search}${deskUrl.hash}`);
        }
    } catch {
        // Keep the current URL when the desk href cannot be parsed.
    }
}

function syncSecondaryWorkspaceTabActiveState(clickedTab = null) {
    if (clickedTab) {
        const track = clickedTab.closest('[role="tablist"]');

        if (! track) {
            return;
        }

        track.querySelectorAll('[data-workspace-tab]').forEach((tab) => {
            tab.classList.remove('workspace-pill--active');
            tab.removeAttribute('aria-selected');
        });

        clickedTab.classList.add('workspace-pill--active');
        clickedTab.setAttribute('aria-selected', 'true');
        syncWorkspaceDeskUrlToTab(clickedTab);

        return;
    }

    const currentUrl = new URL(window.location.href);
    const currentTab = workspaceTabSlug(currentUrl.searchParams.get('tab'));
    const workspaceFrame = document.getElementById('module-workspace-content');
    const frameSrc = workspaceFrame?.src || workspaceFrame?.getAttribute('src') || '';
    let framePath = '';

    try {
        if (frameSrc) {
            framePath = new URL(frameSrc, window.location.origin).pathname;
        }
    } catch {
        framePath = '';
    }

    document.querySelectorAll('.module-workspace-switcher--secondary [role="tablist"]').forEach((track) => {
        let matchedTab = null;
        let frameMatchedTab = null;

        track.querySelectorAll('[data-workspace-tab][href]').forEach((tab) => {
            try {
                const tabKey = workspaceTabSlug(tab.dataset.workspaceTabKey ?? '');
                const contentHref = tab.dataset.workspaceContentHref || '';
                let contentPath = '';

                try {
                    if (contentHref) {
                        contentPath = new URL(contentHref, window.location.origin).pathname;
                    }
                } catch {
                    contentPath = '';
                }

                // Prefer the loaded frame content over a stale desk ?tab= value.
                if (framePath && contentPath && workspaceFeaturePathsMatch(framePath, contentPath)) {
                    frameMatchedTab = tab;
                } else if (! frameMatchedTab && currentTab && tabKey && tabKey === currentTab) {
                    matchedTab = tab;
                } else if (! frameMatchedTab && ! currentTab && ! framePath) {
                    const tabUrl = new URL(tab.getAttribute('href'), window.location.origin);

                    if (
                        tabUrl.pathname === currentUrl.pathname
                        && tabUrl.searchParams.get('tab') === currentUrl.searchParams.get('tab')
                    ) {
                        matchedTab = tab;
                    }
                }
            } catch {
                // Ignore malformed tab hrefs.
            }
        });

        const activeTab = frameMatchedTab || matchedTab;

        track.querySelectorAll('[data-workspace-tab][href]').forEach((tab) => {
            const isActive = tab === activeTab;

            tab.classList.toggle('workspace-pill--active', isActive);
            tab.toggleAttribute('aria-selected', isActive);
        });

        if (! activeTab && ! currentTab && ! framePath) {
            const first = track.querySelector('[data-workspace-tab][href]');

            if (first) {
                first.classList.add('workspace-pill--active');
                first.setAttribute('aria-selected', 'true');
            }
        } else if (activeTab) {
            syncWorkspaceDeskUrlToTab(activeTab);
        }
    });
}

/**
 * If an embedded feature URL leaked into the address bar (pagination/filters),
 * restore the active workspace desk URL so refresh keeps the module shell.
 */
function restoreWorkspaceDeskUrlInHistory() {
    if (! window.history?.replaceState) {
        return;
    }

    let currentUrl;

    try {
        currentUrl = new URL(window.location.href);
    } catch {
        return;
    }

    if (! currentUrl.pathname.startsWith('/admin/')) {
        return;
    }

    if (currentUrl.pathname.startsWith('/admin/workspaces/')) {
        return;
    }

    if (currentUrl.searchParams.get('embedded') !== '1') {
        return;
    }

    const activeTab = document.querySelector(
        '.module-workspace-switcher--secondary [data-workspace-tab][href].workspace-pill--active',
    ) || document.querySelector(
        '.module-workspace-switcher--primary [data-workspace-tab][href].workspace-pill--active',
    );

    const deskHref = activeTab?.getAttribute('href');

    if (! deskHref) {
        return;
    }

    try {
        const deskUrl = new URL(deskHref, window.location.origin);

        if (deskUrl.pathname.startsWith('/admin/workspaces/')) {
            window.history.replaceState({}, '', `${deskUrl.pathname}${deskUrl.search}${deskUrl.hash}`);
        }
    } catch {
        // Keep the current URL when the desk href cannot be parsed.
    }
}

function shouldPromoteWorkspaceLinkToMain(href) {
    if (! href) {
        return false;
    }

    try {
        const path = new URL(href, window.location.origin).pathname;

        // Keep parity with AdminLayout::routeShouldPromoteToMainShell — detail/create
        // surfaces must leave the nested workspace frame and load into erp-main.
        if (
            /\/admin\/invoices\/[0-9A-Za-z]{16}(\/document)?$/.test(path)
            || /\/admin\/quotations\/list\/[0-9A-Za-z]{16}(\/document)?$/.test(path)
            || /\/admin\/payments\/[0-9A-Za-z]{16}(\/receipt)?$/.test(path)
            || /\/admin\/sales-orders\/list\/[0-9A-Za-z]{16}$/.test(path)
            || /\/admin\/hr\/employees\/\d+$/.test(path)
            || /\/admin\/employees\/email\/compose$/.test(path)
        ) {
            return true;
        }

        // Match detail/create path segments only — do not treat list routes like
        // /receipts or /vendors as leave-workspace surfaces.
        if (/\/(create|edit|compose|document|pdf)(\/|$)/.test(path)) {
            return true;
        }

        if (/\/receipt(\/|$)/.test(path) && ! /\/receipts(\/|$)/.test(path)) {
            return true;
        }

        // Public-hash resource show URLs (and common document suffixes).
        if (/\/admin\/(?:[\w-]+\/)+[0-9A-Za-z]{16}(?:\/(?:document|receipt|pdf|edit))?$/.test(path)) {
            return true;
        }

        return false;
    } catch {
        return false;
    }
}

/**
 * Full-document Turbo visits (or hard navigation) — keep for auth/context switches,
 * downloads, and explicit opt-outs. Everything else should stay in erp-main.
 */
function shouldKeepFullDocumentNavigation(element, href = null) {
    if (! element) {
        return false;
    }

    if (
        element.getAttribute('data-turbo') === 'false'
        || element.getAttribute('target') === '_blank'
        || element.hasAttribute('data-erp-full-document')
    ) {
        return true;
    }

    const targetHref = href
        ?? element.getAttribute('href')
        ?? element.getAttribute('action')
        ?? '';

    if (! targetHref || targetHref.startsWith('#') || targetHref.startsWith('javascript:')) {
        return false;
    }

    try {
        const url = new URL(targetHref, window.location.origin);

        if (url.origin !== window.location.origin) {
            return true;
        }

        const path = url.pathname;

        if (
            path === '/logout'
            || path.endsWith('/logout')
            || path.includes('/login')
            || path.includes('/admin/context')
        ) {
            return true;
        }

        if (path.includes('/download') || /\.pdf$/i.test(path)) {
            return true;
        }
    } catch {
        return false;
    }

    return false;
}

function stripEmbeddedQueryFromHref(link) {
    try {
        const url = new URL(link.href, window.location.origin);

        if (url.searchParams.has('embedded')) {
            url.searchParams.delete('embedded');
            link.href = `${url.pathname}${url.search}${url.hash}`;
        }
    } catch {
        // Keep the original href when it cannot be parsed.
    }
}

function ensureMainFrameNavigation(link) {
    link.removeAttribute('data-turbo');
    link.setAttribute('data-turbo-frame', 'erp-main');

    if (! link.hasAttribute('data-turbo-action')) {
        link.setAttribute('data-turbo-action', 'advance');
    }

    stripEmbeddedQueryFromHref(link);
}

function wireMainFrameForms(root) {
    if (! root) {
        return;
    }

    root.querySelectorAll('form[action]').forEach((form) => {
        if (form.closest('#module-workspace-content')) {
            return;
        }

        if (shouldKeepFullDocumentNavigation(form)) {
            return;
        }

        if (form.closest('#erp-form-modal') || form.closest('[data-erp-form-modal-panel]')) {
            return;
        }

        const turboFrame = form.getAttribute('data-turbo-frame');

        if (
            ! turboFrame
            || turboFrame === '_top'
            || turboFrame === 'erp-main'
        ) {
            form.setAttribute('data-turbo-frame', 'erp-main');
        }
    });
}

function wireMainFrameLinks(root) {
    if (! root || root.id !== 'erp-main') {
        return;
    }

    root.querySelectorAll('a[href]').forEach((link) => {
        if (link.closest('#module-workspace-content')) {
            return;
        }

        if (shouldKeepFullDocumentNavigation(link, link.href)) {
            return;
        }

        if (link.closest('#erp-form-modal') || link.hasAttribute('data-erp-modal-open') || link.hasAttribute('data-no-modal')) {
            return;
        }

        if (erpModalManager.isModalFormUrl(link.href)) {
            link.setAttribute('data-erp-modal-open', '');
            link.removeAttribute('data-turbo-frame');

            return;
        }

        const turboFrame = link.getAttribute('data-turbo-frame');

        // Promote accidental full-document targets (and legacy leave-workspace) into erp-main.
        if (
            turboFrame === '_top'
            || link.hasAttribute('data-leave-workspace')
            || shouldPromoteWorkspaceLinkToMain(link.href)
        ) {
            ensureMainFrameNavigation(link);

            return;
        }

        if (! turboFrame) {
            // Default shell navigation to erp-main. Nested workspace content must
            // opt in explicitly (secondary tabs / workspace-link / WorkspaceEmbed).
            ensureMainFrameNavigation(link);

            return;
        }

        if (! link.hasAttribute('data-turbo-action') && turboFrame === 'erp-main') {
            link.setAttribute('data-turbo-action', 'advance');
        }

        if (turboFrame === 'module-workspace-content') {
            try {
                const url = new URL(link.href, window.location.origin);

                if (! url.searchParams.has('embedded')) {
                    url.searchParams.set('embedded', '1');
                    link.href = `${url.pathname}${url.search}${url.hash}`;
                }
            } catch {
                // Keep the original href when it cannot be parsed.
            }
        }
    });

    wireMainFrameForms(root);
}

function wireEmbeddedWorkspaceLinks(root) {
    if (! root) {
        return;
    }

    const insideWorkspaceContent = root.id === 'module-workspace-content';

    root.querySelectorAll('a[href]').forEach((link) => {
        if (shouldKeepFullDocumentNavigation(link, link.href)) {
            return;
        }

        if (link.closest('#erp-form-modal') || link.hasAttribute('data-erp-modal-open') || link.hasAttribute('data-no-modal')) {
            return;
        }

        if (erpModalManager.isModalFormUrl(link.href)) {
            link.setAttribute('data-erp-modal-open', '');
            link.removeAttribute('data-turbo-frame');
            stripEmbeddedQueryFromHref(link);

            return;
        }

        const turboFrame = link.getAttribute('data-turbo-frame');
        const promoteToMain = shouldPromoteWorkspaceLinkToMain(link.href)
            || turboFrame === 'erp-main'
            || turboFrame === '_top'
            || link.hasAttribute('data-leave-workspace');

        // Leave-workspace / _top used to force a hard reload; prefer erp-main so the
        // admin shell (sidebar/topbar) stays mounted.
        if (promoteToMain) {
            ensureMainFrameNavigation(link);

            return;
        }

        if (! link.hasAttribute('data-turbo-frame')) {
            link.setAttribute('data-turbo-frame', 'module-workspace-content');
        } else if (link.getAttribute('data-turbo-frame') !== 'module-workspace-content') {
            return;
        }

        if (! link.hasAttribute('data-turbo-action')) {
            link.setAttribute('data-turbo-action', 'advance');
        }

        try {
            const url = new URL(link.href, window.location.origin);

            if (! url.searchParams.has('embedded')) {
                url.searchParams.set('embedded', '1');
                link.href = `${url.pathname}${url.search}${url.hash}`;
            }
        } catch {
            // Keep the original href when it cannot be parsed.
        }
    });

    root.querySelectorAll('form[action]').forEach((form) => {
        if (shouldKeepFullDocumentNavigation(form)) {
            return;
        }

        const method = (form.getAttribute('method') ?? 'get').toLowerCase();
        let turboFrame = form.getAttribute('data-turbo-frame');

        // Promote legacy full-document form targets into the nested workspace frame
        // (or keep erp-main when the form intentionally leaves the desk).
        if (turboFrame === '_top') {
            form.removeAttribute('data-turbo');
            form.setAttribute('data-turbo-frame', insideWorkspaceContent ? 'module-workspace-content' : 'erp-main');
            turboFrame = form.getAttribute('data-turbo-frame');
        }

        if (insideWorkspaceContent && turboFrame === 'erp-main') {
            form.setAttribute('data-turbo-frame', 'module-workspace-content');
            turboFrame = 'module-workspace-content';
        }

        if (turboFrame === 'erp-main') {
            return;
        } else if (turboFrame && turboFrame !== 'module-workspace-content') {
            return;
        } else if (! turboFrame) {
            const isWorkspaceCreateEditForm = Boolean(
                form.closest('.erp-form-grid, .erp-form-shell, [data-erp-form-modal-panel]'),
            );

            if (isWorkspaceCreateEditForm && method !== 'get') {
                form.setAttribute('data-turbo', 'false');

                return;
            }

            form.setAttribute('data-turbo-frame', 'module-workspace-content');
        }

        if (method !== 'get') {
            if (! form.querySelector('input[name="embedded"]')) {
                const embeddedInput = document.createElement('input');

                embeddedInput.type = 'hidden';
                embeddedInput.name = 'embedded';
                embeddedInput.value = '1';
                form.appendChild(embeddedInput);
            }

            return;
        }

        try {
            const action = new URL(form.getAttribute('action'), window.location.origin);

            if (! action.searchParams.has('embedded')) {
                action.searchParams.set('embedded', '1');
                form.setAttribute('action', `${action.pathname}${action.search}`);
            }
        } catch {
            // Keep the original form action when it cannot be parsed.
        }
    });

    if (insideWorkspaceContent) {
        root.querySelectorAll('[data-turbo-frame="erp-main"]').forEach((element) => {
            if (element.tagName === 'A' || element.tagName === 'FORM') {
                return;
            }

            const targetUrl = element.getAttribute('href')
                ?? element.getAttribute('data-href')
                ?? element.getAttribute('action');

            if (targetUrl && shouldPromoteWorkspaceLinkToMain(targetUrl)) {
                return;
            }

            element.setAttribute('data-turbo-frame', 'module-workspace-content');
        });
    }
}

function promoteEmbeddedWorkspaceNavigation(frame, responseUrl = null) {
    const url = responseUrl || frame?.src || frame?.getAttribute('src');

    if (! url) {
        return false;
    }

    try {
        const target = new URL(url, window.location.origin);
        target.searchParams.delete('embedded');
        Turbo.visit(target.toString(), { frame: 'erp-main', action: 'advance' });

        return true;
    } catch {
        return false;
    }
}

function refreshEmbeddedWorkspaceFrame(frame) {
    if (! frame) {
        return;
    }

    cleanupRowActionMenus(frame);
    Alpine.destroyTree(frame);
    Alpine.initTree(frame);
    wireEmbeddedWorkspaceLinks(frame);
    promoteFlashAlertsToToast(frame);
    initSubmitFeedbackForms(frame);
    bindFormSettingsForms(frame);
    bindIndexFilterForms(frame);
    bindWebsiteSettingsForms(frame);
    syncSecondaryWorkspaceTabActiveState();
    restoreWorkspaceDeskUrlInHistory();
}

document.addEventListener('turbo:before-cache', () => {
    if (window.__erpOpenRowMenu) {
        window.__erpOpenRowMenu.close();
    }

    const frame = document.getElementById('erp-main');

    if (frame) {
        cleanupRowActionMenus(frame);
        Alpine.destroyTree(frame);
    }
});

document.addEventListener('change', (event) => {
    const requirementSelect = event.target.closest?.('.form-field-requirement');

    if (! requirementSelect || requirementSelect.dataset.registryRequired === '1') {
        return;
    }

    const row = requirementSelect.closest('tr');
    const visibilitySelect = row?.querySelector('.form-field-visibility');
    const visibilityHint = row?.querySelector('.form-field-visibility-hint');

    if (! visibilitySelect || visibilitySelect.dataset.registryRequired === '1') {
        return;
    }

    if (requirementSelect.value === 'required') {
        visibilitySelect.value = 'visible';
        visibilitySelect.disabled = true;
        visibilityHint?.classList.remove('hidden');
    } else {
        visibilitySelect.disabled = false;
        visibilityHint?.classList.add('hidden');
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (! form?.hasAttribute?.('data-erp-form-settings')) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    if (typeof window.__erpSubmitFormSettings === 'function') {
        window.__erpSubmitFormSettings(form);
    }
}, true);

document.addEventListener('turbo:submit-end', (event) => {
    resetSubmitFeedbackForm(event.detail?.formSubmission?.formElement);
});

document.addEventListener('turbo:frame-load', (event) => {
    if (event.target.id === 'erp-main') {
        const scrollContainer = shellScrollContainer();

        if (scrollContainer && ! restoreFormSettingsScrollPosition()) {
            scrollContainer.scrollTop = 0;
        }

        refreshFrameAlpine(event.target);
        erpModalManager.restoreWorkspaceState();
        syncSecondaryWorkspaceTabActiveState();
        bindFormSettingsForms(event.target);
        initDocumentPdfDownload(event.target);
        ensureWorkspaceContentFrameLoads(event.target);
        flushPendingDeskToast();
    }

    if (event.target.id === 'module-workspace-content') {
        refreshEmbeddedWorkspaceFrame(event.target);
        syncSecondaryWorkspaceTabActiveState();
        initDocumentPdfDownload(event.target);
    }

    if (event.target.id === 'erp-form-modal' || event.target.id === 'erp-preview-drawer') {
        erpModalManager.initFrame(event.target);
    }
});

document.addEventListener('turbo:frame-missing', async (event) => {
    if (event.target.id === 'module-workspace-content') {
        event.preventDefault();

        const response = event.detail?.response;
        const status = response?.status ?? 0;
        // before-fetch-request already incremented depth for this frame; frame-render
        // will not run after preventDefault, so always clear busy/progress here.
        endErpNavigation(event.target);

        // Never promote error responses into erp-main — that reloads the shell,
        // re-fetches the broken content URL, and loops forever on the skeleton.
        if (! response || status >= 400) {
            const message = status >= 500
                ? 'This workspace page failed to load. Please refresh and try again.'
                : 'Unable to load workspace content. Please refresh the page.';
            showFormSettingsSweetAlert(message, 'error');
            event.target.innerHTML = `<div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-6 text-sm text-rose-800">${message}</div>`;
            // Clear src so a later erp-main reload does not re-fetch the broken URL.
            event.target.removeAttribute('src');

            return;
        }

        try {
            const html = await response.clone().text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const sourceFrame = doc.querySelector('turbo-frame#module-workspace-content');

            if (sourceFrame) {
                const nestedShell = sourceFrame.querySelector('.module-shell');

                if (nestedShell) {
                    const nestedContentFrame = sourceFrame.querySelector('turbo-frame#module-workspace-content[src]');
                    const nestedSrc = nestedContentFrame?.getAttribute('src');

                    if (nestedSrc && window.Turbo) {
                        window.Turbo.visit(nestedSrc, {
                            frame: 'module-workspace-content',
                            action: 'replace',
                        });

                        return;
                    }

                    if (promoteEmbeddedWorkspaceNavigation(event.target, response.url)) {
                        return;
                    }
                } else {
                    event.target.innerHTML = sourceFrame.innerHTML;
                    refreshEmbeddedWorkspaceFrame(event.target);

                    return;
                }
            }
        } catch {
            // Fall through to a one-shot promote only for successful HTML without a frame.
        }

        if (! promoteEmbeddedWorkspaceNavigation(event.target, response.url)) {
            showFormSettingsSweetAlert('Unable to load workspace content. Please refresh the page.', 'error');
        }

        return;
    }

    // Prevent Turbo's default full-document visit when erp-main is missing from a
    // frame response — that remounts the sidebar and feels like a hard reload.
    if (event.target.id === 'erp-main') {
        event.preventDefault();
        endErpNavigation(event.target);

        const response = event.detail?.response;
        const status = response?.status ?? 0;

        if (! response || status >= 400) {
            showFormSettingsSweetAlert('Unable to load that page. Please try again.', 'error');

            return;
        }

        try {
            const html = await response.clone().text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const sourceFrame = doc.querySelector('turbo-frame#erp-main');

            if (sourceFrame) {
                event.target.innerHTML = sourceFrame.innerHTML;
                refreshFrameAlpine(event.target);
                ensureWorkspaceContentFrameLoads(event.target);
                syncSecondaryWorkspaceTabActiveState();

                if (response.url && window.history?.pushState) {
                    const next = new URL(response.url, window.location.origin);
                    window.history.pushState({}, '', `${next.pathname}${next.search}${next.hash}`);
                }

                return;
            }
        } catch {
            // Fall through to toast.
        }

        showFormSettingsSweetAlert('Unable to load that page in the workspace shell. Please refresh.', 'error');

        return;
    }

    if (event.target.id !== 'erp-form-modal' && event.target.id !== 'erp-preview-drawer') {
        return;
    }

    event.preventDefault();
    endErpNavigation(event.target);

    const response = event.detail?.response;

    if (! response || response.status >= 400) {
        return;
    }

    try {
        const html = await response.clone().text();
        const panel = erpModalManager.extractModalPanel(html);

        if (! panel) {
            return;
        }

        event.target.innerHTML = panel.outerHTML;
        erpModalManager.initFrame(event.target);
    } catch {
        // Keep the modal open; Turbo will surface the error elsewhere.
    }
});

document.addEventListener('click', (event) => {
    const saveButton = event.target.closest?.('[data-erp-form-settings-save]');

    if (saveButton) {
        const form = saveButton.closest('form[data-erp-form-settings]');

        if (form) {
            event.preventDefault();
            event.stopPropagation();

            if (! saveButton.dataset.defaultLabel) {
                saveButton.dataset.defaultLabel = saveButton.textContent.trim();
            }

            saveButton.disabled = true;
            saveButton.textContent = saveButton.dataset.savingLabel || 'Saving…';

            if (typeof window.__erpSubmitFormSettings === 'function') {
                window.__erpSubmitFormSettings(form).finally(() => {
                    saveButton.disabled = false;
                    saveButton.textContent = saveButton.dataset.defaultLabel || 'Save form settings';
                });
            } else {
                saveButton.disabled = false;
                saveButton.textContent = saveButton.dataset.defaultLabel || 'Save form settings';
                showErpSweetAlert('Unable to save form settings. Please refresh the page and try again.', 'error');
            }
        }

        return;
    }

    const tab = event.target.closest?.('.module-workspace-switcher--secondary [data-workspace-tab][href]');

    if (tab) {
        syncSecondaryWorkspaceTabActiveState(tab);
    }
}, true);

document.addEventListener('turbo:click', (event) => {
    const link = event.detail?.originalEvent?.target?.closest?.(
        '.module-workspace-switcher--secondary a[data-workspace-tab][href]',
    );

    if (! link || ! window.Turbo) {
        return;
    }

    const contentHref = link.dataset.workspaceContentHref;
    const workspaceFrame = document.getElementById('module-workspace-content');
    const deskHref = link.getAttribute('href');

    if (! contentHref || ! workspaceFrame || ! deskHref) {
        return;
    }

    let deskUrl;
    let contentUrl;
    let currentUrl;

    try {
        deskUrl = new URL(deskHref, window.location.origin);
        contentUrl = new URL(contentHref, window.location.origin);
        currentUrl = new URL(window.location.href);
    } catch {
        return;
    }

    // Already on this module section desk — swap the content frame and keep the shell.
    if (
        ! currentUrl.pathname.startsWith('/admin/workspaces/')
        || deskUrl.pathname !== currentUrl.pathname
    ) {
        return;
    }

    event.preventDefault();
    syncSecondaryWorkspaceTabActiveState(link);

    if (window.history?.pushState) {
        window.history.pushState({}, '', `${deskUrl.pathname}${deskUrl.search}${deskUrl.hash}`);
    }

    window.Turbo.visit(contentUrl.toString(), {
        frame: 'module-workspace-content',
        action: 'replace',
    });
});

/**
 * Force sidebar + primary workspace hops through #erp-main.
 * Without this, a missing-frame fallback remounts the whole document (sidebar flash).
 */
document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
        return;
    }

    const link = event.target.closest?.(
        '#erp-sidebar a[href][data-turbo-frame="erp-main"], .module-workspace-switcher--primary a[data-workspace-tab][href]',
    );

    if (! link || ! window.Turbo || ! document.getElementById('erp-main')) {
        return;
    }

    if (
        link.getAttribute('data-turbo') === 'false'
        || link.getAttribute('target') === '_blank'
        || link.hasAttribute('data-erp-modal-open')
        || link.hasAttribute('data-erp-full-document')
    ) {
        return;
    }

    let target;

    try {
        target = new URL(link.href, window.location.origin);
    } catch {
        return;
    }

    if (target.origin !== window.location.origin) {
        return;
    }

    event.preventDefault();
    document.dispatchEvent(new CustomEvent('close-nav'));

    const frame = link.getAttribute('data-turbo-frame') || 'erp-main';

    window.Turbo.visit(target.href, {
        frame: frame === 'module-workspace-content' ? 'module-workspace-content' : 'erp-main',
        action: 'advance',
    });
}, true);

function initNativeDialogs(root = document) {
    if (! root?.querySelectorAll) {
        return;
    }

    root.querySelectorAll('[data-open-dialog]').forEach((button) => {
        if (button.dataset.nativeDialogBound === '1') {
            return;
        }

        button.dataset.nativeDialogBound = '1';
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.openDialog ?? '');

            if (dialog && typeof dialog.showModal === 'function') {
                dialog.showModal();
                syncConsumptionQuantityHints(dialog);
            }
        });
    });

    root.querySelectorAll('[data-close-dialog]').forEach((button) => {
        if (button.dataset.nativeDialogBound === '1') {
            return;
        }

        button.dataset.nativeDialogBound = '1';
        button.addEventListener('click', () => {
            const dialog = document.getElementById(button.dataset.closeDialog ?? '');

            if (dialog) {
                dialog.close();
            }
        });
    });

    root.querySelectorAll('form[data-consumption-form]').forEach((form) => {
        if (form.dataset.consumptionHintsBound === '1') {
            return;
        }

        form.dataset.consumptionHintsBound = '1';
        const itemSelect = form.querySelector('[data-consumption-item]');

        itemSelect?.addEventListener('change', () => syncConsumptionQuantityHints(form));
    });

    const params = new URLSearchParams(window.location.search);
    const openId = params.get('open');

    if (! openId) {
        return;
    }

    const dialog = root.getElementById?.(openId) ?? document.getElementById(openId);

    if (dialog && typeof dialog.showModal === 'function' && dialog.dataset.autoOpened !== '1') {
        dialog.dataset.autoOpened = '1';
        dialog.showModal();
        syncConsumptionQuantityHints(dialog);
    }
}

function syncConsumptionQuantityHints(root) {
    const form = root?.matches?.('form[data-consumption-form]')
        ? root
        : root?.querySelector?.('form[data-consumption-form]');

    if (! form) {
        return;
    }

    const itemSelect = form.querySelector('[data-consumption-item]');
    const qtyInput = form.querySelector('[data-consumption-qty]');
    const warehouseSelect = form.querySelector('[data-consumption-warehouse]');

    if (! itemSelect || ! qtyInput) {
        return;
    }

    let qtyHints = {};
    let warehouseHints = {};

    try {
        qtyHints = JSON.parse(form.dataset.qtyHints || '{}');
        warehouseHints = JSON.parse(form.dataset.warehouseHints || '{}');
    } catch {
        qtyHints = {};
        warehouseHints = {};
    }

    const itemId = String(itemSelect.value || '');
    const selectedOption = itemSelect.selectedOptions?.[0];
    const suggestedQty = selectedOption?.dataset?.suggestedQty
        ?? qtyHints[itemId]
        ?? qtyHints[Number(itemId)]
        ?? null;
    const suggestedWarehouse = selectedOption?.dataset?.suggestedWarehouse
        ?? warehouseHints[itemId]
        ?? warehouseHints[Number(itemId)]
        ?? null;

    if (suggestedQty !== null && suggestedQty !== undefined && suggestedQty !== '') {
        qtyInput.value = suggestedQty;
    }

    if (warehouseSelect && suggestedWarehouse) {
        warehouseSelect.value = String(suggestedWarehouse);
    }
}

document.addEventListener('turbo:load', () => {
    const frame = document.getElementById('erp-main');

    if (frame) {
        refreshFrameAlpine(frame);
    } else {
        syncShellFromFrame();
    }

    promoteFlashAlertsToToast(document);
    flushPendingDeskToast();

    const workspaceFrame = document.getElementById('module-workspace-content');

    if (workspaceFrame) {
        wireEmbeddedWorkspaceLinks(workspaceFrame);
        bindFormSettingsForms(workspaceFrame);
        bindIndexFilterForms(workspaceFrame);
        bindWebsiteSettingsForms(workspaceFrame);
    }

    bindFormSettingsForms(document.getElementById('erp-main'));
    bindIndexFilterForms(document);
    bindWebsiteSettingsForms(document);
    syncSecondaryWorkspaceTabActiveState();
    initDocumentPdfDownload();
    initSubmitFeedbackForms();
    initSharedInboxPoll();
    initSharedInboxListPoll();
    initInboxTopbarBadgePoll();
    initNativeDialogs(document);
});

function initSharedInboxPoll() {
    const container = document.getElementById('inbox-messages');

    if (!container || container.dataset.inboxPollBound === '1') {
        return;
    }

    const feedUrl = container.dataset.inboxFeedUrl;

    if (!feedUrl) {
        return;
    }

    container.dataset.inboxPollBound = '1';

    let fingerprint = container.dataset.inboxFeedFingerprint || '';
    let pollTimer = null;

    const isNearBottom = () => {
        const threshold = 100;

        return container.scrollHeight - container.scrollTop - container.clientHeight < threshold;
    };

    const refresh = async () => {
        if (document.hidden) {
            return;
        }

        try {
            const response = await fetch(feedUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (!data?.fingerprint || data.fingerprint === fingerprint) {
                return;
            }

            const wasNearBottom = isNearBottom();
            fingerprint = data.fingerprint;
            container.dataset.inboxFeedFingerprint = fingerprint;
            container.innerHTML = data.html;

            if (wasNearBottom) {
                container.scrollTop = container.scrollHeight;
            }

            if (window.Alpine) {
                window.Alpine.initTree(container);
            }
        } catch {
            // ignore transient network errors
        }
    };

    pollTimer = window.setInterval(refresh, 4000);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refresh();
        }
    });

    container.addEventListener('turbo:before-cache', () => {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }

        container.dataset.inboxPollBound = '0';
    });
}

function initSharedInboxListPoll(root = document) {
    const panel = root.querySelector?.('[data-inbox-list-panel]') ?? document.querySelector('[data-inbox-list-panel]');

    if (!panel || panel.dataset.inboxListPollBound === '1') {
        return;
    }

    const summaryUrl = panel.dataset.inboxUnreadSummaryUrl;

    if (!summaryUrl) {
        return;
    }

    panel.dataset.inboxListPollBound = '1';

    let pollTimer = null;

    const updateRowBadge = (row, count) => {
        let badge = row.querySelector('[data-conversation-unread-badge]');
        const preview = row.querySelector('.truncate.text-\\[13px\\]');

        if (count <= 0) {
            badge?.remove();

            return;
        }

        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'shared-inbox__unread-badge';
            badge.setAttribute('data-conversation-unread-badge', '');

            const metaRow = preview?.parentElement ?? row.querySelector('.mt-0\\.5');
            metaRow?.appendChild(badge);
        }

        badge.textContent = String(count);
        badge.setAttribute('aria-label', `${count} unread`);
    };

    const refresh = async () => {
        if (document.hidden) {
            return;
        }

        try {
            const response = await fetch(summaryUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const unreadById = new Map((data.conversations ?? []).map((item) => [String(item.id), item.unread_count]));

            panel.querySelectorAll('[data-conversation-id]').forEach((row) => {
                const conversationId = row.getAttribute('data-conversation-id');
                const count = unreadById.has(conversationId) ? Number(unreadById.get(conversationId)) : 0;

                updateRowBadge(row, count);
            });

            updateInboxTopbarBadge(data.conversation_count ?? 0);
        } catch {
            // ignore transient network errors
        }
    };

    refresh();
    pollTimer = window.setInterval(refresh, 10000);

    panel.addEventListener('turbo:before-cache', () => {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }

        panel.dataset.inboxListPollBound = '0';
    });
}

function updateInboxTopbarBadge(count) {
    const badge = document.querySelector('[data-inbox-topbar-badge]');

    if (badge) {
        if (count <= 0) {
            badge.hidden = true;
            badge.textContent = '0';
        } else {
            badge.hidden = false;
            badge.textContent = count > 99 ? '99+' : String(count);
        }
    }

    const navLink = document.querySelector('[data-nav-route="admin.workspaces.communications"]');

    if (!navLink) {
        return;
    }

    let navBadge = navLink.querySelector('[data-communications-nav-badge]');

    if (count <= 0) {
        navBadge?.remove();

        return;
    }

    if (!navBadge) {
        navBadge = document.createElement('span');
        navBadge.className = 'erp-nav-badge erp-nav-badge--quote';
        navBadge.setAttribute('data-communications-nav-badge', '');
        navLink.appendChild(navBadge);
    }

    navBadge.textContent = count > 99 ? '99+' : String(count);
}

function initInboxTopbarBadgePoll() {
    if (document.body.dataset.inboxTopbarPollBound === '1') {
        return;
    }

    const topbarLink = document.querySelector('[data-inbox-topbar-link]');
    const summaryUrl = topbarLink?.dataset.inboxUnreadSummaryUrl
        ?? document.querySelector('[data-inbox-list-panel]')?.dataset.inboxUnreadSummaryUrl;

    if (!summaryUrl) {
        return;
    }

    document.body.dataset.inboxTopbarPollBound = '1';

    const refresh = async () => {
        if (document.hidden) {
            return;
        }

        try {
            const response = await fetch(summaryUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            updateInboxTopbarBadge(data.conversation_count ?? 0);
        } catch {
            // ignore transient network errors
        }
    };

    refresh();
    window.setInterval(refresh, 15000);
}
