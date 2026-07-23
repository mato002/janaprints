
<div class="public-portfolio-modal gallery-preview-modal" data-portfolio-modal hidden aria-hidden="true">
    <div class="public-portfolio-modal__backdrop gallery-preview-modal__backdrop" data-portfolio-close></div>
    <div
        class="public-portfolio-modal__dialog gallery-preview-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="portfolio-modal-title"
    >
        <button type="button" class="public-portfolio-modal__close gallery-preview-close" data-portfolio-close aria-label="Close gallery preview">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="public-portfolio-modal__layout gallery-preview-dialog__body">
            <div class="public-portfolio-modal__media gallery-preview-media">
                <img class="gallery-preview-media__image" src="" alt="" data-portfolio-modal-image loading="lazy">
            </div>
            <div class="public-portfolio-modal__content gallery-preview-details">
                <div class="public-portfolio-modal__intro">
                    <span class="public-portfolio-modal__category" data-portfolio-modal-category></span>
                    <h3 id="portfolio-modal-title" class="public-portfolio-modal__title" data-portfolio-modal-title></h3>
                    <p class="public-portfolio-modal__location" data-portfolio-modal-location hidden></p>
                </div>

                <div class="public-portfolio-modal__details">
                    <div class="public-portfolio-modal__detail" data-portfolio-modal-detail="description" hidden>
                        <h4 class="public-portfolio-modal__detail-label">Description</h4>
                        <p class="public-portfolio-modal__detail-value" data-portfolio-modal-value="description"></p>
                    </div>

                    <div class="public-portfolio-modal__detail" data-portfolio-modal-detail="materials" hidden>
                        <h4 class="public-portfolio-modal__detail-label">Materials Used</h4>
                        <p class="public-portfolio-modal__detail-value" data-portfolio-modal-value="materials"></p>
                    </div>

                    <div class="public-portfolio-modal__detail" data-portfolio-modal-detail="quantity" hidden>
                        <h4 class="public-portfolio-modal__detail-label">Quantity Produced</h4>
                        <p class="public-portfolio-modal__detail-value" data-portfolio-modal-value="quantity"></p>
                    </div>

                    <div class="public-portfolio-modal__detail" data-portfolio-modal-detail="timeline" hidden>
                        <h4 class="public-portfolio-modal__detail-label">Completion Timeline</h4>
                        <p class="public-portfolio-modal__detail-value" data-portfolio-modal-value="timeline"></p>
                    </div>

                    <div class="public-portfolio-modal__detail" data-portfolio-modal-detail="outcome" hidden>
                        <h4 class="public-portfolio-modal__detail-label">Outcome</h4>
                        <p class="public-portfolio-modal__detail-value" data-portfolio-modal-value="outcome"></p>
                    </div>
                </div>

                <div class="public-portfolio-modal__actions">
                    <?php if (isset($component)) { $__componentOriginalab8f5d6997167bf1906093658ed3789d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8f5d6997167bf1906093658ed3789d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public.button','data' => ['href' => ''.e($quoteFormHref).'','variant' => 'primary','dataPortfolioCloseOnClick' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e($quoteFormHref).'','variant' => 'primary','data-portfolio-close-on-click' => true]); ?>
                        Request Similar Project
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab8f5d6997167bf1906093658ed3789d)): ?>
<?php $attributes = $__attributesOriginalab8f5d6997167bf1906093658ed3789d; ?>
<?php unset($__attributesOriginalab8f5d6997167bf1906093658ed3789d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab8f5d6997167bf1906093658ed3789d)): ?>
<?php $component = $__componentOriginalab8f5d6997167bf1906093658ed3789d; ?>
<?php unset($__componentOriginalab8f5d6997167bf1906093658ed3789d); ?>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/portfolio-modal.blade.php ENDPATH**/ ?>