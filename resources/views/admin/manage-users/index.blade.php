@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Users')

<div class="rightBlog">
    <div class="outerCentBox">
        <h2>Users</h2>
        <div class="contentBlog">
              <div class="row">
                <div class="col-md-12">
                     <div class="searchBar">
                        <div>
                            <form method="GET" action="{{ route('admin.userIndex') }}" class="d-flex align-items-end gap-2" onsubmit="return validateSearch()">
                                <div class="mb-3 headerserarch flex-grow-1">
                                    <input type="text" 
                                           class="form-control" 
                                           id="searchInput" 
                                           name="search"
                                           value="{{ request('search') }}"
                                           placeholder="Search by name or email">
                                    <i class="inputicon"><img src="{{ asset('assets/images/search.svg') }}"></i>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary me-2">Search</button>
                                    <button type="button" onclick="window.location.href='{{ route('admin.userIndex') }}'" class="btn btn-secondary">
                                        Reset
                                    </button>
                                </div>
                            </form>
                            
                            <script>
                            function validateSearch() {
                                const input = document.getElementById('searchInput').value.trim();
                                if (input === '') {
                                    return false; // Prevent form submit
                                }
                                return true; // Allow submit
                            }
                            </script>
                            
                        </div>
                        <div>
                            @if($users->hasPages())
                                <ul class="pagination mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($users->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link" aria-label="Previous">
                                                <span aria-hidden="true"><img src="{{ asset('assets/images/p-pre.svg') }}"></span>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $users->previousPageUrl() }}" aria-label="Previous">
                                                <span aria-hidden="true"><img src="{{ asset('assets/images/p-pre.svg') }}"></span>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                    $pageUrls = $users->getUrlRange(1, $users->lastPage());
                                @endphp
                                
                                @foreach ($pageUrls as $page => $url)
                                    @php
                                        // If page is 1, strip the query param
                                        $cleanUrl = ($page == 1) ? route('admin.userIndex', request()->except('page')) : $url;
                                    @endphp
                                
                                    @if ($page == $users->currentPage())
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $cleanUrl }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach
                                

                                    {{-- Next Page Link --}}
                                    @if ($users->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $users->nextPageUrl() }}" aria-label="Next">
                                                <span aria-hidden="true"><img src="{{ asset('assets/images/p-next.svg') }}"></span>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link" aria-label="Next">
                                                <span aria-hidden="true"><img src="{{ asset('assets/images/p-next.svg') }}"></span>
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            @endif
                        </div>
                     </div>
                </div>

                <div class="col-md-12">
                  <div class="table-responsive">
                        <table class="table align-middle userList" border="1">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Active Plan</th>
                                    <th>Subscription Plan</th>
                                    <th>Verification Status</th>
                                    <th>Account Status</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                  <tr>
                                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                      <span class="{{ $user->has_active_plan ? 'activePalnGreen' : 'activePalnGray' }}">
                                        {{ $user->has_active_plan ? 'Yes' : 'No' }}
                                      </span>
                                    </td>
                                    <td>{{ $user->is_subscribed ? 'Yes' : 'N/A' }}</td>
                                    <td>{{ ucwords($user?->userIdentity?->verification_status ?? 'Unverified') }}</td>
                                    <td> 
                                        <label class="switch">
                                            <input type="checkbox" data-status="{{ $user->status }}" @if ($user->status) checked @endif onchange="updateStatus({{ $user->id }},'User','{{ route('admin.updateStatus') }}',this)">
                                            <span class="slider">
                                            </span>
                                        </label>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.userdetails', $user->id) }}">
                                            <img src="{{ asset('assets/images/arrow_forward.svg') }}" alt="">
                                        </a>
                                    </td>
                                    
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="6" class="text-center">
                                        @if(request('search'))
                                            No users found matching "{{ request('search') }}".
                                        @else
                                            No users found.
                                        @endif
                                    </td>
                                  </tr>
                                @endforelse
                              </tbody>
                              
                        </table>  
                    </div>
                </div>

                {{-- Show pagination info --}}
                @if($users->hasPages())
                    <div class="col-md-12 mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                                </small>
                            </div>
                        </div>
                    </div>
                @endif

              </div>
        </div>
    </div>
</div>



@endsection