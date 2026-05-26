<?php

namespace Database\Seeders;

use App\Models\AssetAccessLevel;
use App\Models\Asset;
use App\Models\AssetGroup;
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
        $now = now();
        AssetAccessLevel::insert([
            [
                'name'                        => 'HQ (Full Access)',
                'created_at'                  => $now,
                'updated_at'                  => $now,
                'add_edit_user'               => true,
                'add_edit_branch'             => true,
                'add_edit_department'         => true,
                'add_edit_asset_grouping'     => true,
                'add_edit_supplier'           => true,
                'add_edit_access'             => true,
                'add_edit_asset'              => true,
                'transfer'                    => true,
                'import_csv'                  => true,
                'approve_disaprove_transfer'  => true,
                'approve_disaprove_dispose'   => true,
                'change_color'                => true,
                'change_user_dept'            => true,
                'sort_order'            => 1,
            ],
            [
                'name'                        => 'HQ',
                'created_at'                  => $now,
                'updated_at'                  => $now,
                'add_edit_user'               => true,
                'add_edit_branch'             => true,
                'add_edit_department'         => true,
                'add_edit_asset_grouping'     => true,
                'add_edit_supplier'           => true,
                'add_edit_access'             => false,
                'add_edit_asset'              => true,
                'transfer'                    => true,
                'import_csv'                  => false,
                'approve_disaprove_transfer'  => true,
                'approve_disaprove_dispose'   => false,
                'change_color'                => true,
                'change_user_dept'            => true,
                'sort_order'            => 2,
            ],
            [
                'name'                        => 'CSE',
                'created_at'                  => $now,
                'updated_at'                  => $now,
                'add_edit_user'               => false,
                'add_edit_branch'             => false,
                'add_edit_department'         => false,
                'add_edit_asset_grouping'     => false,
                'add_edit_supplier'           => false,
                'add_edit_access'             => false,
                'add_edit_asset'              => false,
                'transfer'                    => true,
                'import_csv'                  => false,
                'approve_disaprove_transfer'  => false,
                'approve_disaprove_dispose'   => false,
                'change_color'                => false,
                'change_user_dept'            => false,
                'sort_order'            => 3,
            ],
            [
                'name'                        => 'CSO',
                'created_at'                  => $now,
                'updated_at'                  => $now,
                'add_edit_user'               => false,
                'add_edit_branch'             => false,
                'add_edit_department'         => false,
                'add_edit_asset_grouping'     => false,
                'add_edit_supplier'           => false,
                'add_edit_access'             => false,
                'add_edit_asset'              => false,
                'transfer'                    => true,
                'import_csv'                  => false,
                'approve_disaprove_transfer'  => false,
                'approve_disaprove_dispose'   => false,
                'change_color'                => false,
                'change_user_dept'            => false,
                'sort_order'            => 4,
            ],
            [
                'name'                        => 'View Only',
                'created_at'                  => $now,
                'updated_at'                  => $now,
                'add_edit_user'               => false,
                'add_edit_branch'             => false,
                'add_edit_department'         => false,
                'add_edit_asset_grouping'     => false,
                'add_edit_supplier'           => false,
                'add_edit_access'             => false,
                'add_edit_asset'              => false,
                'transfer'                    => false,
                'import_csv'                  => false,
                'approve_disaprove_transfer'  => false,
                'approve_disaprove_dispose'   => false,
                'change_color'                => false,
                'change_user_dept'            => false,
                'sort_order'            => 5,
            ],
        ]);

        /**
         * Seed for AssetGroup
         */

        AssetGroup::create([
            'name'             => 'A',
            'description'      => 'Land, Building (door, glasses, windows, gates & tiles), Compound Structure, Drainage & Fending, Toilets fixtures, Pantry fixtures, Store fixtures, Floor furnishing & etc',
            'branc_pic'        => 'SOM & Store Coordinator',
            'remark'           => 'No asset no except Aircon. A likely 5 years interval',
            'require_asset_no' => false,
        ]);

        AssetGroup::create([
            'name'                  => 'A1',
            'description'           => 'All fixtures with frequent service necessary: lighting, alarm, piping & etc',
            'lifespan_from'         => 60,
            'lifespan_to'           => 120,
            'service_interval_from' => 6,
            'branc_pic'             => 'SOM & Store Coordinator',
            'remark'                => 'No asset no except Aircon. A likely 5 years interval',
        ]);

        AssetGroup::create([
            'name'                  => 'B',
            'description'           => 'Furniture & Fittings: Table, Chairs, Sofa, Cabinets and etc',
            'lifespan_from'         => 120,
            'lifespan_to'           => 240,
            'service_interval_from' => 24,
            'branc_pic'             => 'CSE & Store Coordinator',
            'remark'                => 'To periodically tighten all screws',
        ]);

        AssetGroup::create([
            'name'                  => 'C',
            'description'           => 'Office Equipment, Automation phone & system, PC, Copier, TV, Projector, Water filter, CCTV, air conditioner & etc',
            'lifespan_from'         => 60,
            'lifespan_to'           => 120,
            'service_interval_from' => 12,
            'service_interval_to'   => 24,
            'branc_pic'             => 'CSE & Store Coordinator',
        ]);

        AssetGroup::create([
            'name'                  => 'C1',
            'description'           => 'All telephone line (land line, mobile line) and internet line',
            'branc_pic'             => 'CSE & Managers',
            'require_asset_no' => false,
        ]);

        AssetGroup::create([
            'name'                  => 'D',
            'description'           => 'Workshop Equipment & Operation Machine & Tools, Vehicle lift, Air compressor, Jet Cleaner, Engine Crane, Heavy Duty Jack & Stands & etc',
            'lifespan_from'         => 60,
            'lifespan_to'           => 180,
            'service_interval_from' => 6,
            'service_interval_to'   => 24,
            'branc_pic'             => 'Srv Manager & Chief Mechanic',
        ]);

        AssetGroup::create([
            'name'                  => 'D1',
            'description'           => 'Operation tools with less value & smaller size (<RM100) such as spanner, screw driver etc',
            'lifespan_from'         => 12,
            'lifespan_to'           => 36,
            'branc_pic'             => 'Srv Manager & Chief Mechanic',
            'require_asset_no' => false,
        ]);

        AssetGroup::create([
            'name'                  => 'D2',
            'description'           => 'Special tools all from principle or any tools (value > RM 100) as management classified in this group',
            'lifespan_from'         => 60,
            'lifespan_to'           => 180,
            'branc_pic'             => 'Srv Manager & Chief Mechanic',
        ]);

        AssetGroup::create([
            'name'                  => 'E',
            'description'           => 'Motor Vehicles & Bike demo or company or allocated to personel usage',
            'lifespan_from'         => 60,
            'lifespan_to'           => 240,
            'branc_pic'             => 'Manager & Store Coordinator',
        ]);

        AssetGroup::create([
            'name'                  => 'F',
            'description'           => 'Marketing equipment & Material: Canvas, Canopy, Display Chairs & Tables & all set up materials',
            'lifespan_from'         => 12,
            'lifespan_to'           => 36,
            'branc_pic'             => 'Marketing Executive & User',
        ]);

        AssetGroup::create([
            'name'                  => 'G',
            'description'           => 'Workshop Equipment & Operation Machine & Tools, Vehicle lift, Air compressor, Jet Cleaner, Engine Crane, Heavy Duty Jack & Stands & etc',
            'lifespan_from'         => 60,
            'lifespan_to'           => 120,
            'branc_pic'             => 'MD',
            'remark'                => 'Will re-update every 3 years interval',
            'require_asset_no'      => false,
        ]);

        /**
         * Seed for Supplier
         */
        Supplier::create([
            'name'       => 'A-TECH GLOBAL SERVICES',
            'phone_no'   => '010-947 7896',
            'is_active'  => true,
            'is_deleted' => false,
        ]);

        Supplier::create([
            'name'       => 'A-TECH IT SERVICES',
            'phone_no'   => '016-3757896',
            'is_active'  => true,
            'is_deleted' => false,
        ]);

        Supplier::create([
            'name'       => 'PRO TECH SALES & SERVICES',
            'phone_no'   => '012-8087098',
            'is_active'  => true,
            'is_deleted' => false,
        ]);

        Supplier::create([
            'name'       => 'AA STATIONERY SDN BHD',
            'phone_no'   => '019-851 0559',
            'is_active'  => true,
            'is_deleted' => false,
        ]);

        Supplier::create([
            'name'       => 'TCT TRADING SDN BHD',
        ]);

        Supplier::create([
            'name'       => 'SERVAY HYPERMARKET SDN BHD',
            'phone_no'   => '014-5195686',
        ]);

        Supplier::create([
            'name'       => 'ALL IT HYPERMARKET SDN BHD (SHOPEE)',
            'phone_no'   => 'REFER TO SHOPEE',
        ]);

        /**
         * Seed for ShippingOption
         */
        ShippingOption::create(['name' => 'Staff Delivery']);
        ShippingOption::create(['name' => 'Bus']);
        ShippingOption::create(['name' => 'City Link']);
        ShippingOption::create(['name' => 'J&T EXPRESS']);

        /**
         * Seed branches.
         */
        $branches = [
            // UM BRANCH
            ['branch_name' => 'UM TWU', 'code' => 'UTW', 'is_active' => true],
            ['branch_name' => 'UM LD',  'code' => 'ULD', 'is_active' => true],
            ['branch_name' => 'UM SDK', 'code' => 'USS', 'is_active' => true],
            ['branch_name' => 'UM KK1', 'code' => 'UK1', 'is_active' => true],
            ['branch_name' => 'UM KK2', 'code' => 'UK2', 'is_active' => true],
            ['branch_name' => 'UM KGU', 'code' => 'UKN', 'is_active' => true],
            ['branch_name' => 'UMTWUCAFE', 'code' => 'TWCAFE', 'is_active' => true],
            ['branch_name' => 'UMKKCAFE', 'code' => 'KKCAFE', 'is_active' => true],
            // UMI BRANCH
            ['branch_name' => 'UMI TWU', 'code' => 'ITW', 'is_active' => true],
            ['branch_name' => 'UMI LD', 'code' => 'ILD', 'is_active' => true],
            ['branch_name' => 'UMI SDK', 'code' => 'ISS', 'is_active' => true],
            ['branch_name' => 'UMI KK1', 'code' => 'IK1', 'is_active' => true],
            ['branch_name' => 'UMI KK2', 'code' => 'IK2', 'is_active' => true],
            ['branch_name' => 'UMI KK3', 'code' => 'IK3', 'is_active' => true],
            ['branch_name' => 'UMI KGU', 'code' => 'IKN', 'is_active' => true],
            ['branch_name' => 'UMI KCH', 'code' => 'IKH', 'is_active' => true],
            // TM BRANCH
            ['branch_name' => 'TM TWU', 'code' => 'TTW', 'is_active' => true],
            ['branch_name' => 'TM LD', 'code' => 'TLD', 'is_active' => true],
            ['branch_name' => 'TM SDK', 'code' => 'TSS', 'is_active' => true],
            ['branch_name' => 'TM KK', 'code' => 'TKK', 'is_active' => true],
            ['branch_name' => 'TM KK2', 'code' => 'TKK2', 'is_active' => true],
            ['branch_name' => 'TM KGU', 'code' => 'TKN', 'is_active' => true],
            ['branch_name' => 'TM KCH', 'code' => 'TKH', 'is_active' => true],
            // UCAP BRANCH
            ['branch_name' => 'UCAP KK', 'code' => 'CKK', 'is_active' => true],
            ['branch_name' => 'UCAP TWU', 'code' => 'CTWU', 'is_active' => true],
            // U4 BRANCH
            ['branch_name' => 'U4U KK', 'code' => 'U4KK', 'is_active' => true],
            ['branch_name' => 'U4U TWU', 'code' => 'U4TWU', 'is_active' => true],
            ['branch_name' => 'U4U SDK', 'code' => 'U4SDK', 'is_active' => true],
            // UCH BRANCH
            ['branch_name' => 'UCH KK', 'code' => 'UCH', 'is_active' => true],
            ['branch_name' => 'UCH KK(IMAGO)', 'code' => 'UCHIMG', 'is_active' => true],
            ['branch_name' => 'UCH TWU', 'code' => 'UCHTWU', 'is_active' => true],
            ['branch_name' => 'UCH SDK', 'code' => 'UCHSDK', 'is_active' => true],
            ['branch_name' => 'UCH KGU', 'code' => 'UCHKN', 'is_active' => true],
            ['branch_name' => 'UCH LD', 'code' => 'UCHLD', 'is_active' => true],
            ['branch_name' => 'UCHASD KK', 'code' => 'UCHASDKK', 'is_active' => true],
            ['branch_name' => 'UCHASD TWU', 'code' => 'UCHASDTWU', 'is_active' => true],
            ['branch_name' => 'UCHASDSDK', 'code' => 'UCHASDSDK', 'is_active' => true],
            // UEH BRANCH
            ['branch_name' => 'UEH KK', 'code' => 'EHKK', 'is_active' => true],
            // UJSB BRANCH
            ['branch_name' => 'UJSB', 'code' => 'UJKK', 'is_active' => true],
            ['branch_name' => 'UJSB (IMAGO)', 'code' => 'UJIMG', 'is_active' => true],
            // IT BRANCH
            ['branch_name' => 'IT DEPARTMENT', 'code' => 'ITKK', 'is_active' => true],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        $user = User::create([
            'name' => 'MOHD IZWAN BIN MANDA',
            'username' => 'ISIZWAN',
            'password' => Hash::make('1234'),
            'asset_access_level_id' => 1,
            'is_active' => true,
            'is_accessible' => true,
        ]);

        // Attach branch ID = 1
        $user->branches()->attach(41);

        $user2 = User::create([
            'name' => 'FEINZ NEY TONNY',
            'username' => 'ISFEINZ',
            'password' => Hash::make('1234'),
            'asset_access_level_id' => 1,
            'is_active' => true,
            'is_accessible' => true,
        ]);

        $user2->branches()->attach(41);

        $user3 = User::create([
            'name' => 'MISS WONG',
            'username' => 'WONG',
            'password' => Hash::make('1234'),
            'asset_access_level_id' => 1,
            'is_active' => true,
            'is_accessible' => true,
        ]);

        $user3->branches()->attach(41);

        $user4 = User::create([
            'name' => 'NUR AFIQAH CYINDY',
            'username' => 'NAFIQAHCYINDY@GMAIL.COM',
            'password' => Hash::make('1234'),
            'asset_access_level_id' => 1,
            'is_active' => true,
            'is_accessible' => true,
        ]);

        $user4->branches()->attach(41);
    }
}
