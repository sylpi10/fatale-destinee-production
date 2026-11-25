<?php

declare(strict_types=1);

namespace App\Twig;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFunction;

final class ImageExtension
{
    /** Widths of the copies made by LiipImagine (filter sets w400, w800, w1600 in liip_imagine.yaml). */
    private const array WIDTHS = [400, 800, 1600];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')] private readonly string $publicDir,
        private readonly CacheManager $imagineCacheManager,
        private readonly Packages $packages,
    ) {
    }

    /**
     * src, srcset, sizes, width and height of an uploaded image: the browser downloads the
     * smallest copy that fills the room given by $sizes on its screen (density included).
     *
     * @param string $sizes width of the image in the page, e.g. "320px" or "(min-width: 960px) 550px, 100vw"
     */
    #[AsTwigFunction('image_attrs', isSafe: ['html'])]
    public function imageAttrs(string $path, string $sizes): string
    {
        $path = ltrim($path, '/');
        $original = $this->packages->getUrl($path);
        $size = @getimagesize($this->publicDir.'/'.$path);
        if (false === $size) {
            return \sprintf('src="%s"', htmlspecialchars($original));
        }
        [$width, $height] = $size;

        // Only copies smaller than the original; the original itself is the largest choice.
        $candidates = [];
        foreach (self::WIDTHS as $copyWidth) {
            if ($copyWidth < $width) {
                $candidates[$copyWidth] = $this->imagineCacheManager->getBrowserPath($path, 'w'.$copyWidth, referenceType: UrlGeneratorInterface::ABSOLUTE_PATH);
            }
        }
        $candidates[$width] = $original;

        // Browsers without srcset get the 800 px copy (or the original when it is smaller).
        $src = $candidates[800] ?? $original;
        $srcset = implode(', ', array_map(
            static fn (int $candidateWidth, string $url): string => $url.' '.$candidateWidth.'w',
            array_keys($candidates),
            $candidates,
        ));

        return \sprintf(
            'src="%s" srcset="%s" sizes="%s" width="%d" height="%d"',
            htmlspecialchars($src),
            htmlspecialchars($srcset),
            htmlspecialchars($sizes),
            $width,
            $height,
        );
    }
}
