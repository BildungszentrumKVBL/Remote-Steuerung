<?php

namespace App\Tests\Helper;

use App\Entity\Page\PageStatusEnum;
use App\Helper\ReflectionHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class ReflectionHelperTest.
 */
class ReflectionHelperTest extends TestCase
{
    public function testClassToTranslationString()
    {
        $translationString = ReflectionHelper::classToTranslationString(PageStatusEnum::class);
        $this->assertEquals('page.status', $translationString);
    }

    public function testConstToChoicesArray()
    {
        $choices = ReflectionHelper::constToChoicesArray(PageStatusEnum::class);
        $this->assertEquals(
            [
                'page.status.preview'   => 1,
                'page.status.published' => 2,
            ], $choices
        );
    }
}
