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
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\ShipmentCreateManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class CreateShipment
 * @package SoftCommerce\Avocado\Console\Command
 */
class CreateShipment extends AbstractCommand
{
    const COMMAND_NAME = 'softcommerce_avocado:create_shipment';
    const ID_FILTER = 'id';

    /**
     * @var ShipmentCreateManagementInterface
     */
    private ShipmentCreateManagementInterface $_shipmentCreateManagement;

    /**
     * CreateShipment constructor.
     * @param ShipmentCreateManagementInterface $orderCreateManagement
     * @param State $appState
     * @param Logger $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string|null $name
     */
    public function __construct(
        ShipmentCreateManagementInterface $orderCreateManagement,
        State $appState,
        Logger $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        string $name = null
    ) {
        $this->_shipmentCreateManagement = $orderCreateManagement;
        parent::__construct($appState, $logger, $searchCriteriaBuilder, $name);
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Create Avocado Shipment.')
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

        if ($idFilter = $input->getOption(self::ID_FILTER)) {
            $ids = explode(',', str_replace(' ', '', $idFilter));
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(OrderInterface::ENTITY_ID, $ids, 'in')
                ->create();
            /** @todo implement id filter */
            // $this->_shipmentCreateManagement->setSearchCriteriaRequest($searchCriteria);
            $output->writeln(sprintf('<info>Creating Avocado shipments by ID(s) %s.</info>', $idFilter));
        } else {
            $output->writeln(sprintf('<info>Creating Avocado shipments.</info>'));
        }

        $this->_shipmentCreateManagement->execute();
        $this->executeAfter($output, $this->_shipmentCreateManagement->getResponse());

        $output->writeln('<info>Done.</info>');

        return;
    }
}
