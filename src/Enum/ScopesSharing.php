<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Enum;

class ScopesSharing
{
    public const NAME = 'sharing';

    public const SCOPES_ALL = [
        self::SCOPE_PASSPORT,
        self::SCOPE_INTERNAL_PASSPORT,
        self::SCOPE_FOREIGN_PASSPORT,
        self::SCOPE_TAXPAYER_CARD,
        self::SCOPE_REFERENCE_INTERNALLY_DISPLACED_PERSON,
        self::SCOPE_BIRTH_CERTIFICATE,
        self::SCOPE_DRIVER_LICENSE,
        self::SCOPE_VEHICLE_LICENSE,
        self::SCOPE_STUDENT_ID_CARD,
        self::SCOPE_EDUCATION_DOCUMENT,
    ];

    /**
     * Паспорт
     */
    public const SCOPE_PASSPORT = 'passport';

    /**
     * Паспорт громадянина України у формі ID-картки
     */
    public const SCOPE_INTERNAL_PASSPORT = 'internal-passport';

    /**
     * Закордонний паспорт
     */
    public const SCOPE_FOREIGN_PASSPORT = 'foreign-passport';

    /**
     * РНОКПП (ІПН)
     */
    public const SCOPE_TAXPAYER_CARD = 'taxpayer-card';

    /**
     * Довідка внутрішньо переміщеної особи (ВПО)
     */
    public const SCOPE_REFERENCE_INTERNALLY_DISPLACED_PERSON = 'reference-internally-displaced-person';

    /**
     * Свідоцтво про народження дитини
     */
    public const SCOPE_BIRTH_CERTIFICATE = 'birth-certificate';

    /**
     * Водійські права
     */
    public const SCOPE_DRIVER_LICENSE = 'driver-license';

    /**
     * Техпаспорт
     */
    public const SCOPE_VEHICLE_LICENSE = 'vehicle-license';

    /**
     * Студентський квиток
     */
    public const SCOPE_STUDENT_ID_CARD = 'student-id-card';

    /**
     * Освітні документи
     */
    public const SCOPE_EDUCATION_DOCUMENT = 'education-document';
}
