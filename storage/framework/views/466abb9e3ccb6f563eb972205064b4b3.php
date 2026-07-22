<section class="ess-card">
    <h2 class="ess-section-title"><?php echo e(__('Update profile')); ?></h2>
    <p class="mb-4 text-sm text-erp-muted"><?php echo e(__('You can update personal contact details. Employment and payroll fields are managed by HR.')); ?></p>

    <form method="POST" action="<?php echo e(route('ess.profile.update')); ?>" enctype="multipart/form-data" class="space-y-4">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div>
            <label class="ess-label" for="photo"><?php echo e(__('Profile photo')); ?></label>
            <input type="file" id="photo" name="photo" accept="image/*" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="phone"><?php echo e(__('Phone')); ?></label>
            <input type="text" id="phone" name="phone" value="<?php echo e(old('phone', $profile['phone'])); ?>" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="email"><?php echo e(__('Personal email')); ?></label>
            <input type="email" id="email" name="email" value="<?php echo e(old('email', $profile['email'])); ?>" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="address"><?php echo e(__('Address')); ?></label>
            <textarea id="address" name="address" rows="3" class="ess-input w-full"><?php echo e(old('address', $profile['address'])); ?></textarea>
        </div>

        <div>
            <label class="ess-label" for="emergency_contact_name"><?php echo e(__('Emergency contact name')); ?></label>
            <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo e(old('emergency_contact_name', $profile['emergency_contact_name'])); ?>" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="emergency_contact_phone"><?php echo e(__('Emergency contact phone')); ?></label>
            <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo e(old('emergency_contact_phone', $profile['emergency_contact_phone'])); ?>" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="next_of_kin_name"><?php echo e(__('Next of kin name')); ?></label>
            <input type="text" id="next_of_kin_name" name="next_of_kin_name" value="<?php echo e(old('next_of_kin_name', $profile['next_of_kin_name'])); ?>" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="next_of_kin_phone"><?php echo e(__('Next of kin phone')); ?></label>
            <input type="text" id="next_of_kin_phone" name="next_of_kin_phone" value="<?php echo e(old('next_of_kin_phone', $profile['next_of_kin_phone'])); ?>" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="next_of_kin_relationship"><?php echo e(__('Next of kin relationship')); ?></label>
            <input type="text" id="next_of_kin_relationship" name="next_of_kin_relationship" value="<?php echo e(old('next_of_kin_relationship', $profile['next_of_kin_relationship'])); ?>" class="ess-input w-full">
        </div>

        <button type="submit" class="ess-btn ess-btn--primary w-full"><?php echo e(__('Save profile')); ?></button>
    </form>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\tabs\profile.blade.php ENDPATH**/ ?>