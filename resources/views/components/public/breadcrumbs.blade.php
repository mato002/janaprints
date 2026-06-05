@props(['items' => []])

@if (count($items))
    <nav class="public-breadcrumbs" aria-label="Breadcrumb">
        <div class="public-container">
            <ol class="public-breadcrumbs__list">
                @foreach ($items as $item)
                    <li class="public-breadcrumbs__item">
                        @if (! $loop->last)
                            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                        @else
                            <span aria-current="page">{{ $item['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </nav>
@endif
