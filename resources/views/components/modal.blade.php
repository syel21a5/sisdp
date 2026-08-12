<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" @isset($static) data-bs-backdrop="static" @endisset>
    <!-- Adicionado modal-dialog-centered e removido modal-lg padrão -->
    <div class="modal-dialog modal-dialog-centered {{ $size ?? '' }}">
        <div class="modal-content border-0 shadow-lg {{ isset($rounded) ? 'rounded-'.$rounded : 'rounded-4' }}" style="overflow: hidden;">
            <div class="modal-header {{ $headerClass ?? 'bg-primary text-white border-0' }} py-3 px-4">
                <h5 class="modal-title fw-bold" id="{{ $id }}Label">
                    @if(isset($icon)) <i class="bi {{ $icon }} me-2"></i> @endif
                    {{ $title }}
                </h5>
                <button type="button" class="btn-close {{ isset($closeWhite) && $closeWhite ? 'btn-close-white' : '' }}" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 {{ $bodyClass ?? '' }}">
                {{ $slot }}
            </div>
            @if(isset($footer))
            <div class="modal-footer {{ $footerClass ?? 'border-top-0 bg-light px-4 py-3' }}">
                {{ $footer }}
            </div>
            @endif
        </div>
    </div>
</div>

