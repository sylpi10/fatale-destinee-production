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

    /**
     * A text typed in the admin, as one line cut at a word, for a meta description
     * (search engines show about 155 characters).
     */
    #[AsTwigFilter('excerpt')]
    public function excerpt(?string $text, int $length = 155): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $cut = mb_substr($text, 0, $length - 1);
        $lastSpace = mb_strrpos($cut, ' ');

        return rtrim(false === $lastSpace ? $cut : mb_substr($cut, 0, $lastSpace), ' ,;:.–-').'…';
    }
}
