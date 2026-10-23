<?php declare(strict_types=1);

namespace Loki\Flatpickr\Test\Integration\Layout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Asset\PropertyGroup;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
#[AppIsolation(true)]
class FlatpickrLayoutTest extends TestCase
{
    public function testFlatpickrHandleAddsDirectiveBlock(): void
    {
        $layout = $this->loadPage(['default', 'loki_flatpickr'])->getLayout();

        $block = $layout->getBlock('loki.script.directive.flatpickr');
        $this->assertInstanceOf(Template::class, $block);
        $this->assertSame('Loki_Flatpickr::script/directive-flatpickr.phtml', $block->getTemplate());
        $this->assertSame('loki.script', $layout->getParentName('loki.script.directive.flatpickr'));
    }

    public function testLokiComponentsHandleIncludesFlatpickrHandle(): void
    {
        $page = $this->loadPage(['default', 'loki_components']);

        $this->assertInstanceOf(Template::class, $page->getLayout()->getBlock('loki.script.directive.flatpickr'));
        $this->assertInstanceOf(Template::class, $page->getLayout()->getBlock('loki.script.flatpickr-loader'));
    }

    public function testFlatpickrHandleAddsCssToHead(): void
    {
        $page = $this->loadPage(['default', 'loki_flatpickr']);
        $assetCollection = $page->getConfig()->getAssetCollection();

        $this->assertNotNull($assetCollection->getAll()['Loki_Flatpickr::css/flatpickr.min.css'] ?? null);
        $this->assertNull($assetCollection->getAll()['Loki_Flatpickr::js/flatpickr.min.js'] ?? null);

        $cssGroup = $this->getAssetGroup($page, 'Loki_Flatpickr::css/flatpickr.min.css');
        $this->assertStringNotContainsString('defer', $this->getAttributes($cssGroup));
    }

    public function testFlatpickrHandleAddsLoaderBeforeLokiScript(): void
    {
        $layout = $this->loadPage(['default', 'loki_flatpickr'])->getLayout();

        $block = $layout->getBlock('loki.script.flatpickr-loader');
        $this->assertInstanceOf(Template::class, $block);
        $this->assertSame('Loki_Flatpickr::script/flatpickr-loader.phtml', $block->getTemplate());
        $this->assertSame('before.body.end', $layout->getParentName('loki.script.flatpickr-loader'));

        $children = $layout->getChildNames('before.body.end');
        $this->assertLessThan(
            array_search('loki.script', $children, true),
            array_search('loki.script.flatpickr-loader', $children, true)
        );

        $html = $block->toHtml();
        $this->assertMatchesRegularExpression(
            '#<script id="loki-flatpickr-js"\s+src="[^"]+Loki_Flatpickr/js/flatpickr\.min\.js"></script>#',
            $html
        );
        $this->assertStringContainsString("script.id === 'loki-flatpickr-js'", $html);
    }

    public function testTestHandleAddsExampleBlock(): void
    {
        $layout = $this->loadPage(['default', 'loki_flatpickr_test'])->getLayout();

        $block = $layout->getBlock('loki-flatpickr.test');
        $this->assertInstanceOf(Template::class, $block);
        $this->assertSame('Loki_Flatpickr::test/example.phtml', $block->getTemplate());
        $this->assertSame('content', $layout->getParentName('loki-flatpickr.test'));
        $this->assertInstanceOf(Template::class, $layout->getBlock('loki.script.directive.flatpickr'));
    }

    public function testTestOptionsHandleSetsBlockOptions(): void
    {
        $layout = $this->loadPage(['default', 'loki_flatpickr_test', 'loki_flatpickr_test_options'])->getLayout();

        $block = $layout->getBlock('loki.script.directive.flatpickr');
        $this->assertInstanceOf(Template::class, $block);
        $this->assertSame(
            ['altFormat' => 'd/m/Y', 'ariaDateFormat' => 'j \'M" Y'],
            $block->getData('options')
        );
    }

    public function testLayoutFilesUseValidSchemaLocation(): void
    {
        $layoutFiles = glob(dirname(__DIR__, 3) . '/view/frontend/layout/*.xml');
        $this->assertNotEmpty($layoutFiles);

        foreach ($layoutFiles as $layoutFile) {
            $xml = simplexml_load_file($layoutFile);
            $schemaLocation = (string)$xml->attributes('http://www.w3.org/2001/XMLSchema-instance')
                ->noNamespaceSchemaLocation;

            $this->assertSame(
                'urn:magento:framework:View/Layout/etc/page_configuration.xsd',
                $schemaLocation,
                basename($layoutFile)
            );
        }
    }

    private function getAssetGroup(Page $page, string $assetId): PropertyGroup
    {
        foreach ($page->getConfig()->getAssetCollection()->getGroups() as $group) {
            if (array_key_exists($assetId, $group->getAll())) {
                return $group;
            }
        }

        $this->fail(sprintf('Asset "%s" not found in any asset group', $assetId));
    }

    private function getAttributes(PropertyGroup $group): string
    {
        $attributes = $group->getProperty('attributes');
        if (is_array($attributes)) {
            return implode(' ', array_merge(array_keys($attributes), array_values($attributes)));
        }

        return (string)$attributes;
    }

    private function loadPage(array $handles): Page
    {
        $objectManager = ObjectManager::getInstance();
        $objectManager->get(DesignInterface::class)->setDesignTheme('Magento/luma', 'frontend');

        /** @var Page $page */
        $page = $objectManager->get(PageFactory::class)->create(true);
        $page->getConfig()->setPageLayout('1column');
        $page->addHandle($handles);
        $page->getLayout()->getBlock('root');

        return $page;
    }
}
