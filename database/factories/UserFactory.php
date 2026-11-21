<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * The users table is phone-based (email was dropped).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '09'.fake()->unique()->numerify('#########'),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Privileged flags are deliberately not mass-assignable on the model.
     * Factories are trusted fixtures, so apply them with forceFill here while
     * still honouring flags explicitly passed to create()/make().
     *
     * @param  array<string, mixed>  $attributes
     */
    public function newModel(array $attributes = []): User
    {
        /** @var User $model */
        $model = parent::newModel(Arr::except($attributes, ['is_admin', 'is_active']));

        $model->forceFill(array_merge(
            ['is_admin' => false, 'is_active' => true],
            Arr::only($attributes, ['is_admin', 'is_active']),
        ));

        return $model;
    }

    /**
     * @return array<string, mixed>
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => ['is_admin' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
