<!-- Preview Button Component -->
@php
    $defaultLabel = $buttonLabel ?? 'Preview';
    $previewTitle = $previewTitle ?? 'Data Preview';

    // Ensure queryParams is an array
    $queryParamsArray = is_array($queryParams ?? []) ? $queryParams ?? [] : [];

    // Build URL with query parameters
    $previewUrl = $apiUrl . '?' . http_build_query($queryParamsArray);
@endphp

<div class="preview-button-wrapper" style="display: inline-block;">
    <a href="{{ $previewUrl }}" class="btn btn-info preview-btn"
        style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 6px; border: none; background-color: #17a2b8; color: white; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none;">
        <span class="preview-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </span>
        <span class="preview-label">{{ $defaultLabel }}</span>
    </a>
</div>

<style>
    .preview-btn {
        transition: all 0.3s ease;
    }

    .preview-btn:hover {
        background-color: #138496 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        color: white !important;
        text-decoration: none !important;
    }

    .preview-btn:active {
        transform: translateY(0);
    }
</style>
