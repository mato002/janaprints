<?php

namespace Database\Factories\Artwork;

use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtworkRequest>
 */
class ArtworkRequestFactory extends Factory
{
    protected $model = ArtworkRequest::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => fn (array $attrs) => Branch::factory()->create(['company_id' => $attrs['company_id']])->id,
            'customer_id' => fn (array $attrs) => Customer::factory()->create([
                'company_id' => $attrs['company_id'],
                'branch_id' => $attrs['branch_id'],
            ])->id,
            'quotation_id' => null,
            'request_number' => 'AR-'.fake()->unique()->numerify('#####'),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'requested_by' => User::factory(),
            'assigned_designer_id' => null,
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::Requested,
            'due_date' => fake()->optional()->dateTimeBetween('+1 day', '+30 days'),
            'current_version' => 0,
        ];
    }
}
