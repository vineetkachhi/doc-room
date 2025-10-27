<?php
use Illuminate\Database\Seeder;
use App\Group;

class TestingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('groups')->delete();

      Group::create([
        'name' => 'Amy\'s Clinic',
      ]);

      Group::create([
        'name' => 'Ray\'s Clinic',
      ]);
    }
}
