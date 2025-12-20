@extends('layouts.app')

@section('title', 'Referrals')
@section('page-title', 'Programu ya Referral')
@section('page-subtitle', 'Alika marafiki na upate bonus!')

@push('styles')
<style>
    /* Referral page responsive styles */
    .referral-code-box {
        display: inline-flex;
        align-items: center;
        gap: var(--space-4);
        padding: var(--space-4) var(--space-6);
        background: var(--bg-dark);
        border-radius: var(--radius-xl);
        border: 2px solid var(--primary);
    }
    
    .referral-code-text {
        font-family: monospace;
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary);
        letter-spacing: 0.1em;
    }
    
    .share-buttons {
        display: flex;
        justify-content: center;
        gap: var(--space-4);
        flex-wrap: wrap;
    }
    
    .how-it-works-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-6);
    }
    
    @media (max-width: 768px) {
        .referral-code-box {
            flex-direction: column;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            gap: var(--space-3);
            padding: var(--space-4);
        }
        
        .referral-code-text {
            font-size: 1.5rem;
        }
        
        .referral-code-box .btn {
            width: 100%;
        }
        
        .share-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-2);
        }
        
        .share-buttons .btn {
            font-size: 0.8rem;
            padding: var(--space-3);
        }
        
        .how-it-works-grid {
            grid-template-columns: 1fr;
            gap: var(--space-4);
        }
        
        .how-it-works-item {
            display: flex;
            align-items: center;
            text-align: left;
            gap: var(--space-4);
        }
        
        .how-it-works-item .icon-wrapper {
            margin: 0 !important;
            flex-shrink: 0;
        }
        
        /* Mobile referral cards */
        .referral-card {
            background: var(--bg-elevated);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: var(--space-3);
        }
        
        .referral-card:last-child {
            margin-bottom: 0;
        }
        
        .referral-card-header {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-3);
        }
        
        .referral-card-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 480px) {
        .referral-code-text {
            font-size: 1.25rem;
        }
        
        .share-buttons {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- Referral Stats -->
<div class="grid grid-3 mb-8">
    <div class="stat-card">
        <div class="stat-value">{{ $totalReferrals }}</div>
        <div class="stat-label">Jumla ya Referrals</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $referrals->where('is_active', true)->count() }}</div>
        <div class="stat-label">Wanaofanya Kazi</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">TZS 0</div>
        <div class="stat-label">Bonus Uliyopata</div>
    </div>
</div>

<!-- Referral Code Card -->
<div class="card mb-8" style="padding: var(--space-6); background: var(--gradient-glow); text-align: center;">
    <h3 class="mb-2">Referral Code Yako</h3>
    <p style="color: var(--text-muted); margin-bottom: var(--space-4);">Shiriki code hii na marafiki wako</p>
    
    <div class="referral-code-box">
        <span class="referral-code-text">
            {{ auth()->user()->referral_code }}
        </span>
        <button onclick="copyCode()" class="btn btn-primary">
            <i data-lucide="copy"></i>
            Copy
        </button>
    </div>
    
    <div class="share-buttons mt-6">
        <button onclick="shareWhatsApp()" class="btn btn-secondary">
            <i data-lucide="message-circle" style="color: #25D366;"></i>
            WhatsApp
        </button>
        <button onclick="shareFacebook()" class="btn btn-secondary">
            <i data-lucide="facebook" style="color: #1877F2;"></i>
            Facebook
        </button>
        <button onclick="shareTwitter()" class="btn btn-secondary">
            <i data-lucide="twitter" style="color: #1DA1F2;"></i>
            Twitter
        </button>
        <button onclick="copyLink()" class="btn btn-secondary">
            <i data-lucide="link"></i>
            Copy Link
        </button>
    </div>
</div>

