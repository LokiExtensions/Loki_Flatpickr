<?php declare(strict_types=1);

namespace Loki\Flatpickr\Test\Integration\Template;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class DirectiveFlatpickrTemplateTest extends TestCase
{
    public function testRendersAlpineDirective(): void
    {
        $html = $this->render(null);

        $this->assertStringContainsString("document.addEventListener('alpine:init'", $html);
        $this->assertStringContainsString("Alpine.directive('flatpickr'", $html);
        $this->assertStringContainsString('flatpickr(el, options);', $html);
    }

    public function testRendersDefaultOptions(): void
    {
        $options = $this->getRenderedOptions($this->render(null));

        $this->assertTrue($options['altInput']);
        $this->assertSame('Y-m-d', $options['altFormat']);
        $this->assertSame('Y-m-d', $options['dateFormat']);
        $this->assertArrayHasKey('firstDayOfWeek', $options['locale']);
    }

    #[ConfigFixture('general/locale/firstday', '1', 'store', 'default')]
    public function testRendersFirstDayOfWeekFromConfig(): void
    {
        $options = $this->getRenderedOptions($this->render(null));

        $this->assertSame(1, $options['locale']['firstDayOfWeek']);
    }

    public function testRendersBlockOptions(): void
    {
        $options = $this->getRenderedOptions($this->render(['altFormat' => 'd-m-Y', 'minDate' => 'today']));

        $this->assertSame('d-m-Y', $options['altFormat']);
        $this->assertSame('today', $options['minDate']);
        $this->assertSame('Y-m-d', $options['dateFormat']);
    }

    public function testEscapesOptionsSafelyForScript(): void
    {
        $value = 'j \'M" </script><script>alert(1)</script>';
        $html = $this->render(['altFormat' => $value]);

        $this->assertSame(1, substr_count($html, '<script>'));
        $this->assertSame(1, substr_count($html, '</script>'));
        $this->assertSame($value, $this->getRenderedOptions($html)['altFormat']);
    }

    private function getRenderedOptions(string $html): array
    {
        $this->assertMatchesRegularExpression('/const globalOptions = (\{.*\});/', $html);
        preg_match('/const globalOptions = (\{.*\});/', $html, $match);

        $options = json_decode($match[1], true);
        $this->assertIsArray($options, 'Rendered options are not valid JSON: ' . $match[1]);

        return $options;
    }

    private function render(?array $options): string
    {
        $layout = ObjectManager::getInstance()->create(LayoutInterface::class);
        $block = $layout->createBlock(Template::class, 'loki.script.directive.flatpickr', [
            'data' => [
                'template' => 'Loki_Flatpickr::script/directive-flatpickr.phtml',
                'options' => $options,
            ],
        ]);

        return $block->toHtml();
    }
}
