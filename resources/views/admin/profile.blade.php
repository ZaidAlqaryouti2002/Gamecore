@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
    @php($admin = request()->user())

    <section class="admin-profile" aria-labelledby="admin-account-title">
        <h2 id="admin-account-title">Account details</h2>
        <dl>
            <dt>Name</dt>
            <dd>{{ $admin->name }}</dd>
            <dt>Email</dt>
            <dd>{{ $admin->email }}</dd>
            <dt>Phone</dt>
            <dd>{{ $admin->phone ?: 'Not set' }}</dd>
        </dl>
    </section>
@endsection
