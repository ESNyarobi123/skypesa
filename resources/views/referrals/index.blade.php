@extends('layouts.app')

@section('title', __('messages.referrals.title'))
@section('page-title', __('messages.referrals.title'))
@section('page-subtitle', __('messages.referrals.subtitle')))

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
        <div class="stat-label">{{ __('messages.referrals.total_referrals') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $referrals->where('is_active', true)->count() }}</div>
        <div class="stat-label">{{ __('messages.referrals.active_referrals') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--primary);">TZS {{ number_format($referralEarnings, 0) }}</div>
        <div class="stat-label">{{ __('messages.referrals.bonus_earned') }}</div>
    </div>
</div>

<!-- Bonus Info Card -->
<div class="card mb-8" style="padding: var(--space-5); background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05)); border: 1px solid rgba(16, 185, 129, 0.3);">
    <div style="display: flex; align-items: center; gap: var(--space-4); flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <h4 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">💰</span>
                Bonus za Referral
            </h4>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                Alika marafiki na mpate bonus pamoja!
            </p>
        </div>
        <div style="display: flex; gap: var(--space-4); flex-wrap: wrap;">
            <div style="background: var(--bg-dark); padding: var(--space-3) var(--space-4); border-radius: var(--radius-lg); text-align: center; min-width: 120px;">
                <div style="font-size: 0.75rem; color: var(--text-muted);">Wewe Utapata</div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary);">TZS {{ number_format($referralBonusReferrer, 0) }}</div>
            </div>
            <div style="background: var(--bg-dark); padding: var(--space-3) var(--space-4); border-radius: var(--radius-lg); text-align: center; min-width: 120px;">
                <div style="font-size: 0.75rem; color: var(--text-muted);">Rafiki Atapata</div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--success);">TZS {{ number_format($referralBonusNewUser, 0) }}</div>
            </div>
        </div>
    </div>
    @if($referralRequireTask)
    <div style="margin-top: var(--space-3); padding: var(--space-2) var(--space-3); background: rgba(245, 158, 11, 0.1); border-radius: var(--radius-md); font-size: 0.75rem; color: var(--warning);">
        <i data-lucide="info" style="width: 14px; height: 14px; display: inline; vertical-align: middle;"></i>
        Bonus italipwa rafiki akimaliza task yake ya kwanza
    </div>
    @endif
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
                <h5 class="mb-2">3. Pata TZS {{ number_format($referralBonusReferrer, 0) }}!</h5>
                <p style="font-size: 0.875rem; margin: 0;">Unapata bonus rafiki akimaliza task yake ya kwanza</p>
            </div>
        </div>
    </div>
</div>

