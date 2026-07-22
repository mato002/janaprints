<?php ($artworksUrl = url('admin/quotations/customers')); ?>

<div
    x-data="{
        artworks: [],
        customerArtworkId: <?php echo \Illuminate\Support\Js::from((string) old('customer_artwork_id', ''))->toHtml() ?>,
        loading: false,
        async loadArtworks(customerId) {
            if (!customerId) {
                this.artworks = [];
                this.customerArtworkId = '';
                return;
            }
            this.loading = true;
            try {
                const response = await fetch(`${<?php echo \Illuminate\Support\Js::from($artworksUrl)->toHtml() ?>}/${customerId}/artworks`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) {
                    this.artworks = [];
                    return;
                }
                const data = await response.json();
                this.artworks = data.artworks ?? [];
                if (this.customerArtworkId && !this.artworks.some((item) => String(item.id) === String(this.customerArtworkId))) {
                    this.customerArtworkId = '';
                }
            } finally {
                this.loading = false;
            }
        },
        bindCustomerSelect() {
            const select = document.getElementById('customer_id');
            if (!select) {
                return;
            }
            select.addEventListener('change', (event) => {
                this.customerArtworkId = '';
                this.loadArtworks(event.target.value);
            });
            if (select.value) {
                this.loadArtworks(select.value);
            }
        },
    }"
    x-init="bindCustomerSelect()"
>
    <label class="erp-label" for="customer_artwork_id"><?php echo e(__('Artwork')); ?></label>
    <select
        id="customer_artwork_id"
        name="customer_artwork_id"
        class="erp-input w-full"
        x-model="customerArtworkId"
        :disabled="loading"
    >
        <option value=""><?php echo e(__('None')); ?></option>
        <template x-for="item in artworks" :key="item.id">
            <option :value="item.id" x-text="item.label"></option>
        </template>
    </select>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\quotations\partials\artwork-picker-field.blade.php ENDPATH**/ ?>