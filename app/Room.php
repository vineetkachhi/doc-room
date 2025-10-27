<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
  public function doctor() {
    return $this->belongsTo('App\Doctor');
  }
}