<!-- Referral Chain Visualization -->
<div class="card mb-8" style="padding: var(--space-6); overflow: hidden;">
    <div class="flex justify-between items-center mb-6">
        <h4 style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 1.25rem;">🔗</span>
            {{ __('messages.referrals.your_network') ?? 'Your Referral Network' }}
        </h4>
        <span style="font-size: 0.85rem; color: var(--text-muted);">
            {{ $totalReferrals }} {{ $totalReferrals == 1 ? 'member' : 'members' }}
        </span>
    </div>
    
    @if($referrals->count() > 0)
    <!-- Chain Tree Visualization -->
    <div class="referral-chain">
        <!-- Root User (You) -->
        <div class="chain-root">
            <div class="chain-node root-node">
                <div class="node-avatar-wrapper">
                    <img src="{{ auth()->user()->getAvatarUrl() }}" alt="You" class="node-avatar">
                    <span class="crown-badge">👑</span>
                </div>
                <div class="node-info">
                    <span class="node-name">{{ auth()->user()->name }}</span>
                    <span class="node-role">You (Referrer)</span>
                </div>
            </div>
            
            <!-- Connecting Line -->
            <div class="chain-connector">
                <div class="connector-line"></div>
                <div class="connector-branches">
                    @foreach($referrals->take(10) as $index => $referral)
                    <div class="branch-line" style="--delay: {{ $index * 0.1 }}s;"></div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Referral Nodes -->
        <div class="chain-nodes">
            @foreach($referrals->take(10) as $index => $referral)
            <div class="chain-node member-node {{ $referral->is_active ? 'active' : 'inactive' }}" style="--delay: {{ $index * 0.1 }}s;">
                <div class="node-avatar-wrapper">
                    <img src="{{ $referral->getAvatarUrl() }}" alt="{{ $referral->name }}" class="node-avatar">
                    @if($referral->is_active)
                    <span class="status-indicator active">✓</span>
                    @else
                    <span class="status-indicator inactive">⏸</span>
                    @endif
                </div>
                <div class="node-info">
                    <span class="node-name">{{ Str::limit($referral->name, 12) }}</span>
                    <span class="node-date">{{ $referral->created_at->format('d M') }}</span>
                </div>
                <div class="node-stats">
                    @if($referral->taskCompletions()->count() > 0)
                    <span class="stat-badge tasks">
                        ⚡ {{ $referral->taskCompletions()->count() }}
                    </span>
                    @endif
                    @if($referral->first_task_completed)
                    <span class="stat-badge bonus">
                        💰 +{{ number_format($referralBonusReferrer, 0) }}
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        
        @if($referrals->count() > 10)
        <div class="chain-more">
            <span>+{{ $referrals->count() - 10 }} more members</span>
        </div>
        @endif
    </div>
    @else
    <!-- Empty State -->
    <div class="empty-chain">
        <div class="empty-illustration">
            <span class="big-emoji">🌱</span>
            <div class="empty-lines">
                <span></span><span></span><span></span>
            </div>
        </div>
        <h4>Start Growing Your Network!</h4>
        <p>Share your code and watch your referral tree grow</p>
        <button onclick="copyLink()" class="btn btn-primary mt-4">
            <i data-lucide="share-2"></i>
            Share Your Link
        </button>
    </div>
    @endif
</div>

<style>
/* Referral Chain Styles */
.referral-chain {
    position: relative;
}

.chain-root {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: var(--space-6);
}

.chain-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: var(--space-3);
    border-radius: var(--radius-xl);
    background: var(--bg-elevated);
    border: 2px solid transparent;
    transition: all 0.3s ease;
    min-width: 100px;
    animation: nodeAppear 0.5s ease forwards;
    animation-delay: var(--delay, 0s);
    opacity: 0;
    transform: translateY(20px);
}

@keyframes nodeAppear {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chain-node.root-node {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.05));
    border-color: var(--primary);
    box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);
    opacity: 1;
    transform: none;
}

.chain-node.member-node:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.chain-node.active {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), transparent);
}

.chain-node.inactive {
    background: linear-gradient(135deg, rgba(115, 115, 115, 0.1), transparent);
    opacity: 0.7;
}

.node-avatar-wrapper {
    position: relative;
    margin-bottom: var(--space-2);
}

.node-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--bg-dark);
}

.root-node .node-avatar {
    width: 70px;
    height: 70px;
    border: 4px solid var(--primary);
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
}

.crown-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    font-size: 1.25rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

.status-indicator {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    border: 2px solid var(--bg-dark);
}

.status-indicator.active {
    background: var(--success);
    color: white;
}

.status-indicator.inactive {
    background: var(--text-muted);
    color: white;
}

.node-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.node-name {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text-primary);
}

.node-role {
    font-size: 0.7rem;
    color: var(--primary);
    font-weight: 500;
}

.node-date {
    font-size: 0.7rem;
    color: var(--text-muted);
}

.node-stats {
    display: flex;
    gap: 4px;
    margin-top: var(--space-2);
    flex-wrap: wrap;
    justify-content: center;
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 2px 6px;
    border-radius: var(--radius-full);
    font-size: 0.65rem;
    font-weight: 600;
}

.stat-badge.tasks {
    background: rgba(99, 102, 241, 0.2);
    color: #818cf8;
}

.stat-badge.bonus {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success);
}

/* Connector Lines */
.chain-connector {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: var(--space-3) 0;
}

.connector-line {
    width: 3px;
    height: 40px;
    background: linear-gradient(to bottom, var(--primary), rgba(16, 185, 129, 0.3));
    border-radius: 2px;
}

.connector-branches {
    display: flex;
    gap: 0;
    position: relative;
    height: 20px;
}

