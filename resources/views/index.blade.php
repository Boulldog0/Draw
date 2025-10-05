@extends('layouts.app')

@section('title', trans('draw::messages.title'))

@section('content')
    @php
        use Carbon\Carbon;
        use Azuriom\Models\User;
    @endphp

    <div class="mb-3">
        <h1>{{ trans('draw::messages.title') }}</h1>
        <div class="row">
            <div class="container py-5">
                @if($activeDraws->isNotEmpty())
                    <h1 class="mb-4">🎟️ {{ trans('draw::messages.avaiable_draws') }}</h1>
                    @foreach($activeDraws as $draw)
                        @php
                            $user_entries = $draw->entries->count();
                            $drawWinners = $draw->winners;
                            $link = str('/draw/' . $draw->id . '/participate');
                            $drawWinners = $winners[$draw->id] ?? collect();
                        @endphp

                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h5 class="card-title mb-0 fw-bold">{{ $draw->name }}</h5>
                                            @if($draw->pined)
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-pin-angle-fill"></i> {{ trans('draw::messages.pined') }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($draw->price === 0)
                                            <p class="card-subtitle text-muted small">
                                                {{ trans('draw::messages.free_draw') }} |
                                                {{ trans('draw::messages.winners') }} : {{ $draw->winners }} |
                                                {{ trans('draw::messages.end_at') }} {{ format_date(Carbon::parse($draw->expires_at)) }}
                                            </p>
                                        @else
                                            <p class="card-subtitle text-muted small">
                                                {{ trans('draw::messages.price') }} {{ $draw->price }} {{ money_name() }} |
                                                {{ trans('draw::messages.winners') }} : {{ $draw->winners }} |
                                                {{ trans('draw::messages.end_at') }} {{ format_date(Carbon::parse($draw->expires_at)) }}
                                            </p>
                                        @endif
                                    </div>

                                    <div>
                                        @if(!$draw->closed && $draw->is_open)
                                            <span class="badge bg-success">{{ trans('draw::messages.open') }}</span>
                                        @elseif(!$draw->is_open)
                                            <span class="badge bg-secondary">{{ trans('draw::admin.closed') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ trans('draw::messages.close') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <p class="mt-3 mb-3">{{ $draw->description }}</p>

                                @if($draw->closed && $drawWinners->isNotEmpty())
                                    <div class="mt-3">
                                        <strong>{{ trans('draw::messages.winners_list') }}:</strong>
                                        <ul class="list-unstyled mb-2">
                                            @foreach($drawWinners as $winner)
                                                <li>• {{ $winner->user->name ?? 'Utilisateur #' . $winner->user_id }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ trans('draw::messages.entries', ['entries' => $user_entries, 'max_entries' => $max_entries]) }}
                                    </small>
                                    <form action="{{ $link }}" method="POST" class="ms-2">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-primary btn-sm"
                                                @if($draw->closed || !$draw->is_open || $user_entries >= $draw->max_entries_per_player) disabled @endif>
                                            {{ trans('draw::messages.participate') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning text-center">
                        ❌ {{ trans('draw::messages.no_active_draws') }} ❌
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
