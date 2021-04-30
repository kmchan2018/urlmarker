
@extends('skeleton')
@section('id', 'login')
@section('title', 'Login As Existing User')

@section('main')
    <header class="caption">
        <div class="caption-title">Login As Existing User</div>
    </header>

    <form action="{{ action('LoginController@handle') }}" method="POST" class="m-3">
        @csrf

        @if ($prompt['type'] < 0)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                The previous login has failed.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <p>
            Welcome to a private URL marker service. The service is restricted to registered
            users only. Before using the service, please login to the service here.
        </p>

        <div class="form-group">
            <label for="name">User Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $prefill['name'] }}">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="buttons mt-4">
            <button class="btn btn-primary" type="submit" title="Press here to login after you complete the above fields">Login</button>
        </div>
    </form>
@endsection

