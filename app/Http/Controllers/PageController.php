<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * FAQ Page
     */
    public function faq()
    {
        $faqs = [
            [
                'question' => 'SKYpesa ni nini?',
                'answer' => 'SKYpesa ni jukwaa la Tanzania linaloweza kujipatia pesa halali kwa kukamilisha kazi rahisi kama kutazama matangazo, kufanya survey, na kujiunga na programu za washirika wetu.',
            ],
            [
                'question' => 'Je, ni bure kujisajili?',
                'answer' => 'Ndiyo! Kujisajili ni bure kabisa. Unapata akaunti ya Basic bila malipo yoyote. Unaweza kuupgrade mpaka VIP kwa faida zaidi.',
            ],
            [
                'question' => 'Pesa zinaingizwa lini?',
                'answer' => 'Pesa zinaingizwa moja kwa moja kwenye wallet yako mara tu ukikamilisha kazi. Unaweza kutoa pesa wakati wowote ukifikia kiwango cha chini cha kutoa.',
            ],
            [
                'question' => 'Kiwango cha chini cha kutoa pesa ni kiasi gani?',
                'answer' => 'Kiwango cha chini cha kutoa pesa ni TZS ' . number_format(Setting::get('withdrawal_minimum', 5000), 0) . '. Pesa zinatumwa moja kwa moja kwenye M-Pesa, Tigo Pesa, au Airtel Money.',
            ],
            [
                'question' => 'Ninawezaje kupata pesa zaidi?',
                'answer' => 'Kuna njia kadhaa: (1) Upgrade mpaka VIP kupata kazi zaidi na malipo makubwa, (2) Waitishe marafiki kwa referral code yako na upate bonus, (3) Kamilisha Daily Goals kupata zawadi za ziada.',
            ],
            [
                'question' => 'Je, SKYpesa ni halali?',
                'answer' => 'Ndiyo! SKYpesa ni jukwaa halali. Tunashirikiana na makampuni ya matangazo ya kimataifa kama Adsterra na Monetag. Watumiaji wote wanalipwa kwa wakati.',
            ],
            [
                'question' => 'Inachukua muda gani kupata pesa?',
                'answer' => 'Pesa zinatumwa ndani ya saa 24 baada ya kuomba kutoa. Mara nyingi, pesa zinafika ndani ya dakika chache.',
            ],
            [
                'question' => 'Je, ninaweza kutumia simu ya kawaida?',
                'answer' => 'Hapana, unahitaji smartphone na internet. SKYpesa inafanya kazi vizuri kwenye simu ya Android na iPhone.',
            ],
            [
                'question' => 'Vipi kama nina shida na akaunti yangu?',
                'answer' => 'Wasiliana na timu yetu ya msaada kupitia WhatsApp, email (support@skypesa.com), au tumia Support Center ndani ya app.',
            ],
            [
                'question' => 'Je, referral program inafanya kazi vipi?',
                'answer' => 'Kwa kila rafiki anayejisajili kwa kutumia referral code yako na kukamilisha task ya kwanza, unapata bonus ya TZS ' . number_format(Setting::get('referral_bonus', 1000), 0) . '!',
            ],
        ];

        return view('pages.faq', compact('faqs'));
    }

    /**
     * Contact Us Page
     */
    public function contact()
    {
        $contact = [
            'email' => Setting::get('support_email', 'support@skypesa.com'),
            'phone' => Setting::get('support_phone', '+255 700 000 000'),
            'whatsapp' => Setting::get('whatsapp_support_number', '255700000000'),
            'address' => Setting::get('company_address', 'Dar es Salaam, Tanzania'),
            'hours' => Setting::get('support_hours', 'Jumatatu - Ijumaa: 9:00 AM - 6:00 PM EAT'),
        ];

        return view('pages.contact', compact('contact'));
    }

    /**
     * Terms of Service Page
     */
    public function terms()
    {
        $companyName = Setting::get('company_name', 'SKYpesa');
        $lastUpdated = '26 December 2024';

        return view('pages.terms', compact('companyName', 'lastUpdated'));
    }

    /**
     * Privacy Policy Page
     */
    public function privacy()
    {
        $companyName = Setting::get('company_name', 'SKYpesa');
        $lastUpdated = '26 December 2024';

        return view('pages.privacy', compact('companyName', 'lastUpdated'));
    }
}
