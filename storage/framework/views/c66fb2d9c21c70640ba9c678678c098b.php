<style>
    @media print {
        @page {
            margin: 8mm 0 0 0;
            size: A4;
        }

        html,
        body {
            background: #fff !important;
            height: auto !important;
            margin: 0 !important;
            overflow: visible !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body * {
            visibility: hidden;
        }

        #invoice-document,
        #invoice-document *,
        #quotation-document,
        #quotation-document *,
        #payment-receipt,
        #payment-receipt * {
            visibility: visible;
        }

        #invoice-document,
        #quotation-document,
        #payment-receipt {
            background: #fff !important;
            border: none !important;
            box-shadow: none !important;
            left: 0;
            margin: 0 !important;
            max-width: none !important;
            padding: 0 !important;
            position: absolute;
            top: 0;
            width: 100%;
        }

        #invoice-document .jp-doc,
        #quotation-document .jp-doc,
        #payment-receipt .jp-doc {
            margin: 0 !important;
            padding: 0 6mm 36mm !important;
        }

        #invoice-document .jp-doc__payment-footer,
        #quotation-document .jp-doc__payment-footer,
        #payment-receipt .jp-doc__payment-footer {
            border-radius: 0;
            bottom: 0;
            left: 0;
            margin-top: 0;
            padding: 3.5mm 6mm;
            position: fixed;
            right: 0;
            width: 100%;
        }

        .jp-doc-actions,
        .jp-doc-print-hide {
            display: none !important;
        }
    }
</style>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/documents/partials/print-styles.blade.php ENDPATH**/ ?>