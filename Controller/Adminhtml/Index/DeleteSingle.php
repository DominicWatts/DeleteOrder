<?php

namespace Xigen\DeleteOrder\Controller\Adminhtml\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Controller\Adminhtml\Order;
use Psr\Log\LoggerInterface;
use Xigen\DeleteOrder\Helper\Data;

class DeleteSingle extends Order
{
    const ADMIN_RESOURCE = 'Magento_Sales::delete';

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $helper = $this->_objectManager->get(Data::class);

        $order = $this->_initOrder();
        if ($order) {
            try {
                /** delete order data */
                $helper->deleteOrder($order->getId());
                $this->messageManager->addSuccessMessage(__('The order has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while deleting the order. Please try again later.')
                );
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
                return $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            }
        }
        return $resultRedirect->setPath('sales/order/');
    }
}
