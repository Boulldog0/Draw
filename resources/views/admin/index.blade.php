@extends('admin.layouts.admin')

@section('title', trans('draw::admin.title'))

@section('content')
    @php
        use Carbon\Carbon;
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ trans('draw::admin.manage') }}</h1>
        <a href="{{ route('draw.admin.draws.add') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ trans('draw::admin.create') }}
        </a>
    </div>

    @if($draws->isEmpty())
        <div class="alert alert-info">
            {{ trans('draw::admin.empty') }}
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>{{ trans('draw::admin.fields.title') }}</th>
                        <th>{{ trans('draw::admin.fields.price') }}</th>
                        <th>{{ trans('draw::admin.fields.winners') }}</th>
                        <th>{{ trans('draw::admin.fields.ends_at') }}</th>
                        <th>{{ trans('draw::admin.fields.entries') }}</th>
                        <th>{{ trans('draw::admin.fields.status') }}</th>
                        <th>{{ trans('draw::admin.fields.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($draws as $draw)
                        @php
                            $entryCount = $entries->where('draw_id', $draw->id)->count();
                        @endphp
                        <tr>
                            <td>{{ $draw->name }}</td>
                            <td>{{ $draw->price }} {{ money_name() }}</td>
                            <td>{{ $draw->winners }}</td>
                            <td>{{ format_date(Carbon::parse($draw->expires_at), true) }}</td>
                            <td>{{ $entryCount }}</td>
                            <td>
                                @if($draw->expires_at < now())
                                    <span class="badge bg-danger">{{ trans('draw::admin.expired') }}</span>
                                @elseif($draw->closed)
                                    <span class="badge bg-secondary">{{ trans('draw::admin.end') }}</span>
                                @elseif(!$draw->is_open)
                                    <span class="badge bg-secondary">{{ trans('draw::admin.closed') }}</span>
                                @else
                                    <span class="badge bg-success">{{ trans('draw::admin.active') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    <a href="{{ route('draw.admin.draws.entries', $draw) }}"><button class="btn btn-info btn-icon" title="{{ trans('draw::admin.view_entries') }}">
                                        <i class="bi bi-people-fill"></i>
                                    </button></a>

                                    @if(!$draw->closed)
                                        <a href="{{ route('draw.admin.draws.edit', $draw) }}"><button class="btn btn-warning btn-icon" title="{{ trans('draw::admin.edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button></a>

                                        <form action="{{ route('draw.admin.draws.close', $draw) }}" method="POST" onsubmit="return confirm('{{ trans('draw::admin.confirm') }}');">
                                            @csrf
                                            <button type="submit" class="btn btn-dark btn-icon" title="{{ trans('draw::admin.close') }}">
                                                <i class="bi bi-lock-fill"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('draw.admin.draws.replay', $draw) }}" method="POST" onsubmit="return confirm('{{ trans('draw::admin.confirm_replay') }}');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-icon" title="{{ trans('draw::admin.replay') }}">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>
                                    @endif
                            
                                    <form action="{{ route('draw.admin.draws.delete', $draw) }}" method="POST" onsubmit="return confirm('{{ trans('draw::admin.confirm_delete') }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-icon" title="{{ trans('draw::admin.delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
