<?php

namespace App\Http\Controllers;

use App\User;
use App\Group;
use App\Role;
use App\Room;
use Illuminate\Http\Request;
use App\Helpers\DateTimeFormatter;
use \Auth;
use JavaScript;
use DateTime;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($groupId)
    {
      $group = Group::where([
        ['id', $groupId],
        ['active', 1],
      ])->first();

      if(!$group) {
        return response(view('errors', ['message' => 'Not Found: 404']), 404);
      }

      $rooms = $group->rooms;
      $doctors = $group->doctors;
      $unassignedRooms = [];

      foreach($doctors as &$doctor) {
        $temp = [];
        foreach($rooms as $room) {
          if($room['doctor_id'] === $doctor['id']) {
            if(!$room->assigned_at)
            {
              $roomObject = Room::find($room->id);
              $roomObject->assigned_at = date("Y-m-d H:i:s");
              $roomObject->save();
              $room->assigned_at = $roomObject->assigned_at;
            }
            $roomTimerData = DateTimeFormatter::getTimeDifference($room->assigned_at);
            $temp[] = [
              'id' => $room->id,
              'name' => $room->name,
              'doctor_id'=> $room->doctor_id,
              'sort_index' => $room->sort_index,
              'timer_minutes' => $roomTimerData['minutes'],
              'timer_seconds' => $roomTimerData['seconds'],
              'timer_colon' => ':',
              'assigned_at' => $room->assigned_at,
              'current_time' => date("Y-m-d H:i:s"),
            ];
          }
        }
        $doctor['rooms'] = $temp;
      }
      foreach($rooms as $room) {
        if($room['doctor_id'] == 0) {
          $unassignedRooms[] = [
            'id' => $room->id,
            'name' => $room->name,
            'doctor_id'=> $room->doctor_id,
            'sort_index' => $room->sort_index,
          ];
        }
      }

      JavaScript::put([
        'doctors' => $doctors,
        'unassignedRooms' => $unassignedRooms,
        'roomWidth' => $group->room_column_width,
        'doctorWidth' => $group->doctor_column_width,
        'groupId' => $groupId,
        'app_name' => env('HEROKU_APP_NAME'),
      ]);

      $userGroups = Auth::user()->groups;
      return view('home', [
        'group' => $group,
        'userGroups' => $userGroups,
      ]);
    }

    public function super() {
      $users = User::all();
      $groups = Group::all();

      $roles = [];
      $userGroups = [];
      foreach($users as $user) {
        $roles[$user['id']] = $user->role;
        $userGroups[$user['id']] = $user->groups;
      }

      return view('superuser', [
        'users' => $users,
        'groups' => $groups,
        'roles' => $roles,
        'userGroups' => $userGroups,
      ]);
    }

    public function admin() {
      $currentUser = Auth::user();
      $currentUserGroups = $currentUser->groups;

      $groups = [];
      foreach($currentUserGroups as $group) {
        $groups[] = $group['id'];
      }

      $users = User::whereHas('groups', function($q) use($groups) {
        $q->whereIn('id', $groups);
      })->get();

      $roles = [];
      $groups = [];
      foreach($users as $user) {
        $roles[$user['id']] = $user->role;
        $groups[$user['id']] = $user->groups;
      }

      return view('adminuser', [
        'users' => $users,
        'roles' => $roles,
        'groups' => $groups,
      ]);
    }
}
