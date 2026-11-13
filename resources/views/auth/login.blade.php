@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Iniciar sesión</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="user" class="col-md-4 col-form-label text-md-end">Usuario</label>

                            <div class="col-md-6">
                                <input id="user" type="text" class="form-control @error('user') is-invalid @enderror" name="user" value="{{ old('user') }}" required autocomplete="user" autofocus>

                                @error('user')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">Contraseña</label>

                            <div class="col-md-6">
                                <div class="password-wrapper">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                    <button type="button" id="togglePassword" class="password-toggle" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                                        <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        <svg class="icon-eye-off d-none" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94"/>
                                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/>
                                            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                                            <line x1="2" y1="2" x2="22" y2="22"/>
                                        </svg>
                                    </button>

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div> --}}

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Iniciar sesión
                                </button>

                                {{-- @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif --}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .password-wrapper {
        position: relative;
    }
    .password-wrapper .form-control {
        padding-right: 2.75rem;
    }
    /* Quita el ícono rojo de Bootstrap para que no choque con el ojo */
    .password-wrapper .form-control.is-invalid {
        background-image: none;
        padding-right: 2.75rem;
    }
    .password-toggle {
        position: absolute;
        top: 0;
        right: 0;
        height: calc(1.5em + .75rem + 2px); /* alto del input */
        width: 2.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 0;
        background: transparent;
        color: #6c757d;
        cursor: pointer;
    }
    .password-toggle:hover,
    .password-toggle:focus-visible {
        color: #212529;
    }
    .password-toggle:focus {
        outline: none;
    }
    .password-toggle:focus-visible {
        outline: 2px solid rgba(13, 110, 253, .5);
        outline-offset: -4px;
        border-radius: .375rem;
    }
</style>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const show = input.type === 'password';

        input.type = show ? 'text' : 'password';
        this.querySelector('.icon-eye').classList.toggle('d-none', show);
        this.querySelector('.icon-eye-off').classList.toggle('d-none', !show);
        this.title = show ? 'Ocultar contraseña' : 'Mostrar contraseña';
        this.setAttribute('aria-label', this.title);
        input.focus();
    });
</script>
@endsection
