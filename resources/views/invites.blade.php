
@extends('skeleton')
@section('id', 'invites')
@section('title', 'Invite Codes - URL Marker')

@section('main')
    @if ($prompt['type'] > 0)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            @if ($prompt['type'] === App\Http\Controllers\InviteController::CREATION_SUCCESS)
                You have successfully created invite code {{ $prompt['target']['code'] }}.
            @elseif ($prompt['type'] === App\Http\Controllers\InviteController::DELETION_SUCCESS)
                You have successfully cancelled invite code {{ $prompt['target']['code'] }}.
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
                admin updating the same invite code. You may refresh the page to check its current status.
            @elseif ($prompt['type'] === App\Http\Controllers\InviteController::MISSING_ERROR)
                The last operation failed because the invite code cannot be found. The error is reported
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
        <div class="caption-title">Invite Codes</div>
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

    @if (count($invites) > 0)
        <table class="table model-table">
            <tbody>
                @foreach ($invites as $invite)
                    <tr class="invite">
                        <td class="label pl-3" title="{{ $invite->notes }}"><div class="text-truncate">{{ $invite->code }}</div></td>
                        <td class="status font-weight-lighter">
                            @if ($invite->expired !== true)
                                Active Until {{ $invite->expired_at->format('Y-m-d H:i') }} UTC
                            @else
                                Expired
                            @endif
                        </td>
                        <td class="actions pr-3 iconbar"><!--
                            @if ($invite->expired !== true)
                                --><form method="POST" action="{{ action("InviteController@delete", [ 'id' => $invite->id ]) }}">
                                    @method("DELETE")
                                    @csrf
                                    <input type="hidden" name="offset" value="{{ $offset }}" />
                                    <button class="iconbar-item iconbar-icon" type="submit" title="Cancel this invite code"><span class="fas fa-trash"></span></button>
                                </form><!--
                            @endif
                        --></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (count($invites) == 0)
        <div class="missing m-3">
            There are no invite codes found in the system. To create a new invite code, please use
            the create form at the right of the page.
        </div>
    @endif
@endsection

@section('aside')
    <form class="card bg-light border-0" method="POST" action="{{ action('InviteController@create') }}">
        @method("PUT")
        @csrf

        <div class="card-body">
            <div class="form-iconic-input bg-white border-0">
                <label htmlFor="notes"><span class="fas fa-clipboard"></span></label>
                <input id="notes" type="text" name="notes" placeholder="Notes" />
            </div>
            <button type="submit" class="btn btn-block btn-light mt-3">Create</button>
        </div>
    </form>
@endsection

