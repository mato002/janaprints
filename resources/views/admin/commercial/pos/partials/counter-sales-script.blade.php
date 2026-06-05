<script>
    function posCounterWorkstation(config) {
        return {
            csrf: config.csrf,
            urls: config.urls,
            permissions: config.permissions,
            customers: config.customers ?? [],
            cashiers: config.cashiers ?? [],
            customerCreateUrl: config.customerCreateUrl,
            resumeFetchUrl: config.resumeFetchUrl,
            varianceTolerance: config.varianceTolerance,

            hasSession: config.session?.has_session ?? false,
            sessionData: config.session?.session ?? {},
            sessionMetrics: config.session?.metrics ?? {},

            barcodeQuery: '',
            searchQuery: '',
            searchResults: [],
            searchLoading: false,
            lines: [],
            saleDiscount: 0,
            saleTax: 0,
            walkIn: true,
            customerId: '',
            customerSearch: '',

            isResume: false,
            resumeSaleId: null,
            resumePayUrl: null,
            resumeCancelUrl: null,
            resumeLabel: '',

            showOpenSessionModal: false,
            showPaymentModal: false,
            showReceiptModal: false,
            showCustomerModal: false,
            showCloseDrawer: false,
            showHeldDrawer: false,
            loading: false,

            openSessionForm: {
                cashier_id: config.defaultCashierId,
                terminal: config.defaultTerminal ?? '',
                opening_float: 0,
                opening_cash: 0,
                opening_notes: '',
            },
            openSessionError: '',

            paymentMethod: '',
            paymentReference: '',
            amountReceived: 0,
            paymentError: '',

            receipt: null,

            closePreview: null,
            closeForm: { actual_cash: 0, closing_notes: '' },
            closeError: '',

            heldSales: [],
            heldCount: 0,
            heldLoading: false,

            async init() {
                if (this.resumeFetchUrl) {
                    await this.loadResumeFromUrl(this.resumeFetchUrl);
                }
            },

            get customerLabel() {
                if (this.walkIn) return @json(__('Walk-in customer'));
                const match = this.customers.find(c => String(c.id) === String(this.customerId));
                return match?.company_name ?? @json(__('Select customer'));
            },

            get filteredCustomers() {
                const q = this.customerSearch.trim().toLowerCase();
                if (!q) return this.customers.slice(0, 20);
                return this.customers.filter(c => c.company_name.toLowerCase().includes(q)).slice(0, 20);
            },

            get changeDue() {
                return Math.max(0, Number(this.amountReceived || 0) - this.grandTotal);
            },

            get closeVariance() {
                if (!this.closePreview) return 0;
                return Number(this.closeForm.actual_cash || 0) - Number(this.closePreview.expected_cash || 0);
            },

            headers(json = true) {
                const h = {
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                };
                if (json) h['Content-Type'] = 'application/json';
                return h;
            },

            async refreshSession() {
                const response = await fetch(this.urls.session, { headers: this.headers(false) });
                const data = await response.json();
                this.hasSession = data.has_session;
                this.sessionData = data.session ?? {};
                this.sessionMetrics = data.metrics ?? {};
                if (data.permissions?.canCloseSession !== undefined) {
                    this.permissions.canCloseSession = data.permissions.canCloseSession;
                }
            },

            async submitOpenSession() {
                this.loading = true;
                this.openSessionError = '';
                try {
                    const response = await fetch(this.urls.openSession, {
                        method: 'POST',
                        headers: this.headers(),
                        body: JSON.stringify(this.openSessionForm),
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        this.openSessionError = data.message || Object.values(data.errors ?? {}).flat().join(' ');
                        return;
                    }
                    this.hasSession = data.session.has_session;
                    this.sessionData = data.session.session ?? {};
                    this.sessionMetrics = data.session.metrics ?? {};
                    this.permissions.canCloseSession = true;
                    this.showOpenSessionModal = false;
                } finally {
                    this.loading = false;
                }
            },

            async openCloseDrawer() {
                this.showCloseDrawer = true;
                this.closePreview = null;
                this.closeError = '';
                const response = await fetch(this.urls.closePreview, { headers: this.headers(false) });
                const data = await response.json();
                if (response.ok) {
                    this.closePreview = data;
                    this.closeForm.actual_cash = data.expected_cash;
                }
            },

            async submitCloseSession() {
                this.loading = true;
                this.closeError = '';
                try {
                    const response = await fetch(this.urls.closeSession, {
                        method: 'POST',
                        headers: this.headers(),
                        body: JSON.stringify(this.closeForm),
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        this.closeError = data.message || Object.values(data.errors ?? {}).flat().join(' ');
                        return;
                    }
                    this.hasSession = false;
                    this.sessionData = {};
                    this.sessionMetrics = {};
                    this.permissions.canCloseSession = false;
                    this.showCloseDrawer = false;
                    this.clearCart(false);
                    alert(data.message);
                } finally {
                    this.loading = false;
                }
            },

            async openHeldDrawer() {
                this.showHeldDrawer = true;
                this.heldLoading = true;
                try {
                    const response = await fetch(this.urls.heldSales, { headers: this.headers(false) });
                    const data = await response.json();
                    this.heldSales = data.holds ?? [];
                    this.heldCount = this.heldSales.length;
                } finally {
                    this.heldLoading = false;
                }
            },

            async resumeHeld(hold) {
                const response = await fetch(hold.resume_url, { headers: this.headers(false) });
                const data = await response.json();
                if (!response.ok) return;
                this.applyHeldCart(data.cart);
                this.showHeldDrawer = false;
            },

            async loadResumeFromUrl(url) {
                const response = await fetch(url, { headers: this.headers(false) });
                const data = await response.json();
                if (response.ok) this.applyHeldCart(data.cart);
            },

            applyHeldCart(cart) {
                this.lines = cart.lines ?? [];
                this.saleDiscount = cart.saleDiscount ?? 0;
                this.saleTax = cart.saleTax ?? 0;
                this.walkIn = cart.walkIn ?? true;
                this.customerId = cart.customerId ?? '';
                this.isResume = true;
                this.resumeSaleId = cart.sale_id;
                this.resumePayUrl = cart.pay_url;
                this.resumeCancelUrl = cart.cancel_url;
                this.resumeLabel = @json(__('Resuming')) + ' ' + cart.sale_number;
            },

            async cancelHeld(hold) {
                if (!confirm(@json(__('Cancel this held sale?')))) return;
                await fetch(hold.cancel_url, { method: 'POST', headers: this.headers(false), body: new URLSearchParams({ _token: this.csrf }) });
                await this.openHeldDrawer();
            },

            selectCustomer(customer) {
                this.walkIn = false;
                this.customerId = customer.id;
                this.showCustomerModal = false;
            },

            async scanBarcode() {
                const code = this.barcodeQuery.trim();
                if (!code) return;
                const product = await this.fetchProducts({ barcode: code });
                if (product) {
                    this.addProduct(product);
                    this.barcodeQuery = '';
                    this.$refs.barcodeInput?.focus();
                    return;
                }
                this.searchQuery = code;
                await this.searchProducts();
            },

            searchParams(extra = {}) {
                const params = new URLSearchParams(extra);
                if (!this.walkIn && this.customerId) {
                    params.set('customer_id', String(this.customerId));
                }

                return params;
            },

            async searchProducts() {
                const q = this.searchQuery.trim();
                if (q.length < 2) { this.searchResults = []; return; }
                this.searchLoading = true;
                try {
                    const response = await fetch(`${this.urls.search}?q=${encodeURIComponent(q)}`, { headers: this.headers(false) });
                    const data = await response.json();
                    this.searchResults = data.products ?? [];
                } finally { this.searchLoading = false; }
            },

            async fetchProducts(params) {
                const query = new URLSearchParams(params).toString();
                const response = await fetch(`${this.urls.search}?${query}`, { headers: this.headers(false) });
                const data = await response.json();
                return (data.products ?? []).length ? data.products[0] : null;
            },

            addProduct(product) {
                const existing = this.lines.find(line => String(line.item_id) === String(product.id));
                if (existing) { existing.quantity = Number(existing.quantity || 0) + 1; return; }
                this.lines.push({
                    item_id: product.id,
                    description: product.name,
                    quantity: 1,
                    unit_price: Number(product.unit_price || 0),
                    discount_amount: 0,
                    tax_amount: 0,
                });
                this.searchResults = [];
                this.searchQuery = '';
            },

            increaseQty(index) { this.lines[index].quantity = Number(this.lines[index].quantity || 0) + 1; },
            decreaseQty(index) {
                const next = Number(this.lines[index].quantity || 0) - 1;
                if (next < 0.001) { this.removeLine(index); return; }
                this.lines[index].quantity = next;
            },
            removeLine(index) { this.lines.splice(index, 1); },

            clearCart(confirm = true) {
                if (confirm && !window.confirm(@json(__('Clear the current sale?')))) return;
                this.lines = [];
                this.saleDiscount = 0;
                this.saleTax = 0;
                this.paymentMethod = '';
                this.paymentReference = '';
                this.amountReceived = 0;
                this.walkIn = true;
                this.customerId = '';
                this.isResume = false;
                this.resumeSaleId = null;
                this.resumePayUrl = null;
                this.resumeCancelUrl = null;
                this.resumeLabel = '';
            },

            selectPayment(method) {
                this.paymentMethod = method;
                if (method === 'cash') this.amountReceived = this.grandTotal;
            },

            openPaymentModal() {
                this.paymentError = '';
                this.paymentMethod = '';
                this.amountReceived = 0;
                this.showPaymentModal = true;
            },

            salePayload(action) {
                return {
                    action,
                    is_walk_in: this.walkIn,
                    customer_id: this.walkIn ? null : (this.customerId || null),
                    discount_amount: this.saleDiscount,
                    tax_amount: this.saleTax,
                    payment_method: this.paymentMethod || null,
                    payment_reference: this.paymentReference || null,
                    lines: this.lines.map(line => ({
                        inventory_item_id: line.item_id || null,
                        description: line.description,
                        quantity: line.quantity,
                        unit_price: line.unit_price,
                        discount_amount: line.discount_amount || 0,
                        tax_amount: line.tax_amount || 0,
                    })),
                };
            },

            async submitHold() {
                if (!this.lines.length || this.isResume) return;
                this.loading = true;
                try {
                    const response = await fetch(this.urls.store, {
                        method: 'POST',
                        headers: this.headers(),
                        body: JSON.stringify(this.salePayload('hold')),
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        alert(data.message || Object.values(data.errors ?? {}).flat().join(' '));
                        return;
                    }
                    this.clearCart(false);
                    await this.refreshSession();
                    this.heldCount += 1;
                    alert(data.message);
                } finally { this.loading = false; }
            },

            async submitPayment() {
                if (!this.paymentMethod) { this.paymentError = @json(__('Select a payment method.')); return; }
                this.loading = true;
                this.paymentError = '';
                try {
                    const url = this.isResume ? this.resumePayUrl : this.urls.store;
                    const payload = this.isResume
                        ? { ...this.salePayload('pay'), action: undefined }
                        : this.salePayload('pay');
                    if (this.isResume) delete payload.action;

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: this.headers(),
                        body: JSON.stringify(this.isResume ? {
                            is_walk_in: payload.is_walk_in,
                            customer_id: payload.customer_id,
                            discount_amount: payload.discount_amount,
                            tax_amount: payload.tax_amount,
                            payment_method: this.paymentMethod,
                            payment_reference: this.paymentReference,
                            lines: payload.lines,
                        } : payload),
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        this.paymentError = data.message || Object.values(data.errors ?? {}).flat().join(' ');
                        return;
                    }
                    this.receipt = data.receipt;
                    this.showPaymentModal = false;
                    this.showReceiptModal = true;
                    await this.refreshSession();
                } finally { this.loading = false; }
            },

            async cancelSale() {
                if (this.isResume && this.resumeCancelUrl) {
                    if (!confirm(@json(__('Cancel this held sale?')))) return;
                    await fetch(this.resumeCancelUrl, { method: 'POST', headers: this.headers(false), body: new URLSearchParams({ _token: this.csrf }) });
                    this.clearCart(false);
                    return;
                }
                this.clearCart(true);
            },

            printReceipt() { window.print(); },

            newSale() {
                this.showReceiptModal = false;
                this.receipt = null;
                this.clearCart(false);
            },

            lineTotal(line) {
                const base = Math.max(0, (Number(line.quantity) * Number(line.unit_price)) - Number(line.discount_amount || 0));
                return base + Number(line.tax_amount || 0);
            },
            get lineDiscountTotal() { return this.lines.reduce((s, l) => s + Number(l.discount_amount || 0), 0); },
            get lineTaxTotal() { return this.lines.reduce((s, l) => s + Number(l.tax_amount || 0), 0); },
            get subtotal() {
                return this.lines.reduce((s, l) => s + Math.max(0, (Number(l.quantity) * Number(l.unit_price)) - Number(l.discount_amount || 0)), 0);
            },
            get totalDiscount() { return this.lineDiscountTotal + Number(this.saleDiscount || 0); },
            get totalTax() { return this.lineTaxTotal + Number(this.saleTax || 0); },
            get grandTotal() { return Math.max(0, this.subtotal - Number(this.saleDiscount || 0) + this.totalTax); },
            formatMoney(value) { return Number(value || 0).toFixed(2); },
        };
    }
</script>
