@extends('admin.layouts.admin')

@section('title', trans('draw::admin.nav.rewards'))

@section('content')
    <h2 class="mb-4">{{ trans('draw::admin.manage_rewards') }}</h2>

    <a href="{{ route('draw.admin.rewards.add') }}" class="btn btn-primary mb-3">
        {{ trans('draw::admin.new_reward') }}
    </a>

    @if($rewards->isEmpty())
        <div class="alert alert-info">
            {{ trans('draw::admin.rewards_empty') }}
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ trans('messages.fields.name') }}</th>
                        <th>{{ trans('messages.fields.money') }}</th>
                        <th>{{ trans('draw::admin.fields.commands') }}</th>
                        <th>{{ trans('draw::admin.fields.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rewards as $reward)
                        <tr>
                            <td>{{ $reward->id }}</td>
                            <td>{{ $reward->name }}</td>
                            <td>{{ $reward->money ?? '—' }}</td>
                            <td>
                                @foreach($reward->commands ?? [] as $command)
                                    <div><code>{{ $command }}</code></div>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('draw.admin.rewards.edit', $reward) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="{{ route('draw.admin.rewards.delete', $reward) }}" class="d-inline-block" onsubmit="return confirm('Confirmer ?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ trans('draw::admin.delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
