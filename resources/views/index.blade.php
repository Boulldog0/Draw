@extends('layouts.app')

@section('title', trans('draw::messages.title'))

@section('content')
    @php
        use Carbon\Carbon;
        use Azuriom\Models\User;
    @endphp
    <style>
        .draw-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: box-shadow 0.2s;
        }
        .draw-card:hover {
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .draw-title {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .draw-meta {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
    <div class="mb-3">
        <h1>{{ trans('draw::messages.title') }}</h1>
        <div class="row">
            <div class="container py-5">
                @if($activeDraws->isNotEmpty())
                    <h1 class="mb-4">🎟️ {{ trans('draw::messages.avaiable_draws') }}</h1>
                    @foreach($activeDraws as $draw)
                       @php
                            $user_entries = $entries->where('draw_id', $draw->id)->count();
                            $max_entries = $draw->max_entries_per_player;
                    
                            $placeholders = [
                                '{entries}' => $user_entries,
                                '{max_entries}' => $max_entries,
                            ];

                            $link = str('/draw/' . $draw->id . '/participate');

                            $drawWinners = $winners[$draw->id] ?? collect();
                        @endphp
                        <div class="draw-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="draw-title">{{ $draw->name }}</div>
                                        @if($draw->pined)
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-pin-angle-fill"></i> {{ trans('draw::messages.pined') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($draw->price === 0)
                                        <div class="draw-meta">{{ trans('draw::messages.free_draw') }} | {{ trans('draw::messages.winners') }} : {{ $draw->winners }} | {{ trans('draw::messages.end_at') }} {{ format_date(Carbon::parse($draw->expires_at)) }}</div>
                                    @else
                                        <div class="draw-meta">{{ trans('draw::messages.price') }} {{ $draw->price }} {{ money_name() }} | {{ trans('draw::messages.winners') }} : {{ $draw->winners }} | {{ trans('draw::messages.end_at') }} {{ format_date(Carbon::parse($draw->expires_at)) }}</div>
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

                            <p class="mt-2">{{ $draw->description }}</p>

                            @if($draw->closed && $drawWinners->isNotEmpty())
                                <div class="mt-3">
                                    <strong>{{ trans('draw::messages.winners_list') }}:</strong>
                                    <ul class="mb-2">
                                        @foreach($drawWinners as $winner)
                                            <li>{{ User::find($winner->user_id)->name ?? 'Utilisateur #' . $winner }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <small>{{ str_replace(array_keys($placeholders), array_values($placeholders), trans('draw::messages.entries')) }}</small>
                                <form action="{{ $link }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" @if($draw->closed || !$draw->is_open || $user_entries >= $draw->max_entries_per_player) disabled @endif>
                                        {{ trans('draw::messages.participate') }}
                                    </button>
                                </form>
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
