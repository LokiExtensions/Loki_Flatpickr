<?php
declare(strict_types=1);

namespace Loki\Flatpickr\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class Options implements ArgumentInterface
{
    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function toJson(AbstractBlock $block): string
    {
        $options = array_merge($this->getDefaultOptions(), (array)$block->getOptions());

        return json_encode($options);
    }

    public function getDefaultOptions(): array
    {
        return [
            'altInput' => true,
            'altFormat' => 'Y-m-d',
            'dateFormat' => 'Y-m-d',
            'locale' => [
                'firstDayOfWeek' => $this->getFirstDayOfWeek(),
            ]
        ];
    }

    private function getFirstDayOfWeek(): int
    {
        return (int)$this->scopeConfig->getValue(
            'general/locale/firstday',
            ScopeInterface::SCOPE_STORE
        );
    }
}
