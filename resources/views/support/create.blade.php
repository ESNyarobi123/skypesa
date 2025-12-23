@extends('layouts.app')

@section('title', 'Fungua Tiketi')
@section('page-title', 'Fungua Tiketi')
@section('page-subtitle', 'Tueleze shida yako tukusaidie')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card glass" style="padding: 2rem;">
        <form action="{{ route('support.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Somo la Tiketi</label>
                <input type="text" name="subject" class="form-control" placeholder="Mfano: Shida ya kutoa pesa" required>
            </div>
            <div class="form-group">
                <label class="form-label">Ujumbe wako</label>
                <textarea name="message" class="form-control" rows="6" placeholder="Elezea shida yako kwa kina..." required></textarea>
            </div>
            <div class="flex gap-4 mt-6">
                <a href="{{ route('support.index') }}" class="btn btn-secondary flex-1">Ghairi</a>
                <button type="submit" class="btn btn-primary flex-1">
                    <i data-lucide="send"></i>
                    Fungua Tiketi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
