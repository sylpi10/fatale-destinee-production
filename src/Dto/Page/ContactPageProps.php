<?php

declare(strict_types=1);

namespace App\Dto\Page;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class ContactPageProps
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $email,
        public string $registrationPhone,
        public string $registrationEmail,
    ) {}
}
