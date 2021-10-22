<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Controller\Adminhtml\Calculation;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Config implements ActionInterface
{
    /**
     * @var PageFactory
     */
    private PageFactory $pageFactory;

    /**
     * @param PageFactory $pageFactory
     */
    public function __construct(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @inheirtDoc
     */
    public function execute(): ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Page $page */
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Magento_Stores::stores')
            ->getConfig()
            ->getTitle()
            ->prepend(__('Calculation Configuration'));

        return $page;
    }
}
