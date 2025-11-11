@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Users')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Edit User</h4>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="whiteBg">
                    <form action="{{ route('admin.userUpdate', $singleUser->id) }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="first-name" class="form-label">First name</label>
                                <input type="text" class="form-control" name="first_name"
                                    value="{{ $singleUser->first_name }}" placeholder="Enter first name"
                                    id="first-name">
                                @error('first_name')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last-name" class="form-label">Last name</label>
                                <input type="text" class="form-control" name="last_name"
                                    value="{{ $singleUser->last_name }}" placeholder="Enter last name" id="last-name">
                                @error('last_name')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="text" class="form-control" name="email"
                                    value="{{ $singleUser->email }}" placeholder="Enter email" id="email">
                                @error('email')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Enter password"
                                    id="password">
                                @error('password')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="profile_photo" class="form-label">Profile photo</label>
                                <input type="file" class="form-control" name="profile_photo" id="profile_photo">
                                @error('profile_photo')
                                    <div class="form-valid-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="1" {{ $singleUser->status == '1' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="0" {{ $singleUser->status == '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                                @error('status')
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
