@extends('layouts.app')

@section('content')
    <style>
        /* 404 এর স্টাইলগুলো এখানেও কাজ করবে */
        .error-code-500 {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .server-icon {
            color: #ff4b2b;
            font-size: 80px;
            margin-bottom: 20px;
        }
    </style>

    <div class="container error-container">
        <div class="error-card">
            <div class="server-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="error-code error-code-500">500</div>
            <h2 class="fw-bold mb-3">সার্ভারে সমস্যা হয়েছে!</h2>
            <p class="text-muted mb-4">আমাদের দিক থেকে কিছু একটা ভুল হয়েছে। আমরা এটি ঠিক করার চেষ্টা করছি। দয়া করে কিছুক্ষণ
                পর আবার চেষ্টা করুন।</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="javascript:location.reload()" class="btn btn-outline-secondary px-4 py-2"
                    style="border-radius: 10px;">
                    <i class="bi bi-arrow-clockwise"></i> রিফ্রেশ দিন
                </a>
                <a href="{{ route('home') }}" class="btn-home mt-0">
                    হোমে যান
                </a>
            </div>
        </div>
    </div>
@endsection
