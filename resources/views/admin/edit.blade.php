@extends('admin.layouts.admin')

@section('title', isset($draw) ? trans('draw::admin.edit') : trans('draw::admin.create'))

@section('content')
    @php
        use Carbon\Carbon;
        $now = Carbon::now()->format('Y-m-d\TH:i');
    @endphp

    <div class="card shadow-sm border-0 p-4">
        <div class="card-header bg-transparent border-bottom">
            <h5 class="mb-0">{{ isset($draw) ? trans('draw::admin.edit') : trans('draw::admin.create') }}</h5>
        </div>

        <div class="card-body mt-3">
            <form action="{{ isset($draw) ? route('draw.admin.draws.edit.submit', $draw) : route('draw.admin.draws.add.submit') }}" method="POST">
                @csrf
                @if(isset($draw) && !$draw->is_open)
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>{{ trans('draw::admin.draw_stopped') }}</div>
                    </div>
                @endif

                @if(!setting('draw.cron_activated', false))
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>{{ trans('draw::admin.cron_not_activated') }}</div>
                    </div>
                @endif
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="title" class="form-label">{{ trans('draw::admin.fields.title') }}</label>
                        <input type="text" class="form-control" name="title" id="title" value="{{ old('title', $draw->name ?? '') }}" required>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.title') }}</small>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="price" class="form-label">{{ trans('draw::admin.fields.price') }}</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="price" id="price" min="0" step="0.01" value="{{ old('price', $draw->price ?? 0) }}" required>
                            <span class="input-group-text">{{ money_name() }}</span>
                        </div>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.price') }}</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">{{ trans('draw::admin.fields.description') }}</label>
                    <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', $draw->description ?? '') }}</textarea>
                    <small class="text-muted">{{ trans('draw::admin.placeholders.description') }}</small>
                </div>
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="winners" class="form-label">{{ trans('draw::admin.fields.winners') }}</label>
                        <input type="number" class="form-control"  name="winners" id="winners" min="1" value="{{ old('winners', $draw->winners ?? 1) }}" required>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.winners') }}</small>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label for="max_entries_per_player" class="form-label">{{ trans('draw::admin.fields.max_entries') }}</label>
                        <input type="number" class="form-control" name="max_entries_per_player" id="max_entries_per_player" min="1" value="{{ old('max_entries_per_player', $draw->max_entries_per_player ?? 1) }}" required>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.max_entries') }}</small>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label for="max_entries_total" class="form-label">{{ trans('draw::admin.fields.max_entries_total') }}</label>
                        <input type="number" class="form-control"  name="max_entries_total" id="max_entries_total" min="0" value="{{ old('max_entries_total', $draw->max_entries ?? 0) }}" required>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.max_entries_total') }}</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="expires_at" class="form-label">{{ trans('draw::admin.fields.expires_at') }}</label>
                    <input type="datetime-local" class="form-control" name="expires_at" id="expires_at" value="{{ old('expires_at', isset($draw) ? $draw->expires_at->format('Y-m-d\TH:i') : '') }}" min="{{ $now }}" required>
                    <small class="text-muted">{{ trans('draw::admin.placeholders.expires_at') }}</small>
                </div>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="pined" class="form-label">{{ trans('draw::admin.fields.pined') }}</label>
                        <select class="form-select" name="pined" id="pined">
                            <option value="0" @selected(old('pined', $draw->pined ?? false))>{{ trans('messages.yes') }}</option>
                            <option value="1" @selected(!old('pined', $draw->pined ?? false))>{{ trans('messages.no') }}</option>
                        </select>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.pined') }}</small>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="auto_send" class="form-label">{{ trans('draw::admin.fields.auto_send') }}</label>
                        <select class="form-select"
                                name="auto_send"
                                id="auto_send"
                                @if(!setting('draw.cron_activated', false)) disabled @endif>
                            <option value="0" @selected(old('auto_send', $draw->auto_send ?? false))>{{ trans('messages.yes') }}</option>
                            <option value="1" @selected(!old('auto_send', $draw->auto_send ?? false))>{{ trans('messages.no') }}</option>
                        </select>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.auto_send') }}</small>
                        @if(!setting('draw.cron_activated', false))
                            <div class="alert alert-warning d-flex align-items-center gap-2 mt-2">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div>{{ trans('draw::admin.placeholders.auto_send_disabled') }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="rewards" class="form-label">{{ trans('draw::admin.fields.rewards') }}</label>
                        <select class="form-select" id="rewards" name="rewards[]" multiple>
                            @php
                                $selectedRewards = old('rewards', isset($draw) ? $draw->rewards->pluck('id')->toArray() : []);
                            @endphp
                            @foreach($rewards as $reward)
                                <option value="{{ $reward->id }}" @selected(in_array($reward->id, $selectedRewards))>
                                    {{ $reward->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.rewards') }}</small>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                    </button>
                    <a href="{{ route('draw.admin.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-return-left"></i> {{ trans('messages.actions.cancel') }}
                    </a>
                </div>
            </form>
            @if(isset($draw))
                <hr>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <form action="{{ route('draw.admin.draws.stop', $draw) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-sign-stop"></i> {{ trans('draw::admin.stop_draw') }}
                        </button>
                    </form>

                    @if($draw->closed)
                        <form action="{{ route('draw.admin.draws.replay', $draw) }}" method="POST" onsubmit="return confirm('{{ trans('draw::admin.confirm_replay') }}');">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-arrow-clockwise"></i> {{ trans('draw::admin.replay') }}
                            </button>
                        </form>
                    @else
                        <form action="{{ route('draw.admin.draws.close', $draw) }}" method="POST" onsubmit="return confirm('{{ trans('draw::admin.confirm') }}');">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                <i class="bi bi-x-octagon"></i> {{ trans('draw::admin.close_draw') }}
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('draw.admin.draws.delete', $draw) }}" method="POST" onsubmit="return confirm('{{ trans('draw::admin.confirm_delete') }}');">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> {{ trans('draw::admin.delete') }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
