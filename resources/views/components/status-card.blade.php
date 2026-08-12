<div class="card bg-{{ $color }} text-{{ $color == 'warning' || $color == 'light' ? 'dark' : 'white' }} h-100 shadow-sm card-status-clicavel" data-status="{{ $status }}" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="{{ $title ?? 'Clique para ver detalhes' }}">
    <div class="card-body text-center py-3">
        <h2 class="card-title mb-1 fw-bold" id="{{ $id }}">0</h2>
        <p class="card-text mb-0 small">{{ $label }}</p>
    </div>
</div>

