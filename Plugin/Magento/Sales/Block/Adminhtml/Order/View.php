<?php


namespace Xigen\DeleteOrder\Plugin\Magento\Sales\Block\Adminhtml\Order;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Xigen\DeleteOrder\Helper\Data;
use Magento\Framework\UrlInterface;

/**
 * View class
 */
class View
{
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    protected $_urlBuilder;

    /**
     * AddDeleteButton constructor.
     * @param Data $helper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Data $helper,
        AuthorizationInterface $authorization,
        UrlInterface $url
    ) {
        $this->helper = $helper;
        $this->_authorization = $authorization;
        $this->_urlBuilder = $url;
    }

    /**
     * @param OrderView $object
     * @param LayoutInterface $layout
     * @return array
     */
    public function beforeSetLayout(
        OrderView $object,
        LayoutInterface $layout
    ) {
        if ($this->_authorization->isAllowed('Magento_Sales::delete')) {
            $url = $this->_urlBuilder->getUrl(
                'xigen_deleteorder/index/deleteSingle',
                ['order_id' => $object->getOrderId()]
            );
            $message = __('Are you sure you want to delete this order?');
            $object->addButton(
                'order_delete',
                [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'id' => 'order-view-delete-button',
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')"
                ]
            );
        }
        return [$layout];
    }
}
