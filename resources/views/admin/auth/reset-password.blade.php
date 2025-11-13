@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Reset Password')

<div class="row g-0 login-wrapper">
    <div class="col-lg-6 login-left">
        <div class="text-center loginLeftImg"></div>
    </div>
    <div class="col-lg-6  col-md-12 login-right">
        <div class="loginRightImg">
            <div class="login-container">
                <div class="text-center mb-5"><img src="{{ asset('assets/img/logo.png') }}"></div>
                <form method="post" action="{{ route('admin.resetPassword', $token) }}">
                    @csrf
                    <div class="whiteBg">
                        <h4>Reset password</h4>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New password</label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" placeholder="Enter password"
                                    name="new_password" value="admin12345">
                            </div>
                            @error('new_password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm password</label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg"
                                    placeholder="Enter confirm password" name="new_password_confirmation"
                                    value="admin12345">
                            </div>
                            @error('new_password_confirmation')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="btn-block">
                            <button type="submit" class="btn btn-lg btn-primary w-100">
                                Continue
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
