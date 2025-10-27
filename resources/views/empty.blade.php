<div class="room-view empty">
  <add-options-popout user-id="{{Auth::User()->id}}" group-id="{{$group['id']}}"></add-options-popout>
  <div class="group-select has-other-groups">
    {{$group['name']}}<i class="arrow-down fas fa-angle-down"></i>
  </div>
  <div class="rooms-container">
    <div class="doctors">
      <div class="room-col">
        Room
      </div>
      <div class="doctor-cols">
        Doctor
      </div>
    </div>
    <div class="rooms">
      <div class="room-col"></div>
    </div>
  </div>
</div>
