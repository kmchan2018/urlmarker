
@extends('skeleton')
@section('id', 'register')
@section('title', 'Become a New User')

@section('main')
    <header class="caption">
        <div class="caption-title">Become a New User</div>
    </header>

    <form action="{{ action('RegisterController@handle') }}" method="POST" class="m-3">
        @csrf

        @if ($prompt['type'] < 0)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @if ($prompt['type'] === App\Http\Controllers\RegisterController::EMPTY_CODE_ERROR)
                    Registration failed due to empty invite code.
                @elseif ($prompt['type'] === App\Http\Controllers\RegisterController::INVALID_CODE_ERROR)
                    Registration failed due to invalid invite code.
                @elseif ($prompt['type'] === App\Http\Controllers\RegisterController::EMPTY_NAME_ERROR)
                    Registration failed due to empty user name.
                @elseif ($prompt['type'] === App\Http\Controllers\RegisterController::USED_NAME_ERROR)
                    Registration failed due to used user name.
                @elseif ($prompt['type'] === App\Http\Controllers\RegisterController::EMPTY_PASSWORD_ERROR)
                    Registration failed due to empty password.
                @elseif ($prompt['type'] === App\Http\Controllers\RegisterController::WEAK_PASSWORD_ERROR)
                    Registration failed due to weak password.
                @elseif ($prompt['type'] === App\Http\Controllers\RegisterController::MISMATCH_PASSWORD_ERROR)
                    Registration failed due to mismatched password.
                @elseif ($prompt['type'] === App\Http\Controllers\RegisterController::BACKEND_ERROR)
                    Registration failed due to system error.
                @elseif ($prompt['type'] === App\Http\Controllers\RegisterController::UNKNOWN_ERROR)
                    Registration failed due to unknown error.
                @endif
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <p>
            To become a new user, please contact the server owner and asks for an
            invite code. Once you have the code, you can complete the following
            form and create a new account here.
        </p>

        <div class="form-group">
            <label for="code">Invite Code</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ $prefill['code'] }}">
        </div>

        <div class="form-group">
            <label for="name">User Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $prefill['name'] }}">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="confirm">Confirm Password</label>
            <input type="password" class="form-control" id="confirm" name="confirm">
        </div>

        <div class="buttons mt-4">
            <button type="submit" class="btn btn-primary">Join</button>
        </div>
    </form>
@endsection

