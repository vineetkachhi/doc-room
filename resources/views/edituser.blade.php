@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header">Edit {{$user['name']}}</div>

                <div class="card-body">
                    <form method="POST" action="">
                        @csrf

                        @if(isset($errors) && $errors->first('message'))
                          <div class="form-group">
                            <div class="alert alert-danger">
                              {{$errors->first('message')}}
                            </div>
                          </div>
                        @endif

                        @if(isset($updated) && $updated)
                          <div class="form-group">
                            <div class="alert alert-success">
                              {{$updated}}
                            </div>
                          </div>
                        @endif

                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">E-Mail Address</label>

                            <div class="col-md-6">
                                <input id="email" value="{{$user['email']}}" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="username" class="col-sm-4 col-form-label text-md-right">Username</label>

                            <div class="col-md-6">
                                <input id="username" value="{{$user['username']}}" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required autofocus>

                                @if ($errors->has('username'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="group" class="col-md-4 col-form-label text-md-right">Role</label>

                            <div class="col-md-6">
                              <select class="form-control{{ $errors->has('group') ? ' is-invalid' : '' }}" name="role">
                                <option value="">Select a role</option>
                                @foreach($roles as $role)
                                  <option value="{{$role['id']}}" {{$role['id'] === $user['role_id'] ? 'selected' : ''}}>{{$role['name']}}</option>
                                @endforeach
                              </select>

                              @if ($errors->has('group'))
                                  <span class="invalid-feedback">
                                      <strong>{{ $errors->first('group') }}</strong>
                                  </span>
                              @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Groups</label>

                            <div class="col-md-6">
                              @foreach($groups as $group)
                                <label>
                                    <input type="checkbox" value="{{$group['id']}}" name="group[]" {{ $group['inGroup'] ? 'checked' : '' }}> {{$group['name']}}
                                </label>
                              @endforeach
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">Active Groups</label>

                            <div class="col-md-6">
                              @foreach($groups as $group)
                                <label>
                                    <input type="checkbox" value="{{$group['id']}}" name="activeGroup[]" {{ ($group['inGroup'] && $group['active']) ? 'checked' : '' }}> {{$group['name']}}
                                </label>
                              @endforeach
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">

                                <button type="submit" class="btn btn-primary">
                                    Submit
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
