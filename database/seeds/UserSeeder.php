<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //cabang alam sutera
        DB::table('users')->insert([
            'staffing_number' => '12345',
            'username' => 'drh Rambad',
            'fullname' => 'Rambat Santoso',
            'email' => 'rambgmu@gmail.com',
            'password' => bcrypt('P@ssw0rd12345'),
            'phone_number' => '085947566558',
            'role' => 'admin',
            'branch_id' => 1,
            'status' => '1',
            'created_by' => 'Rambat Santoso',
            'created_at' => '2022-02-03'
        ]);

        DB::table('branches')->insert([
          'branch_code' => '001',
          'branch_name' => 'Pondok Aren',
          'isDeleted' => 0,
          'user_id' => 1,
          'user_update_id' => 1,
          'created_at' => '2022-02-03',
          'updated_at' => '2022-02-03',
          'address' => 'Pondok Aren'
      ]);
    }
}
