<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Entity\Traits;

use ORMBundle\Entity\Traits\TranslationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslationTrait::class)]
final class TranslationTraitTest extends TestCase
{
    public function testGetFieldTranslationReturnsValue(): void
    {
        $translation = $this->createTranslation('en', 'English Title');
        $entity = $this->createEntityWithTranslations([$translation]);

        $result = $entity->testGetFieldTranslation('title', 'en');

        self::assertSame('English Title', $result);
    }

    public function testGetFieldTranslationReturnsNullWhenNotFound(): void
    {
        $entity = $this->createEntityWithTranslations([]);

        $result = $entity->testGetFieldTranslation('title', 'en');

        self::assertNull($result);
    }

    public function testGetFieldTranslationsReturnsAllWithNulls(): void
    {
        $translations = [
            $this->createTranslation('en', 'English Title'),
            $this->createTranslation('fr', null),
        ];
        $entity = $this->createEntityWithTranslations($translations);

        $result = $entity->testGetFieldTranslations('title');

        self::assertSame(['en' => 'English Title', 'fr' => null], $result);
    }

    public function testGetFieldTranslationsExcludesNulls(): void
    {
        $translations = [
            $this->createTranslation('en', 'English Title'),
            $this->createTranslation('fr', null),
        ];
        $entity = $this->createEntityWithTranslations($translations);

        $result = $entity->testGetFieldTranslations('title', false);

        self::assertSame(['en' => 'English Title'], $result);
    }

    private function createEntityWithTranslations(array $translations): object
    {
        return new class($translations) {
            use TranslationTrait;

            protected array $translations;

            public function __construct(array $translations)
            {
                $this->translations = $translations;
            }

            public function testGetFieldTranslation(string $field, string $locale): ?string
            {
                return $this->getFieldTranslation($field, $locale);
            }

            public function testGetFieldTranslations(string $field, bool $addNull = true): array
            {
                return $this->getFieldTranslations($field, $addNull);
            }
        };
    }

    private function createTranslation(string $locale, ?string $title): \stdClass
    {
        $translation = new \stdClass();
        $translation->locale = $locale;
        $translation->title = $title;

        return $translation;
    }
}
