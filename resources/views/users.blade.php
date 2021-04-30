
@extends('skeleton')
@section('id', 'users')
@section('title', 'Users - URL Marker')

@section('main')
    @if ($prompt['type'] > 0)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            @if ($prompt['type'] === App\Http\Controllers\UserController::ACTIVATION_SUCCESS)
                You have successfully activated user {{ $prompt['target']['name'] }}.
            @elseif ($prompt['type'] === App\Http\Controllers\UserController::SUSPENSION_SUCCESS)
                You have successfully suspended user {{ $prompt['target']['name'] }}.
            @elseif ($prompt['type'] === App\Http\Controllers\UserController::RESTORATION_SUCCESS)
                You have successfully restored user {{ $prompt['target']['name'] }}.
            @elseif ($prompt['type'] === App\Http\Controllers\UserController::TERMINATION_SUCCESS)
                You have successfully terminated user {{ $prompt['target']['name'] }}.
            @elseif ($prompt['type'] === App\Http\Controllers\UserController::PROMOTION_SUCCESS)
                You have successfully promote user {{ $prompt['target']['name'] }} to admin user.
            @elseif ($prompt['type'] === App\Http\Controllers\UserController::DEMOTION_SUCCESS)
                You have successfully demote user {{ $prompt['target']['name'] }} to normal user.
            @else
                Your last operation is completed.
            @endif
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($prompt['type'] < 0)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            @if ($prompt['type'] === App\Http\Controllers\UserController::CONFLICT_ERROR)
                The last operation failed because it is not applicable. Usually it is caused by other
                admin updating the same user. You may refresh the page to check its current status.
            @elseif ($prompt['type'] === App\Http\Controllers\UserController::MISSING_ERROR)
                The last operation failed because the user cannot be found. The error is reported
                for resolution.
            @elseif ($prompt['type'] === App\Http\Controllers\UserController::BACKEND_ERROR)
                The last operation failed due to internal error. The error is reported for resolution.
                Please try again later.
            @else
                The last operation failed due to unknown error. The error is reported for resolution.
                Please try again later.
            @endif
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <header class="caption">
        <div class="caption-title">Users</div>
        <div class="caption-actions iconbar" role="toolbar"><!--
            @if ($urls['head'] !== null)
                --><a class="iconbar-item iconbar-icon" href="{{ $urls['head'] }}"><span class="fas fa-angle-double-left"></span></a><!--
            @else
                --><label class="iconbar-placeholder iconbar-icon"><span class="fas fa-angle-double-left"></span></label><!--
            @endif
            @if ($urls['prev'] !== null)
                --><a class="iconbar-item iconbar-icon" href="{{ $urls['prev'] }}"><span class="fas fa-angle-left"></span></a><!--
            @else
                --><label class="iconbar-placeholder iconbar-icon"><span class="fas fa-angle-left"></span></label><!--
            @endif
            @if ($urls['next'] !== null)
                --><a class="iconbar-item iconbar-icon" href="{{ $urls['next'] }}"><span class="fas fa-angle-right"></span></a><!--
            @else
                --><label class="iconbar-placeholder iconbar-icon"><span class="fas fa-angle-right"></span></label><!--
            @endif
            --><span class="iconbar-separator"></span><!--
            --><a class="iconbar-item iconbar-icon" href="{{ $urls['self'] }}"><span class="fas fa-sync"></span></a><!--
        --></div>
    </header>

    <table class="table model-table">
        <tbody>
            @foreach ($users as $user)
                <tr class="users">
                    <td class="label pl-3" title="{{ $user->name }}">
                        <div class="text-truncate">
                            @if ($user->role === App\Models\User::ADMIN)
                                <span class="icon"><span class="fas fa-user-tie"></span></span>
                            @else
                                <span class="icon"><span class="fas fa-user"></span></span>
                            @endif
                            {{ $user->name }}
                        </div>
                    </td>
                    <td class="status font-weight-lighter">
                        @if ($user->status === App\Models\User::CREATED)
                            Created
                        @elseif ($user->status === App\Models\User::ACTIVE)
                            Active
                        @elseif ($user->status === App\Models\User::SUSPENDED)
                            Suspended
                        @elseif ($user->status === App\Models\User::TERMINATED)
                            Terminated
                        @else
                            Unknown
                        @endif
                    </td>
                    <td class="actions pr-3 iconbar"><!--
                        @if ($user->status === App\Models\User::CREATED)
                            --><form method="POST" action="{{ action("UserController@update", [ 'id' => $user->id ]) }}">
                                @method("PATCH")
                                @csrf
                                <input type="hidden" name="type" value="activate" />
                                <input type="hidden" name="offset" value="{{ $offset }}" />
                                <button class="iconbar-item iconbar-icon" type="submit" title="Activate this user"><span class="fas fa-check"></span></button>
                            </form><!--
                        @elseif ($user->status === App\Models\User::ACTIVE)
                            @if ($user->role === App\Models\User::NORMAL)
                                --><form method="POST" action="{{ action("UserController@update", [ 'id' => $user->id ]) }}">
                                    @method("PATCH")
                                    @csrf
                                    <input type="hidden" name="type" value="promote" />
                                    <input type="hidden" name="offset" value="{{ $offset }}" />
                                    <button class="iconbar-item iconbar-icon" type="submit" title="Promote this user to admin user"><span class="fas fa-arrow-up"></span></button>
                                </form><!--
                            @elseif ($user->role === App\Models\User::ADMIN)
                                --><form method="POST" action="{{ action("UserController@update", [ 'id' => $user->id ]) }}">
                                    @method("PATCH")
                                    @csrf
                                    <input type="hidden" name="type" value="demote" />
                                    <input type="hidden" name="offset" value="{{ $offset }}" />
                                    <button class="iconbar-item iconbar-icon" type="submit" title="Demote this user to normal user"><span class="fas fa-arrow-down"></span></button>
                                </form><!--
                            @endif
                            --><form method="POST" action="{{ action("UserController@update", [ 'id' => $user->id ]) }}">
                                @method("PATCH")
                                @csrf
                                <input type="hidden" name="type" value="suspend" />
                                <input type="hidden" name="offset" value="{{ $offset }}" />
                                <button class="iconbar-item iconbar-icon" type="submit" title="Suspend this user temporarily"><span class="fas fa-lock"></span></button>
                            </form><!--
                            --><form method="POST" action="{{ action("UserController@update", [ 'id' => $user->id ]) }}">
                                @method("PATCH")
                                @csrf
                                <input type="hidden" name="type" value="issue" />
                                <input type="hidden" name="offset" value="{{ $offset }}" />
                                <button class="iconbar-item iconbar-icon" type="submit" title="Issue a reset token to the user"><span class="fas fa-ticket-alt"></span></button>
                            </form><!--
                            --><form method="POST" action="{{ action("UserController@update", [ 'id' => $user->id ]) }}">
                                @method("PATCH")
                                @csrf
                                <input type="hidden" name="type" value="terminate" />
                                <input type="hidden" name="offset" value="{{ $offset }}" />
                                <button class="iconbar-item iconbar-icon" type="submit" title="Terminate this user permanently"><span class="fas fa-trash"></span></button>
                            </form><!--
                        @elseif ($user->status === App\Models\User::SUSPENDED)
                            --><form method="POST" action="{{ action("UserController@update", [ 'id' => $user->id ]) }}">
                                @method("PATCH")
                                @csrf
                                <input type="hidden" name="type" value="restore" />
                                <input type="hidden" name="offset" value="{{ $offset }}" />
                                <button class="iconbar-item iconbar-icon" type="submit" title="Restore this user from suspension"><span class="fas fa-unlock"></span></button>
                            </form><!--
                            --><form method="POST" action="{{ action("UserController@update", [ 'id' => $user->id ]) }}">
                                @method("PATCH")
                                @csrf
                                <input type="hidden" name="type" value="terminate" />
                                <input type="hidden" name="offset" value="{{ $offset }}" />
                                <button class="iconbar-item iconbar-icon" type="submit" title="Terminate this user permanently"><span class="fas fa-trash"></span></button>
                            </form><!--
                        @endif
                    --></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

