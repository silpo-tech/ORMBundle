<?php

declare(strict_types=1);

namespace ORMBundle\Entity\Traits;

trait TranslationTrait
{
    protected function getFieldTranslation(string $field, string $locale): ?string
    {
        foreach ($this->translations as $translation) {
            if ($locale === $translation->locale) {
                return $translation->$field;
            }
        }

        return null;
    }

    protected function getFieldTranslations(string $field, bool $addNull = true): array
    {
        $result = [];
        foreach ($this->translations as $translation) {
            if (null !== $translation->$field || $addNull) {
                $result[$translation->locale] = $translation->$field;
            }
        }

        return $result;
    }
}
