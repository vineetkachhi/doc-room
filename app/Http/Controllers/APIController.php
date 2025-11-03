<?php

namespace App\Http\Controllers;

use Psr\Log\LoggerInterface;
use Illuminate\Http\Request;
use App\Helpers\DateTimeFormatter;
use App\Room;
use App\Doctor;
use App\Group;
use Ably;

class APIController extends Controller
{
  private $logger;

  public function __construct(LoggerInterface $logger = null)
  {
    $this->logger = $logger;
  }
  public function broadcast($groupId)
  {
    $ably = Ably::channel('door-room-channel_' . env('HEROKU_APP_NAME') . '_' . $groupId)->publish('update', '');
  }

  public function add(Request $data)
  {
    try {
      if ($data['room']) {
        $room = new Room;
        $room->name = $data['room'];
        $room->group_id = $data['groupId'];

        $room->save();
      }

      if ($data['doctor']) {
        // Get current max sort_index for the group
        $maxSort = Doctor::where('group_id', $data['groupId'])->max('sort_index');

        // Create new doctor
        $doctor = new Doctor;
        $doctor->name = $data['doctor'];
        $doctor->group_id = $data['groupId'];
        $doctor->sort_index = $maxSort + 1; // Set new max + 1
        $doctor->save();
      }

      $this->broadcast($data['groupId']);

      return ['success' => true];
    } catch (\Exception $e) {
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function move(Request $data)
  {
    if ($this->logger) {
      $this->logger->info('Doing work');
    }
    try {

      if (!isset($data['roomId']) || !isset($data['doctorId'])) {
        // dd($data['doctorId']);
        return ['success' => false, 'message' => 'roomId or doctorId missing.'];
      }

      $room = Room::find($data['roomId']);

      if (!$room) {
        return ['success' => false, 'message' => 'room not found.'];
      }

      $count = Room::where('doctor_id', $data['doctorId'])->where('group_id', $data['groupId'])->count();

      $room->doctor_id = $data['doctorId'];
      $room->sort_index = $count;
      if ($data['from'] == 0) {
        $room->assigned_at = date("Y-m-d H:i:s");
      }
      if ($data['doctorId'] == 0) {
        $room->assigned_at = null;
      }
      $room->save();

      // sort old column
      $oldColumn = Room::where('doctor_id', $data['from'])->where('group_id', $data['groupId'])->get()->sortBy('sort_index');

      $counter = 0;
      foreach ($oldColumn as $room) {
        $room->sort_index = $counter++;
        $room->save();
      }

      $this->broadcast($data['groupId']);

      return ['success' => true, 'message' => 'room moved.'];
    } catch (\Exception $e) {
      $this->logger->error('Oh no!', array('exception' => $e));
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function sort(Request $data)
  {
    try {
      $rooms = $data['rooms'];

      if (!$rooms) {
        return ['success' => false, 'message' => 'rooms is empty.'];
      }

      foreach ($rooms as $room) {
        $entity = Room::find($room['id']);
        $entity->sort_index = $room['sort_index'];
        $entity->save();
      }

      $this->broadcast($data['groupId']);

      return ['success' => true, 'message' => 'room sorted.'];
    } catch (\Exception $e) {
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function remove(Request $data)
  {
    $type = $data['type'];
    $id = $data['id'];

    if ($type === 'room') {
      $entity = Room::find($id);
    }
    if ($type === 'doctor') {
      $entity = Doctor::find($id);
      $rooms = $entity->rooms;

      foreach ($rooms as $room) {
        $room->doctor_id = 0;
        $room->save();
      }
    }

    if ($entity) {
      $entity->delete();
    } else {
      return ['success' => false, 'message' => 'entity not found.'];
    }

    $this->broadcast($data['groupId']);

    return ['success' => true];
  }

  public function setWidth(Request $data)
  {
    $group = Group::find($data['groupId']);

    $group->room_column_width = $data['roomWidth'];
    $group->doctor_column_width = $data['doctorWidth'];
    $group->save();

    $this->broadcast($data['groupId']);

    return ['success' => true];
  }

  public function refreshAll(Request $data)
  {
    $group = Group::find($data['groupId']);
    $rooms = $group->rooms;
    $doctors = $group->doctors()->orderBy('sort_index', 'asc')->get();
    $unassignedRooms = [];

    foreach ($doctors as &$doctor) {
      $temp = [];
      foreach ($rooms as $room) {
        if ($room['doctor_id'] === $doctor['id']) {
          $roomTimerData = DateTimeFormatter::getTimeDifference($room->assigned_at);
          $temp[] = [
            'id' => $room->id,
            'name' => $room->name,
            'doctor_id' => $room->doctor_id,
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

    foreach ($rooms as $room) {
      if ($room['doctor_id'] == 0) {
        $unassignedRooms[] = [
          'id' => $room->id,
          'name' => $room->name,
          'doctor_id' => $room->doctor_id,
          'sort_index' => $room->sort_index,
        ];
      }
    }

    return [
      'unassignedRooms' => $unassignedRooms,
      'doctors' => $doctors,
      'roomWidth' => $group->room_column_width,
      'doctorWidth' => $group->doctor_column_width,
    ];
  }


  public function updateDoctor(Request $data)
  {
    try {
      if (!isset($data['id']) || !isset($data['name'])) {
        return ['success' => false, 'message' => 'doctorId or doctorName missing.'];
      }

      $doctor = Doctor::find($data['id']);

      if (!$doctor) {
        return ['success' => false, 'message' => 'doctor not found.'];
      }

      $doctor->name = $data['name'];
      $doctor->save();

      $this->broadcast($data['groupId']);

      return ['success' => true];
    } catch (\Exception $e) {
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }


  public function sortDoctor(Request $data)
  {
    try {
      $doctors = $data['doctors'];
      // dd($doctors);
      if (!$doctors) {
        return ['success' => false, 'message' => 'doctors is empty.'];
      }

      foreach ($doctors as $doctor) {
        $entity = Doctor::find($doctor['id']);
        $entity->sort_index = $doctor['sort_index'];
        $entity->save();
      }

      $this->broadcast($data['groupId']);

      return ['success' => true, 'message' => 'doctor sorted.'];
    } catch (\Exception $e) {
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }
}
