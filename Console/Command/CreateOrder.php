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
use SoftCommerce\Avocado\Api\OrderCreateManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateOrder
 * @package SoftCommerce\Avocado\Console\Command
 */
class CreateOrder extends AbstractCommand
{
    const COMMAND_NAME = 'softcommerce_avocado:create_order';
    const ID_FILTER = 'id';

    /**
     * @var OrderCreateManagementInterface
     */
    private OrderCreateManagementInterface $_orderCreateManagement;

    /**
     * CreateOrder constructor.
     * @param OrderCreateManagementInterface $orderCreateManagement
     * @param State $appState
     * @param Logger $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string|null $name
     */
    public function __construct(
        OrderCreateManagementInterface $orderCreateManagement,
        State $appState,
        Logger $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        string
       $name = null
    ) {
        $this->_orderCreateManagement = $orderCreateManagement;
        parent::__construct($appState, $logger, $searchCriteriaBuilder, $name);
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Create Avocado Order.')
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
            $this->_orderCreateManagement->setSearchCriteriaRequest($searchCriteria);
            $output->writeln(sprintf('<info>Creating Avocado orders by ID(s) %s.</info>', $idFilter));
        } else {
            $output->writeln(sprintf('<info>Creating Avocado orders.</info>'));
        }

        $this->_orderCreateManagement->execute();
        $this->executeAfter($output, $this->_orderCreateManagement->getResponse());

        $output->writeln('<info>Done.</info>');

        return;
    }
}
