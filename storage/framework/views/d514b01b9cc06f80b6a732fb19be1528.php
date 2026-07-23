
<div x-show="showOpenSessionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-erp-primary/50" @click="showOpenSessionModal = false"></div>
    <div class="relative w-full max-w-md rounded-xl border border-erp-border bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold"><?php echo e(__('Open POS session')); ?></h3>
        <form class="mt-4 space-y-3" @submit.prevent="submitOpenSession()">
            <div>
                <label class="text-xs text-slate-500"><?php echo e(__('Cashier')); ?></label>
                <select class="erp-input mt-1 w-full" x-model="openSessionForm.cashier_id" required>
                    <template x-for="cashier in cashiers" :key="cashier.id">
                        <option :value="cashier.id" x-text="cashier.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500"><?php echo e(__('Terminal')); ?></label>
                <input type="text" class="erp-input mt-1 w-full" x-model="openSessionForm.terminal">
            </div>
            <div>
                <label class="text-xs text-slate-500"><?php echo e(__('Opening float')); ?></label>
                <input type="number" step="0.01" min="0" class="erp-input mt-1 w-full" x-model.number="openSessionForm.opening_float" required>
            </div>
            <div>
                <label class="text-xs text-slate-500"><?php echo e(__('Opening cash')); ?></label>
                <input type="number" step="0.01" min="0" class="erp-input mt-1 w-full" x-model.number="openSessionForm.opening_cash" required>
            </div>
            <div>
                <label class="text-xs text-slate-500"><?php echo e(__('Notes')); ?></label>
                <textarea class="erp-input mt-1 w-full" rows="2" x-model="openSessionForm.opening_notes"></textarea>
            </div>
            <p class="text-sm text-red-600" x-text="openSessionError" x-show="openSessionError"></p>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="erp-btn-primary" :disabled="loading"><?php echo e(__('Open session')); ?></button>
                <button type="button" class="erp-btn-secondary" @click="showOpenSessionModal = false"><?php echo e(__('Cancel')); ?></button>
            </div>
        </form>
    </div>
</div>


<div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-erp-primary/50" @click="showPaymentModal = false"></div>
    <div class="relative w-full max-w-md rounded-xl border border-erp-border bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold"><?php echo e(__('Complete payment')); ?></h3>
        <p class="mt-1 text-sm text-slate-500"><?php echo e(__('Grand total')); ?>: <strong x-text="formatMoney(grandTotal)"></strong></p>
        <div class="mt-4 grid grid-cols-2 gap-2">
            <template x-for="method in ['cash','mpesa','card','bank']" :key="method">
                <button type="button" class="erp-btn-secondary text-sm capitalize" :class="paymentMethod === method && 'ring-2 ring-erp-primary'" @click="selectPayment(method)" x-text="method === 'mpesa' ? 'M-Pesa' : method"></button>
            </template>
        </div>
        <div class="mt-3 space-y-3">
            <div x-show="paymentMethod === 'cash'">
                <label class="text-xs text-slate-500"><?php echo e(__('Amount received')); ?></label>
                <input type="number" step="0.01" min="0" class="erp-input mt-1 w-full" x-model.number="amountReceived">
                <p class="mt-1 text-sm" x-show="changeDue > 0"><?php echo e(__('Change due')); ?>: <span class="font-medium tabular-nums" x-text="formatMoney(changeDue)"></span></p>
            </div>
            <div>
                <label class="text-xs text-slate-500"><?php echo e(__('Reference number')); ?></label>
                <input type="text" class="erp-input mt-1 w-full" x-model="paymentReference">
            </div>
            <p class="text-xs text-slate-400"><?php echo e(__('Split payment extension point — multi-tender checkout will plug in here.')); ?></p>
        </div>
        <p class="mt-3 text-sm text-red-600" x-text="paymentError" x-show="paymentError"></p>
        <div class="mt-4 flex gap-2">
            <button type="button" class="erp-btn-primary" @click="submitPayment()" :disabled="loading || !paymentMethod"><?php echo e(__('Complete sale')); ?></button>
            <button type="button" class="erp-btn-secondary" @click="showPaymentModal = false"><?php echo e(__('Cancel')); ?></button>
        </div>
    </div>
</div>


