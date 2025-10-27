<?php
use Illuminate\Database\Seeder;
use App\Role;
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('roles')->delete();

      Role::create([
        'name' => 'normal',
        'permission_level' => 0,
      ]);

      Role::create([
        'name' => 'admin',
        'permission_level' => 1,
      ]);

      Role::create([
        'name' => 'super',
        'permission_level' => 2,
      ]);
    }
}
