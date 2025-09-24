@extends('admin.layouts.admin')

@section('title', trans('draw::admin.entries_title') . $draw->name)

@section('content')
    @php
        use Azuriom\Models\User;
    @endphp
    <div class="mb-4">
        <h2 class="h5">{{ $draw->name }} (# {{ $draw->id }})</h2>
        <p class="text-muted">{{ trans('draw::admin.entries_description') }}</p>
    </div>

    @if($entries->isEmpty())
        <div class="alert alert-info">
            {{ trans('draw::admin.no_entries') }}
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ trans('draw::admin.fields.user') }}</th>
                        <th>{{ trans('draw::admin.fields.entries') }}</th>
                        <th>{{ trans('draw::admin.fields.date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries->groupBy('user_id') as $userId => $userEntries)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ User::find($userId)->name ?? '#' . $userId }}</td>
                            <td>{{ $userEntries->count() }}</td>
                            <td>{{ $userEntries->first()->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <a href="{{ route('draw.admin.index') }}" class="btn btn-danger mt-3">
        <i class="bi bi-arrow-return-left"></i> {{ trans('messages.actions.cancel') }}
    </a>
@endsection
