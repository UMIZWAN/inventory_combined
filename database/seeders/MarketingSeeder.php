<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\MarketingAccessLevel;
use App\Models\MarketingCategory;
use App\Models\MarketingItem;
use App\Models\MarketingItemValue;
use App\Models\MarketingTransactionPurpose;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        MarketingAccessLevel::insert([
            [
                'name'                       => 'HQ (Full Access)',
                'created_at'                 => $now,
                'updated_at'                 => $now,
                'settings'                   => true,
                'add_edit_role'              => true,
                'view_role'                  => true,
                'add_edit_user'              => true,
                'view_user'                  => true,
                'add_edit_asset'             => true,
                'view_asset'                 => true,
                'view_asset_masterlist'      => true,
                'add_edit_branch'            => true,
                'view_branch'                => true,
                'add_edit_transaction'       => true,
                'view_transaction'           => true,
                'approve_reject_transaction' => true,
                'receive_transaction'        => true,
                'amend_asset'                => true,
                'add_edit_purchase_order'    => true,
                'view_purchase_order'        => true,
                'add_edit_supplier'          => true,
                'view_supplier'              => true,
                'add_edit_tax'               => true,
                'view_tax'                   => true,
                'view_reports'               => true,
                'download_reports'           => true,
                'sort_order'                 => 1,
            ],
            [
                'name'                       => 'Manager',
                'created_at'                 => $now,
                'updated_at'                 => $now,
                'settings'                   => false,
                'add_edit_role'              => false,
                'view_role'                  => true,
                'add_edit_user'              => false,
                'view_user'                  => true,
                'add_edit_asset'             => true,
                'view_asset'                 => true,
                'view_asset_masterlist'      => true,
                'add_edit_branch'            => false,
                'view_branch'                => true,
                'add_edit_transaction'       => true,
                'view_transaction'           => true,
                'approve_reject_transaction' => true,
                'receive_transaction'        => true,
                'amend_asset'                => false,
                'add_edit_purchase_order'    => true,
                'view_purchase_order'        => true,
                'add_edit_supplier'          => false,
                'view_supplier'              => true,
                'add_edit_tax'               => false,
                'view_tax'                   => true,
                'view_reports'               => true,
                'download_reports'           => true,
                'sort_order'                 => 2,
            ],
            [
                'name'                       => 'Operator',
                'created_at'                 => $now,
                'updated_at'                 => $now,
                'settings'                   => false,
                'add_edit_role'              => false,
                'view_role'                  => false,
                'add_edit_user'              => false,
                'view_user'                  => false,
                'add_edit_asset'             => false,
                'view_asset'                 => true,
                'view_asset_masterlist'      => true,
                'add_edit_branch'            => false,
                'view_branch'                => true,
                'add_edit_transaction'       => true,
                'view_transaction'           => true,
                'approve_reject_transaction' => false,
                'receive_transaction'        => true,
                'amend_asset'                => false,
                'add_edit_purchase_order'    => false,
                'view_purchase_order'        => true,
                'add_edit_supplier'          => false,
                'view_supplier'              => true,
                'add_edit_tax'               => false,
                'view_tax'                   => false,
                'view_reports'               => false,
                'download_reports'           => false,
                'sort_order'                 => 3,
            ],
            [
                'name'                       => 'View Only',
                'created_at'                 => $now,
                'updated_at'                 => $now,
                'settings'                   => false,
                'add_edit_role'              => false,
                'view_role'                  => false,
                'add_edit_user'              => false,
                'view_user'                  => false,
                'add_edit_asset'             => false,
                'view_asset'                 => true,
                'view_asset_masterlist'      => true,
                'add_edit_branch'            => false,
                'view_branch'                => true,
                'add_edit_transaction'       => false,
                'view_transaction'           => true,
                'approve_reject_transaction' => false,
                'receive_transaction'        => false,
                'amend_asset'                => false,
                'add_edit_purchase_order'    => false,
                'view_purchase_order'        => true,
                'add_edit_supplier'          => false,
                'view_supplier'              => true,
                'add_edit_tax'               => false,
                'view_tax'                   => false,
                'view_reports'               => false,
                'download_reports'           => false,
                'sort_order'                 => 4,
            ],
        ]);

        /**
         * Item categories
         */
        MarketingCategory::create(['name' => 'Promotional Items']);
        MarketingCategory::create(['name' => 'Print Materials']);
        MarketingCategory::create(['name' => 'Signage']);
        MarketingCategory::create(['name' => 'Event Supplies']);
        MarketingCategory::create(['name' => 'Apparel']);

        /**
         * Transaction purposes
         */
        MarketingTransactionPurpose::create(['transaction_purpose_name' => 'Branch Restock']);
        MarketingTransactionPurpose::create(['transaction_purpose_name' => 'Event / Roadshow']);
        MarketingTransactionPurpose::create(['transaction_purpose_name' => 'Customer Giveaway']);
        MarketingTransactionPurpose::create(['transaction_purpose_name' => 'Internal Use']);
        MarketingTransactionPurpose::create(['transaction_purpose_name' => 'Damaged / Replacement']);

        $this->seedItems();
    }

    private function seedItems(): void
    {
        $categoryId = MarketingCategory::pluck('id', 'name');
        $branchId   = Branch::pluck('id', 'code');

        $items = [
            [
                'item_running_number' => 'PUM01',
                'name'                => 'UM ISUZU ABOARD',
                'type'                => '3.5ft x 1.5ft',
                'category'            => 'Promotional Items',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 150.00,
                'sales_cost'          => 150.00,
                'stable_unit'         => 5,
                'stock'               => [['USS', 2]],
            ],
            [
                'item_running_number' => 'GUG01',
                'name'                => 'UNIVERSAL GROUP TRAVEL ORGANIZER SET',
                'type'                => 'BLACK COLOR',
                'category'            => 'Promotional Items',
                'unit_measure'        => 'set',
                'purchase_cost'       => 18.00,
                'sales_cost'          => 28.00,
                'stable_unit'         => 10,
                'stock'               => [['USS', 2], ['UK1', 5]],
            ],
            [
                'item_running_number' => 'BRO01',
                'name'                => 'PRODUCT BROCHURE TRI-FOLD',
                'type'                => 'A4 GLOSSY',
                'category'            => 'Print Materials',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 0.45,
                'sales_cost'          => 1.00,
                'stable_unit'         => 200,
                'stock'               => [['UTW', 150], ['ULD', 80]],
            ],
            [
                'item_running_number' => 'FLY01',
                'name'                => 'FLYER A5',
                'type'                => 'A5 COATED',
                'category'            => 'Print Materials',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 0.20,
                'sales_cost'          => 0.50,
                'stable_unit'         => 500,
                'stock'               => [['UTW', 320]],
            ],
            [
                'item_running_number' => 'SIG01',
                'name'                => 'OUTDOOR PVC BANNER',
                'type'                => '10ft x 4ft',
                'category'            => 'Signage',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 80.00,
                'sales_cost'          => 120.00,
                'stable_unit'         => 3,
                'stock'               => [['UTW', 2], ['UK1', 1]],
            ],
            [
                'item_running_number' => 'SIG02',
                'name'                => 'FLOOR STAND DISPLAY',
                'type'                => 'METAL, 6ft',
                'category'            => 'Signage',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 220.00,
                'sales_cost'          => 280.00,
                'stable_unit'         => 2,
                'stock'               => [['USS', 1]],
            ],
            [
                'item_running_number' => 'EVT01',
                'name'                => 'CANOPY 10x10',
                'type'                => 'BLUE',
                'category'            => 'Event Supplies',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 350.00,
                'sales_cost'          => 450.00,
                'stable_unit'         => 2,
                'stock'               => [['UK1', 1], ['UK2', 1]],
            ],
            [
                'item_running_number' => 'EVT02',
                'name'                => 'FOLDING DISPLAY TABLE',
                'type'                => '6ft',
                'category'            => 'Event Supplies',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 180.00,
                'sales_cost'          => 240.00,
                'stable_unit'         => 4,
                'stock'               => [['UTW', 3]],
            ],
            [
                'item_running_number' => 'APP01',
                'name'                => 'POLO SHIRT BRANDED',
                'type'                => 'NAVY, MEDIUM',
                'category'            => 'Apparel',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 32.00,
                'sales_cost'          => 55.00,
                'stable_unit'         => 20,
                'stock'               => [['UTW', 15], ['ULD', 10], ['USS', 8]],
            ],
            [
                'item_running_number' => 'APP02',
                'name'                => 'CAP BRANDED',
                'type'                => 'ONE SIZE',
                'category'            => 'Apparel',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 12.00,
                'sales_cost'          => 22.00,
                'stable_unit'         => 30,
                'stock'               => [['UTW', 25], ['UK1', 12]],
            ],

            // ── Promotional Items (extra) ──
            [
                'item_running_number' => 'PUM02',
                'name'                => 'CERAMIC MUG BRANDED',
                'type'                => 'WHITE, 350ml',
                'category'            => 'Promotional Items',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 6.50,
                'sales_cost'          => 15.00,
                'stable_unit'         => 50,
                'stock'               => [['UTW', 40], ['UK1', 25], ['USS', 12]],
            ],
            [
                'item_running_number' => 'PUM03',
                'name'                => 'METAL PEN BRANDED',
                'type'                => 'BLACK INK',
                'category'            => 'Promotional Items',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 2.40,
                'sales_cost'          => 6.00,
                'stable_unit'         => 100,
                'stock'               => [['UTW', 80], ['ULD', 60], ['UK1', 45]],
            ],
            [
                'item_running_number' => 'PUM04',
                'name'                => 'KEYCHAIN RUBBER LOGO',
                'type'                => 'EMBOSSED',
                'category'            => 'Promotional Items',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 3.20,
                'sales_cost'          => 8.00,
                'stable_unit'         => 80,
                'stock'               => [['USS', 50], ['UK2', 30]],
            ],
            [
                'item_running_number' => 'GUG02',
                'name'                => 'CANVAS TOTE BAG',
                'type'                => 'NATURAL, 14"x16"',
                'category'            => 'Promotional Items',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 8.50,
                'sales_cost'          => 18.00,
                'stable_unit'         => 40,
                'stock'               => [['UTW', 30], ['ULD', 18]],
            ],
            [
                'item_running_number' => 'GUG03',
                'name'                => 'WIRELESS POWER BANK 10000mAh',
                'type'                => 'BLACK',
                'category'            => 'Promotional Items',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 45.00,
                'sales_cost'          => 75.00,
                'stable_unit'         => 15,
                'stock'               => [['UK1', 8], ['USS', 5]],
            ],

            // ── Print Materials (extra) ──
            [
                'item_running_number' => 'BRO02',
                'name'                => 'PRODUCT CATALOG BOOKLET',
                'type'                => '32 PAGES, A4',
                'category'            => 'Print Materials',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 4.50,
                'sales_cost'          => 9.00,
                'stable_unit'         => 50,
                'stock'               => [['UTW', 35], ['UK1', 28]],
            ],
            [
                'item_running_number' => 'FLY02',
                'name'                => 'FLYER A4',
                'type'                => 'A4 GLOSSY',
                'category'            => 'Print Materials',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 0.35,
                'sales_cost'          => 0.80,
                'stable_unit'         => 500,
                'stock'               => [['UTW', 400], ['ULD', 200]],
            ],
            [
                'item_running_number' => 'FLY03',
                'name'                => 'FLYER DL SIZE',
                'type'                => 'DL MATT',
                'category'            => 'Print Materials',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 0.18,
                'sales_cost'          => 0.45,
                'stable_unit'         => 600,
                'stock'               => [['USS', 250]],
            ],
            [
                'item_running_number' => 'POS01',
                'name'                => 'PROMO POSTER A2',
                'type'                => 'A2 GLOSSY',
                'category'            => 'Print Materials',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 6.00,
                'sales_cost'          => 12.00,
                'stable_unit'         => 30,
                'stock'               => [['UTW', 20], ['ULD', 12], ['UK1', 10]],
            ],

            // ── Signage (extra) ──
            [
                'item_running_number' => 'SIG03',
                'name'                => 'PULL-UP ROLL BANNER',
                'type'                => '85cm x 200cm',
                'category'            => 'Signage',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 120.00,
                'sales_cost'          => 180.00,
                'stable_unit'         => 4,
                'stock'               => [['UTW', 3], ['UK1', 2]],
            ],
            [
                'item_running_number' => 'SIG04',
                'name'                => 'INDOOR FABRIC BANNER',
                'type'                => '6ft x 3ft',
                'category'            => 'Signage',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 65.00,
                'sales_cost'          => 95.00,
                'stable_unit'         => 5,
                'stock'               => [['ULD', 3]],
            ],
            [
                'item_running_number' => 'SIG05',
                'name'                => 'LED EDGE LIT LIGHTBOX',
                'type'                => '24" x 36"',
                'category'            => 'Signage',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 480.00,
                'sales_cost'          => 650.00,
                'stable_unit'         => 1,
                'stock'               => [['UK1', 1]],
            ],
            [
                'item_running_number' => 'SIG06',
                'name'                => 'ACRYLIC COUNTER STAND',
                'type'                => 'A5 SLOT',
                'category'            => 'Signage',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 22.00,
                'sales_cost'          => 38.00,
                'stable_unit'         => 10,
                'stock'               => [['USS', 6], ['UK2', 4]],
            ],

            // ── Event Supplies (extra) ──
            [
                'item_running_number' => 'EVT03',
                'name'                => 'TABLECLOTH BRANDED',
                'type'                => '6ft FITTED',
                'category'            => 'Event Supplies',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 55.00,
                'sales_cost'          => 85.00,
                'stable_unit'         => 4,
                'stock'               => [['UTW', 3], ['UK1', 2]],
            ],
            [
                'item_running_number' => 'EVT04',
                'name'                => 'HELIUM BALLOON SET',
                'type'                => 'ASSORTED 50 PCS',
                'category'            => 'Event Supplies',
                'unit_measure'        => 'set',
                'purchase_cost'       => 40.00,
                'sales_cost'          => 70.00,
                'stable_unit'         => 5,
                'stock'               => [['ULD', 2]],
            ],
            [
                'item_running_number' => 'EVT05',
                'name'                => 'FOLDING EVENT CHAIR',
                'type'                => 'WHITE',
                'category'            => 'Event Supplies',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 38.00,
                'sales_cost'          => 60.00,
                'stable_unit'         => 20,
                'stock'               => [['UTW', 15], ['USS', 8], ['UK1', 10]],
            ],
            [
                'item_running_number' => 'EVT06',
                'name'                => 'POPUP DISPLAY TENT 3x3',
                'type'                => 'BLUE, FULL PRINT',
                'category'            => 'Event Supplies',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 520.00,
                'sales_cost'          => 720.00,
                'stable_unit'         => 2,
                'stock'               => [['UK1', 1]],
            ],

            // ── Apparel (extra) ──
            [
                'item_running_number' => 'APP03',
                'name'                => 'T-SHIRT ROUND NECK',
                'type'                => 'WHITE, LARGE',
                'category'            => 'Apparel',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 18.00,
                'sales_cost'          => 35.00,
                'stable_unit'         => 30,
                'stock'               => [['UTW', 22], ['ULD', 15], ['UK1', 18]],
            ],
            [
                'item_running_number' => 'APP04',
                'name'                => 'WINDBREAKER JACKET BRANDED',
                'type'                => 'NAVY, MEDIUM',
                'category'            => 'Apparel',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 78.00,
                'sales_cost'          => 130.00,
                'stable_unit'         => 10,
                'stock'               => [['UTW', 6], ['USS', 4]],
            ],
            [
                'item_running_number' => 'APP05',
                'name'                => 'LANYARD WITH BADGE HOLDER',
                'type'                => 'BLUE, BRANDED',
                'category'            => 'Apparel',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 4.20,
                'sales_cost'          => 9.50,
                'stable_unit'         => 80,
                'stock'               => [['UTW', 60], ['UK1', 35], ['ULD', 28]],
            ],
            [
                'item_running_number' => 'APP06',
                'name'                => 'SAFETY VEST REFLECTIVE',
                'type'                => 'YELLOW, ONE SIZE',
                'category'            => 'Apparel',
                'unit_measure'        => 'pcs',
                'purchase_cost'       => 14.00,
                'sales_cost'          => 28.00,
                'stable_unit'         => 25,
                'stock'               => [['USS', 18], ['UK2', 10]],
            ],
        ];

        foreach ($items as $row) {
            $item = MarketingItem::create([
                'name'                => $row['name'],
                'item_running_number' => $row['item_running_number'],
                'type'                => $row['type'],
                'category_id'         => $categoryId[$row['category']] ?? null,
                'unit_measure'        => $row['unit_measure'],
                'purchase_cost'       => $row['purchase_cost'],
                'sales_cost'          => $row['sales_cost'],
                'stable_unit'         => $row['stable_unit'],
            ]);

            foreach ($row['stock'] as [$branchCode, $qty]) {
                if (!isset($branchId[$branchCode])) {
                    continue;
                }
                MarketingItemValue::create([
                    'item_id'      => $item->id,
                    'branch_id'    => $branchId[$branchCode],
                    'current_unit' => $qty,
                ]);
            }
        }
    }
}