<!-- How it Works -->
<div class="card mb-8" style="padding: var(--space-6);">
    <h4 class="mb-6" style="display: flex; align-items: center; gap: 0.5rem;">
        <i data-lucide="help-circle" style="color: var(--primary); width: 20px; height: 20px;"></i>
        Jinsi Inavyofanya Kazi
    </h4>
    
    <div class="how-it-works-grid">
        <div class="how-it-works-item text-center">
            <div class="icon-wrapper" style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); flex-shrink: 0;">
                <i data-lucide="share-2" style="color: var(--primary);"></i>
            </div>
            <div>
                <h5 class="mb-2">1. Shiriki Code</h5>
                <p style="font-size: 0.875rem; margin: 0;">Shiriki referral code yako na marafiki</p>
            </div>
        </div>
        <div class="how-it-works-item text-center">
            <div class="icon-wrapper" style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); flex-shrink: 0;">
                <i data-lucide="user-plus" style="color: var(--primary);"></i>
            </div>
            <div>
                <h5 class="mb-2">2. Wanajiunga</h5>
                <p style="font-size: 0.875rem; margin: 0;">Marafiki wanafungua akaunti na code yako</p>
            </div>
        </div>
        <div class="how-it-works-item text-center">
            <div class="icon-wrapper" style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); flex-shrink: 0;">
                <i data-lucide="gift" style="color: var(--primary);"></i>
            </div>
            <div>
                <h5 class="mb-2">3. Pata Bonus</h5>
                <p style="font-size: 0.875rem; margin: 0;">Unapata bonus wanapoanza kufanya tasks</p>
            </div>
        </div>
    </div>
</div>

<!-- Referrals List -->
<div class="flex justify-between items-center mb-4">
    <h3>Marafiki Waliojiunga</h3>
</div>

<!-- Desktop Table View -->
<div class="card hide-mobile">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Jina</th>
                    <th>Tarehe ya Kujiunga</th>
                    <th>Hali</th>
                    <th>Tasks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($referrals as $referral)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="{{ $referral->getAvatarUrl() }}" alt="{{ $referral->name }}" 
                                 style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                            <div style="min-width: 0;">
                                <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $referral->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $referral->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">{{ $referral->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if($referral->is_active)
                        <span class="badge badge-success">Active</span>
                        @else
                        <span class="badge badge-error">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $referral->taskCompletions()->count() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                        <i data-lucide="users" style="width: 48px; height: 48px; margin: 0 auto var(--space-4); display: block;"></i>
                        Bado hujaleta marafiki. Anza kushiriki code yako!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Card View -->
<div class="show-mobile" style="display: none;">
    @forelse($referrals as $referral)
    <div class="referral-card">
        <div class="referral-card-header">
            <img src="{{ $referral->getAvatarUrl() }}" alt="{{ $referral->name }}" 
                 style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover;">
            <div style="min-width: 0; flex: 1;">
                <div style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $referral->name }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $referral->created_at->format('d/m/Y') }}</div>
            </div>
            @if($referral->is_active)
            <span class="badge badge-success">Active</span>
            @else
            <span class="badge badge-error">Inactive</span>
            @endif
        </div>
        <div class="referral-card-stats">
            <span style="color: var(--text-muted);">Tasks Completed</span>
            <span style="font-weight: 600;">{{ $referral->taskCompletions()->count() }}</span>
        </div>
    </div>
    @empty
    <div class="card card-body text-center">
        <i data-lucide="users" style="width: 48px; height: 48px; color: var(--text-muted); margin: 0 auto var(--space-4);"></i>
        <p style="color: var(--text-muted);">Bado hujaleta marafiki. Anza kushiriki code yako!</p>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($referrals->hasPages())
<div class="flex justify-center mt-6">
    {{ $referrals->links() }}
</div>
@endif

@push('scripts')
<script>
    const referralCode = '{{ auth()->user()->referral_code }}';
    const referralLink = '{{ url('/register?ref=' . auth()->user()->referral_code) }}';
    const shareText = 'Jiunge na SKYpesa na uanze kupata pesa kwa kutazama matangazo! Tumia code yangu: ' + referralCode;
    
    function copyCode() {
        navigator.clipboard.writeText(referralCode).then(() => {
            alert('Code imekopishwa: ' + referralCode);
        });
    }
    
    function copyLink() {
        navigator.clipboard.writeText(referralLink).then(() => {
            alert('Link imekopishwa!');
        });
    }
    
    function shareWhatsApp() {
        window.open('https://wa.me/?text=' + encodeURIComponent(shareText + '\n' + referralLink), '_blank');
    }
    
    function shareFacebook() {
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(referralLink), '_blank');
    }
    
    function shareTwitter() {
        window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(shareText) + '&url=' + encodeURIComponent(referralLink), '_blank');
    }
</script>
@endpush
@endsection
