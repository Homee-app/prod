@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Change Password')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Change Password</h4>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="whiteBg">
                    <form action="{{ route('admin.changePassword') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label for="current-password" class="form-label">Current Password</label>
                                <input type="text" class="form-control" name="current_password"
                                    placeholder="Enter current password" id="current-password">
                                @error('current_password')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="new-password" class="form-label">New Password</label>
                                <input type="text" class="form-control" name="new_password"
                                    placeholder="Enter password" id="new-password">
                                @error('new_password')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <input type="text" class="form-control" name="new_password_confirmation"
                                    placeholder="Enter confirm password" id="confirm-password">
                                @error('new_password_confirmation')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn admin-btn">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
