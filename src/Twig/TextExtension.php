<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Attribute\AsTwigFilter;

final class TextExtension
{
    /**
     * Splits a text typed in the admin into paragraphs, on blank lines.
     *
     * @return list<string>
     */
    #[AsTwigFilter('paragraphs')]
    public function paragraphs(?string $text): array
    {
        return array_values(array_filter(
            array_map(trim(...), preg_split('/\R\s*\R/', (string) $text) ?: []),
            static fn (string $paragraph): bool => '' !== $paragraph,
        ));
    }
}
