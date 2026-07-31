@props([
    'categories',
    'filters' => [],
    'categoryName' => 'category_id',
    'subcategoryName' => 'subcategory_id',
    'categoryLabel' => null,
    'subcategoryLabel' => null,
    'selectClass' => 'erp-toolbar-select',
])

@php
    $categoryLabel ??= __('Category');
    $subcategoryLabel ??= __('Subcategory');
    $selectedCategory = (string) ($filters[$categoryName] ?? '');
    $selectedSubcategory = (string) ($filters[$subcategoryName] ?? '');
@endphp

<div
    x-data="erpCategorySubcategoryFilter({
        refreshUrl: @js(route('admin.lookups.subcategories')),
        categoryId: @js($selectedCategory),
        subcategoryId: @js($selectedSubcategory),
    })"
    {{ $attributes->class(['contents']) }}
>
    <select
        id="{{ $categoryName }}"
        name="{{ $categoryName }}"
        class="{{ $selectClass }}"
        aria-label="{{ $categoryLabel }}"
        x-model="categoryId"
        @change="onCategoryChange()"
    >
        <option value="">{{ __('All categories') }}</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
    <select
        id="{{ $subcategoryName }}"
        name="{{ $subcategoryName }}"
        class="{{ $selectClass }}"
        aria-label="{{ $subcategoryLabel }}"
        x-model="subcategoryId"
        :disabled="! categoryId"
    >
        <option value="">{{ __('All subcategories') }}</option>
        <template x-for="option in subcategories" :key="option.value">
            <option :value="String(option.value)" x-text="option.label"></option>
        </template>
    </select>
</div>