<div x-show="showReceiptModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-erp-primary/50" @click="showReceiptModal = false"></div>
    <div class="relative w-full max-w-md max-h-[90vh] overflow-y-auto rounded-xl border border-erp-border bg-white p-6 shadow-xl" id="pos-receipt-modal">
        <template x-if="receipt">
            <div>
                <div class="text-center border-b border-erp-border pb-4 mb-4">
                    <h3 class="text-lg font-semibold" x-text="receipt.branch_name"></h3>
                    <p class="text-sm text-slate-500"><?php echo e(__('POS Receipt')); ?></p>
                    <p class="font-mono text-xs mt-1" x-text="receipt.sale_number"></p>
                    <p class="text-xs text-slate-400" x-text="receipt.sale_date"></p>
                    <p class="text-xs text-slate-400"><?php echo e(__('Cashier')); ?>: <span x-text="receipt.cashier_name"></span></p>
                </div>
                <p class="text-sm mb-3"><?php echo e(__('Customer')); ?>: <span x-text="receipt.customer_label"></span></p>
                <table class="w-full text-sm mb-4">
                    <template x-for="item in receipt.items" :key="item.description">
                        <tr class="border-b border-erp-border/50">
                            <td class="py-1" x-text="item.description"></td>
                            <td class="py-1 text-center tabular-nums" x-text="item.quantity"></td>
                            <td class="py-1 text-right tabular-nums" x-text="formatMoney(item.line_total)"></td>
                        </tr>
                    </template>
                </table>
                <div class="space-y-1 text-sm border-t border-erp-border pt-3">
                    <div class="flex justify-between"><span><?php echo e(__('Total')); ?></span><span class="font-bold tabular-nums" x-text="formatMoney(receipt.total_amount)"></span></div>
                    <template x-for="payment in receipt.payments" :key="payment.method">
                        <div class="text-xs text-slate-500" x-text="payment.method + ' — ' + formatMoney(payment.amount)"></div>
                    </template>
                </div>
            </div>
        </template>
        <div class="mt-4 flex flex-wrap gap-2 print:hidden">
            <button type="button" class="erp-btn-primary" @click="printReceipt()"><?php echo e(__('Print receipt')); ?></button>
            <button type="button" class="erp-btn-secondary" @click="printReceipt()" x-show="permissions.canReprint"><?php echo e(__('Reprint receipt')); ?></button>
            <a :href="receipt?.full_receipt_url" target="_blank" class="erp-btn-secondary" x-show="receipt"><?php echo e(__('Open full receipt')); ?></a>
            <button type="button" class="erp-btn-secondary" @click="newSale()"><?php echo e(__('New sale')); ?></button>
            <button type="button" class="erp-btn-secondary" @click="showReceiptModal = false"><?php echo e(__('Close')); ?></button>
        </div>
    </div>
</div>


<div x-show="showCustomerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-erp-primary/50" @click="showCustomerModal = false"></div>
    <div class="relative w-full max-w-md rounded-xl border border-erp-border bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold"><?php echo e(__('Customer')); ?></h3>
        <label class="mt-3 flex items-center gap-2 text-sm">
            <input type="checkbox" x-model="walkIn" @change="walkIn && (customerId = '')">
            <?php echo e(__('Walk-in customer')); ?>

        </label>
        <input type="search" class="erp-input mt-3 w-full" placeholder="<?php echo e(__('Search customer…')); ?>" x-model="customerSearch" :disabled="walkIn">
        <div class="mt-2 max-h-48 overflow-y-auto divide-y divide-erp-border">
            <template x-for="customer in filteredCustomers" :key="customer.id">
                <button type="button" class="w-full px-2 py-2 text-left text-sm hover:bg-slate-50" @click="selectCustomer(customer)" x-text="customer.company_name"></button>
            </template>
        </div>
        <a :href="customerCreateUrl" target="_blank" class="mt-3 inline-block text-xs text-erp-primary underline" x-show="permissions.canAddCustomer"><?php echo e(__('Add new customer')); ?></a>
        <button type="button" class="erp-btn-secondary mt-4 w-full" @click="showCustomerModal = false"><?php echo e(__('Done')); ?></button>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/commercial/pos/partials/workstation/modals.blade.php ENDPATH**/ ?>