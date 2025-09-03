<?php
declare(strict_types=1);

namespace Loki\Flatpickr\ViewModel;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Options implements ArgumentInterface
{
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
        ];
    }
}
