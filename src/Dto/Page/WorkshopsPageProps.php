<?php

declare(strict_types=1);

namespace App\Dto\Page;

use App\Dto\WorkshopSlotDto;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class WorkshopsPageProps
{
    /**
     * @param list<WorkshopSlotDto> $slots
     * @param list<string>          $schedulingNotes
     * @param list<string>          $content
     */
    public function __construct(
        public string $intro,
        public array $slots,
        public array $schedulingNotes,
        public array $content,
        public string $price,
        public string $priceNote,
    ) {}
}
