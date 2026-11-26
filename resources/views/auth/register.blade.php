@extends('layouts.store-auth')

@section('title', 'Create an Account')

@section('content')
  <a class="skip-link" href="#account-form">Skip to registration</a>
  <main class="auth-layout">
    <section class="auth-panel" aria-labelledby="auth-title">
      <a class="back-to-store" href="{{ route('home') }}">
      <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to home
      </a>
      <header class="auth-heading">
        <h1 id="auth-title">Create an Account</h1>
        <p>Make yourself at home. Create your GameCore account.</p>
      </header>
      <form id="account-form" method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="form-field">
          <label for="full-name">Full Name</label>
          <input id="full-name" name="name" value="{{ old('name') }}" type="text" autocomplete="name"
            placeholder="Enter your full name" minlength="2" maxlength="80" required>
        </div>
        <div class="form-field">
          <label for="email">Email Address</label>
          <input id="email" name="email" value="{{ old('email') }}" type="email" autocomplete="username"
            placeholder="Enter your email address" maxlength="254" required>
        </div>
        <div class="form-field">
          <label for="password">Password</label>
          <div class="password-field">
            <input id="password" name="password" type="password" autocomplete="new-password"
              placeholder="Create a password" minlength="8" aria-describedby="password-help" required>
            <button class="password-toggle" type="button" data-password="password"
              aria-controls="password" aria-label="Show password" aria-pressed="false" title="Show password">
              <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
          </div>
          <p class="field-help" id="password-help">Use at least 8 characters, including a number.</p>
        </div>
        <div class="form-field">
          <label for="confirm-password">Confirm Password</label>
          <div class="password-field">
            <input id="confirm-password" name="password_confirmation" type="password"
              autocomplete="new-password" placeholder="Confirm your password" required>
            <button class="password-toggle" type="button" data-password="confirm-password"
              aria-controls="confirm-password" aria-label="Show confirm password"
              aria-pressed="false" title="Show confirm password">
              <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        @if ($errors->any())
          <ul class="auth-message" role="alert">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        @endif
        <button class="auth-submit" id="submit-button" type="submit">Sign Up</button>
      </form>
      <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Log In</a></p>
    </section>
  </main>
@endsection
