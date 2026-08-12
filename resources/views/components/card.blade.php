<div class="card border-0 shadow-sm {{ $class ?? '' }}" @isset($style) style="{{ $style }}" @endisset>
    @if(isset($title))
    <div class="card-header {{ $headerClass ?? 'bg-primary text-white' }} py-2 {{ isset($headerExtraClass) ? $headerExtraClass : '' }}">
        <h5 class="mb-0">
            @if(isset($icon))<i class="bi {{ $icon }} me-2"></i>@endif
            {{ $title }}
        </h5>
        @if(isset($headerActions))
            {{ $headerActions }}
        @endif
    </div>
    @endif
    <div class="card-body {{ $bodyClass ?? 'py-3' }}">
        {{ $slot }}
    </div>
</div>

