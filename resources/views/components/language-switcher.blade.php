{{-- Language Switcher Component --}}
<div class="language-switcher" id="languageSwitcher">
    <button type="button" class="lang-toggle-btn" id="langToggleBtn" aria-label="{{ __('messages.common.language') }}">
        <i data-lucide="globe"></i>
        <span class="current-lang">{{ app()->getLocale() === 'sw' ? 'SW' : 'EN' }}</span>
        <i data-lucide="chevron-down" class="chevron-icon"></i>
    </button>
    
    <div class="lang-dropdown" id="langDropdown">
        <a href="{{ route('language.switch', 'en') }}" class="lang-option {{ app()->getLocale() === 'en' ? 'active' : '' }}">
            <span class="lang-flag">🇬🇧</span>
            <span class="lang-name">English</span>
            @if(app()->getLocale() === 'en')
                <i data-lucide="check" class="check-icon"></i>
            @endif
        </a>
        <a href="{{ route('language.switch', 'sw') }}" class="lang-option {{ app()->getLocale() === 'sw' ? 'active' : '' }}">
            <span class="lang-flag">🇹🇿</span>
            <span class="lang-name">Kiswahili</span>
            @if(app()->getLocale() === 'sw')
                <i data-lucide="check" class="check-icon"></i>
            @endif
        </a>
    </div>
</div>

<style>
    .language-switcher {
        position: relative;
        z-index: 200;
    }
    
    .lang-toggle-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.875rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: var(--radius-lg, 12px);
        color: var(--text-secondary, #9ca3af);
        font-size: 0.8125rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .lang-toggle-btn:hover {
        background: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.3);
        color: var(--primary, #10b981);
    }
    
    .lang-toggle-btn i {
        width: 16px;
        height: 16px;
    }
    
    .lang-toggle-btn .chevron-icon {
        width: 14px;
        height: 14px;
        transition: transform 0.2s ease;
    }
    
    .language-switcher.open .lang-toggle-btn .chevron-icon {
        transform: rotate(180deg);
    }
    
    .current-lang {
        font-weight: 600;
        color: var(--primary, #10b981);
    }
    
    .lang-dropdown {
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        min-width: 160px;
        background: linear-gradient(145deg, #1a1a1a 0%, #151515 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: var(--radius-lg, 12px);
        padding: 0.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s ease;
    }
    
    .language-switcher.open .lang-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .lang-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0.875rem;
        border-radius: 8px;
        color: var(--text-secondary, #9ca3af);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.15s ease;
    }
    
    .lang-option:hover {
        background: rgba(16, 185, 129, 0.1);
        color: var(--primary, #10b981);
    }
    
    .lang-option.active {
        background: rgba(16, 185, 129, 0.15);
        color: var(--primary, #10b981);
    }
    
    .lang-flag {
        font-size: 1.25rem;
    }
    
    .lang-name {
        flex: 1;
    }
    
    .check-icon {
        width: 16px;
        height: 16px;
        color: var(--primary, #10b981);
    }
    
    /* Mobile Styles */
    @media (max-width: 768px) {
        .lang-toggle-btn {
            padding: 0.375rem 0.625rem;
        }
        
        .lang-dropdown {
            right: -0.5rem;
            min-width: 150px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const languageSwitcher = document.getElementById('languageSwitcher');
        const langToggleBtn = document.getElementById('langToggleBtn');
        
        if (langToggleBtn && languageSwitcher) {
            langToggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                languageSwitcher.classList.toggle('open');
                lucide.createIcons();
            });
            
            // Close on click outside
            document.addEventListener('click', function(e) {
                if (!languageSwitcher.contains(e.target)) {
                    languageSwitcher.classList.remove('open');
                }
            });
            
            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    languageSwitcher.classList.remove('open');
                }
            });
        }
    });
</script>
