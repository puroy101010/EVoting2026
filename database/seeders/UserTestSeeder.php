<?php

namespace Database\Seeders;

use App\Models\Stockholder;
use App\Models\StockholderAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        // Froilan Asuncion
        $user = new User();
        $user->id = 2;
        $user->email = 'asuncion.froilan1010@gmail.com';
        $user->password = bcrypt('admin1234');
        $user->role = 'stockholder';
        $user->createdBy = 1;
        $user->save();

        $user = new User();
        $user->id = 3;
        $user->email = null;
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();


        $stockholder = new Stockholder();
        $stockholder->accountNo = '0001';
        $stockholder->stockholder = 'Froilan Asuncion';
        $stockholder->accountType = 'indv';
        $stockholder->voteInPerson = 'stockholder';
        $stockholder->userId = 2; // Assuming 2 is the ID of the user who created this stockholder
        $stockholder->createdBy = 1; // Assuming 1 is the ID of the user who created this stockholder
        $stockholder->save();

        $account = new StockholderAccount();
        $account->accountKey = '0001-1';
        $account->suffix = 1;
        $account->corpRep =  null;
        $account->authSignatory = null;
        $account->isDelinquent = false;
        $account->userId = 3; // Assuming 1 is the ID of the user who created this account
        $account->stockholderId = 1; // Assuming 1 is the ID of the stockholder associated with this account
        $account->createdBy = 1; // Assuming 1 is the ID of the user who created this account
        $account->save();



        //Ruel Querioso
        $user = new User();
        $user->id = 4;
        $user->email = 'rurru@gmail.com';
        $user->password = bcrypt('admin1234');
        $user->role = 'stockholder';
        $user->createdBy = 1;
        $user->save();

        $user = new User();
        $user->id = 5;
        $user->email = null;
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();

        $user = new User();
        $user->id = 6;
        $user->email = null;
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();


        $stockholder = new Stockholder();
        $stockholder->accountNo = '0002';
        $stockholder->stockholder = 'Ruel Querioso';
        $stockholder->accountType = 'indv';
        $stockholder->voteInPerson = 'stockholder';
        $stockholder->userId = 4; // Assuming 4 is the ID of the user who created this stockholder
        $stockholder->createdBy = 1; // Assuming 1 is the ID of the user who created this stockholder
        $stockholder->save();


        $account = new StockholderAccount();
        $account->accountKey = '0002-1';
        $account->suffix = 1;
        $account->corpRep =  null;
        $account->authSignatory = null;
        $account->isDelinquent = false;
        $account->userId = 5; // Assuming 1 is the ID of the user who created this account
        $account->stockholderId = 2; // Assuming 1 is the ID of the stockholder associated with this account
        $account->createdBy = 1; // Assuming 1 is the ID of the user who created this account
        $account->save();


        $account = new StockholderAccount();
        $account->accountKey = '0002-2';
        $account->suffix = 2;
        $account->corpRep =  null;
        $account->authSignatory = null;
        $account->isDelinquent = false;
        $account->userId = 6; // Assuming 1 is the ID of the user who created this account
        $account->stockholderId = 2; // Assuming 1 is the ID of the stockholder associated with this account
        $account->createdBy = 1; // Assuming 1 is the ID of the user who created this account
        $account->save();






        // Valley Golf and Country Club, Inc.
        $user->save();
        $user = new User();
        $user->id = 7;
        $user->email = 'valleygolf@gmail.com';
        $user->password = null;
        $user->role = 'stockholder';
        $user->createdBy = 1;
        $user->save();



        // 0001-01 - No corporate representative
        $user = new User();
        $user->id = 8;
        $user->email = null;
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();

        // 0001-02 - With corporate representative (Lani Layco)
        $user = new User();
        $user->id = 9;
        $user->email = "lanilayco@gmail.com";
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();

        // 0001-03 - With corporate representative (Rafael)
        $user = new User();
        $user->id = 10;
        $user->email = "rafael@gmail.com";
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();


        // 0001-04 - With corporate representative (Puroy)
        $user->save();
        $user = new User();
        $user->id = 11;
        $user->email = "puroy@gmail.com";
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();


        // 0001-04 - With corporate representative (Puroy)
        $user = new User();
        $user->id = 12;
        $user->email = "puroy@gmail.com";
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();

        // 0001-05 - With corporate representative (Puroy)
        $user = new User();
        $user->id = 13;
        $user->email = "myrighthand@gmail.com";
        $user->password = null;
        $user->role = 'corp-rep';
        $user->createdBy = 1;
        $user->save();





        $stockholder = new Stockholder();
        $stockholder->accountNo = '0003';
        $stockholder->stockholder = 'Valley Golf and Country Club, Inc.';
        $stockholder->accountType = 'corp';
        $stockholder->voteInPerson = 'stockholder';
        $stockholder->userId = 7;
        $stockholder->createdBy = 1;
        $stockholder->save();



        $account = new StockholderAccount();
        $account->accountKey = '0003-1';
        $account->suffix = 1;
        $account->corpRep =  null;
        $account->authSignatory = null;
        $account->isDelinquent = false;
        $account->userId = 8;
        $account->stockholderId = 3;
        $account->createdBy = 1;
        $account->save();


        $account = new StockholderAccount();
        $account->accountKey = '0003-2';
        $account->suffix = 2;
        $account->corpRep =  "Lani Layco";
        $account->authSignatory = null;
        $account->isDelinquent = false;
        $account->userId = 9;
        $account->stockholderId = 3;
        $account->createdBy = 1;
        $account->save();


        $account = new StockholderAccount();
        $account->accountKey = '0003-3';
        $account->suffix = 3;
        $account->corpRep =  "Rafael";
        $account->authSignatory = null;
        $account->isDelinquent = true;
        $account->userId = 10;
        $account->stockholderId = 3;
        $account->createdBy = 1;
        $account->save();



        $account = new StockholderAccount();
        $account->accountKey = '0003-4';
        $account->suffix = 4;
        $account->corpRep =  "Puroy";
        $account->authSignatory = null;
        $account->isDelinquent = false;
        $account->userId = 11;
        $account->stockholderId = 3;
        $account->createdBy = 1;
        $account->save();

        $account = new StockholderAccount();
        $account->accountKey = '0003-5';
        $account->suffix = 5;
        $account->corpRep =  "Puroy";
        $account->authSignatory = null;
        $account->isDelinquent = true;
        $account->userId = 12;
        $account->stockholderId = 3;
        $account->createdBy = 1;
        $account->save();


        $account = new StockholderAccount();
        $account->accountKey = '0003-6';
        $account->suffix = 6;
        $account->corpRep =  "Puroy";
        $account->authSignatory = null;
        $account->isDelinquent = false;
        $account->userId = 13;
        $account->stockholderId = 3;
        $account->createdBy = 1;
        $account->save();
    }
}
