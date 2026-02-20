<?php

namespace App\Services;

use App\Models\Document;
use App\Enums\DocumentStatus;
use Faker\Factory as Faker;

class DocumentGeneratorService
{
    private const DEFAULT_COUNT = 1000;

    public function generate(int $count = self::DEFAULT_COUNT): void
    {
        Document::truncate();

        $items = [];
        $faker = Faker::create();

        for ($i = 0; $i < $count; $i++) {
            $items[] = [
                'name' => 'Document ' . ($i + 1),
                'status' => $i % 2 === 0 ? DocumentStatus::Allowed->value : DocumentStatus::Prohibited->value,
                'description' => $faker->text(100),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Document::insert($items);
    }
}
