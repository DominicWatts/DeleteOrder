<?php

// phpcs:disable Generic.Files.LineLength

namespace Xigen\DeleteOrder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepositoryInterface;

    /**
     * Data constructor
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface
    ) {
        $this->logger = $logger;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        parent::__construct($context);
    }

    /**
     * Get order by Id.
     * @param int $orderId
     * @return \Magento\Sales\Model\Data\Order
     */
    public function getOrderById($orderId)
    {
        try {
            return $this->orderRepositoryInterface->get($orderId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }

    /**
     * Load order by increment Id
     * @param string $incrementId
     * @return \Magento\Sales\Model\Data\Order
     */
    public function getOrderByIncrementId($incrementId = null)
    {
        if (!$incrementId) {
            return false;
        }
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();
        $order = $this->orderRepository
            ->getList($searchCriteria)
            ->getFirstItem();
        if ($order && $order->getId()) {
            return $order;
        }
        return false;
    }

    /**
     * Delete order by Entity ID
     * @param $orderId
     */
    public function deleteOrder($orderId)
    {
        //Sales Order Tables
        $tableSalesOrder = $this->resource->getTableName('sales_order');
        $tableSalesOrderGrid = $this->resource->getTableName('sales_order_grid');
        $tableSalesOrderItem = $this->resource->getTableName('sales_order_item');
        $tableSalesOrderPayment = $this->resource->getTableName('sales_order_payment');
        $tableSalesOrderTax = $this->resource->getTableName('sales_order_tax');
        $tableSalesOrderTaxItem = $this->resource->getTableName('sales_order_tax_item');

        //Sales Order Invoice Tables
        $tableSalesInvoice = $this->resource->getTableName('sales_invoice');
        $tableSalesInvoiceComment = $this->resource->getTableName('sales_invoice_comment');
        $tableSalesInvoiceGrid = $this->resource->getTableName('sales_invoice_grid');
        $tableSalesInvoiceItem = $this->resource->getTableName('sales_invoice_item');

        //Sales Order Shipment Tables
        $tableSalesShipment = $this->resource->getTableName('sales_shipment');
        $tableSalesShipmentComment = $this->resource->getTableName('sales_shipment_comment');
        $tableSalesShipmentGrid = $this->resource->getTableName('sales_shipment_grid');
        $tableSalesShipmentItem = $this->resource->getTableName('sales_shipment_item');

        //Sales Order Credit Memo Tables
        $tableSalesCreditmemo = $this->resource->getTableName('sales_creditmemo');
        $tableSalesCreditmemoComment = $this->resource->getTableName('sales_creditmemo_comment');
        $tableSalesCreditmemoGrid = $this->resource->getTableName('sales_creditmemo_grid');
        $tableSalesCreditmemoItem = $this->resource->getTableName('sales_creditmemo_item');
        // Delete Order Credits Memos
        $this->connection->delete($tableSalesCreditmemoComment, "parent_id in (SELECT entity_id FROM " . $tableSalesCreditmemo . " WHERE order_id = " . $orderId . ")");
        $this->connection->delete($tableSalesCreditmemoItem, "parent_id in (SELECT entity_id FROM " . $tableSalesCreditmemo . " WHERE order_id = " . $orderId . ")");
        $this->connection->delete($tableSalesCreditmemo, "order_id = " . $orderId);
        $this->connection->delete($tableSalesCreditmemoGrid, "order_id = " . $orderId);

        // Delete Order Invoices
        $this->connection->delete($tableSalesInvoiceComment, "parent_id in (SELECT entity_id FROM " . $tableSalesInvoice . " WHERE order_id = " . $orderId . ")");
        $this->connection->delete($tableSalesInvoiceItem, "parent_id in (SELECT entity_id FROM " . $tableSalesInvoice . " WHERE order_id = " . $orderId . ")");
        $this->connection->delete($tableSalesInvoice, "order_id = " . $orderId);
        $this->connection->delete($tableSalesInvoiceGrid, "order_id = " . $orderId);

        // Delete Order Items
        $this->connection->delete($tableSalesOrderItem, "order_id = " . $orderId);

        // Delete Order Payments
        $this->connection->delete($tableSalesOrderPayment, "parent_id = " . $orderId);
        // Delete Order Tax
        $this->connection->delete($tableSalesOrderTaxItem, "tax_id in (SELECT tax_id FROM " . $tableSalesOrderTax . " WHERE order_id = " . $orderId . ")");
        $this->connection->delete($tableSalesOrderTax, "order_id = " . $orderId);

        // Delete Order Shipments
        $this->connection->delete($tableSalesShipmentComment, "parent_id in (SELECT order_id FROM " . $tableSalesShipment . " WHERE order_id = " . $orderId . ")");
        $this->connection->delete($tableSalesShipmentItem, "parent_id in (SELECT order_id FROM " . $tableSalesShipment . " WHERE order_id = " . $orderId . ")");
        $this->connection->delete($tableSalesShipment, "order_id = " . $orderId);
        $this->connection->delete($tableSalesShipmentGrid, "order_id = " . $orderId);

        // Delete Orders
        $this->connection->delete($tableSalesOrder, "entity_id = " . $orderId);
        $this->connection->delete($tableSalesOrderGrid, "entity_id = " . $orderId);
    }

    /**
     * Delete all orders
     */
    public function deleteAll()
    {
        //Sales Order Tables
        $tableSalesOrder = $this->resource->getTableName('sales_order');
        $tableSalesOrderGrid = $this->resource->getTableName('sales_order_grid');
        $tableSalesOrderItem = $this->resource->getTableName('sales_order_item');
        $tableSalesOrderPayment = $this->resource->getTableName('sales_order_payment');
        $tableSalesOrderTax = $this->resource->getTableName('sales_order_tax');
        $tableSalesOrderTaxItem = $this->resource->getTableName('sales_order_tax_item');

        //Sales Order Invoice Tables
        $tableSalesInvoice = $this->resource->getTableName('sales_invoice');
        $tableSalesInvoiceComment = $this->resource->getTableName('sales_invoice_comment');
        $tableSalesInvoiceGrid = $this->resource->getTableName('sales_invoice_grid');
        $tableSalesInvoiceItem = $this->resource->getTableName('sales_invoice_item');

        //Sales Order Shipment Tables
        $tableSalesShipment = $this->resource->getTableName('sales_shipment');
        $tableSalesShipmentComment = $this->resource->getTableName('sales_shipment_comment');
        $tableSalesShipmentGrid = $this->resource->getTableName('sales_shipment_grid');
        $tableSalesShipmentItem = $this->resource->getTableName('sales_shipment_item');

        //Sales Order Credit Memo Tables
        $tableSalesCreditmemo = $this->resource->getTableName('sales_creditmemo');
        $tableSalesCreditmemoComment = $this->resource->getTableName('sales_creditmemo_comment');
        $tableSalesCreditmemoGrid = $this->resource->getTableName('sales_creditmemo_grid');
        $tableSalesCreditmemoItem = $this->resource->getTableName('sales_creditmemo_item');

        // Delete Credits Memos
        $this->connection->delete($tableSalesCreditmemoComment, "");
        $this->connection->delete($tableSalesCreditmemoItem, "");
        $this->connection->delete($tableSalesCreditmemo, "");
        $this->connection->delete($tableSalesCreditmemoGrid, "");

        // Delete Order Invoices
        $this->connection->delete($tableSalesInvoiceComment, "");
        $this->connection->delete($tableSalesInvoiceItem, "");
        $this->connection->delete($tableSalesInvoice, "");
        $this->connection->delete($tableSalesInvoiceGrid, "");

        // Delete Order Items
        $this->connection->delete($tableSalesOrderItem, "");

        // Delete Order Payments
        $this->connection->delete($tableSalesOrderPayment, "");
        // tax
        $this->connection->delete($tableSalesOrderTaxItem, "");
        $this->connection->delete($tableSalesOrderTax, "");

        // Delete Order Shipments
        $this->connection->delete($tableSalesShipmentComment, "");
        $this->connection->delete($tableSalesShipmentItem, "");
        $this->connection->delete($tableSalesShipment, "");
        $this->connection->delete($tableSalesShipmentGrid, "");

        // Delete Orders
        $this->connection->delete($tableSalesOrder, "");
        $this->connection->delete($tableSalesOrderGrid, "");
    }
}
