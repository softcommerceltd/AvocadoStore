<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Console\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use SoftCommerce\Avocado\Api\OrderCollectManagementInterface;
use SoftCommerce\Avocado\Api\ShipmentCollectManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CollectShipment
 * @package SoftCommerce\Avocado\Console\Command
 */
class CollectShipment extends AbstractCommand
{
    const COMMAND_NAME = 'softcommerce_avocado:collect_shipment';
    const ID_FILTER = 'id';

    /**
     * @var ShipmentRepositoryInterface
     */
    private ShipmentRepositoryInterface $_salesOrderShipmentRepository;

    /**
     * @var OrderCollectManagementInterface
     */
    private ShipmentCollectManagementInterface $_shipmentCollectManagement;

    /**
     * CollectShipment constructor.
     * @param ShipmentRepositoryInterface $salesOrderShipmentRepository
     * @param ShipmentCollectManagementInterface $shipmentCollectManagement
     * @param State $appState
     * @param Logger $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string|null $name
     */
    public function __construct(
        ShipmentRepositoryInterface $salesOrderShipmentRepository,
        ShipmentCollectManagementInterface $shipmentCollectManagement,
        State $appState,
        Logger $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        string $name = null
    ) {
        $this->_salesOrderShipmentRepository = $salesOrderShipmentRepository;
        $this->_shipmentCollectManagement = $shipmentCollectManagement;
        parent::__construct($appState, $logger, $searchCriteriaBuilder, $name);
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Collect Avocado Shipment.')
            ->setDefinition([
                new InputOption(
                    self::ID_FILTER,
                    '-i',
                    InputOption::VALUE_REQUIRED,
                    'ID Filter'
                )
            ]);
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_appState->setAreaCode(Area::AREA_ADMINHTML);

        if (!$idFilter = $input->getOption(self::ID_FILTER)) {
            throw new \InvalidArgumentException(
                'Required argument "source" is missing. Available arguments: ' . $this->getSynopsis()
            );
        }

        $ids = explode(',', str_replace(' ', '', $idFilter));
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(ShipmentInterface::ENTITY_ID, $ids, 'in')
            ->create();

        $output->writeln(sprintf('<info>Collecting Avocado shipments by ID %s.</info>', $idFilter));

        $orders = $this->_salesOrderShipmentRepository->getList($searchCriteria);
        $errors = [];
        foreach ($orders->getItems() as $shipment) {
            try {
                $this->_shipmentCollectManagement
                    ->setRequest($shipment)
                    ->execute();
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
                return;
            }
        }

        if (!empty($errors)) {
            $errors = implode(', ', $errors);
            $output->writeln('<error>' . sprintf('Processed with errors. %s', $errors) . '</error>');
        } else {
            $output->writeln('<info>Done.</info>');
        }

        return;
    }
}
