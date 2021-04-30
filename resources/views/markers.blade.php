
@extends('skeleton')
@section('id', 'markers')
@section('title', 'Markers - URL Marker')

@section('main')
    @if ($prompt['type'] > 0)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            @if ($prompt['type'] === App\Http\Controllers\MarkerController::CREATION_SUCCESS)
                You have successfully created a new marker.
            @elseif ($prompt['type'] === App\Http\Controllers\MarkerController::DELETION_SUCCESS)
                You have successfully deleted an existing marker.
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
            @if ($prompt['type'] === App\Http\Controllers\MarkerController::EMPTY_URL_ERROR)
                The marker cannot be created due to empty target URL.
            @elseif ($prompt['type'] === App\Http\Controllers\MarkerController::MISSING_ERROR)
                The last operation failed because the marker cannot be found. The error is reported
                for resolution.
            @elseif ($prompt['type'] === App\Http\Controllers\MarkerController::BACKEND_ERROR)
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
        @if ($search === '')
            <div class="caption-title">All Markers</div>
        @else
            <div class="caption-title">Matches for "{{ $search }}"</div>
        @endif

        <div class="caption-actions" role="toolbar">
            <input type="checkbox" id="toolbar-searchbox">
            <div class="part1 iconbar"><!--
                --><a class="iconbar-item iconbar-icon" href="{{ route('markers') }}"><span class="fas fa-home"></span></a><!--
                --><label class="iconbar-item iconbar-icon" for="toolbar-searchbox"><span class="fas fa-search"></span></label><!--
                --><span class="iconbar-separator"></span><!--
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
            <div class="part2">
                <form class="input-bar bg-white border-0" method="GET" action="{{ route('markers') }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search">
                    <button type="submit"><span class="fas fa-search"></span></button>
                    <label class="button" type="reset" for="toolbar-searchbox"><span class="fas fa-times"></span></label>
                </form>
            </div>
        </div>
    </header>

    @if (count($markers) > 0)
        <table class="table model-table">
            <tbody>
                @foreach ($markers as $marker)
                    <tr class="users">
                        <td class="label pl-3" title="{{ $marker->url }}">
                            <div class="text-truncate">
                                <a href="{{ $marker->url }}" target="_blank">{{ $marker->url }}</a>
                            </div>
                        </td>
                        <td class="client font-weight-lighter">
                            {{ $marker->handler }}
                        </td>
                        <td class="date font-weight-lighter">
                            {{ $marker->relative_created_at }}
                        </td>
                        <td class="actions pr-3 iconbar"><!--
                            --><form method="POST" action="{{ action("MarkerController@update", [ 'id' => $marker->id ]) }}">
                                @method("PATCH")
                                @csrf
                                <input type="hidden" name="type" value="trash" />
                                <input type="hidden" name="search" value="{{ $search }}" />
                                <input type="hidden" name="offset" value="{{ $offset }}" />
                                <button class="iconbar-item iconbar-icon" type="submit" title="Delete this marker"><span class="fas fa-trash"></span></button>
                            </form><!--
                        --></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (count($markers) == 0)
        <div class="missing m-3">
            No markers have been added. To start adding markers, please install Greasemonkey
            or other compatible extension into your browser and install the user scripts
            for the websites you want to work with.
        </div>
    @endif
@endsection

@section('aside')
    <form class="card bg-light border-0" method="POST" action="{{ action('MarkerController@create') }}">
        <input type="hidden" name="route" value="markers" />
        <input type="hidden" name="search" value="{{ $search }}" />
        <input type="hidden" name="offset" value="{{ $offset }}" />
        @method("PUT")
        @csrf

        <div class="card-body">
            <div class="form-group">
                <div class="form-iconic-input bg-white border-0">
                    <label htmlFor="url"><span class="fas fa-link"></span></label>
                    <input id="url" type="text" name="url" placeholder="URL" />
                </div>
            </div>

            <div class="form-group">
                <div class="form-iconic-input bg-white border-0">
                    <label htmlFor="notes"><span class="fas fa-sticky-note"></span></label>
                    <input id="notes" type="text" name="notes" placeholder="Notes" />
                </div>
            </div>

            <button type="submit" class="btn btn-block btn-light mt-3">Mark</button>
        </div>
    </form>
@endsection

