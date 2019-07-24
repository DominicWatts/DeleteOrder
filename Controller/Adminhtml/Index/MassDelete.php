<?php


namespace Xigen\DeleteOrder\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Xigen\DeleteOrder\Helper\Data as DataHelper;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session.
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::delete';
    /**
     * @var OrderRepository
     */
    protected $orderRepository;
    /**
     * @var DataHelper
     */
    protected $helper;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderRepository $orderRepository
     * @param DataHelper $dataHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepository $orderRepository,
        DataHelper $dataHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->helper = $dataHelper;
        $this->logger = $logger;
    }

    /**
     * @param AbstractCollection $collection
     * @return Redirect
     */
    protected function massAction(
        AbstractCollection $collection
    ) {

        $deleted = 0;
        /** @var OrderInterface $order */
        foreach ($collection->getItems() as $order) {
            try {
                /** delete order data */
                $this->helper->deleteOrder($order->getId());
                $deleted++;
            } catch (Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Cannot delete order #%1. Please try again later.',
                    $order->getIncrementId()));
            }
        }
        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 order(s) has been deleted.', $deleted));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order/');
        return $resultRedirect;
    }
}
