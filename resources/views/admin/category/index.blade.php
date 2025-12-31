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
                <h2 class="fw-bold mb-1"><i class="bi bi-people-fill me-2"></i> Category</h2>
                <p class="mb-0 opacity-75">। ক্যাটেগরি</p>
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
                                <th>Name</th>
                                <th>Slug</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $key => $category)
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">{{ $categories->firstItem() + $key }}</td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $category->name }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $category->slug }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-bold text-dark fs-6">
                                            @if ($category->status == 1)
                                                <span class="stat-badge">Active</span>
                                            @else
                                                <span class="stat-badge">Inactive</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('category.edit', $category->id) }}"
                                            class="btn btn-sm btn-view shadow-sm">
                                            <i class="bi bi-pencil-fill me-1"></i> edit
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
                        Showing <span class="text-primary">{{ $categories->count() }}</span> of
                        <span class="text-primary">{{ $categories->total() }}</span> participants
                    </div>
                    <div>
                        {{ $categories->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
