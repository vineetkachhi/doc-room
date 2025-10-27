@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header">Edit {{$group['name']}}</div>

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
                            <label for="name" class="col-sm-4 col-form-label text-md-right">Name</label>

                            <div class="col-md-6">
                                <input id="name" value="{{$group['name']}}" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-sm-4 col-form-label text-md-right">Active</label>

                            <div class="col-md-6">
                              <label>
                                  <input type="checkbox" value="{{$group['id']}}" name="active" {{ $group['active'] ? 'checked' : '' }}>
                              </label>
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
