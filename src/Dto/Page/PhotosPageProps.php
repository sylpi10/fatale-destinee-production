<?php

declare(strict_types=1);

namespace App\Dto\Page;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class PhotosPageProps
{
    /**
     * @param list<string> $photos
     */
    public function __construct(
        public string $title,
        public string $caption,
        public array $photos,
    ) {}
}
