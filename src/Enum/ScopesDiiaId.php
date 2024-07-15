<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Enum;

class ScopesDiiaId
{
    public const NAME = 'diiaId';

    public const SCOPES_ALL = [
        self::SCOPE_HASHED_FILES_SIGNING,
    ];

    public const SCOPE_HASHED_FILES_SIGNING = 'hashedFilesSigning';
}
