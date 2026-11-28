@extends('layouts.admin')

@php
    $page = config('admin-tables.'.$resource);
    [$title, $description, $icon, $singular, $columns, $fields] = $page;
    $filter = $page[6] ?? null;
@endphp

@section('title', $title)

@section('topbar-description')
    <p class="management-description">{{ $description }}</p>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-management.css') }}">
@endpush

@section('content')
    <section class="management-card" id="management" data-resource="{{ $resource }}"
             data-endpoint="{{ url('api/admin/'.$resource) }}" data-lookups="{{ url('api/admin/lookups') }}"
             data-categories="{{ url('api/admin/categories') }}" data-csrf="{{ csrf_token() }}">
        <div class="management-toolbar">
            <label class="management-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span class="management-sr">Search {{ strtolower($title) }}</span>
                <input type="search" maxlength="100" placeholder="Search {{ strtolower($title) }}…" data-search>
            </label>
            @if ($filter)
                <select aria-label="{{ $filter[1] }}" data-filter></select>
            @endif
            @if ($fields)
                <button type="button" class="management-primary" data-add>
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add {{ $singular }}
                </button>
            @endif
        </div>
        <p class="management-status" role="status" data-status>Loading {{ strtolower($title) }}…</p>
        <div class="management-table-wrap" tabindex="0" role="region" aria-label="{{ $title }} table">
            <table>
                <thead>
                    <tr>
                        @foreach ($columns as [$key, $heading])
                            <th scope="col">{{ $heading }}</th>
                        @endforeach
                        @if ($fields)
                            <th scope="col">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <nav class="management-pagination" aria-label="Table pages" hidden>
            <button type="button" data-prev>Previous</button>
            <span data-page></span>
            <button type="button" data-next>Next</button>
        </nav>
        @if ($fields)
            <dialog class="management-dialog">
                <div class="management-dialog-header">
                    <h2 id="management-dialog-title"></h2>
                    <button type="button" aria-label="Close" data-close><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </div>
                <form>
                    <div data-fields></div>
                    <p class="management-error" role="alert" data-error hidden></p>
                    <footer>
                        <button type="button" data-cancel>Cancel</button>
                        <button type="submit" class="management-primary" data-save>Save</button>
                    </footer>
                </form>
            </dialog>
        @endif
    </section>
@endsection

@push('scripts')
    <script>window.adminTable = {{ Illuminate\Support\Js::from(compact('singular', 'columns', 'fields', 'filter')) }};</script>
    <script src="{{ asset('js/admin-management.js') }}" defer></script>
@endpush
