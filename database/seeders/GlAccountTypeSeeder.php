<?php

namespace Database\Seeders;

use App\Enums\GlAccountTypeCode;
use App\Models\Accounting\GlAccountType;
use Illuminate\Database\Seeder;

class GlAccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['code' => GlAccountTypeCode::Asset, 'sort' => 10],
            ['code' => GlAccountTypeCode::Liability, 'sort' => 20],
            ['code' => GlAccountTypeCode::Equity, 'sort' => 30],
            ['code' => GlAccountTypeCode::Revenue, 'sort' => 40],
            ['code' => GlAccountTypeCode::CostOfSales, 'sort' => 50],
            ['code' => GlAccountTypeCode::Expense, 'sort' => 60],
        ];

        foreach ($definitions as $index => $definition) {
            $code = $definition['code'];

            GlAccountType::query()->updateOrCreate(
                ['code' => $code->value],
                [
                    'name' => $code->label(),
                    'normal_balance' => $code->defaultNormalBalance()->value,
                    'sort_order' => $definition['sort'],
                ],
            );
        }
    }
}
