<?php


namespace Xigen\DeleteOrder\Plugin\Magento\Ui\Component;

use Magento\Framework\AuthorizationInterface;
use Magento\Ui\Component\MassAction as UiMassAction;
use Xigen\DeleteOrder\Helper\Data;

/**
 * MassAction class
 */
class MassAction
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    /**
     * AddDeleteAction constructor.
     * @param Data $helper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Data $helper,
        AuthorizationInterface $authorization
    ) {
        $this->helper = $helper;
        $this->_authorization = $authorization;
    }

    /**
     * @param UiMassAction $object
     * @param $result
     * @return mixed
     */
    public function afterGetChildComponents(
        UiMassAction $object,
        $result
    ) {
        if (!isset($result['deleteorder_delete'])) {
            return $result;
        }
        if (!$this->_authorization->isAllowed('Magento_Sales::delete')) {
            unset($result['deleteorder_delete']);
        }
        return $result;
    }
}
