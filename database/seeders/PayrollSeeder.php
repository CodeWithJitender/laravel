<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\EmployeeSalaryStructure;
use App\Models\User;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Default Salary Components
        $components = [
            [
                'component_name' => 'Basic Salary',
                'component_code' => 'BASIC',
                'component_type' => 'earning',
                'calculation_type' => 'percentage_of_gross',
                'default_value' => 50.00,
            ],
            [
                'component_name' => 'House Rent Allowance',
                'component_code' => 'HRA',
                'component_type' => 'earning',
                'calculation_type' => 'percentage_of_basic',
                'default_value' => 50.00,
            ],
            [
                'component_name' => 'Special Allowance',
                'component_code' => 'SPECIAL_ALLOWANCE',
                'component_type' => 'earning',
                'calculation_type' => 'percentage_of_gross',
                'default_value' => 25.00,
            ],
            [
                'component_name' => 'Overtime Allowance',
                'component_code' => 'OVERTIME',
                'component_type' => 'earning',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ],
            [
                'component_name' => 'Provident Fund',
                'component_code' => 'PF',
                'component_type' => 'deduction',
                'calculation_type' => 'percentage_of_basic',
                'default_value' => 12.00,
            ],
            [
                'component_name' => 'Employee State Insurance',
                'component_code' => 'ESI',
                'component_type' => 'deduction',
                'calculation_type' => 'percentage_of_gross',
                'default_value' => 0.75,
            ],
            [
                'component_name' => 'Professional Tax',
                'component_code' => 'PT',
                'component_type' => 'deduction',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ],
            [
                'component_name' => 'Tax Deducted at Source',
                'component_code' => 'TDS',
                'component_type' => 'deduction',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ],
            [
                'component_name' => 'Loan Recovery',
                'component_code' => 'LOAN_RECOVERY',
                'component_type' => 'deduction',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ],
            [
                'component_name' => 'Advance Recovery',
                'component_code' => 'ADVANCE_RECOVERY',
                'component_type' => 'deduction',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ],
        ];

        $componentInstances = [];
        foreach ($components as $c) {
            $componentInstances[$c['component_code']] = SalaryComponent::updateOrCreate(
                ['component_code' => $c['component_code']],
                [
                    'component_name' => $c['component_name'],
                    'component_type' => $c['component_type'],
                    'calculation_type' => $c['calculation_type'],
                    'default_value' => $c['default_value'],
                    'status' => 'active',
                ]
            );
        }

        // 2. Create Default Salary Structure
        $structure = SalaryStructure::updateOrCreate(
            ['name' => 'Standard Salary Structure'],
            [
                'description' => 'Default company structure with standard earnings and deductions.',
                'status' => 'active',
            ]
        );

        // 3. Attach components to structure
        $pivotData = [
            'BASIC' => ['calculation_value' => 50.00, 'sort_order' => 1],
            'HRA' => ['calculation_value' => 50.00, 'sort_order' => 2],
            'SPECIAL_ALLOWANCE' => ['calculation_value' => 25.00, 'sort_order' => 3],
            'OVERTIME' => ['calculation_value' => 0.00, 'sort_order' => 4],
            'PF' => ['calculation_value' => 12.00, 'sort_order' => 5],
            'ESI' => ['calculation_value' => 0.75, 'sort_order' => 6],
            'PT' => ['calculation_value' => 0.00, 'sort_order' => 7],
            'TDS' => ['calculation_value' => 0.00, 'sort_order' => 8],
            'LOAN_RECOVERY' => ['calculation_value' => 0.00, 'sort_order' => 9],
            'ADVANCE_RECOVERY' => ['calculation_value' => 0.00, 'sort_order' => 10],
        ];

        $syncArray = [];
        foreach ($pivotData as $code => $data) {
            if (isset($componentInstances[$code])) {
                $syncArray[$componentInstances[$code]->id] = $data;
            }
        }
        $structure->components()->sync($syncArray);

        // 4. Assign structure to seeded users
        $assignments = [
            'admin@company.com' => 150000.00,
            'manager@company.com' => 80000.00,
            'employee@company.com' => 45000.00,
        ];

        foreach ($assignments as $email => $gross) {
            $user = User::where('email', $email)->first();
            if ($user) {
                // Ensure they don't already have one
                EmployeeSalaryStructure::updateOrCreate(
                    [
                        'employee_id' => $user->id,
                        'salary_structure_id' => $structure->id,
                    ],
                    [
                        'effective_from' => '2025-01-01',
                        'effective_to' => null,
                        'monthly_gross_salary' => $gross,
                        'annual_ctc' => $gross * 12,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
