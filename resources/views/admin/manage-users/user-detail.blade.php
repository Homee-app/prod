@extends('admin.layout.index')
@section('content')
@section('admin-title', 'User Details')


<div id="loader-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999; background:rgba(255,255,255,0.7); text-align:center;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">
        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Processing Verification, please wait...</p>
    </div>
</div>
<div class="rightBlog">
    <div class="outerCentBox">
        <h2><a href="{{ route('admin.userIndex') }}"><img class="me-2" src="{{ asset('assets/images/back_arr.svg') }}"></a> Users / View User
        </h2>
        <div class="contentBlog p-0">
            <div class="row">
                <div class="col-md-12">

                    <div class="d-flex align-items-start leftTabBar">

                        <div class="nav flex-column nav-pills me-3 leftSubTab" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <button class="nav-link active" id="v-pills-General-tab" data-bs-toggle="pill"
                                data-bs-target="#v-pills-General" type="button" role="tab"
                                aria-controls="v-pills-General" aria-selected="true">General <i><img
                                        src="{{ asset('assets/images/right_a.svg') }}"></i></button>
                            <button class="nav-link" id="v-pills-Transactions-tab" data-bs-toggle="pill"
                                data-bs-target="#v-pills-Transactions" type="button" role="tab"
                                aria-controls="v-pills-Transactions" aria-selected="false">Transactions <i><img
                                        src="{{ asset('assets/images/right_a.svg') }}"></i></button>
                            <button class="nav-link" id="v-pills-Identity-tab" data-bs-toggle="pill"
                                data-bs-target="#v-pills-Identity" type="button" role="tab"
                                aria-controls="v-pills-Identity" aria-selected="false">Identity Verification <i><img
                                        src="{{ asset('assets/images/right_a.svg') }}"></i></button>
                        </div>

                        <div class="tab-content" id="v-pills-tabContent">

                            <div class="tab-pane fade show active" id="v-pills-General" role="tabpanel"
                                aria-labelledby="v-pills-General-tab" tabindex="0">
                                <h4>General</h4>

                                <div class="d-flex flex-wrap">
                                    <div class="profileImgBox me-4"> 
                                        <img src="{{ $user->profile_photo ? asset($user->profile_photo) : asset('assets/images/profile.jpg') }}" alt="User Image">
                                    </div>
                                    <div>
                                        <p><span>Name: </span> {{ $user->first_name }} {{ $user->last_name }}</p>
                                        <p><span>Email: </span> {{ $user->email }}</p>
                                        {{-- <p><span>Account created: </span> {{ $user->created_at->format('g:ia jS F Y') }} --}}
                                        </p>
                                        <p><span>Last active: </span> 4:01pm 4th June 2024</p>
                                        <p><span>Date of birth: </span>
                                            {{ \Carbon\Carbon::parse($user->dob)->format('jS F Y') }}</p>
                                    </div>
                                </div>

                                <hr>

                                <div class="subDetailsBox">
                                    @if(isset($all_questions_data))
                                        @forelse ($all_questions_data as $questions)
                                            <div class="row">
                                                @forelse ($questions as $question)
                                                    @php
                                                        $options = $question['options'] ?? [];
                                                        $answers = $question['user_answer'] ?? [];
                                                        $type = $question['type_for_web'];
                                                    @endphp

                                                    <div class="col-md-12">
                                                        <h6>{{ $question['title_for_web'] ?? 'No title' }}</h6>
                                                    </div>

                                                    @if(!empty($answers))
                                                        @foreach($answers as $answer)
                                                            <div class="mb-3 col-md-6">
                                                                @php
                                                                    $option = collect($options)->firstWhere('id', $answer['option_id'] ?? null);
                                                                @endphp

                                                                @switch($type)
                                                                    @case(1)
                                                                        <p>{{ $option['label_for_web'] ?? 'Option not found' }}</p>
                                                                        <p>{{ $option['answer'] ?? '' }}</p>
                                                                        @break

                                                                    @case(2)
                                                                        <p>{{ $option['label_for_web'] ?? 'Option not found' }}</p>
                                                                        @break

                                                                    @case(3)
                                                                        <p>{{ $answer['answer'] ?? 'N/A' }}</p>
                                                                        @break
                                                                    @case(4)
                                                                        <p>{{ $answer['answer'] ?? 'N/A' }}</p>
                                                                        @break

                                                                    @default
                                                                        <p>N/A</p>
                                                                @endswitch
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="mb-3 col-md-6">
                                                            <label>No Answer</label>
                                                            <p>N/A</p>
                                                        </div>
                                                    @endif
                                                @empty
                                                @endforelse
                                                <hr>
                                            </div>
                                        @empty
                                        @endforelse
                                    @else


                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Your lifestyle and habits</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>What gender best describes you?</label>
                                                <p>Woman</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>What sexuality best describes you?</label>
                                                <p>Straight</p>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Your employment</h6>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label>What is your current employment status?</label>
                                                <p>Full-time</p>
                                            </div>
                                        </div>

                                        <hr>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Your lifestyle and habits</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>Do you drink?</label>
                                                <p>Yes</p>
                                            </div>
                                            <div class="mb-3">
                                                <label>Do you have any dietary requirements?</label>
                                                <p>Lactose Intolerant, Vegetarian</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>Do you smoke?</label>
                                                <p>NO</p>
                                            </div>

                                        </div>

                                        <hr>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Your Lifestyle</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>Morning Person</label>
                                                <p>Yes</p>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>Clean Freak</label>
                                                <p>NO</p>
                                            </div>

                                        </div>

                                        <hr>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Your Perfect Home</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>What suburbs are you interested in?</label>
                                                <p>Coorparoo, 4151</p>
                                            </div>
                                            <div class="mb-3">
                                                <label>What date are you available?</label>
                                                <p>10th June 2020</p>
                                            </div>

                                            <div class="mb-3">
                                                <label>Rental History</label>
                                                <div class="form-check">

                                                    <input class="form-check-input" type="checkbox" value=""
                                                        id="checkDefault">
                                                    <label class="form-check-label mt-1 ms-1" for="checkDefault">
                                                        Rental history available
                                                    </label>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>What is your preferred stay length?</label>
                                                <p>2 weeks</p>
                                            </div>
                                            <div class="mb-3">
                                                <label>What is your rental budget range?</label>
                                                <p>Rent min $20.00 - Rent max $50.00</p>
                                            </div>
                                            <div class="mb-3">
                                                <label>Property Preferences</label>
                                                <p>Room Furnishings - <strong>Flexible</strong> </p>
                                            </div>

                                        </div>

                                        <hr>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Tell Us More About You</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>What languages do you speak?</label>
                                                <p>English</p>
                                            </div>
                                            <div class="mb-3">
                                                <label>Open to Overnight Guests</label>
                                                <p>Yes</p>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label>Political Views</label>
                                                <p>Liberal</p>
                                            </div>
                                            <div class="mb-3">
                                                <label>Pets</label>
                                                <p>I have a pet</p>
                                            </div>

                                        </div>

                                        <hr>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Your interests</h6>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label>Choose 3 things you’re really into</label>
                                                <p>Coffee , Foodie, Camping</p>
                                            </div>


                                        </div>


                                        <hr>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Your religious beliefs</h6>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label>What is your religion?</label>
                                                <p>Abc</p>
                                            </div>


                                        </div>


                                        <hr>
                                    </div>
                                    @endif
                                </div>



                            </div>
                            <div class="tab-pane fade" id="v-pills-Transactions" role="tabpanel"
                                aria-labelledby="v-pills-Transactions-tab" tabindex="0">
                                <h4>Transactions</h4>


                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table align-middle userList" border="1">
                                            <thead>
                                                <tr>
                                                    <th>Payment</th>
                                                    <th>Date</th>
                                                    <th>Price</th>
                                                    <th>End Date</th>
                                                    <th>&nbsp;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Boosts</td>
                                                    <td>21 March 2025</td>
                                                    <td>$2.99</td>
                                                    <td>24 March 2025</td>
                                                    <td class="text-end"><a href="#"><img
                                                                src="assets/images/arrow_forward.svg"
                                                                alt=""></a></td>
                                                </tr>
                                                <tr>
                                                    <td>Boosts</td>
                                                    <td>21 March 2025</td>
                                                    <td>$2.99</td>
                                                    <td>-</td>
                                                    <td class="text-end"><a href="#"><img
                                                                src="assets/images/arrow_forward.svg"
                                                                alt=""></a></td>
                                                </tr>
                                                <tr>
                                                    <td>Boosts</td>
                                                    <td>21 March 2025</td>
                                                    <td>$2.99</td>
                                                    <td>24 March 2025</td>
                                                    <td class="text-end"><a href="#"><img
                                                                src="assets/images/arrow_forward.svg"
                                                                alt=""></a></td>
                                                </tr>
                                                <tr>
                                                    <td>Boosts</td>
                                                    <td>21 March 2025</td>
                                                    <td>$2.99</td>
                                                    <td>24 March 2025</td>
                                                    <td class="text-end"><a href="#"><img
                                                                src="assets/images/arrow_forward.svg"
                                                                alt=""></a></td>
                                                </tr>
                                                <tr>
                                                    <td>Boosts</td>
                                                    <td>21 March 2025</td>
                                                    <td>$2.99</td>
                                                    <td>24 March 2025</td>
                                                    <td class="text-end"><a href="#"><img
                                                                src="assets/images/arrow_forward.svg"
                                                                alt=""></a></td>
                                                </tr>


                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                            </div>



                            <div class="tab-pane fade" id="v-pills-Identity" role="tabpanel"
                                aria-labelledby="v-pills-Identity-tab" tabindex="0">
                                <h4>Identity Verification</h4>

                                @if ($user->userIdentity)
                                <div class="id-row d-flex flex-wrap">
                                    <div class="idBlog me-3">
                                        @if ($user->userIdentity && $user->userIdentity->front_of_id_path)
                                            <img src="{{ asset($user->userIdentity->front_of_id_path) }}" alt="Front of ID">
                                        @endif
                                        <h6 class="mt-2 text-center">Front of ID</h6>
                                    </div>
                                    <div class="idBlog">
                                        @if ($user->userIdentity && $user->userIdentity->back_of_id_path)
                                            <img src="{{ asset($user->userIdentity->back_of_id_path) }}" alt="Back of ID">
                                        @endif
                                        <h6 class="mt-2 text-center">Back of ID</h6>
                                    </div>
                                </div>
                                @endif

                                <hr>
    
                                <p>
                                  <span>Identity Verification: </span>
                                  @if ($user->userIdentity)
                                      {{-- Display status if userIdentity exists --}}
                                      @if ($user->userIdentity->verification_status == 'approved')
                                          <a href="#" class="btn btn-success btnVerifiedUser">Verified User</a>
                                      @elseif($user->userIdentity->verification_status == 'pending')
                                          <a href="#" class="btn btn-warning btnVerifiedUser">Pending Verification</a>
                                      @elseif($user->userIdentity->verification_status == 'rejected')
                                          <a href="#" class="btn btn-danger btnVerifiedUser">Rejected</a>
                                      @elseif($user->userIdentity->verification_status == 're-submit')
                                          <a href="#" class="btn btn-secondary btnVerifiedUser">Re-submit Requested</a>
                                      @else
                                          {{-- Fallback for any other unexpected status --}}
                                          <a href="#" class="btn btn-light btnVerifiedUser">Status Unknown</a>
                                      @endif
                              
                                      {{-- Show Verify User button ONLY if status is NOT 'approved' --}}
                                      @if ($user->userIdentity->verification_status != 'approved' && $user->userIdentity->verification_status != 'rejected')
                                          <a href="#" class="btn btn-primary btnVerifyUser" data-bs-toggle="modal"
                                             data-bs-target="#verifyUser" data-user-id="{{ $user->id }}">Verify User</a>
                                      @endif
                              
                                  @else
                                      <a href="#" class="btn btn-light btnVerifiedUser">Not Submitted</a> 
                                  @endif
                                </p>
                              
                              @if ($user->userIdentity)
                                <p id="v-user-Identity-verified-date">
                                    <span>Date Verified: </span>
                                    @if ($user->userIdentity && $user->userIdentity->verification_status == 'approved' && $user->userIdentity->verified_at)
                                        {{ \Carbon\Carbon::parse($user->userIdentity->verified_at)->format('g:ia jS F Y') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                              
                                <p  id="v-user-Identity-verified-id" ><span>ID verification: </span> {{ $user->userIdentity->id_type }}</p>
                              @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>




<div class="modal popupModal fade" id="verifyUser" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel"> Verify</h1>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="modalBlog">
                            <div class="mb-3">
                                <p>{{ __('messages.confirm_verify',['item' => 'user'])}} </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer ">
                {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Reject</button> --}}
                <button type="button" class="btn btn-secondary" id="rejectVerificationBtn" data-user-id="{{ $user->id }}">Reject</button>
                <button type="button" class="btn btn-primary" id="confirmVerificationBtn" data-user-id="{{ $user->id }}">Verify</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/advancedFormat.js"></script>

@endpush

@push('scripts')
<script>
    const statusUpdateUrlTemplate = @json(route('admin.verifyIdentity', ['user' => '__USER_ID__']));
    dayjs.extend(dayjs_plugin_advancedFormat);
    // Shared AJAX function
    function updateIdentityStatus(userId, status) {
        const url = statusUpdateUrlTemplate.replace('__USER_ID__', userId);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status,
            },
            success: function(response) {
                $('#loader-overlay').hide();
                const updateData = response?.data;

                if (response.success) {
                    toastr.success(response.message || `User status updated to ${status}`, 'Success!');

                    // Optional UI feedback (e.g., change badge text before reload)
                    let statusText = status === 'approved' ? 'Verified User' : 'Rejected';
                    let statusClass = status === 'approved' ? 'btn-success' : 'btn-danger';
                    let verified_at = new Date(updateData.verified_at);
                        verified_at =   dayjs(verified_at).format('h:mma Do MMMM YYYY')
                    $('#v-pills-Identity p span:contains("Identity Verification")')
                        .next('a.btnVerifiedUser')
                        .removeClass('btn-warning btn-danger btn-secondary btn-light btn-success')
                        .addClass(statusClass)
                        .text(statusText);

                        $('#v-user-Identity-verified-id').html('<span>ID verification: </span>' + updateData.id_type);
                        $('#v-user-Identity-verified-date').html('<span>Date Verified: </span>' + verified_at);

                     $('.btnVerifyUser').hide();
                    // Reload after short delay
                } else {
                    toastr.error(response.message || 'Failed to update user status.');
                }
            },
            error: function(xhr) {
                $('#loader-overlay').hide();
                toastr.error('An error occurred during the request.');
                console.info(xhr.responseText);
            }
        });

        $('#verifyUser').modal('hide'); // Close modal in any case
    }

    $(document).ready(function() {
        $('#confirmVerificationBtn').on('click', function() {
            $('#loader-overlay').show();
            const userId = $(this).data('user-id');
            updateIdentityStatus(userId, 'approved');
        });

        $('#rejectVerificationBtn').on('click', function() {
            $('#loader-overlay').show();
            const userId = $(this).data('user-id');
            updateIdentityStatus(userId, 'rejected');
        });
    });
</script>
@endpush

@endsection