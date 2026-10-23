<?php declare(strict_types=1);

namespace Loki\Flatpickr\Test\Unit\ViewModel;

use Loki\Flatpickr\ViewModel\Options;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function testGetDefaultOptions(): void
    {
        $this->assertSame([
            'altInput' => true,
            'altFormat' => 'Y-m-d',
            'dateFormat' => 'Y-m-d',
            'locale' => [
                'firstDayOfWeek' => 1,
            ],
        ], $this->getOptions('1')->getDefaultOptions());
    }

    public function testFirstDayOfWeekIsReadFromStoreConfig(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('general/locale/firstday', ScopeInterface::SCOPE_STORE)
            ->willReturn('6');

        $options = new Options($scopeConfig);
        $this->assertSame(6, $options->getDefaultOptions()['locale']['firstDayOfWeek']);
    }

    public function testFirstDayOfWeekDefaultsToSunday(): void
    {
        $this->assertSame(0, $this->getOptions(null)->getDefaultOptions()['locale']['firstDayOfWeek']);
    }

    public function testToJsonWithoutBlockOptions(): void
    {
        $options = $this->getOptions('0');
        $json = $options->toJson($this->getBlock(null));

        $this->assertSame($options->getDefaultOptions(), json_decode($json, true));
    }

    public function testToJsonMergesBlockOptions(): void
    {
        $json = $this->getOptions('0')->toJson($this->getBlock([
            'altFormat' => 'd-m-Y',
            'minDate' => 'today',
        ]));

        $this->assertSame([
            'altInput' => true,
            'altFormat' => 'd-m-Y',
            'dateFormat' => 'Y-m-d',
            'locale' => [
                'firstDayOfWeek' => 0,
            ],
            'minDate' => 'today',
        ], json_decode($json, true));
    }

    public function testToJsonReplacesNestedOptionsAsAWhole(): void
    {
        $json = $this->getOptions('1')->toJson($this->getBlock([
            'locale' => ['rangeSeparator' => ' - '],
        ]));

        $this->assertSame(['rangeSeparator' => ' - '], json_decode($json, true)['locale']);
    }

    public function testToJsonIsSafeToOutputInsideHtmlScript(): void
    {
        $value = 'j \'M" </script><b>&amp;';
        $json = $this->getOptions('0')->toJson($this->getBlock(['altFormat' => $value]));

        foreach (["'", '<', '>', '&'] as $character) {
            $this->assertStringNotContainsString($character, $json);
        }

        $this->assertSame($value, json_decode($json, true)['altFormat']);
    }

    private function getOptions(?string $firstDay): Options
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn($firstDay);

        return new Options($scopeConfig);
    }

    private function getBlock(?array $options): Template
    {
        $block = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $block->setData('options', $options);

        return $block;
    }
}
