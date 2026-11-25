<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;

final class ImageExtension
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public')] private readonly string $publicDir,
    ) {
    }

    /**
     * width="…" height="…" of an image of public/ (an upload): the browser keeps its room
     * before it loads, so the page doesn't jump. Empty when the file is missing.
     */
    #[AsTwigFunction('image_size', isSafe: ['html'])]
    public function imageSize(string $path): string
    {
        $size = @getimagesize($this->publicDir.'/'.ltrim($path, '/'));

        return false === $size ? '' : \sprintf('width="%d" height="%d"', $size[0], $size[1]);
    }
}
