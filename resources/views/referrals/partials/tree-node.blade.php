@php
    $hasChildren = !empty($node['children']);
@endphp

<details class="mb-2" {{ $loopDepth === 0 ? 'open' : '' }}>
    <summary style="list-style: none; cursor: pointer;">
        <div class="card shadow-sm border-0 mb-2">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    @if (!empty($node['image']))
                        <img src="{{ $node['image'] }}" alt="user"
                            style="width:48px;height:48px;border-radius:50%;object-fit:cover;margin-right:12px;">
                    @else
                        <div class="d-flex align-items-center justify-content-center text-white"
                            style="width:48px;height:48px;border-radius:50%;margin-right:12px;background:#007bff;font-weight:700;">
                            {{ strtoupper(substr($node['name'] ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h6 class="mb-1">{{ $node['name'] ?? 'N/A' }}</h6>
                        <div class="text-muted small">
                            Code: <strong>{{ $node['referral_code'] ?? 'N/A' }}</strong> |
                            Direct Referrals: <strong>{{ $node['total_referrals'] ?? 0 }}</strong> |
                            Earned: <strong>{{ number_format($node['total_earned'] ?? 0, 2) }} R.s</strong>
                        </div>
                    </div>
                </div>
                <span class="badge badge-light">Level {{ $loopDepth + 1 }}</span>
            </div>
        </div>
    </summary>

    @if ($hasChildren)
        <div style="margin-left: 24px; border-left: 2px dashed #dee2e6; padding-left: 16px;">
            @foreach ($node['children'] as $child)
                @include('referrals.partials.tree-node', ['node' => $child, 'loopDepth' => $loopDepth + 1])
            @endforeach
        </div>
    @endif
</details>
