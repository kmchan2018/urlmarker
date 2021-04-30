
@extends('skeleton')
@section('id', 'password_reset')
@section('title', 'Forget My Password')

@section('main')
    <header class="caption">
        <div class="caption-title">Forget My Password</div>
    </header>

    <form action="{{ action('PasswordResetController@handle') }}" method="POST" class="m-3">
        @csrf

        @if ($prompt['type'] < 0)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @if ($prompt['type'] === App\Http\Controllers\PasswordResetController::EMPTY_CODE_ERROR)
                    Password reset failed due to empty invite code.
                @elseif ($prompt['type'] === App\Http\Controllers\PasswordResetController::INVALID_CODE_ERROR)
                    Password reset failed due to invalid invite code.
                @elseif ($prompt['type'] === App\Http\Controllers\PasswordResetController::EMPTY_NAME_ERROR)
                    Password reset failed due to invalid user name.
                @elseif ($prompt['type'] === App\Http\Controllers\PasswordResetController::INVALID_NAME_ERROR)
                    Password reset failed due to invalid user name.
                @elseif ($prompt['type'] === App\Http\Controllers\PasswordResetController::EMPTY_PASSWORD_ERROR)
                    Password reset failed due to empty password.
                @elseif ($prompt['type'] === App\Http\Controllers\PasswordResetController::WEAK_PASSWORD_ERROR)
                    Password reset failed due to weak password.
                @elseif ($prompt['type'] === App\Http\Controllers\PasswordResetController::MISMATCH_PASSWORD_ERROR)
                    Password reset failed due to mismatched password.
                @elseif ($prompt['type'] === App\Http\Controllers\PasswordResetController::BACKEND_ERROR)
                    Password reset failed due to system error.
                @elseif ($prompt['type'] === App\Http\Controllers\PasswordResetController::UNKNOWN_ERROR)
                    Password reset failed due to unknown error.
                @endif
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <p>
            If you forget your password, please contact the server owner and ask
            for a reset token. Once you have the token, you can reset your password
            without using the old password by completing the form underneath.
            <i>Be warned that your user will be locked during the reset procedure
            and no further login is possible until the password is reset.</i>
        </p>

        <div class="form-group">
            <label for="code">Reset Token</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ $prefill['code'] }}">
        </div>

        <div class="form-group">
            <label for="name">User Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $prefill['name'] }}">
        </div>

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="confirm">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm" name="confirm">
        </div>

        <div class="buttons mt-4">
            <button type="submit" class="btn btn-primary">Reset</button>
        </div>
    </form>
@endsection

