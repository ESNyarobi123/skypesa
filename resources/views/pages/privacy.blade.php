@extends('layouts.public')

@section('title', 'Sera ya Faragha')

@section('content')
<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-body" style="padding: 2.5rem;">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div style="width: 70px; height: 70px; background: var(--gradient-glow); border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i data-lucide="shield" style="width: 35px; height: 35px; color: var(--primary);"></i>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem;">Sera ya Faragha</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Imesasishwa: {{ $lastUpdated }}
            </p>
        </div>

        <div class="privacy-content" style="line-height: 1.8; color: var(--text-secondary);">
            
            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">1.</span> Utangulizi
                </h2>
                <p>
                    {{ $companyName }} ("sisi", "yetu") inathamini faragha yako. Sera hii inaeleza jinsi tunavyokusanya, 
                    kutumia, na kulinda taarifa zako za kibinafsi unapotumia huduma zetu.
                </p>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">2.</span> Taarifa Tunazokusanya
                </h2>
                <p style="margin-bottom: 1rem;">Tunakusanya aina zifuatazo za taarifa:</p>
                <ul style="list-style: disc; padding-left: 1.5rem;">
                    <li style="margin-bottom: 0.5rem;"><strong>Taarifa za Akaunti:</strong> Jina, email, namba ya simu, na avatar.</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Taarifa za Malipo:</strong> Namba ya M-Pesa/Simu ya malipo kwa ajili ya kutoa pesa.</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Taarifa za Matumizi:</strong> Kazi ulizokamilisha, pesa ulizopata, na historia ya miamala.</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Taarifa za Kifaa:</strong> Aina ya browser, mfumo wa uendeshaji, na IP address kwa usalama.</li>
                </ul>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">3.</span> Jinsi Tunavyotumia Taarifa
                </h2>
                <p style="margin-bottom: 1rem;">Tunatumia taarifa zako kwa:</p>
                <ul style="list-style: disc; padding-left: 1.5rem;">
                    <li style="margin-bottom: 0.5rem;">Kutoa na kuboresha huduma zetu.</li>
                    <li style="margin-bottom: 0.5rem;">Kusindika malipo yako na kutoa pesa.</li>
                    <li style="margin-bottom: 0.5rem;">Kukutumia taarifa muhimu kuhusu akaunti yako.</li>
                    <li style="margin-bottom: 0.5rem;">Kuzuia ulaghai na kudumisha usalama wa jukwaa.</li>
                    <li style="margin-bottom: 0.5rem;">Kuboresha uzoefu wako wa mtumiaji.</li>
                </ul>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">4.</span> Usalama wa Taarifa
                </h2>
                <p>
                    Tunatumia hatua za kiteknolojia na kiutawala kulinda taarifa zako. Hii inajumuisha:
                </p>
                <ul style="list-style: disc; padding-left: 1.5rem; margin-top: 0.5rem;">
                    <li style="margin-bottom: 0.5rem;">Encryption ya data (SSL/TLS).</li>
                    <li style="margin-bottom: 0.5rem;">Passwords zilizofichwa kwa njia salama (hashing).</li>
                    <li style="margin-bottom: 0.5rem;">Udhibiti mkali wa ufikiaji wa data.</li>
                    <li style="margin-bottom: 0.5rem;">Ukaguzi wa mara kwa mara wa usalama.</li>
                </ul>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">5.</span> Kushiriki Taarifa
                </h2>
                <p style="margin-bottom: 1rem;">
                    Hatushiriki taarifa zako na watu wa nje isipokuwa:
                </p>
                <ul style="list-style: disc; padding-left: 1.5rem;">
                    <li style="margin-bottom: 0.5rem;">Watoa huduma za malipo (kama ZenoPay) kwa usindikaji wa pesa.</li>
                    <li style="margin-bottom: 0.5rem;">Mamlaka za kisheria inapohitajika na sheria.</li>
                    <li style="margin-bottom: 0.5rem;">Washirika wetu wa matangazo (kwa namna isiyokutambulisha moja kwa moja).</li>
                </ul>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">6.</span> Haki Zako
                </h2>
                <p style="margin-bottom: 1rem;">Una haki ya:</p>
                <ul style="list-style: disc; padding-left: 1.5rem;">
                    <li style="margin-bottom: 0.5rem;">Kufikia na kupakua taarifa zako.</li>
                    <li style="margin-bottom: 0.5rem;">Kusahihisha taarifa zisizo sahihi.</li>
                    <li style="margin-bottom: 0.5rem;">Kufuta akaunti yako na taarifa zote.</li>
                    <li style="margin-bottom: 0.5rem;">Kukataa kupokea barua pepe za matangazo.</li>
                </ul>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">7.</span> Cookies
                </h2>
                <p>
                    Tunatumia cookies na teknolojia zinazofanana kuboresha huduma zetu na kukupa uzoefu bora. 
                    Unaweza kudhibiti cookies kupitia mipangilio ya browser yako.
                </p>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">8.</span> Mawasiliano
                </h2>
                <p>
                    Kwa maswali kuhusu sera hii ya faragha, wasiliana nasi:
                </p>
                <ul style="list-style: none; padding: 0; margin-top: 0.5rem;">
                    <li style="margin-bottom: 0.25rem;">📧 Email: privacy@skypesa.com</li>
                    <li>📱 WhatsApp: +255 700 000 000</li>
                </ul>
            </section>

        </div>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center;">
            <p style="color: var(--text-muted); font-size: 0.85rem;">
                Faragha yako ni muhimu kwetu. Asante kwa kutuamini.
            </p>
            <a href="{{ route('pages.terms') }}" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">
                <i data-lucide="file-text" style="width: 16px; height: 16px;"></i>
                Soma Masharti ya Huduma
            </a>
        </div>
    </div>
</div>
@endsection
