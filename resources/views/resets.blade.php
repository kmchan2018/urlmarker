
@extends('skeleton')
@section('id', 'resets')
@section('title', 'Reset Tokens - URL Marker')

@section('main')
    @if ($prompt['type'] > 0)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            @if ($prompt['type'] === App\Http\Controllers\ResetController::DELETION_SUCCESS)
                You have successfully cancelled reset token {{ $prompt['target']['token'] }} for user
                {{ $prompt['target']['email'] }}.
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
            @if ($prompt['type'] === App\Http\Controllers\InviteController::CONFLICT_ERROR)
                The last operation failed because it is not applicable. Usually it is caused by other
                admin updating the same reset token. You may refresh the page to check its current status.
            @elseif ($prompt['type'] === App\Http\Controllers\InviteController::MISSING_ERROR)
                The last operation failed because the reset token cannot be found. The error is reported
                for resolution.
            @elseif ($prompt['type'] === App\Http\Controllers\InviteController::BACKEND_ERROR)
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
        <div class="caption-title">Reset Tokens</div>
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

    @if (count($resets) > 0)
        <table class="table model-table">
            <tbody>
                @foreach ($resets as $reset)
                    <tr class="reset">
                        <td class="user pl-3" title="{{ $reset->email }}"><div class="text-truncate">{{ $reset->email }}</div></td>
                        <td class="token pl-3" title="{{ $reset->token }}"><div class="text-truncate">{{ $reset->token }}</div></td>
                        <td class="status font-weight-lighter">
                            @if ($reset->expired === false)
                                Active Until {{ $reset->expired_at->format('Y-m-d H:i') }} UTC
                            @else
                                Expired
                            @endif
                        </td>
                        <td class="actions pr-3 iconbar"><!--
                            @if ($reset->expired === false)
                                --><form method="POST" action="{{ action("ResetController@delete", [ 'id' => $reset->id ]) }}">
                                    @method("DELETE")
                                    @csrf
                                    <input type="hidden" name="offset" value="{{ $offset }}" />
                                    <button class="iconbar-item iconbar-icon" type="submit" title="Cancel this reset token"><span class="fas fa-trash"></span></button>
                                </form><!--
                            @endif
                        --></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (count($resets) == 0)
        <div class="missing m-3">
            There are no active reset token found in the system. To create a new reset token for a user,
            visit the <a href="{{ route('users') }}">Users</a> page and click on the create reset
            token button for the respective user.
        </div>
    @endif
@endsection

