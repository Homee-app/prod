

@extends('admin.auth.index')
@section('content')
@section('admin-title', 'Login')
<body class="MainBody">
    <div class="loginOuterBox">
      <div class="loginBox">
        <form method="POST" action="{{ route('admin.verifyOtp') }}">
          @csrf
          <div class="row">
            <div class="col-md-12">
              <div class="mb-3">
                <img src="{{ asset('assets/images/logo.svg') }}" alt="Logo">
              </div>
            </div>
  
            <div class="col-md-12">
              <div class="mb-3">
                <h1>2 Factor Authentication</h1>
                <p class="otpMessage">
                  We’ve sent a 6-digit code to your designated email. Enter it below to verify your sign in.
                </p>
              </div>
            </div>
  
            <div class="col-md-12">
              <div class="mb-3">
                <div class="form-group otpBox d-flex gap-2 justify-content-center">
                    @for ($i = 0; $i < 6; $i++)
                      <input type="text" name="otp[]" maxlength="1" pattern="\d*" inputmode="numeric"
                             class="form-control text-center otp-input" {{ $i === 0 ? 'autofocus' : '' }}>
                    @endfor
                  </div>
                  <div id="otpErrorMessage" style="color: red; margin-top: 5px;"></div> {{-- 🟠 Error message --}}
              </div>
            </div>
  
            <div class="col-md-12">
              <div class="mb-1">
                <p>Didn’t receive a code? <a href="#" id="resendOtpLink" class="greenALink">Resend OTP</a></p>
                <span id="resendOtpMessage" class="text-success d-none">OTP has been sent!</span>
              </div>
            </div>
  
            <div class="col-md-12">
              <div class="mb-1">
                <div class="d-flex align-items-center">
                  <button type="submit" class="btn btn-primary">
                    Sign In &nbsp; <img src="{{ asset('assets/images/btn-arrow.svg') }}" alt="">
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  
    {{-- OTP Input Auto Move Script --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
      const otpSentAt = {{ session('admin_otp_sent_at', now()->timestamp) }};
      const resendCooldown = 30; // seconds
  $(document).ready(function () {
    $('.otp-input').on('input', function () {
      const input = $(this);
      const value = input.val().replace(/[^0-9]/g, '');

      // Keep only the last digit
      input.val(value.charAt(value.length - 1));

      // Move to next input if a digit is entered
      if (value.length === 1) {
        input.removeClass('is-invalid'); // 
        input.next('.otp-input').focus();
      }
    });

    $('.otp-input').on('keydown', function (e) {
      const input = $(this);
      if (e.key === 'Backspace' && input.val() === '') {
        input.prev('.otp-input').focus();
      }
    });

    $('form').on('submit', function (e) {
    let valid = true;
    let otpValue = '';
    $('#otpErrorMessage').text(''); // clear previous message

    $('.otp-input').each(function () {
        const val = $(this).val();
        otpValue += val;
        if (val === '') {
            valid = false;
        }
    });

    if (!valid || otpValue.length !== 6) {
        e.preventDefault();
        $('#otpErrorMessage').text('Please enter all 6 digits of the OTP.');
    }
});


  });
  $(document).ready(function () {
  const now = Math.floor(Date.now() / 1000); // current timestamp in seconds
  const timeSinceSent = now - otpSentAt;
  const remaining = Math.max(0, resendCooldown - timeSinceSent);

  const $link = $('#resendOtpLink');
  if (remaining > 0) {
    $link.addClass('disabled').css('pointer-events', 'none');
    startCountdown(remaining, $link);
  }

  $('#resendOtpLink').on('click', function (e) {
  e.preventDefault();

  const $link = $(this);
  const $message = $('#resendOtpMessage');

  // Show "Sending..." and disable the link immediately
  $link.text('Sending...').addClass('disabled').css('pointer-events', 'none');

  $.ajax({
    url: "{{ route('admin.resendOtp') }}",
    type: "POST",
    data: { _token: "{{ csrf_token() }}" },
    success: function (response) {
      if (response.status) {
        toastr_alert('Success', response.message, 'success');
        startCountdown(resendCooldown, $link); // Start the countdown only on success
      } else {
        toastr_alert('Error', response.message, 'error');
        $link.text('Send again').removeClass('disabled').css('pointer-events', 'auto');
      }
    },
    error: function () {
      $message.removeClass('d-none')
              .text('Something went wrong. Please try again.')
              .css('color', 'red');

      $link.text('Send again').removeClass('disabled').css('pointer-events', 'auto');
    }
  });
});


  function startCountdown(seconds, $link) {
    let timeLeft = seconds;
    $link.text(`Resend OTP in ${timeLeft}s`);

    const timer = setInterval(() => {
      timeLeft--;
      $link.text(`Resend OTP in ${timeLeft}s`);

      if (timeLeft <= 0) {
        clearInterval(timer);
        $link.text('Resend OTP').removeClass('disabled').css('pointer-events', 'auto');
      }
    }, 1000);
  }
});

</script>
  </body>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
  </body>
@endsection
