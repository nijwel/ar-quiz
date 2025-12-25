@extends('layouts.app')

@section('content')
    <style>
        .error-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .error-card {
            background: white;
            padding: 50px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }

        .error-code {
            font-size: 100px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 20px;
        }

        .error-illustration {
            width: 200px;
            margin-bottom: 30px;
        }

        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 35px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(78, 84, 200, 0.4);
            color: white;
        }
    </style>

    <div class="container error-container">
        <div class="error-card">
            <div class="error-code">404</div>
            <h2 class="fw-bold mb-3">পেজটি খুঁজে পাওয়া যায়নি!</h2>
            <p class="text-muted mb-4">দুঃখিত, আপনি যে কুইজ বা পেজটি খুঁজছেন তা হয়তো মুছে ফেলা হয়েছে অথবা লিঙ্কটি ভুল।</p>
            <a href="{{ route('home') }}" class="btn-home">
                <i class="bi bi-house-door me-2"></i> হোম পেজে ফিরে যান
            </a>
        </div>
    </div>
@endsection
