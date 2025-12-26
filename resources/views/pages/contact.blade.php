@extends('layouts.public')

@section('title', 'Wasiliana Nasi')

@section('content')
<div class="grid grid-2 gap-8">
    <!-- Contact Info -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body" style="padding: 2rem;">
                <div style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                    <i data-lucide="headphones" style="width: 30px; height: 30px; color: var(--primary);"></i>
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">Timu Yetu ya Msaada</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">
                    Tuko tayari kukusaidia wakati wowote. Chagua njia inayokufaa zaidi.
                </p>

                <div style="display: grid; gap: 1rem;">
                    <!-- Email -->
                    <a href="mailto:{{ $contact['email'] }}" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px; text-decoration: none; transition: all 0.2s;">
                        <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="mail" style="width: 24px; height: 24px; color: #3b82f6;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: white;">Email</div>
                            <div style="font-size: 0.875rem; color: var(--text-muted);">{{ $contact['email'] }}</div>
                        </div>
                    </a>

                    <!-- Phone -->
                    <a href="tel:{{ str_replace(' ', '', $contact['phone']) }}" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px; text-decoration: none; transition: all 0.2s;">
                        <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="phone" style="width: 24px; height: 24px; color: var(--primary);"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: white;">Simu</div>
                            <div style="font-size: 0.875rem; color: var(--text-muted);">{{ $contact['phone'] }}</div>
                        </div>
                    </a>

                    <!-- WhatsApp -->
                    <a href="https://wa.me/{{ str_replace(['+', ' '], '', $contact['whatsapp']) }}" target="_blank" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px; text-decoration: none; transition: all 0.2s;">
                        <div style="width: 48px; height: 48px; background: rgba(37, 211, 102, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="#25D366">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: white;">WhatsApp</div>
                            <div style="font-size: 0.875rem; color: var(--text-muted);">Majibu ya haraka zaidi</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <i data-lucide="clock" style="width: 20px; height: 20px; color: var(--primary);"></i>
                    <span style="font-weight: 600;">Saa za Kazi</span>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">{{ $contact['hours'] }}</p>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Jumamosi & Jumapili: 10:00 AM - 4:00 PM EAT</p>
            </div>
        </div>
    </div>

    <!-- Contact Form / Login Prompt -->
    <div class="card">
        <div class="card-body" style="padding: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">
                <i data-lucide="send" style="width: 20px; height: 20px; display: inline; color: var(--primary);"></i>
                Tuma Ujumbe
            </h3>

            @auth
                <form action="{{ route('support.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Mada</label>
                        <select name="subject" class="form-input" required>
                            <option value="">-- Chagua mada --</option>
                            <option value="general">Swali la Kawaida</option>
                            <option value="payment">Malipo & Kutoa Pesa</option>
                            <option value="subscription">Subscription/VIP</option>
                            <option value="tasks">Kazi/Tasks</option>
                            <option value="account">Akaunti Yangu</option>
                            <option value="bug">Kuripoti Makosa</option>
                            <option value="other">Mengineyo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ujumbe Wako</label>
                        <textarea name="message" class="form-input" rows="5" placeholder="Eleza tatizo au swali lako kwa undani..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i data-lucide="send" style="width: 18px; height: 18px;"></i>
                        Tuma Ujumbe
                    </button>
                </form>
            @else
                <div style="text-align: center; padding: 2rem 0;">
                    <div style="width: 60px; height: 60px; background: var(--gradient-glow); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <i data-lucide="message-circle" style="width: 30px; height: 30px; color: var(--primary);"></i>
                    </div>
                    <h4 style="margin-bottom: 0.5rem;">Unahitaji Kuingia</h4>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.9rem;">
                        Ingia kwenye akaunti yako kutuma ujumbe wa msaada
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
                            Ingia
                        </a>
                        <a href="https://wa.me/{{ str_replace(['+', ' '], '', $contact['whatsapp']) }}" target="_blank" class="btn btn-secondary" style="background: #25D366; border-color: #25D366;">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            WhatsApp
                        </a>
                    </div>
                </div>
            @endauth

            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center;">
                <p style="color: var(--text-muted); font-size: 0.85rem;">
                    Au angalia <a href="{{ route('pages.faq') }}" style="color: var(--primary);">Maswali Yanayoulizwa Sana</a> kwa majibu ya haraka
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
