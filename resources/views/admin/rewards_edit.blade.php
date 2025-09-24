@extends('admin.layouts.admin')

@section('title', isset($reward) ? 'Modifier une récompense' : 'Nouvelle récompense')

@section('content')
    <style>
        body {
            background: linear-gradient(145deg, #1e1e2f, #2c2c3f);
            color: #f0f0f0;
        }

        .card {
            background-color: #262639;
            border: none;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
        }

        .form-label, .form-check-label, .form-text {
            color: #ccc;
        }

        .form-control, .form-select, .input-group-text {
            background-color: #1a1a2e;
            color: #f0f0f0;
            border: 1px solid #444;
        }

        .form-control:focus, .form-select:focus {
            border-color: #6c63ff;
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .input-group-text {
            border-left: none;
        }

        .form-check-input:checked {
            background-color: #6c63ff;
            border-color: #6c63ff;
        }
    </style>

    <h2 class="mb-4">
        <i class="bi bi-gift me-2"></i>
        {{ isset($reward) ? 'Modifier une récompense' : 'Nouvelle récompense' }}
    </h2>

    <form method="POST" action="{{ isset($reward) ? route('draw.admin.rewards.edit.submit', $reward) : route('draw.admin.rewards.add.submit') }}">
        @csrf

        <div class="card mb-4">
            <div class="card-body row gx-4 gy-3">
                <div class="col-md-4">
                    <label for="nameInput" class="form-label">{{ trans('messages.fields.name') }}</label>
                    <input type="text" id="nameInput" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $reward->name ?? '') }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="moneyInput" class="form-label">{{ trans('messages.fields.money') }}</label>
                    <div class="input-group">
                        <input type="number" id="moneyInput" name="money" min="0" step="0.01" max="999999"
                            class="form-control @error('money') is-invalid @enderror"
                            value="{{ old('money', $reward->money ?? '') }}">
                        <span class="input-group-text">{{ money_name() }}</span>
                    </div>
                    @error('money')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3 col-md-6">
                    <label for="rewards" class="form-label">{{ trans('draw::admin.fields.servers') }}</label>
                    <select class="selectpicker form-control" id="serversSelect" name="servers[]" multiple="" tabindex="null">
                        @php
                            $selectedServers = old('servers', isset($reward) ? $reward->servers->pluck('id')->toArray() : []);
                        @endphp
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" @selected(in_array($server->id, $selectedServers))>
                                {{ $server->name }}
                            </option>
                        @endforeach
                        </select>
                        <small class="text-muted">{{ trans('draw::admin.placeholders.servers') }}</small>

                        @error('servers')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                 </div>

                <div class="col-md-6">
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" id="needOnlineSwitch" name="need_online"
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
                <i class="bi bi-check-circle me-1"></i> Enregistrer
            </button>
        </div>
    </form>
@endsection
