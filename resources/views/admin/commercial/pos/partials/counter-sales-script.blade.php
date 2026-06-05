<script>
    function posCounterWorkstation(config) {
        const held = config.heldCart ?? null;
        const defaultLine = () => ({
            item_id: '',
            description: '',
            quantity: 1,
            unit_price: 0,
            discount_amount: 0,
            tax_amount: 0,
        });

        return {
            searchUrl: config.searchUrl,
            canHold: config.canHold,
            canComplete: config.canComplete,
            canCancel: config.canCancel,
            isResume: config.isResume,
            action: config.isResume ? 'pay' : 'pay',
            barcodeQuery: '',
            searchQuery: '',
            searchResults: [],
            searchLoading: false,
            lines: held?.lines?.length ? held.lines : [],
            saleDiscount: held?.saleDiscount ?? 0,
            saleTax: held?.saleTax ?? 0,
            walkIn: held?.walkIn ?? true,
            customerId: held?.customerId ?? '',
            paymentMethod: '',
            paymentReference: '',
            showSplitPayment: false,

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
                if (q.length < 2) {
                    this.searchResults = [];
                    return;
                }

                this.searchLoading = true;
                try {
                    const params = this.searchParams({ q });
                    const response = await fetch(`${this.searchUrl}?${params.toString()}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await response.json();
                    this.searchResults = data.products ?? [];
                } finally {
                    this.searchLoading = false;
                }
            },

            async fetchProducts(params) {
                const query = this.searchParams(params).toString();
                const response = await fetch(`${this.searchUrl}?${query}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json();
                const products = data.products ?? [];

                return products.length ? products[0] : null;
            },

            addProduct(product) {
                const existing = this.lines.find(line => String(line.item_id) === String(product.id));
                if (existing) {
                    existing.quantity = Number(existing.quantity || 0) + 1;
                    return;
                }

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

            increaseQty(index) {
                this.lines[index].quantity = Number(this.lines[index].quantity || 0) + 1;
            },

            decreaseQty(index) {
                const next = Number(this.lines[index].quantity || 0) - 1;
                if (next < 0.001) {
                    this.removeLine(index);
                    return;
                }
                this.lines[index].quantity = next;
            },

            removeLine(index) {
                this.lines.splice(index, 1);
            },

            clearCart() {
                if (!confirm(@json(__('Clear the current sale?'))) return;
                this.lines = [];
                this.saleDiscount = 0;
                this.saleTax = 0;
                this.paymentMethod = '';
                this.paymentReference = '';
                this.walkIn = true;
                this.customerId = '';
            },

            selectPayment(method) {
                this.paymentMethod = method;
            },

            lineTotal(line) {
                const base = Math.max(0, (Number(line.quantity) * Number(line.unit_price)) - Number(line.discount_amount || 0));
                return base + Number(line.tax_amount || 0);
            },

            get lineDiscountTotal() {
                return this.lines.reduce((sum, line) => sum + Number(line.discount_amount || 0), 0);
            },

            get lineTaxTotal() {
                return this.lines.reduce((sum, line) => sum + Number(line.tax_amount || 0), 0);
            },

            get subtotal() {
                return this.lines.reduce((sum, line) => {
                    const base = Math.max(0, (Number(line.quantity) * Number(line.unit_price)) - Number(line.discount_amount || 0));
                    return sum + base;
                }, 0);
            },

            get totalDiscount() {
                return this.lineDiscountTotal + Number(this.saleDiscount || 0);
            },

            get totalTax() {
                return this.lineTaxTotal + Number(this.saleTax || 0);
            },

            get grandTotal() {
                return Math.max(0, this.subtotal - Number(this.saleDiscount || 0) + this.totalTax);
            },

            formatMoney(value) {
                return Number(value || 0).toFixed(2);
            },

            submitSale(action) {
                if (!this.lines.length) return;

                if (action === 'pay' && !this.paymentMethod) {
                    alert(@json(__('Select a payment method to complete the sale.')));
                    return;
                }

                this.action = action;
                this.$nextTick(() => document.getElementById('pos-counter-form').submit());
            },
        };
    }
</script>
