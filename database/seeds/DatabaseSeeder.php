<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::table('users')->delete();

        $users = array(

            ['first_name' => 'Shailesh Kumar','username' => 'shailesh', 'mobile' => 1234567890,  'email' => 'slskmr007@scholarspace.org', 'password' => Hash::make(123456),'usertype_id'=>1,'code'=>'qwe4bd55bdb'],
            ['first_name' => 'Dibyanshu', 'username' => 'dibyanshu', 'mobile' => 1234567891,'email' => 'divyanshu@scholarspace.org', 'password' => Hash::make(123456),'usertype_id'=>2,'code'=>'qwe4bd55bdb'],
            ['first_name' => 'Arun Meena', 'username' => 'arun' , 'mobile' => 1234567892, 'email' => 'arun@scholarspace.org', 'password' => Hash::make(123456),'usertype_id'=>1,'code'=>'qwe4bd55bdb'],

            );

        // Loop through each user above and create the record for them in the database
        

        foreach ($users as $user)
        {
            User::create($user);
        }
        
        Model::reguard();
    }
}
