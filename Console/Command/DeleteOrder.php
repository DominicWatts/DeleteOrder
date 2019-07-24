<?php


namespace Xigen\DeleteOrder\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeleteOrder extends Command
{
    const ALL_ARGUMENT = 'all';
    const ORDERID_OPTION = 'orderid';
    const INCREMENTID_OPTION = 'increment';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Xigen\DeleteOrder\Helper\Data
     */
    protected $deleteHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * DeleteOrder constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\State $state
     * @param \Xigen\AutoShipment\Helper\Shipment $shipmentHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\State $state,
        \Xigen\DeleteOrder\Helper\Data $deleteHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->state = $state;
        $this->deleteHelper = $deleteHelper;
        $this->dateTime = $dateTime;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->input = $input;
        $this->output = $output;

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $orderId = $this->input->getOption(self::ORDERID_OPTION);
        $incrementId = $this->input->getOption(self::INCREMENTID_OPTION);
        $all = $input->getArgument(self::ALL_ARGUMENT) ?: false;

        if ($orderId || $incrementId) {
            $this->output->writeln((string) __('%1 Processing order <info>%2</info>',
                $this->dateTime->gmtDate(),
                $orderId ?: $incrementId
            ));

            if ($orderId) {
                $order = $this->deleteHelper->getOrderById($orderId);
            }

            if ($incrementId) {
                $order = $this->deleteHelper->getOrderByIncrementId($incrementId);
            }

            if ($order) {
                $this->output->writeln((string) __('%1 Deleting order <info>%2</info>',
                    $this->dateTime->gmtDate(),
                    $orderId ?: $incrementId
                ));

                try {
                    $this->deleteHelper->deleteOrder($order->getId());
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    return;
                }
            } else {
                $this->output->writeln((string) __('%1 Error not found order <info>%2</info>',
                    $this->dateTime->gmtDate(),
                    $orderId ?: $incrementId
                ));
            }
        } elseif ($all) {
            $this->output->writeln((string) __('%1 Start Processing orders', $this->dateTime->gmtDate()));
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'You are about to remove all your orders. Are you sure?[y/N]',
                false
            );
            if (!$helper->ask($this->input, $this->output, $question) && $this->input->isInteractive()) {
                return Cli::RETURN_FAILURE;
            }
            $this->deleteHelper->deleteAll();
            $this->output->writeln((string) __('%1 Finish Processing orders', $this->dateTime->gmtDate()));
        }
    }

    /**
     * {@inheritdoc}
     * xigen:deleteorder:delete [-o|--orderid ORDERID] [-i|--incrementid [INCREMENTID]] [--] <all>
     */
    protected function configure()
    {
        $this->setName('xigen:deleteorder:delete');
        $this->setDescription('Delete orders by ID or delete them all');
        $this->setDefinition([
            new InputArgument(self::ALL_ARGUMENT, InputArgument::OPTIONAL, 'All'),
            new InputOption(self::ORDERID_OPTION, '-o', InputOption::VALUE_OPTIONAL, 'Order Entity ID'),
            new InputOption(self::INCREMENTID_OPTION, '-i', InputOption::VALUE_OPTIONAL, 'Order Increment ID'),
        ]);
        parent::configure();
    }
}
