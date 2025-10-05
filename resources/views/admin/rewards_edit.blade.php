@extends('admin.layouts.admin')

@section('title', isset($reward) ? trans('draw::admin.edit_reward') : trans('draw::admin.new_reward'))

@section('content')
    <h2 class="mb-4">
        <i class="bi bi-gift me-2"></i>
        {{ isset($reward) ? trans('draw::admin.edit_reward') : trans('draw::admin.new_reward') }}
    </h2>

    <form method="POST" action="{{ isset($reward) ? route('draw.admin.rewards.edit.submit', $reward) : route('draw.admin.rewards.add.submit') }}">
        @csrf

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body row gx-4 gy-3">
                <div class="col-md-4">
                    <label for="nameInput" class="form-label">{{ trans('messages.fields.name') }}</label>
                    <input type="text"
                           id="nameInput"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $reward->name ?? '') }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="moneyInput" class="form-label">{{ trans('messages.fields.money') }}</label>
                    <div class="input-group">
                        <input type="number"
                               id="moneyInput"
                               name="money"
                               min="0"
                               step="0.01"
                               max="999999"
                               class="form-control @error('money') is-invalid @enderror"
                               value="{{ old('money', $reward->money ?? '') }}">
                        <span class="input-group-text">{{ money_name() }}</span>
                    </div>
                    @error('money')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="serversSelect" class="form-label">{{ trans('draw::admin.fields.servers') }}</label>
                    <select class="form-select"
                            id="serversSelect"
                            name="servers[]"
                            multiple>
                        @php
                            $selectedServers = old('servers', isset($reward) ? $reward->servers->pluck('id')->toArray() : []);
                        @endphp
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" @selected(in_array($server->id, $selectedServers))>
                                {{ $server->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ trans('draw::admin.placeholders.servers') }}</div>

                    @error('servers')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch mt-4">
                        <input type="checkbox"
                               id="needOnlineSwitch"
                               name="need_online"
                               class="form-check-input"
                               @checked(old('need_online', $reward->need_online ?? true))>
                        <label class="form-check-label" for="needOnlineSwitch">
                            {{ trans('draw::admin.require_online') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-12 mt-4">
                    <label class="form-label">{{ trans('draw::admin.fields.commands') }}</label>
                    @include('admin.elements.list-input', [
                        'name' => 'commands',
                        'values' => old('commands', $reward->commands ?? [])
                    ])
                    <div class="form-text">
                        {{ trans('draw::admin.commands') }}
                    </div>
                </div>

            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i> {{ trans('messages.actions.save') }}
            </button>
        </div>
    </form>
@endsection
