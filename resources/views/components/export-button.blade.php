<!-- Export Button Component -->
@php
    $defaultLabel = $buttonLabel ?? 'Export to Excel';
    $defaultFileName = $fileName ?? 'export';

    // Ensure queryParams is an array
    $queryParamsArray = is_array($queryParams ?? []) ? $queryParams ?? [] : [];

    // Encode the params
    $paramsJson = !empty($queryParamsArray) ? json_encode($queryParamsArray) : '{}';
@endphp

<div class="export-button-wrapper" style="display: inline-block;">
    <button class="btn btn-success export-btn" id="export-btn-{{ uniqid() }}" data-api-url="{{ $apiUrl }}"
        data-file-name="{{ $defaultFileName }}"
        data-query-params="{{ htmlspecialchars($paramsJson, ENT_QUOTES, 'UTF-8') }}"
        style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 6px; border: none; background-color: #4CAF50; color: white; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
        <span class="export-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
        </span>
        <span class="export-label">{{ $defaultLabel }}</span>
        <span class="export-loader" style="display: none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                style="animation: spin 1s linear infinite;">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 6v6l4 2"></path>
            </svg>
        </span>
    </button>
</div>

<style>
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .export-btn {
        transition: all 0.3s ease;
    }

    .export-btn:hover:not(:disabled) {
        background-color: #43a047 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .export-btn:disabled {
        background-color: #ccc !important;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .export-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .export-loader {
        display: inline-block;
        animation: spin 1s linear infinite;
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    }

    .toast {
        padding: 16px 20px;
        margin-bottom: 10px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .toast-success {
        background-color: #4CAF50;
    }

    .toast-error {
        background-color: #f44336;
    }

    .toast-info {
        background-color: #2196F3;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.export-btn');

        buttons.forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();

                const apiUrl = this.dataset.apiUrl;
                const fileName = this.dataset.fileName;

                // Parse query params from data attribute
                let queryParams = {};
                try {
                    const paramsAttr = this.dataset.queryParams;
                    if (paramsAttr && paramsAttr !== 'null' && paramsAttr !== 'undefined' &&
                        paramsAttr !== '') {
                        const decodedStr = paramsAttr.replace(/&quot;/g, '"');
                        queryParams = JSON.parse(decodedStr);
                        console.log('Exporting with params:', queryParams);
                    }
                } catch (e) {
                    console.error('Failed to parse query params:', e);
                    queryParams = {};
                }

                await handleExport(this, apiUrl, fileName, queryParams);
            });
        });
    });

    async function handleExport(button, apiUrl, fileName, queryParams = {}) {
        // Disable button and show loader
        button.disabled = true;
        const label = button.querySelector('.export-label');
        const loader = button.querySelector('.export-loader');
        const icon = button.querySelector('.export-icon');

        if (label) label.style.display = 'none';
        if (loader) loader.style.display = 'inline-block';
        if (icon) icon.style.display = 'none';

        try {
            // Build URL with query parameters
            const url = new URL(apiUrl, window.location.origin);

            // Add all query parameters to the URL
            Object.keys(queryParams).forEach(key => {
                const value = queryParams[key];
                if (value !== null && value !== undefined && value !== '') {
                    url.searchParams.append(key, value);
                }
            });

            console.log('Export URL:', url.toString());

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) {
                let errorMessage = `Export failed (${response.status})`;
                try {
                    const errorBody = await response.text();
                    if (errorBody) {
                        const parsed = JSON.parse(errorBody);
                        if (parsed.message) {
                            errorMessage = parsed.message;
                        }
                    }
                } catch (_e) {
                    // response may not be JSON; ignore parse errors
                }
                throw new Error(errorMessage);
            }

            // Get filename from response header
            const contentDisposition = response.headers.get('content-disposition');
            let downloadFileName = `${fileName}_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.xlsx`;

            if (contentDisposition) {
                const fileNameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (fileNameMatch && fileNameMatch[1]) {
                    downloadFileName = fileNameMatch[1].replace(/['"]/g, '');
                }
            }

            // Download the file
            const blob = await response.blob();

            if (blob.size === 0) {
                throw new Error('Generated file is empty');
            }

            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = downloadFileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(downloadUrl);

            showToast(`Successfully exported ${downloadFileName}`, 'success');

        } catch (error) {
            console.error('Export error:', error);
            showToast(`Error exporting file: ${error.message}`, 'error');
        } finally {
            // Re-enable button and hide loader
            button.disabled = false;
            if (label) label.style.display = 'inline';
            if (loader) loader.style.display = 'none';
            if (icon) icon.style.display = 'inline-block';
        }
    }

    function showToast(message, type = 'info') {
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());

        const toastContainer = document.querySelector('.toast-container') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }, 4000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }
</script>
