@extends($layout)

@section('title', $title)
@section('page_title', $title)

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card {{ $portal === 'admin' ? 'table-card' : 'portal-card' }}">
            <div class="card-header bg-white">
                <strong>Account</strong>
            </div>

            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $user->name }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $user->email }}</dd>

                    <dt class="col-sm-4">Role</dt>
                    <dd class="col-sm-8">{{ ucfirst($user->role) }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">{{ ucfirst($user->account_status ?: 'active') }}</dd>

                    <dt class="col-sm-4">Last Login</dt>
                    <dd class="col-sm-8">
                        {{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : '-' }}
                        @if ($user->last_login_ip)
                            <div class="text-muted small">{{ $user->last_login_ip }}</div>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card {{ $portal === 'admin' ? 'table-card' : 'portal-card' }}">
            <div class="card-header bg-white">
                <strong>Change Password</strong>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ $updateRoute }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                        @error('current_password') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        <div class="text-muted small">Minimal 10 karakter, huruf besar/kecil, angka, dan simbol.</div>
                        @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>

                    <button class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
