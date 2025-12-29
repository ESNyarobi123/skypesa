@extends('layouts.app')

@section('title', 'Akaunti Yangu')
@section('page-title', 'Akaunti Yangu')
@section('page-subtitle', 'Simamia maelezo yako binafsi')

@section('content')
<div class="grid grid-2" style="gap: var(--space-6);">
    <!-- Left Column - Profile Card -->
    <div>
        <!-- Profile Picture Card -->
        <div class="card card-body mb-6" style="text-align: center;">
            <div style="position: relative; display: inline-block; margin: 0 auto;">
                <img src="{{ $user->getAvatarUrl() }}" 
                     alt="{{ $user->name }}" 
                     id="avatarPreview"
                     style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); box-shadow: 0 8px 32px rgba(16, 185, 129, 0.2);">
                
                <!-- Edit Badge -->
                <label for="avatarInput" 
                       style="position: absolute; bottom: 0; right: 0; width: 36px; height: 36px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: transform 0.2s;"
                       onmouseover="this.style.transform='scale(1.1)'"
                       onmouseout="this.style.transform='scale(1)'">
                    <i data-lucide="camera" style="width: 18px; height: 18px; color: white;"></i>
                </label>
            </div>
            
            <h3 style="margin-top: var(--space-4); margin-bottom: var(--space-1); font-size: 1.25rem;">{{ $user->name }}</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem;">{{ $user->email }}</p>
            
            <div style="display: flex; justify-content: center; gap: var(--space-2); margin-top: var(--space-3);">
                <span style="background: var(--gradient-glow); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600;">
                    <i data-lucide="crown" style="width: 12px; height: 12px; display: inline;"></i>
                    {{ $user->getPlanName() }}
                </span>
                <span style="background: rgba(255,255,255,0.05); color: var(--text-secondary); padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem;">
                    Mwanachama tangu {{ $user->created_at->format('M Y') }}
                </span>
            </div>

            <!-- Avatar Upload Form (Hidden) -->
            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                @csrf
                <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display: none;" onchange="previewAndSubmit(this)">
            </form>

            @if($user->avatar)
            <form action="{{ route('profile.avatar.remove') }}" method="POST" style="margin-top: var(--space-3);">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">
                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    Ondoa Picha
                </button>
            </form>
            @endif
        </div>

        <!-- Account Stats -->
        <div class="card card-body">
            <h4 style="margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-2);">
                <i data-lucide="bar-chart-3" style="color: var(--primary);"></i>
                Takwimu za Akaunti
            </h4>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-4);">
                <div style="background: var(--bg-elevated); padding: var(--space-3); border-radius: var(--radius-md); text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">{{ $user->taskCompletions()->where('status', 'completed')->count() }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Tasks Kamili</div>
                </div>
                <div style="background: var(--bg-elevated); padding: var(--space-3); border-radius: var(--radius-md); text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--success);">TZS {{ number_format($user->wallet?->total_earned ?? 0, 0) }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Jumla Uliyopata</div>
                </div>
                <div style="background: var(--bg-elevated); padding: var(--space-3); border-radius: var(--radius-md); text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--warning);">{{ $user->referrals()->count() }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Marafiki</div>
                </div>
                <div style="background: var(--bg-elevated); padding: var(--space-3); border-radius: var(--radius-md); text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--info);">{{ $user->withdrawals()->where('status', 'paid')->count() }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Malipo</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Edit Forms -->
    <div>
        <!-- Edit Profile Form -->
        <div class="card card-body mb-6">
            <h4 style="margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-2);">
                <i data-lucide="user-pen" style="color: var(--primary);"></i>
                Badilisha Maelezo
            </h4>

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Jina Kamili</label>
                    <div style="position: relative;">
                        <i data-lucide="user" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', $user->name) }}"
                               placeholder="Jina lako kamili"
                               style="padding-left: 44px;"
                               required>
                    </div>
                    @error('name')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Namba ya Simu</label>
                    <div style="position: relative;">
                        <i data-lucide="phone" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                        <input type="tel" name="phone" class="form-control" 
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="0712 345 678"
                               style="padding-left: 44px;"
                               required>
                    </div>
                    @error('phone')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div style="position: relative;">
                        <i data-lucide="mail" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                        <input type="email" class="form-control" 
                               value="{{ $user->email }}"
                               style="padding-left: 44px; opacity: 0.6;"
                               disabled>
                    </div>
                    <span style="font-size: 0.7rem; color: var(--text-muted);">
                        <i data-lucide="info" style="width: 12px; height: 12px; display: inline;"></i>
                        Email haiwezi kubadilishwa
                    </span>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i data-lucide="save"></i>
                    Hifadhi Mabadiliko
                </button>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="card card-body">
            <h4 style="margin-bottom: var(--space-4); display: flex; align-items: center; gap: var(--space-2);">
                <i data-lucide="lock" style="color: var(--warning);"></i>
                Badilisha Password
            </h4>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Password ya Sasa</label>
                    <div style="position: relative;">
                        <i data-lucide="key" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                        <input type="password" name="current_password" class="form-control" 
                               placeholder="Weka password ya sasa"
                               style="padding-left: 44px;"
                               required>
                    </div>
                    @error('current_password')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Password Mpya</label>
                    <div style="position: relative;">
                        <i data-lucide="lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Weka password mpya"
                               style="padding-left: 44px;"
                               minlength="6"
                               required>
                    </div>
                    @error('password')
                        <span style="color: var(--error); font-size: 0.75rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Thibitisha Password Mpya</label>
                    <div style="position: relative;">
                        <i data-lucide="lock-check" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
                        <input type="password" name="password_confirmation" class="form-control" 
                               placeholder="Rudia password mpya"
                               style="padding-left: 44px;"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning" style="width: 100%;">
                    <i data-lucide="refresh-cw"></i>
                    Badilisha Password
                </button>
            </form>
        </div>

        <!-- Referral Code -->
        <div class="card card-body mt-6" style="background: var(--gradient-glow); border: 1px solid rgba(16, 185, 129, 0.2);">
            <h4 style="margin-bottom: var(--space-3); display: flex; align-items: center; gap: var(--space-2);">
                <i data-lucide="share-2" style="color: var(--primary);"></i>
                Kodi Yako ya Mwelekeo
            </h4>
            
            <div style="display: flex; gap: var(--space-2);">
                <input type="text" class="form-control" 
                       value="{{ $user->referral_code }}" 
                       id="referralCode"
                       readonly 
                       style="font-weight: 700; font-size: 1.125rem; text-align: center; background: var(--bg-card); letter-spacing: 0.1em;">
                <button type="button" class="btn btn-primary" onclick="copyReferralCode()" style="white-space: nowrap;">
                    <i data-lucide="copy"></i>
                    Nakili
                </button>
            </div>
            
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: var(--space-2);">
                Shiriki kodi hii na marafiki upate bonus!
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewAndSubmit(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('Picha isizidi 2MB');
                return;
            }
            
            // Preview
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
            
            // Submit form
            document.getElementById('avatarForm').submit();
        }
    }
    
    function copyReferralCode() {
        const code = document.getElementById('referralCode').value;
        navigator.clipboard.writeText(code).then(() => {
            alert('Kodi imenakiliwa: ' + code);
        });
    }
    
    // Re-init icons
    lucide.createIcons();
</script>
@endpush

@push('styles')
<style>
    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
@endsection
