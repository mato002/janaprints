<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Support\Storefront\SeoMeta;
use App\Support\Storefront\StorefrontCatalog;
use App\Support\Storefront\StorefrontUrls;
use App\Support\Storefront\StructuredDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    public function home(): View
    {
        $seo = SeoMeta::forPage('home', [], [
            StructuredDataBuilder::localBusiness(),
            StructuredDataBuilder::website(),
        ]);

        return view('welcome', compact('seo'));
    }

    public function about(): View
    {
        $about = config('storefront.about');
        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'About', 'url' => route('storefront.about')],
        ];

        $seo = SeoMeta::forPage('about', [], [
            StructuredDataBuilder::localBusiness($about['intro'] ?? null),
            StructuredDataBuilder::breadcrumbs($breadcrumbs),
        ]);

        return view('storefront.about', compact('seo', 'about', 'breadcrumbs'));
    }

    public function services(): View
    {
        $capabilities = config('capabilities.capabilities');
        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Services', 'url' => route('storefront.services')],
        ];

        $seo = SeoMeta::forPage('services', [], [
            StructuredDataBuilder::localBusiness(),
            StructuredDataBuilder::breadcrumbs($breadcrumbs),
        ]);

        return view('storefront.services', compact('seo', 'capabilities', 'breadcrumbs'));
    }

    public function serviceShow(string $slug): View
    {
        $service = StorefrontCatalog::findService($slug);

        if (! $service) {
            throw new NotFoundHttpException;
        }

        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Services', 'url' => route('storefront.services')],
            ['label' => $service['title'], 'url' => route('storefront.services.show', $slug)],
        ];

        $faqs = config('conversion.faq');
        $seo = SeoMeta::forService(
            $service['seo'],
            route('storefront.services.show', $slug),
            [
                StructuredDataBuilder::service($service['title'], $service['description']),
                StructuredDataBuilder::breadcrumbs($breadcrumbs),
                StructuredDataBuilder::faqPage($faqs),
            ]
        );

        return view('storefront.service-show', compact('seo', 'service', 'breadcrumbs', 'faqs'));
    }

    public function products(): View
    {
        $products = config('products.items');
        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Products', 'url' => route('storefront.products')],
        ];

        $seo = SeoMeta::forPage('products', [], [
            StructuredDataBuilder::localBusiness(),
            StructuredDataBuilder::breadcrumbs($breadcrumbs),
        ]);

        return view('storefront.products', compact('seo', 'products', 'breadcrumbs'));
    }

    public function productShow(string $slug): View
    {
        $product = StorefrontCatalog::findProduct($slug);

        if (! $product) {
            throw new NotFoundHttpException;
        }

        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Products', 'url' => route('storefront.products')],
            ['label' => $product['name'], 'url' => route('storefront.products.show', $slug)],
        ];

        $seo = SeoMeta::forProduct(
            $product,
            route('storefront.products.show', $slug),
            [
                StructuredDataBuilder::service($product['name'], $product['summary']),
                StructuredDataBuilder::breadcrumbs($breadcrumbs),
            ]
        );

        return view('storefront.product-show', compact('seo', 'product', 'breadcrumbs'));
    }

    public function portfolio(): View
    {
        return $this->gallery();
    }

    public function gallery(): View
    {
        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Gallery', 'url' => route('storefront.gallery')],
        ];

        $seo = SeoMeta::forPage('gallery', [
            'og_image' => asset('images/storefront/gallery/print-production.jpg'),
        ], [
            StructuredDataBuilder::localBusiness(),
            StructuredDataBuilder::breadcrumbs($breadcrumbs),
        ]);

        return view('storefront.gallery', compact('seo', 'breadcrumbs'));
    }

    public function blog(): View
    {
        $blog = config('storefront.blog');
        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Guides', 'url' => route('storefront.blog')],
        ];

        $seo = SeoMeta::forPage('blog', [], [
            StructuredDataBuilder::website(),
            StructuredDataBuilder::breadcrumbs($breadcrumbs),
        ]);

        return view('storefront.blog', compact('seo', 'blog', 'breadcrumbs'));
    }

    public function contact(): RedirectResponse
    {
        return redirect()->to(StorefrontUrls::contactSection());
    }

    public function quote(): View
    {
        $services = config('conversion.services');
        $faq = config('conversion.faq');
        $breadcrumbs = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Request Quote', 'url' => route('storefront.quote')],
        ];

        $seo = SeoMeta::forPage('quote', [], [
            StructuredDataBuilder::localBusiness(),
            StructuredDataBuilder::breadcrumbs($breadcrumbs),
            StructuredDataBuilder::faqPage($faq),
        ]);

        return view('storefront.quote', compact('seo', 'services', 'faq', 'breadcrumbs'));
    }
}
