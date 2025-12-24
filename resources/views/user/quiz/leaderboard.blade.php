@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .page-header {
            background: var(--quiz-gradient);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(78, 84, 200, 0.2);
        }

        .leaderboard-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: #fff;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: var(--quiz-primary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 20px;
            border: none;
        }

        .rank-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            margin: auto;
        }

        .rank-1 {
            background: #FFD700;
            color: #fff;
            box-shadow: 0 3px 10px rgba(255, 215, 0, 0.5);
        }

        .rank-2 {
            background: #C0C0C0;
            color: #fff;
            box-shadow: 0 3px 10px rgba(192, 192, 192, 0.5);
        }

        .rank-3 {
            background: #CD7F32;
            color: #fff;
            box-shadow: 0 3px 10px rgba(205, 127, 50, 0.5);
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: var(--quiz-gradient);
            color: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .avg-progress {
            height: 8px;
            border-radius: 10px;
            background: #eee;
            width: 100px;
            margin: auto;
        }

        .active-user {
            background-color: rgba(78, 84, 200, 0.05) !important;
        }

        .accuracy-text {
            font-weight: 700;
            color: var(--quiz-primary);
        }
    </style>

    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow me-2"></i> Performance Leaderboard</h2>
                <p class="mb-0 opacity-75">গড় নম্বরের (Accuracy) ভিত্তিতে সেরা পারফর্মাররা।</p>
            </div>
            <a href="{{ route('home') }}" class="btn btn-light fw-bold rounded-pill px-4">
                <i class="bi bi-house me-1"></i> Home
            </a>
        </div>

        <div class="card leaderboard-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th class="text-start">Participant</th>
                            <th>Exams</th>
                            <th>Average Score</th>
                            <th>Correct/Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaderboard as $index => $row)
                            @php $avg = round($row->average_score, 1); @endphp
                            <tr class="{{ Auth::id() == $row->user_id ? 'active-user' : '' }}">
                                <td class="text-center">
                                    @if ($index == 0)
                                        <div class="rank-badge rank-1"><i class="bi bi-trophy-fill"></i></div>
                                    @elseif($index == 1)
                                        <div class="rank-badge rank-2"><i class="bi bi-trophy-fill"></i></div>
                                    @elseif($index == 2)
                                        <div class="rank-badge rank-3"><i class="bi bi-trophy-fill"></i></div>
                                    @else
                                        <span class="fw-bold text-muted">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="text-start">
                                    <div class="user-box">
                                        <div class="avatar">{{ strtoupper(substr($row->user->name, 0, 1)) }}</div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $row->user->name }}</div>
                                            @if (Auth::id() == $row->user_id)
                                                <span class="badge bg-primary" style="font-size: 9px;">YOU</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-semibold">
                                        {{ $row->total_quizzes }} <small>Exams</small>
                                    </span>
                                </td>
                                <td>
                                    <div class="accuracy-text mb-1">{{ $avg }}%</div>
                                    <div class="progress avg-progress">
                                        <div class="progress-bar bg-primary" style="width: {{ $avg }}%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">{{ $row->total_correct }}</span>
                                    <small class="text-muted">/ {{ $row->total_attempted }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
