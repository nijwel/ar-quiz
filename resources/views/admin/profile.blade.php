@extends('layouts.app')

@section('content')
    <style>
        :root {
            --admin-gradient: linear-gradient(135deg, #4e54c8 0%, #764ba2 100%);
        }

        .profile-header {
            background: var(--admin-gradient);
            height: 150px;
            border-radius: 20px 20px 0 0;
        }

        .profile-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: -75px;
            overflow: hidden;
        }

        .avatar-wrapper {
            position: relative;
            display: inline-block;
        }

        .profile-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            background: white;
        }

        .nav-pills .nav-link {
            color: #666;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .nav-pills .nav-link.active {
            background: var(--admin-gradient);
            color: white !important;
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.3);
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #e0e0e0;
        }

        .form-control:focus {
            border-color: #4e54c8;
            box-shadow: 0 0 0 0.2rem rgba(78, 84, 200, 0.1);
        }

        .btn-update {
            background: var(--admin-gradient);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.4);
            color: white;
        }
    </style>

    <div class="container py-5">
        <div class="profile-header"></div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card profile-card mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="avatar-wrapper mb-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4e54c8&color=fff&size=128"
                                alt="Admin" class="profile-avatar">
                        </div>
                        <h3 class="fw-bold mb-1">{{ auth()->user()->name }}</h3>
                        <p class="text-muted"><span class="badge bg-soft-primary text-primary px-3">Administrator</span></p>
                    </div>

                    <div class="card-footer bg-white border-0 pb-4">
                        <ul class="nav nav-pills justify-content-center gap-2" id="profileTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="details-tab" data-bs-toggle="pill" href="#details"
                                    role="tab">Details</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="edit-tab" data-bs-toggle="pill" href="#edit"
                                    role="tab">Update Info</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="password-tab" data-bs-toggle="pill" href="#password"
                                    role="tab">Password</a>
                            </li>
                        </ul>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    </div>
                @endif

                <div class="tab-content" id="profileTabContent">
                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                            <h5 class="fw-bold mb-4">Account Information</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold">Full Name</label>
                                    <p class="fs-5 fw-medium">{{ auth()->user()->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold">Email Address</label>
                                    <p class="fs-5 fw-medium">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold">Joined Date</label>
                                    <p class="fs-5 fw-medium">{{ auth()->user()->created_at->format('d M, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="edit" role="tabpanel">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                            <h5 class="fw-bold mb-4">Update Profile Details</h5>
                            <form action="{{ route('admin.profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ auth()->user()->name }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ auth()->user()->email }}" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-update mt-3">Save Changes</button>
                            </form>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                            <h5 class="fw-bold mb-4">Change Password</h5>
                            <form action="{{ route('admin.password.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">New Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" class="form-control"
                                            required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-update mt-3">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
