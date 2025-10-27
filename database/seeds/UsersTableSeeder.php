<?php
use Illuminate\Database\Seeder;
use App\User;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('users')->delete();
      // super user
      // User::create([
      //   'name' => 'Raymond Chan',
      //   'email' => 'chanr32@gmail.com',
      //   'password' => Hash::make('chanr32@gmail.com'),
      //   'primary_group_id' => 0,
      //   'role_id' => 3,
      // ]);

      User::create([
        'name' => 'Administrator',
        'email' => 'admin@doctor-room.com',
        'password' => Hash::make(')i6@6NG6t*:.*>I'),
        'primary_group_id' => 0,
        'role_id' => 3,
      ]);
    }
}
