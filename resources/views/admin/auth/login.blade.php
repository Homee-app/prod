@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Login')
<div class="loginOuterBox">
    <div class="loginBox">
      <form method="POST" action="{{ route('admin.loginAuth') }}">
        @csrf
        <div class="row">
          <div class="col-md-12">
            <div class="mb-3">
              <img src="{{ asset('assets/images/logo.svg') }}">
            </div>
          </div>
  
          <div class="col-md-12">
            <div class="mb-3">
              <h1>Sign in</h1>
              <p>Enter in your login details to sign back into your dashboard.</p>
            </div>
          </div>
  
          <div class="col-md-12">
            <div class="mb-3">
              <div class="form-label">Email</div>
              <div class="form-group">
                <input type="text" class="form-control" placeholder="Enter Email" name="email" autocomplete="off">
                @error('email')
                  <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
  
          <div class="col-md-12">
            <div class="mb-3">
              <div class="form-label">Password</div>
              <div class="form-group input-img">
                <input type="password" class="form-control" placeholder="Enter Password" name="password" autocomplete="off">
                @error('password')
                  <div class="text-danger">{{ $message }}</div>
                @enderror
                <img src="{{ asset('assets/images/eye.svg') }}" alt="" class="eye" id="togglePassword">
              </div>
            </div>
          </div>
  
          <div class="col-md-12">
            <div class="mb-1">
              <div class="d-flex align-items-center">
                <button type="submit" class="btn btn-lg btn-primary">
                  Verify Me &nbsp; <img src="{{ asset('assets/images/btn-arrow.svg') }}" alt="">
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
    const togglePassword = document.getElementById("togglePassword");
    const password = document.querySelector('input[name="password"]'); // Find by name attribute

    togglePassword.addEventListener("click", function () {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);

        // Toggle icon image
        this.src = type === "password"
            ? "{{ asset('assets/images/eye.svg') }}"
            : "{{ asset('assets/images/eye-slash.svg') }}";
    });
});
</script>

@endsection
