<?php

declare(strict_types=1);

namespace App\Dto\Page;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class SpeakerPageProps
{
    /**
     * @param list<string> $bio
     */
    public function __construct(
        public string $name,
        public string $photo,
        public array $bio,
    ) {}
}
