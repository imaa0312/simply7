<?php $page = 'signin-3'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="account-content">
        <div class="login-wrapper login-new">
            <div class="container">
                <div class="login-content user-login">
                    <div class="login-logo">
                        <img src="{{ URL::asset('/build/img/Simply seven square.png') }}" alt="img" width="50%" style="margin-left: auto; margin-right: auto; display: block;">
                    </div>
                    <div class="col-4">
                    <form action="{!! ('auth/login') !!}" method="POST">
                        @csrf
                        <div class="login-userset">
                            <div class="login-userheading">
                                <h3>Sign In</h3>
                            </div>
                            <div class="form-login">
                                <label class="form-label">Username</label>
                                <div class="form-addons">
                                    <input type="text" class="form-control" name="username">
                                    <img src="{{ URL::asset('/build/img/icons/user-icon.svg') }}" alt="img">
                                </div>
                            </div>
                            <div class="form-login">
                                <label>Password</label>
                                <div class="pass-group">
                                    <input type="password" class="pass-input" name="password">
                                    <span class="fas toggle-password fa-eye-slash"></span>
                                </div>
                            </div>
                            <div class="form-login">
                                <button class="btn btn-login" type="submit">Sign In</button>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
                <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                    <p>Copyright &copy; 2025 Simply7. All rights reserved</p>
                </div>
            </div>
        </div>
    </div>
@endsection
