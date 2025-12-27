@extends('layouts.app')

@section('title', 'Akaunti Imezuiwa')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; background: linear-gradient(135deg, #0f0f0f 0%, #1a1a2e 50%, #16213e 100%);">
    <div style="max-width: 500px; width: 100%; text-align: center;">
        
        <!-- Icon -->
        <div style="margin-bottom: 2rem;">
            <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(239, 68, 68, 0.1) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(239, 68, 68, 0.3);">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="m4.93 4.93 14.14 14.14"></path>
                </svg>
            </div>
        </div>

        <!-- Title -->
        <h1 style="font-size: 2rem; font-weight: 700; margin: 0 0 0.75rem 0; color: #ef4444;">
            Akaunti Imezuiwa
        </h1>
        <p style="color: rgba(255, 255, 255, 0.7); margin: 0 0 2rem 0; font-size: 1.1rem;">
            Akaunti yako imezuiwa kwa muda kutokana na shughuli za tuhuma.
        </p>

        <!-- Info Card -->
        <div style="background: rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid rgba(255, 255, 255, 0.1);">
            @if($user->blocked_reason)
            <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                <div style="font-weight: 600; color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.5rem;">Sababu</div>
                <p style="margin: 0; color: #fff;">{{ $user->blocked_reason }}</p>
            </div>
            @endif
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: left;">
                <div>
                    <div style="font-weight: 600; color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.25rem;">Tarehe</div>
                    <p style="margin: 0; color: #fff;">
                        {{ $user->blocked_at ? $user->blocked_at->format('d M, Y') : 'N/A' }}
                    </p>
                </div>
                <div>
                    <div style="font-weight: 600; color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; text-transform: uppercase; margin-bottom: 0.25rem;">Na</div>
                    <p style="margin: 0; color: #fff;">
                        {{ $user->blockedByAdmin?->name ?? 'System (Auto)' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid rgba(16, 185, 129, 0.2);">
            <h3 style="font-size: 1rem; margin: 0 0 1rem 0; color: #10b981; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4"></path>
                    <path d="M12 8h.01"></path>
                </svg>
                Jinsi ya Kufungua Akaunti
            </h3>
            <p style="margin: 0 0 1rem 0; color: rgba(255, 255, 255, 0.8); font-size: 0.9rem;">
                Ili akaunti yako ifunguliwe, tafadhali wasiliana na msimamizi (admin) kupitia WhatsApp kwa kueleza hali yako.
            </p>
            <ol style="margin: 0; padding-left: 1.25rem; color: rgba(255, 255, 255, 0.7); font-size: 0.875rem; text-align: left;">
                <li style="margin-bottom: 0.5rem;">Bofya kitufe cha WhatsApp hapa chini</li>
                <li style="margin-bottom: 0.5rem;">Tuma ujumbe kwa admin unaeleza hali yako</li>
                <li style="margin-bottom: 0.5rem;">Subiri admin akague na kukufungua</li>
                <li>Ukifunguliwa, utaweza kuendelea kutumia SKYpesa</li>
            </ol>
        </div>

        <!-- WhatsApp CTA -->
        @php
            $adminWhatsApp = \App\Models\Setting::get('whatsapp_support_number', '255700000000');
            $cleanNumber = preg_replace('/[^0-9]/', '', $adminWhatsApp);
            $message = urlencode("Habari Admin,\n\nNaomba msaada. Akaunti yangu imezuiwa.\n\nJina: {$user->name}\nEmail: {$user->email}\n\nTafadhali nisaidie kufungua akaunti yangu. Asante!");
            $whatsappUrl = "https://wa.me/{$cleanNumber}?text={$message}";
        @endphp
        
        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" 
           style="display: inline-flex; align-items: center; justify-content: center; gap: 0.75rem; background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); color: #fff; padding: 1rem 2rem; border-radius: 12px; font-size: 1.1rem; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(37, 211, 102, 0.3);"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 30px rgba(37, 211, 102, 0.4)';"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(37, 211, 102, 0.3)';">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            Wasiliana na Admin
        </a>

        <!-- Logout -->
        <div style="margin-top: 2rem;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: none; border: 1px solid rgba(255, 255, 255, 0.2); color: rgba(255, 255, 255, 0.6); padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.875rem; cursor: pointer; transition: all 0.3s ease;"
                        onmouseover="this.style.borderColor='rgba(255, 255, 255, 0.4)'; this.style.color='rgba(255, 255, 255, 0.8)';"
                        onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.color='rgba(255, 255, 255, 0.6)';">
                    Toka Nje (Logout)
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p style="margin-top: 3rem; font-size: 0.75rem; color: rgba(255, 255, 255, 0.4);">
            &copy; {{ date('Y') }} SKYpesa. Haki zote zimehifadhiwa.
        </p>
    </div>
</div>
@endsection
