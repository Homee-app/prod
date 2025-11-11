@extends('admin.auth.index')
@section('admin-title', 'Forgot Password')

@section('content')
<div class="row g-0 login-wrapper">
    <div class="col-lg-6 login-left">
        <div class="text-center loginLeftImg"></div>
    </div>
    <div class="col-lg-6 col-md-12 login-right">
        <div class="loginRightImg">
            <div class="login-container">
                <div class="text-center mb-5"><img src="{{ asset('assets/images/logo-login.svg') }}"></div>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="whiteBg">
                        <h4>Forgot Password</h4>
                        <p>Please enter your email address, you will receive a link to create a new password via email.</p>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="position-relative">
                                <input type="email" class="form-control form-control-lg" placeholder="Please enter your email" name="email">
                            </div>
                            @error('email')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-lg btn-primary w-100">
                            Submit
                        </button>
                        {{-- Show success message --}}
                        @if (session('status'))
                        <div class="alert alert-success text-center mt-3">
                            {{ session('status') }}
                        </div>
                        @endif
                        <div class="text-center mt-3">
                            <a style="text-decoration:none" href="{{ route('login')}}" class="w-100 mt-2"><b> Back to Login </b></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    @if(session('status'))
    toastr.success("{{ session('status') }}");
    @endif
</script>

@endsection
