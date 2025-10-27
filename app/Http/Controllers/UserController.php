<?php

namespace App\Http\Controllers;

use App\User;
use App\Group;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Auth;

class UserController extends Controller
{

  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index($userId, $updated = false) {
    $currentUser = Auth::user();
    $user = User::find($userId);

    // Can not edit users with permissions higher or the same as you
    if($currentUser->role->permission_level <= $user->role->permission_level) {
      return response(view('errors', ['message' => 'Access Denied: 403']), 403);
    }

    if($currentUser->role->permission_level === 2) {
      $groups = Group::all();
    } else {
      $groups = $currentUser->groups;
    }

    $userGroups = $user->groups;

    foreach($groups as &$group) {
      $inGroup = false;
      foreach($userGroups as $userGroup) {
        if($userGroup['id'] === $group['id']) {
          $inGroup = true;
          $group->active = $userGroup->pivot->active;
          break;
        }
      }
      $group->inGroup = $inGroup;
    }

    return view('edituser', [
      'user' => $user,
      'groups' => $groups,
      'roles' => Role::where('permission_level', '<=', $currentUser->role->permission_level)->get(),
      'updated' => ($updated) ? 'User has been updated.' : '',
    ]);
  }

  public function edit(Request $data) {
    $validator = Validator::make($data->all(), [
      'email' => 'required|email|max:255',
      'username' => 'required|min:3|max:20',
    ]);

    if ($validator->fails()) {
      return redirect()->route('admin.edit.user', ['userId' => $data['userId']])->withErrors($validator)->withInput();
    }

    $user = User::find($data['userId']);

    $user->email = $data['email'];
    $user->username = $data['username'];
    $user->role_id = $data['role'];

    $user->groups()->sync($data['group']);

    $activeGroup = ($data['activeGroup']) ? $data['activeGroup'] : [];
    foreach($user->groups as $group) {
      if(in_array((string)$group['id'], $activeGroup)) {
        $user->groups()->updateExistingPivot($group['id'], ['active' => 1]);
      } else {
        $user->groups()->updateExistingPivot($group['id'], ['active' => 0]);
      }
    }

    $user->save();

    return $this->index($data['userId'], true);
  }
}
