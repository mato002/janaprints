<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Lookup\LookupOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function __construct(
        protected LookupOptionService $lookups,
    ) {}

    public function companies(Request $request): JsonResponse
    {
        return $this->respond('companies', $request);
    }

    public function branches(Request $request): JsonResponse
    {
        return $this->respond('branches', $request);
    }

    public function customers(Request $request): JsonResponse
    {
        return $this->respond('customers', $request);
    }

    public function vendors(Request $request): JsonResponse
    {
        return $this->respond('vendors', $request);
    }

    public function categories(Request $request): JsonResponse
    {
        return $this->respond('categories', $request);
    }

    public function subcategories(Request $request): JsonResponse
    {
        return $this->respond('subcategories', $request);
    }

    public function brands(Request $request): JsonResponse
    {
        return $this->respond('brands', $request);
    }

    public function uoms(Request $request): JsonResponse
    {
        return $this->respond('uoms', $request);
    }

    public function items(Request $request): JsonResponse
    {
        return $this->respond('items', $request);
    }

    public function warehouses(Request $request): JsonResponse
    {
        return $this->respond('warehouses', $request);
    }

    public function segments(Request $request): JsonResponse
    {
        return $this->respond('segments', $request);
    }

    public function departments(Request $request): JsonResponse
    {
        return $this->respond('departments', $request);
    }

    public function employees(Request $request): JsonResponse
    {
        return $this->respond('employees', $request);
    }

    public function priceBooks(Request $request): JsonResponse
    {
        return $this->respond('price_books', $request);
    }

    public function leads(Request $request): JsonResponse
    {
        return $this->respond('leads', $request);
    }

    public function leadSources(Request $request): JsonResponse
    {
        return $this->respond('lead_sources', $request);
    }

    public function quotations(Request $request): JsonResponse
    {
        return $this->respond('quotations', $request);
    }

    public function formStatuses(Request $request): JsonResponse
    {
        return $this->respond('form_statuses', $request);
    }

    protected function respond(string $type, Request $request): JsonResponse
    {
        return response()->json($this->lookups->options($type, $request));
    }
}
