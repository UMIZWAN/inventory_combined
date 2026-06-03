<?php

namespace Database\Seeders;

use App\Models\AssetAccessLevel;
use App\Models\AssetGroup;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
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
                'sort_order'                  => 1,
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
                'sort_order'                  => 2,
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
                'sort_order'                  => 3,
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
                'sort_order'                  => 4,
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
                'sort_order'                  => 5,
            ],
        ]);

        /**
         * Asset Groups
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
            'name'             => 'C1',
            'description'      => 'All telephone line (land line, mobile line) and internet line',
            'branc_pic'        => 'CSE & Managers',
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
            'name'             => 'D1',
            'description'      => 'Operation tools with less value & smaller size (<RM100) such as spanner, screw driver etc',
            'lifespan_from'    => 12,
            'lifespan_to'      => 36,
            'branc_pic'        => 'Srv Manager & Chief Mechanic',
            'require_asset_no' => false,
        ]);

        AssetGroup::create([
            'name'          => 'D2',
            'description'   => 'Special tools all from principle or any tools (value > RM 100) as management classified in this group',
            'lifespan_from' => 60,
            'lifespan_to'   => 180,
            'branc_pic'     => 'Srv Manager & Chief Mechanic',
        ]);

        AssetGroup::create([
            'name'          => 'E',
            'description'   => 'Motor Vehicles & Bike demo or company or allocated to personel usage',
            'lifespan_from' => 60,
            'lifespan_to'   => 240,
            'branc_pic'     => 'Manager & Store Coordinator',
        ]);

        AssetGroup::create([
            'name'          => 'F',
            'description'   => 'Marketing equipment & Material: Canvas, Canopy, Display Chairs & Tables & all set up materials',
            'lifespan_from' => 12,
            'lifespan_to'   => 36,
            'branc_pic'     => 'Marketing Executive & User',
        ]);

        AssetGroup::create([
            'name'             => 'G',
            'description'      => 'Workshop Equipment & Operation Machine & Tools, Vehicle lift, Air compressor, Jet Cleaner, Engine Crane, Heavy Duty Jack & Stands & etc',
            'lifespan_from'    => 60,
            'lifespan_to'      => 120,
            'branc_pic'        => 'MD',
            'remark'           => 'Will re-update every 3 years interval',
            'require_asset_no' => false,
        ]);
    }
}
