@php
    // Decode HTML entities if the content is escaped
    $decodedHtml = $html;
    
    // Check if HTML is escaped and decode it (handle both single and double encoding)
    if (is_string($html)) {
        // Try decoding multiple times to handle double encoding
        for ($i = 0; $i < 5; $i++) {
            $previous = $decodedHtml;
            if (strpos($decodedHtml, '&lt;') !== false || strpos($decodedHtml, '&gt;') !== false || strpos($decodedHtml, '&quot;') !== false || strpos($decodedHtml, '&amp;') !== false) {
                $decodedHtml = html_entity_decode($decodedHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                // If no change after decoding, break
                if ($decodedHtml === $previous) {
                    break;
                }
            } else {
                break;
            }
        }
    }
@endphp

<div class="fi-modal-content-ctn">
    <div class="p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Rendered Preview') }}</h3>
            <div class="border border-gray-300 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800 min-h-[200px] max-h-[600px] overflow-auto preview-container">
                @if(!empty($decodedHtml))
                    <div class="html-preview-content" id="html-preview-content-{{ uniqid() }}">
                        {!! $decodedHtml !!}
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 italic">{{ __('No content to preview.') }}</p>
                @endif
            </div>
        </div>
        
        @if(!empty($html))
        <div class="mt-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Raw HTML') }}</h3>
            <div class="border border-gray-300 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900 max-h-[300px] overflow-auto">
                <pre class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words"><code>{{ htmlspecialchars($decodedHtml) }}</code></pre>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Styles extracted to resources/css/components.css --}}

@vite('resources/js/html-preview.js')
