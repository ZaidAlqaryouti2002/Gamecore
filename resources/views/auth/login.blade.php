@extends('layouts.store-auth')

@section('title', ($adminLogin ?? false) ? 'Admin Sign In' : 'Sign In')

@section('content')
  <a class="skip-link" href="#account-form">Skip to sign in</a>
  <main class="auth-layout">
    <section class="auth-panel" aria-labelledby="auth-title">
      <a class="back-to-store" href="{{ route('home') }}">
      <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to home
      </a>
      <header class="auth-heading">
        <h1 id="auth-title">Welcome Back</h1>
        <p>{{ ($adminLogin ?? false) ? 'Sign in to manage GameCore.' : 'Good to see you. Sign in and take a look around.' }}</p>
      </header>
      <form id="account-form" method="POST" action="{{ route(($adminLogin ?? false) ? 'admin.login.store' : 'login.store') }}">
        @csrf
        <div class="form-field">
          <label for="email">Email Address</label>
          <input id="email" name="email" value="{{ old('email') }}" type="email" autocomplete="username"
            placeholder="Enter your email address" maxlength="254" required>
        </div>
        <div class="form-field">
          <label for="password">Password</label>
          <div class="password-field">
            <input id="password" name="password" type="password"
              autocomplete="current-password" placeholder="Enter your password" required>
            <button class="password-toggle" type="button" data-password="password"
              aria-controls="password" aria-label="Show password" aria-pressed="false" title="Show password">
              <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        @unless($adminLogin ?? false)
        <label class="checkbox-label" for="remember-me">
          <input id="remember-me" name="remember" type="checkbox" value="1" @checked(old('remember'))>
          <span>Remember me</span>
        </label>
        @endunless
        @if ($errors->any())
          <ul class="auth-message" role="alert">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        @endif
        <button class="auth-submit" id="submit-button" type="submit">Sign In</button>
      </form>
      @unless($adminLogin ?? false)
      <p class="auth-switch">New here? <a href="{{ route('register') }}">Create an account</a></p>
      @endunless
    </section>
  </main>
@endsection
