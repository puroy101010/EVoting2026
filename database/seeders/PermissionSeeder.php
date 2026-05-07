<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            ['permission' => 'view candidate', 'guard_name' => 'web', 'module' => 'candidate', 'description' => 'view candidates'],
            ['permission' => 'create candidate', 'guard_name' => 'web', 'module' => 'candidate', 'description' => 'create candidates'],
            ['permission' => 'edit candidate', 'guard_name' => 'web', 'module' => 'candidate', 'description' => 'edit candidates'],
            ['permission' => 'delete candidate', 'guard_name' => 'web', 'module' => 'candidate', 'description' => 'delete candidates'],


            ['permission' => 'view agenda', 'guard_name' => 'web', 'module' => 'agenda', 'description' => 'view agendas'],
            ['permission' => 'create agenda', 'guard_name' => 'web', 'module' => 'agenda', 'description' => 'create agendas'],
            ['permission' => 'edit agenda', 'guard_name' => 'web', 'module' => 'agenda', 'description' => 'edit agendas'],
            ['permission' => 'delete agenda', 'guard_name' => 'web', 'module' => 'agenda', 'description' => 'delete agendas'],


            ['permission' => 'view amendment', 'guard_name' => 'web', 'module' => 'amendment', 'description' => 'view amendments'],
            ['permission' => 'create amendment', 'guard_name' => 'web', 'module' => 'amendment', 'description' => 'create amendments'],
            ['permission' => 'edit amendment', 'guard_name' => 'web', 'module' => 'amendment', 'description' => 'edit amendments'],
            ['permission' => 'delete amendment', 'guard_name' => 'web', 'module' => 'amendment', 'description' => 'delete amendments'],

            ['permission' => 'view stockholder', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'view stockholders'],
            ['permission' => 'create stockholder', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'create stockholders'],
            ['permission' => 'edit stockholder', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'edit stockholders'],
            ['permission' => 'delete stockholder', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'delete stockholders'],
            ['permission' => 'export stockholder', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'export stockholders'],
            ['permission' => 'edit stockholder email', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'edit stockholder email'],
            ['permission' => 'edit corporate representative email', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'edit corporate representative email'],
            ['permission' => 'mark stock delinquent', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'mark stockr delinquent'],
            ['permission' => 'mark stock active', 'guard_name' => 'web', 'module' => 'stockholder', 'description' => 'mark stock active'],

            ['permission' => 'view non member', 'guard_name' => 'web', 'module' => 'non member', 'description' => 'view non members'],
            ['permission' => 'create non member', 'guard_name' => 'web', 'module' => 'non member', 'description' => 'create non members'],
            ['permission' => 'edit non member', 'guard_name' => 'web', 'module' => 'non member', 'description' => 'edit non members'],
            ['permission' => 'delete non member', 'guard_name' => 'web', 'module' => 'non member', 'description' => 'delete non members'],
            ['permission' => 'restore non member', 'guard_name' => 'web', 'module' => 'non member', 'description' => 'restore non members'],


            ['permission' => 'view role', 'guard_name' => 'web', 'module' => 'role', 'description' => 'view roles'],
            ['permission' => 'create role', 'guard_name' => 'web', 'module' => 'role', 'description' => 'create roles'],
            ['permission' => 'edit role', 'guard_name' => 'web', 'module' => 'role', 'description' => 'edit roles'],
            ['permission' => 'delete role', 'guard_name' => 'web', 'module' => 'role', 'description' => 'delete roles'],

            ['permission' => 'view setting', 'guard_name' => 'web', 'module' => 'setting', 'description' => 'view settings'],
            ['permission' => 'configure stockholder voting period', 'guard_name' => 'web', 'module' => 'setting', 'description' => 'configure stockholder voting period'],
            ['permission' => 'configure number of vote per share', 'guard_name' => 'web', 'module' => 'setting', 'description' => 'configure number of vote per share'],
            ['permission' => 'configure amendment module', 'guard_name' => 'web', 'module' => 'setting', 'description' => 'enable amendment module'],
            ['permission' => 'configure otp login', 'guard_name' => 'web', 'module' => 'setting', 'description' => 'enable OTP login'],
            ['permission' => 'configure voting confirmation receipt', 'guard_name' => 'web', 'module' => 'setting', 'description' => 'enable voting confirmation receipt'],

            ['permission' => 'view permission', 'guard_name' => 'web', 'module' => 'permission', 'description' => 'view permissions'],
            ['permission' => 'create permission', 'guard_name' => 'web', 'module' => 'permission', 'description' => 'create permissions'],
            ['permission' => 'edit permission', 'guard_name' => 'web', 'module' => 'permission', 'description' => 'edit permissions'],
            ['permission' => 'delete permission', 'guard_name' => 'web', 'module' => 'permission', 'description' => 'delete permissions'],


            ['permission' => 'view admin account', 'guard_name' => 'web', 'module' => 'admin account', 'description' => 'view admin accounts'],
            ['permission' => 'create admin account', 'guard_name' => 'web', 'module' => 'admin account', 'description' => 'create admin accounts'],
            ['permission' => 'edit admin account', 'guard_name' => 'web', 'module' => 'admin account', 'description' => 'edit admin accounts'],
            ['permission' => 'delete admin account', 'guard_name' => 'web', 'module' => 'admin account', 'description' => 'delete admin accounts'],
            ['permission' => 'reset admin password', 'guard_name' => 'web', 'module' => 'admin account', 'description' => 'reset admin password'],

            ['permission' => 'view superadmin account', 'guard_name' => 'web', 'module' => 'superadmin account', 'description' => 'view superadmin accounts'],
            ['permission' => 'create superadmin account', 'guard_name' => 'web', 'module' => 'superadmin account', 'description' => 'create superadmin accounts'],
            ['permission' => 'edit superadmin account', 'guard_name' => 'web', 'module' => 'superadmin account', 'description' => 'edit superadmin accounts'],
            ['permission' => 'delete superadmin account', 'guard_name' => 'web', 'module' => 'superadmin account', 'description' => 'delete superadmin accounts'],



            ['permission' => 'assign bod proxy', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'Assign a Board of Directors proxy'],
            ['permission' => 'cancel bod proxy', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'Cancel a Board of Directors proxy'],
            ['permission' => 'verify bod proxy', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'Mark a Board of Directors proxy as verified'],
            ['permission' => 'remove bod proxy audit', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'Remove audit mark from Board of Directors proxy'],

            ['permission' => 'assign amendment proxy', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'Assign amendment proxy'],
            ['permission' => 'cancel amendment proxy', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'Cancel amendment proxy'],
            ['permission' => 'verify amendment proxy', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'Mark amendment proxy as verified'],
            ['permission' => 'remove amendment proxy audit', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'Remove audit mark from amendment proxy'],

            ['permission' => 'view bod proxy masterlist', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'View all Board of Directors proxies'],
            ['permission' => 'view active bod proxy', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'View active Board of Directors proxies'],
            ['permission' => 'view bod proxy summary', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'View summary of Board of Directors proxies'],
            ['permission' => 'view bod proxy history', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'View history of Board of Directors proxies'],
            ['permission' => 'view bod proxy assignor', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'View Board of Directors proxy assignors'],
            ['permission' => 'view bod proxy', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'View Board of Directors proxy from the stockholder section'],

            ['permission' => 'view amendment proxy masterlist', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'View all amendment proxies'],
            ['permission' => 'view active amendment proxy', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'View active amendment proxies'],
            ['permission' => 'view amendment proxy summary', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'View summary of amendment proxies'],
            ['permission' => 'view amendment proxy history', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'View history of amendment proxies'],
            ['permission' => 'view amendment proxy assignor', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'View amendment proxy assignors'],
            ['permission' => 'view amendment proxy', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'View amendment proxy from the stockholder section'],

            ['permission' => 'view ballot', 'guard_name' => 'web', 'module' => 'ballot', 'description' => 'View ballot'],
            ['permission' => 'export ballot', 'guard_name' => 'web', 'module' => 'ballot', 'description' => 'Export ballot'],

            ['permission' => 'update terms and conditions', 'guard_name' => 'web', 'module' => 'terms and conditions', 'description' => 'Update terms and conditions'],


            ['permission' => 'export board of director masterlist proxies', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'Export Board of Directors masterlist proxies'],
            ['permission' => 'export active board of director proxies', 'guard_name' => 'web', 'module' => 'board of director proxy', 'description' => 'Export active Board of Directors proxies'],

            ['permission' => 'export amendment masterlist proxies', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'Export Amendment masterlist proxies'],
            ['permission' => 'export active amendment proxies', 'guard_name' => 'web', 'module' => 'amendment proxy', 'description' => 'Export active Amendment proxies'],

            ['permission' => 'view attendance', 'guard_name' => 'web', 'module' => 'attendance', 'description' => 'View attendance'],
            ['permission' => 'export attendance', 'guard_name' => 'web', 'module' => 'attendance', 'description' => 'Export attendance'],
            ['permission' => 'print attendance summary', 'guard_name' => 'web', 'module' => 'attendance', 'description' => 'Print attendance summary'],

            ['permission' => 'view activity logs', 'guard_name' => 'web', 'module' => 'activity log', 'description' => 'View activity logs'],

            ['permission' => 'view ballot details', 'guard_name' => 'web', 'module' => 'ballot', 'description' => 'View ballot details'],

            ['permission' => 'view available vote inquiry', 'guard_name' => 'web', 'module' => 'inquiry', 'description' => 'View available vote inquiry page details'],
            ['permission' => 'print proxy by assignee', 'guard_name' => 'web', 'module' => 'report', 'description' => 'Print valid proxies per assignee'],
            ['permission' => 'view developer stock page', 'guard_name' => 'web', 'module' => 'developer', 'description' => 'View developer stock information page'],



        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                [
                    'name' => $permission['permission'],
                    'guard_name' => $permission['guard_name'],
                    'module' => $permission['module'],
                    'description' => $permission['description']
                ],

            );
        }
    }
}
