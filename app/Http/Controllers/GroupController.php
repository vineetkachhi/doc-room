<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use \Auth;

class GroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
      return view('create-group');
    }

    public function create(Request $data) {
      // Limit amount of groups for now
      $groupCount = group::all()->count();
      $groupKey = (env('GROUP_KEY')) ? decrypt(env('GROUP_KEY')) : 1;

      if($groupCount >= $groupKey) {
        return response(view('errors', ['message' => 'Was not able to create new group.']), 406);
      }

      $group = new Group;
      $group->name = $data['name'];

      $group->save();

      return redirect('home/' . $group->id);
    }

    public function editIndex($groupId, $updated = false) {
      $group = Group::find($groupId);

      return view('editgroup', [
        'group' => $group,
        'updated' => ($updated) ? 'Group has been updated.' : '',
      ]);
    }

    public function edit(Request $data) {
      $group = Group::find($data['groupId']);

      $group->name = $data['name'];
      $group->active = ($data['active']) ? 1 : 0;

      $group->save();

      return $this->editIndex($data['groupId'], true);
    }
}
