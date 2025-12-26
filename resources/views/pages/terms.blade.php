@extends('layouts.public')

@section('title', 'Masharti ya Huduma')

@section('content')
<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-body" style="padding: 2.5rem;">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div style="width: 70px; height: 70px; background: var(--gradient-glow); border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <i data-lucide="file-text" style="width: 35px; height: 35px; color: var(--primary);"></i>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem;">Masharti ya Huduma</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Imesasishwa: {{ $lastUpdated }}
            </p>
        </div>

        <div class="terms-content" style="line-height: 1.8; color: var(--text-secondary);">
            
            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">1.</span> Utangulizi
                </h2>
                <p>
                    Karibu {{ $companyName }}. Kwa kutumia tovuti yetu na huduma zetu, unakubali kufuata na kuwa chini ya masharti haya yafuatayo. 
                    Tafadhali soma masharti haya kwa makini kabla ya kutumia huduma zetu.
                </p>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">2.</span> Sifa za Mtumiaji
                </h2>
                <ul style="list-style: disc; padding-left: 1.5rem;">
                    <li style="margin-bottom: 0.5rem;">Lazima uwe na umri wa miaka 18 au zaidi kutumia huduma zetu.</li>
                    <li style="margin-bottom: 0.5rem;">Lazima utoe taarifa sahihi na za kweli wakati wa usajili.</li>
                    <li style="margin-bottom: 0.5rem;">Unaweza kuwa na akaunti moja tu kwenye jukwaa letu.</li>
                    <li style="margin-bottom: 0.5rem;">Unahusika na usalama wa akaunti yako na password yako.</li>
                </ul>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">3.</span> Sheria za Matumizi
                </h2>
                <p style="margin-bottom: 1rem;">Unakubali kutofanya mambo yafuatayo:</p>
                <ul style="list-style: disc; padding-left: 1.5rem;">
                    <li style="margin-bottom: 0.5rem;">Kutumia boti, VPN, au programu za kujiendesha kiotomatiki.</li>
                    <li style="margin-bottom: 0.5rem;">Kuunda akaunti nyingi au akaunti za uongo.</li>
                    <li style="margin-bottom: 0.5rem;">Kujaribu kudanganya au kuiba pesa.</li>
                    <li style="margin-bottom: 0.5rem;">Kushiriki katika shughuli za ulaghai au udanganyifu.</li>
                    <li style="margin-bottom: 0.5rem;">Kushiriki referral code yako kwenye akaunti nyingine yako mwenyewe.</li>
                </ul>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">4.</span> Malipo na Kutoa Pesa
                </h2>
                <ul style="list-style: disc; padding-left: 1.5rem;">
                    <li style="margin-bottom: 0.5rem;">Pesa zote ni za halali na zinaweza kutolewa kupitia M-Pesa, Tigo Pesa, na Airtel Money.</li>
                    <li style="margin-bottom: 0.5rem;">Kuna kiwango cha chini cha kutoa pesa ambacho kinatajwa kwenye ukurasa wa Wallet.</li>
                    <li style="margin-bottom: 0.5rem;">Muda wa usindikaji ni kawaida saa 24, ingawa mara nyingi ni haraka zaidi.</li>
                    <li style="margin-bottom: 0.5rem;">Tunaweza kuweka mipaka ya kutoa pesa kwa usalama.</li>
                </ul>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">5.</span> Usimamishaji wa Akaunti
                </h2>
                <p>
                    Tunaweza kusimamisha au kufunga akaunti yako bila taarifa ikiwa tunashuku ukiukaji wa masharti haya, 
                    shughuli za ulaghai, au matumizi yasiyo ya kawaida ya jukwaa.
                </p>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">6.</span> Mabadiliko ya Masharti
                </h2>
                <p>
                    Tunaweza kubadilisha masharti haya wakati wowote. Mabadiliko makubwa yatatangazwa kupitia email au arifa kwenye jukwaa. 
                    Kuendelea kutumia huduma zetu baada ya mabadiliko kunachukuliwa kama kukubali masharti mapya.
                </p>
            </section>

            <section style="margin-bottom: 2rem;">
                <h2 style="color: white; font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--primary);">7.</span> Mawasiliano
                </h2>
                <p>
                    Kwa maswali kuhusu masharti haya, wasiliana nasi kupitia:
                </p>
                <ul style="list-style: none; padding: 0; margin-top: 0.5rem;">
                    <li style="margin-bottom: 0.25rem;">📧 Email: support@skypesa.com</li>
                    <li>📱 WhatsApp: +255 700 000 000</li>
                </ul>
            </section>

        </div>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center;">
            <p style="color: var(--text-muted); font-size: 0.85rem;">
                Kwa kutumia {{ $companyName }}, unakubali masharti haya.
            </p>
            <a href="{{ route('pages.privacy') }}" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">
                <i data-lucide="shield" style="width: 16px; height: 16px;"></i>
                Soma Sera ya Faragha
            </a>
        </div>
    </div>
</div>
@endsection
