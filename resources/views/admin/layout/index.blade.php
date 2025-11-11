<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin : @yield('admin-title')</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">

    {{-- TOASTR --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">


    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/fav-icon.png') }}">
    @stack('styles')

</head>

<body class="innerbody">

    @if (session('flash-error'))
        <span class="admin-toastr" onclick="toastr_alert('Error','{{ session()->get('flash-error') }}','error')"></span>
    @endif
    @if (session('flash-success'))
        <span class="admin-toastr"
            onclick="toastr_alert('Success','{{ session()->get('flash-success') }}','success')"></span>
    @endif
    <div class="leftBar">  
        <ul>
            <li class="{{ request()->routeIs('admin.userIndex') ? 'active' : '' }}">
                <a href="{{ route('admin.userIndex') }}" class="{{ request()->routeIs('admin.userIndex') ? 'active' : '' }}" data-toggle="tooltip" data-placement="top" title="User Management">
                    <img src="{{ asset('assets/images/user_icon.svg') }}" alt="User List">
                </a>
            </li>
           {{-- <li><a href="#"><img src="{{ asset('assets/images/info_icon.svg') }}"></a></li>
           <li><a href="#"><img src="{{ asset('assets/images/tick_icon.svg') }}"></a></li> --}}
        </ul>
        <div class="logoutBtn">
         <a href="#" data-bs-toggle="modal" data-bs-target="#logout"><img src="{{ asset('assets/images/logout.svg') }}" alt=""></a>
        </div>
     </div>

    <!-- Modal -->
    <div class="modal popupModal fade" id="logout" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Sign Out</h1>
            </div>
            <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                <div class="modalBlog">             
                    <div class="mb-3">
                    <p>Are you sure you want to sign out of the admin panel? You will need to sign back in </p>
                    </div>
                </div>          
                </div>
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Stay Signed In</button>
                <a href="{{route('admin.logout')}}"><button type="submit" class="btn btn-secondary">Sign Out</button></a>

            </div>
        </div>
        </div>
    </div>
  
    <!--header part-->
    <!--PAGE CONTENT-->
    @yield('content')
    <!--PAGE CONTENT-->
    <script>
        var csrf = "{{ csrf_token() }}";
        var baseUrl = "{{ url('/') }}";
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    {{-- TOASTR --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="{{ asset('assets/js/custom.js') }}"></script>
    @stack('scripts')
</body>

</html>
