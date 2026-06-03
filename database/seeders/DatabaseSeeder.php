<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ShippingOption;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /**
         * Shared lookups — used by both Asset and Marketing.
         */
        $this->seedSuppliers();
        $this->seedShippingOptions();
        $this->seedBranches();

        /**
         * Domain-specific seeders.
         */
        $this->call([
            AssetSeeder::class,
            MarketingSeeder::class,
        ]);

        /**
         * Users — depend on access levels created by the domain seeders.
         */
        $this->seedUsers();
    }

    private function seedSuppliers(): void
    {
        $suppliers = [
            ['name' => 'A-TECH GLOBAL SERVICES',           'phone_no' => '010-947 7896'],
            ['name' => 'A-TECH IT SERVICES',               'phone_no' => '016-3757896'],
            ['name' => 'PRO TECH SALES & SERVICES',        'phone_no' => '012-8087098'],
            ['name' => 'AA STATIONERY SDN BHD',            'phone_no' => '019-851 0559'],
            ['name' => 'TCT TRADING SDN BHD'],
            ['name' => 'SERVAY HYPERMARKET SDN BHD',       'phone_no' => '014-5195686'],
            ['name' => 'ALL IT HYPERMARKET SDN BHD (SHOPEE)', 'phone_no' => 'REFER TO SHOPEE'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier + ['is_active' => true, 'is_deleted' => false]);
        }
    }

    private function seedShippingOptions(): void
    {
        ShippingOption::create(['name' => 'Staff Delivery']);
        ShippingOption::create(['name' => 'Bus']);
        ShippingOption::create(['name' => 'City Link']);
        ShippingOption::create(['name' => 'J&T EXPRESS']);
    }

    private function seedBranches(): void
    {
        $branches = [
            // UM BRANCH
            ['branch_name' => 'UM TWU', 'code' => 'UTW'],
            ['branch_name' => 'UM LD',  'code' => 'ULD'],
            ['branch_name' => 'UM SDK', 'code' => 'USS'],
            ['branch_name' => 'UM KK1', 'code' => 'UK1'],
            ['branch_name' => 'UM KK2', 'code' => 'UK2'],
            ['branch_name' => 'UM KGU', 'code' => 'UKN'],
            ['branch_name' => 'UMTWUCAFE', 'code' => 'TWCAFE'],
            ['branch_name' => 'UMKKCAFE', 'code' => 'KKCAFE'],
            // UMI BRANCH
            ['branch_name' => 'UMI TWU', 'code' => 'ITW'],
            ['branch_name' => 'UMI LD', 'code' => 'ILD'],
            ['branch_name' => 'UMI SDK', 'code' => 'ISS'],
            ['branch_name' => 'UMI KK1', 'code' => 'IK1'],
            ['branch_name' => 'UMI KK2', 'code' => 'IK2'],
            ['branch_name' => 'UMI KK3', 'code' => 'IK3'],
            ['branch_name' => 'UMI KGU', 'code' => 'IKN'],
            ['branch_name' => 'UMI KCH', 'code' => 'IKH'],
            // TM BRANCH
            ['branch_name' => 'TM TWU', 'code' => 'TTW'],
            ['branch_name' => 'TM LD', 'code' => 'TLD'],
            ['branch_name' => 'TM SDK', 'code' => 'TSS'],
            ['branch_name' => 'TM KK', 'code' => 'TKK'],
            ['branch_name' => 'TM KK2', 'code' => 'TKK2'],
            ['branch_name' => 'TM KGU', 'code' => 'TKN'],
            ['branch_name' => 'TM KCH', 'code' => 'TKH'],
            // UCAP BRANCH
            ['branch_name' => 'UCAP KK', 'code' => 'CKK'],
            ['branch_name' => 'UCAP TWU', 'code' => 'CTWU'],
            // U4 BRANCH
            ['branch_name' => 'U4U KK', 'code' => 'U4KK'],
            ['branch_name' => 'U4U TWU', 'code' => 'U4TWU'],
            ['branch_name' => 'U4U SDK', 'code' => 'U4SDK'],
            // UCH BRANCH
            ['branch_name' => 'UCH KK', 'code' => 'UCH'],
            ['branch_name' => 'UCH KK(IMAGO)', 'code' => 'UCHIMG'],
            ['branch_name' => 'UCH TWU', 'code' => 'UCHTWU'],
            ['branch_name' => 'UCH SDK', 'code' => 'UCHSDK'],
            ['branch_name' => 'UCH KGU', 'code' => 'UCHKN'],
            ['branch_name' => 'UCH LD', 'code' => 'UCHLD'],
            ['branch_name' => 'UCHASD KK', 'code' => 'UCHASDKK'],
            ['branch_name' => 'UCHASD TWU', 'code' => 'UCHASDTWU'],
            ['branch_name' => 'UCHASDSDK', 'code' => 'UCHASDSDK'],
            // UEH BRANCH
            ['branch_name' => 'UEH KK', 'code' => 'EHKK'],
            // UJSB BRANCH
            ['branch_name' => 'UJSB', 'code' => 'UJKK'],
            ['branch_name' => 'UJSB (IMAGO)', 'code' => 'UJIMG'],
            // IT BRANCH
            ['branch_name' => 'IT DEPARTMENT', 'code' => 'ITKK'],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch + ['is_active' => true]);
        }
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'MOHD IZWAN BIN MANDA',     'username' => 'ISIZWAN'],
            ['name' => 'FEINZ NEY TONNY',          'username' => 'ISFEINZ'],
            ['name' => 'MISS WONG',                'username' => 'WONG'],
            ['name' => 'NUR AFIQAH CYINDY',        'username' => 'NAFIQAHCYINDY@GMAIL.COM'],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name'                       => $userData['name'],
                'username'                   => $userData['username'],
                'password'                   => Hash::make('1234'),
                'asset_access_level_id'      => 1,
                'marketing_access_level_id'  => 1,
                'is_active'                  => true,
                'is_accessible'              => true,
            ]);

            $user->branches()->attach(41);
        }
    }
}
