<?php

declare(strict_types=1);

namespace App\Dto;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class WorkshopSlotDto
{
    public function __construct(
        public string $day,
        public string $hours,
        public string $place,
    ) {}
}
