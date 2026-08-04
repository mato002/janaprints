<?php $__env->startSection('content'); ?>
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#1a2744;">
        Dear <?php echo e($quoteRequest->name); ?>,
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.7;color:#4a5568;">
        Thank you for reaching out to Jana Prints. We have received your quote request and our commercial team is reviewing your requirements.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;border:1px solid #e8ecf2;border-radius:8px;overflow:hidden;">
        <tr>
            <td colspan="2" style="background:#f8fafc;padding:12px 16px;font-size:13px;font-weight:700;color:#0f1b3d;text-transform:uppercase;letter-spacing:0.5px;">Your Request Summary</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#64748b;width:35%;border-top:1px solid #e8ecf2;">Service</td>
            <td style="padding:10px 16px;font-size:14px;color:#1a2744;border-top:1px solid #e8ecf2;"><?php echo e($quoteRequest->service_needed); ?></td>
        </tr>
        <?php if($quoteRequest->quantity): ?>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-top:1px solid #e8ecf2;">Quantity</td>
            <td style="padding:10px 16px;font-size:14px;color:#1a2744;border-top:1px solid #e8ecf2;"><?php echo e($quoteRequest->quantity); ?></td>
        </tr>
        <?php endif; ?>
        <?php if($quoteRequest->deadline): ?>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-top:1px solid #e8ecf2;">Deadline</td>
            <td style="padding:10px 16px;font-size:14px;color:#1a2744;border-top:1px solid #e8ecf2;"><?php echo e($quoteRequest->deadline); ?></td>
        </tr>
        <?php endif; ?>
        <?php if($quoteRequest->company): ?>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-top:1px solid #e8ecf2;">Company</td>
            <td style="padding:10px 16px;font-size:14px;color:#1a2744;border-top:1px solid #e8ecf2;"><?php echo e($quoteRequest->company); ?></td>
        </tr>
        <?php endif; ?>
        <?php if($quoteRequest->artwork_path): ?>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-top:1px solid #e8ecf2;">Artwork</td>
            <td style="padding:10px 16px;font-size:14px;color:#1a2744;border-top:1px solid #e8ecf2;">Uploaded &mdash; our team will review your files</td>
        </tr>
        <?php endif; ?>
    </table>

    <h2 style="margin:0 0 12px;font-size:15px;color:#0f1b3d;">What happens next?</h2>
    <ol style="margin:0 0 24px;padding-left:20px;font-size:14px;line-height:1.8;color:#4a5568;">
        <li>Our team reviews your project requirements</li>
        <li>Artwork is checked if you uploaded files</li>
        <li>Pricing and production guidance are prepared</li>
        <li>A Jana Prints representative contacts you directly</li>
    </ol>

    <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:#0f1b3d;">Need to reach us sooner?</p>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#4a5568;">
        Email: <a href="<?php echo e($contact['email_href']); ?>" style="color:#e91e8c;"><?php echo e($contact['email']); ?></a><br>
        Phone: <a href="<?php echo e($contact['phone_href']); ?>" style="color:#e91e8c;"><?php echo e($contact['phone']); ?></a><br>
        WhatsApp: <a href="https://wa.me/<?php echo e($whatsapp['number']); ?>" style="color:#e91e8c;">Chat with us</a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('mail.layouts.jana', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\mail\public\quote-request-confirmation.blade.php ENDPATH**/ ?>