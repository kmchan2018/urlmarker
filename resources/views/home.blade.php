
@extends('skeleton')
@section('id', 'home')
@section('title', 'Home - URL Marker')

@section('main')
    <header class="caption">
        <div class="caption-title">Recent Markers</div>
        <div class="caption-actions iconbar" role="toolbar">
            <a class="iconbar-item iconbar-icon" href="{{ route('markers') }}"><span class="fas fa-ellipsis-v"></span></a>
        </div>
    </header>

    @if (count($markers) > 0)
        <table class="table model-table">
            <tbody>
                @foreach ($markers as $marker)
                    <tr class="marker">
                        <td class="label pl-3"><div class="text-truncate"><a href="{{ $marker['url'] }}" target="_blank" title="{{ $marker['description'] }}">{{ $marker['url'] }}</a></div></td>
                        <td class="date pr-3 font-weight-lighter">{{ $marker['relative_created_at'] }}</td>
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
        <input type="hidden" name="route" value="home" />
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

