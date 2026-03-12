@extends('admin.layouts.admin')

@section('title', trans('draw::admin.discord_webhook'))

@section('content')
    <div class="card shadow-sm border-0 p-4">
        <div class="card-header bg-transparent border-bottom">
            <h5 class="mb-0">{{trans('draw::admin.discord_webhook')}}</h5>
        </div>

        <div class="card-body mt-3">
            <form action="{{ route('draw.admin.draws.discord_webhook.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="discord_webhook" class="form-label">{{ trans('draw::admin.fields.discord_webhook_link') }}</label>
                    <input type="text" class="form-control" name="discord_webhook" id="discord_webhook" value="{{ $settings['discord_webhook'] ?? '' }}" required>
                    <small class="text-muted">{{ trans('draw::admin.placeholders.discord_webhook') }}</small>
                </div>
                <div class="mb-3">
                    <div class="alert alert-info">{{ trans('draw::admin.info.discord_webhook') }}</div>
                </div>

                <div class="mb-3">
                    <label for="embed_title" class="form-label">{{ trans('draw::admin.fields.embed_title') }}</label>
                    <textarea class="form-control" name="embed_title" id="embed_title" rows="3">{{ $settings['embed_title'] ?? '' }}</textarea>
                    <small class="text-muted">{{ trans('draw::admin.placeholders.embed_title') }}</small>
                </div>
                <div class="mb-3">
                    <label for="embed_description" class="form-label">{{ trans('draw::admin.fields.embed_description') }}</label>
                    <textarea class="form-control" name="embed_description" id="embed_description" rows="3">{{ $settings['embed_description'] ?? '' }}</textarea>
                    <small class="text-muted">{{ trans('draw::admin.placeholders.embed_description') }}</small>
                </div>
                <div class="mb-3 col-md-4">
                    <label for="embed_color" class="form-label">{{ trans('draw::admin.fields.embed_color') }}</label>
                    <input type="color" class="form-control form-control-color" name="embed_color" id="embed_color" value="{{ $settings['embed_color'] ?? '#000000' }}" title="Choisissez une couleur">
                </div>
                <div class="mb-3">
                    <label for="embed_footer" class="form-label">{{ trans('draw::admin.fields.embed_footer') }}</label>
                    <input type="text" class="form-control" name="embed_footer" id="embed_footer" value="{{ $settings['embed_footer'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ trans('draw::admin.fields.embed_fields') }}</label>
                    <div id="fields-container">
                        @php
                            $fields = $settings['fields_container'] ?? [];
                        @endphp
                        @if(is_array($fields))
                        @php $i = 0; @endphp
                            @foreach($fields as $field)
                                <div class="input-group mb-2">
                                    <input type="text" name="fields[{{ $i }}][name]" class="form-control" placeholder="{{ trans('draw::admin.placeholders.field_name') }}" value="{{ $field['name'] ?? '' }}">
                                    <input type="text" name="fields[{{ $i }}][value]" class="form-control" placeholder="{{ trans('draw::admin.placeholders.field_value') }}" value="{{ $field['value'] ?? '' }}">
                                    <button type="button" class="btn btn-outline-danger remove-field"><i class="bi bi-x"></i></button>
                                </div>
                                @php $i++; @endphp
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-field-btn">
                        <i class="bi bi-plus"></i> {{ trans('draw::admin.add_field') }}
                    </button>
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
            <template id="field-template">
                <div class="input-group mb-2" data-index="__IDX__">
                    <input type="text" name="fields[__IDX__][name]" class="form-control" placeholder="{{ trans('draw::admin.placeholders.field_name') }}">
                    <input type="text" name="fields[__IDX__][value]" class="form-control" placeholder="{{ trans('draw::admin.placeholders.field_value') }}">
                    <button type="button" class="btn btn-outline-danger remove-field"><i class="bi bi-x"></i></button>
                </div>
            </template>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const container = document.getElementById('fields-container');
                    const addBtn = document.getElementById('add-field-btn');

                    addBtn.addEventListener('click', function () {
                        const tpl = document.getElementById('field-template');
                        const clone = tpl.content.cloneNode(true);
                        container.appendChild(clone);
                    });

                    container.addEventListener('click', function (e) {
                        if (e.target.closest('.remove-field')) {
                            const row = e.target.closest('.input-group');
                            row && row.remove();
                        }
                    });
                });
            </script>
        </div>
    </div>
@endsection