.branch-line {
    width: 60px;
    height: 3px;
    background: rgba(16, 185, 129, 0.3);
    position: relative;
    animation: branchGrow 0.5s ease forwards;
    animation-delay: var(--delay, 0s);
    transform: scaleX(0);
}

@keyframes branchGrow {
    to { transform: scaleX(1); }
}

.branch-line::after {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    width: 3px;
    height: 20px;
    background: rgba(16, 185, 129, 0.3);
}

/* Chain Nodes Grid */
.chain-nodes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: var(--space-3);
    max-width: 100%;
}

@media (min-width: 768px) {
    .chain-nodes {
        grid-template-columns: repeat(5, 1fr);
    }
}

@media (max-width: 480px) {
    .chain-nodes {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .chain-node {
        min-width: auto;
    }
}

.chain-more {
    text-align: center;
    margin-top: var(--space-4);
    padding: var(--space-3);
    background: var(--bg-elevated);
    border-radius: var(--radius-lg);
    color: var(--text-muted);
    font-size: 0.85rem;
}

/* Empty State */
.empty-chain {
    text-align: center;
    padding: var(--space-8) var(--space-4);
}

.empty-illustration {
    margin-bottom: var(--space-6);
}

.big-emoji {
    font-size: 4rem;
    display: block;
    margin-bottom: var(--space-3);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.empty-lines {
    display: flex;
    justify-content: center;
    gap: var(--space-3);
}

.empty-lines span {
    width: 40px;
    height: 40px;
    border: 2px dashed rgba(16, 185, 129, 0.3);
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.empty-lines span:nth-child(2) {
    animation-delay: 0.3s;
}

.empty-lines span:nth-child(3) {
    animation-delay: 0.6s;
}

.empty-chain h4 {
    margin-bottom: var(--space-2);
}

.empty-chain p {
    color: var(--text-muted);
    font-size: 0.9rem;
}
</style>

<!-- Referrals List (Table Format) -->
<div class="flex justify-between items-center mb-4">
    <h3>{{ __('messages.referrals.invited_friends') }}</h3>
</div>

<!-- Desktop Table View -->
<div class="card hide-mobile">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Tasks</th>
                    <th>Bonus</th>
                </tr>
            </thead>
            <tbody>
                @forelse($referrals as $index => $referral)
                <tr>
                    <td>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">{{ $index + 1 }}</span>
                    </td>
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
                        <span class="badge badge-success">✓ Active</span>
                        @else
                        <span class="badge badge-error">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight: 600;">{{ $referral->taskCompletions()->count() }}</span>
                    </td>
                    <td>
                        @if($referral->first_task_completed)
                        <span style="color: var(--success); font-weight: 600;">+TZS {{ number_format($referralBonusReferrer, 0) }}</span>
                        @else
                        <span style="color: var(--text-muted); font-size: 0.8rem;">Pending</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: var(--space-8); color: var(--text-muted);">
                        <i data-lucide="users" style="width: 48px; height: 48px; margin: 0 auto var(--space-4); display: block;"></i>
                        No referrals yet. Start sharing your code!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile Card View -->
<div class="show-mobile" style="display: none;">
    @forelse($referrals as $index => $referral)
    <div class="referral-card">
        <div class="referral-card-header">
            <span style="background: var(--bg-dark); padding: 4px 8px; border-radius: 50%; font-size: 0.75rem; color: var(--text-muted);">{{ $index + 1 }}</span>
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
            <div>
                <span style="color: var(--text-muted);">Tasks:</span>
                <span style="font-weight: 600;">{{ $referral->taskCompletions()->count() }}</span>
            </div>
            <div>
                @if($referral->first_task_completed)
                <span style="color: var(--success); font-weight: 600;">+TZS {{ number_format($referralBonusReferrer, 0) }}</span>
                @else
                <span style="color: var(--text-muted);">Bonus: Pending</span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="card card-body text-center">
        <i data-lucide="users" style="width: 48px; height: 48px; color: var(--text-muted); margin: 0 auto var(--space-4);"></i>
        <p style="color: var(--text-muted);">No referrals yet. Start sharing your code!</p>
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
