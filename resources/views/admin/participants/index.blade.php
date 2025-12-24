@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #764ba2;
            --quiz-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .page-header {
            background: var(--quiz-gradient);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 10px 20px rgba(78, 84, 200, 0.2);
        }

        .participant-card {
            border: none;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 700;
            color: #6c757d;
            border: none;
            padding: 15px;
        }

        .participant-avatar {
            width: 40px;
            height: 40px;
            background: var(--quiz-gradient);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .stat-badge {
            background: rgba(78, 84, 200, 0.1);
            color: var(--quiz-primary);
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .btn-view {
            background: var(--quiz-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 15px;
            transition: 0.3s;
        }

        .btn-view:hover {
            background: var(--quiz-secondary);
            color: white;
            transform: translateY(-2px);
        }

        .pagination {
            margin-top: 20px;
        }

        .page-item.active .page-link {
            background-color: var(--quiz-primary);
            border-color: var(--quiz-primary);
        }
    </style>

    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-people-fill me-2"></i> Participants</h2>
                <p class="mb-0 opacity-75">সিস্টেমে নিবন্ধিত সকল পরীক্ষার্থীদের তালিকা এখানে দেখুন।</p>
            </div>
            @if (session('success'))
                <div class="alert alert-light py-2 px-3 mb-0 border-0 shadow-sm text-success" style="border-radius: 10px;">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif
        </div>

        <div class="card participant-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#ID</th>
                                <th>Participant Name</th>
                                <th>Email Address</th>
                                <th class="text-center">Total Marks</th>
                                <th class="text-center">Exams Given</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($participants as $key => $participant)
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">{{ $participants->firstItem() + $key }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="participant-avatar me-3">
                                                {{ strtoupper(substr($participant->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $participant->name }}</div>
                                                <small class="text-muted">Joined:
                                                    {{ $participant->created_at->format('M Y') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="bi bi-envelope me-1"></i>
                                            {{ $participant->email }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="fw-bold text-success fs-5">{{ $participant->quiz_results_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="stat-badge">
                                            <i class="bi bi-journal-text me-1"></i>
                                            {{ $participant->quizzes_attempted_count }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('participant.view', $participant->id) }}"
                                            class="btn btn-view shadow-sm">
                                            <i class="bi bi-eye-fill me-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white border-top-0 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted small fw-semibold">
                        Showing <span class="text-primary">{{ $participants->count() }}</span> of
                        <span class="text-primary">{{ $participants->total() }}</span> participants
                    </div>
                    <div>
                        {{ $participants->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
