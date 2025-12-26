@extends('layouts.public')

@section('title', 'Maswali Yanayoulizwa Sana (FAQ)')

@section('content')
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="text-align: center; padding: 3rem 2rem;">
        <div style="width: 80px; height: 80px; background: var(--gradient-glow); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <i data-lucide="help-circle" style="width: 40px; height: 40px; color: var(--primary);"></i>
        </div>
        <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">Maswali Yanayoulizwa Sana</h1>
        <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto;">
            Pata majibu ya haraka kwa maswali ya kawaida kuhusu SKYpesa
        </p>
    </div>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    @foreach($faqs as $index => $faq)
    <div class="card" style="margin-bottom: 1rem;">
        <div 
            class="faq-item"
            style="cursor: pointer; padding: 1.25rem 1.5rem;"
            onclick="toggleFaq({{ $index }})"
        >
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1rem; font-weight: 600; color: white; margin: 0; padding-right: 1rem;">
                    {{ $faq['question'] }}
                </h3>
                <div id="faq-icon-{{ $index }}" style="transition: transform 0.3s ease;">
                    <i data-lucide="chevron-down" style="width: 20px; height: 20px; color: var(--primary);"></i>
                </div>
            </div>
            <div 
                id="faq-answer-{{ $index }}" 
                style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.05); color: var(--text-secondary); line-height: 1.7; font-size: 0.9rem;"
            >
                {{ $faq['answer'] }}
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card" style="margin-top: 3rem; text-align: center;">
    <div class="card-body" style="padding: 2rem;">
        <h3 style="margin-bottom: 0.5rem;">Bado una maswali?</h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
            Timu yetu ya msaada iko tayari kukusaidia 24/7
        </p>
        <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('pages.contact') }}" class="btn btn-primary">
                <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                Wasiliana Nasi
            </a>
            @php
                $whatsapp = \App\Models\Setting::get('whatsapp_support_number', '255700000000');
            @endphp
            <a href="https://wa.me/{{ str_replace(['+', ' '], '', $whatsapp) }}" target="_blank" class="btn btn-secondary" style="background: #25D366; border-color: #25D366;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                WhatsApp
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleFaq(index) {
        const answer = document.getElementById('faq-answer-' + index);
        const icon = document.getElementById('faq-icon-' + index);
        
        if (answer.style.display === 'none') {
            answer.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        } else {
            answer.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }
</script>
@endpush
@endsection
