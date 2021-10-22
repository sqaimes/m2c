<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Ui\Component;

class Form extends \Magento\Ui\Component\Form
{
    /**
     * @inheirtDoc
     */
    public function getDataSourceData(): array
    {
        return [
            'data' => $this->getContext()->getDataProvider()->getData(),
        ];
    }
}
