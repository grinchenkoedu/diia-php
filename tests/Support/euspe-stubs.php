<?php

/**
 * Stand-ins for the parts of matasarei/euspe ^2.0 the README uses, following its published contract.
 * The real library needs IIT's proprietary extension, so it is not a dev dependency.
 */

declare(strict_types=1);

namespace Matasar\Euspe\Dto {
    if (!class_exists(SignInfo::class)) {
        class SignInfo
        {
        }
    }
}

namespace Matasar\Euspe {
    if (!class_exists(EusignSession::class)) {
        final class EusignSession
        {
            public function __construct(int $charset = 0)
            {
            }

            public function open(): void
            {
            }
        }
    }

    if (!class_exists(Hasher::class)) {
        class Hasher
        {
            /**
             * @var string[]
             */
            public static array $hashedFiles = [];

            public function __construct(EusignSession $session)
            {
            }

            /**
             * Raw binary, as the real one returns.
             */
            public function hashFile(string $path): string
            {
                self::$hashedFiles[] = $path;

                return "\x01\x02raw-hash:" . $path;
            }
        }
    }

    if (!class_exists(SignatureVerifier::class)) {
        class SignatureVerifier
        {
            /**
             * @var array<int, array{string, string}>
             */
            public static array $verified = [];

            public function __construct(EusignSession $session)
            {
            }

            public function verifyHash(string $signature, string $hash): Dto\SignInfo
            {
                self::$verified[] = [$signature, $hash];

                return new Dto\SignInfo();
            }
        }
    }
}
