@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header"><strong>Users</strong></div>

                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th scope="col">Id</th>
                      <th scope="col">Name</th>
                      <th scope="col">Role</th>
                      <th scope="col">Groups</th>
                      <th scope="col">Options</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($users as $user)
                      <tr>
                        <th scope="row">{{$user['id']}}</th>
                        <td>{{$user['name']}}</td>
                        <td>{{$roles[$user['id']]['name']}}</td>
                        <td>
                          @if($roles[$user['id']]['id'] >= 3)
                            All
                          @else
                            @foreach($userGroups[$user['id']] as $group)
                              {{$group['name']}}
                              @if($loop->first)<br/>@endif
                            @endforeach
                          @endif
                        </td>
                        <td>
                          @if(Auth::user()->id !== $user['id'])
                            <a href="/edit-user/{{$user['id']}}"><button type="button" class="btn btn-primary">Edit</button></a>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row justify-content-center" style="margin-top: 40px;">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header"><strong>Groups</strong></div>

                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th scope="col">Id</th>
                      <th scope="col">Name</th>
                      <th scope="col">Options</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($groups as $group)
                      <tr>
                        <th scope="row">{{$group['id']}}</th>
                        <td>{{$group['name']}}</td>
                        <td>
                          <a href="/edit-group/{{$group['id']}}"><button type="button" class="btn btn-primary">Edit</button></a>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
