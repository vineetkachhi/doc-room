@extends('layouts.master')

@section('content')
  <div class="room-view">
    <add-options-popout user-id="{{Auth::User()->id}}" group-id="{{$group['id']}}"></add-options-popout>
    <settings-popout user-id="{{Auth::User()->id}}" group-id="{{$group['id']}}" user-role="{{Auth::User()->role_id}}"></settings-popout>
    <div class="group-select {{sizeof($userGroups) > 1 ? 'has-other-groups' : ''}}">
      <dropdown title="{{$group['name']}}" class="dropdown">
        @if(sizeof($userGroups) > 1)
          <div class="dropdown-menu">
            @foreach($userGroups as $userGroup)
              @if($userGroup->id !== $group->id)
                <div class="option">
                  <a href="/docroom/home/{{$userGroup->id}}">
                    {{$userGroup->name}}
                  </a>
                </div>
              @endif
            @endforeach
          </div>
          <i class="arrow-down fas fa-angle-down"></i>
        @endif
      </dropdown>
    </div>
    <rooms-view user-id="{{Auth::User()->id}}" group-id="{{$group['id']}}"></rooms-view>
  </div>

@endsection
