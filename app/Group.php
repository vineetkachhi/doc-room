<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
  public function users() {
    return $this->belongsToMany('App\User')->withTimestamps();
  }

  public function rooms() {
    return $this->hasMany('App\Room')->orderBy('sort_index');
  }

  public function doctors() {
    return $this->hasMany('App\Doctor');
  }
}
