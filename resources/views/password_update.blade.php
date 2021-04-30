
@extends('skeleton')
@section('id', 'password_update')
@section('title', 'Change Password - URL Marker')

@section('main')
    <header class="caption">
        <div class="caption-title">Change Password</div>
    </header>

    <form class="m-3" method="POST" action="{{ action('PasswordUpdateController@handle') }}">
        @csrf

        <p>
            To change your password, please complete the fields below and press the change
            button. After the password change, you will be logged out from the system. You
            need to login back into the system with your new password.
        </p>

        @if ($prompt['type'] < 0)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @if ($prompt['type'] === App\Http\Controllers\ChangePasswordController::EMPTY_PASSWORD_ERROR)
                    The previous password change request failed because the new password is empty.
                @elseif ($prompt['type'] === \App\Http\Controllers\ChangePasswordController::MISMATCH_PASSWORD_ERROR)
                    The previous password change request failed because the new password does not match
                    with the confirmatory one.
                @elseif ($prompt['type'] === \App\Http\Controllers\ChangePasswordController::AUTH_ERROR)
                    The previous password change request failed because the current password is
                    incorrect.
                @else
                    The previous password change request cannot be handled due to internal error. The error is
                    reported for resolution. Please try again later.
                @endif
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="form-group">
            <label for="current">Current Password</label>
            <input type="password" class="form-control" id="current" name="current">
        </div>
        <div class="form-group">
            <label for="incoming">New Password</label>
            <input type="password" class="form-control" id="incoming" name="incoming">
        </div>
        <div class="form-group mb-4">
            <label for="repeat">Confirm New Password</label>
            <input type="password" class="form-control" id="repeat" name="repeat">
        </div>

        <div class="buttons mt-4">
            <button type="submit" class="btn btn-primary">Change</button>
        </div>
    </form>
@endsection

