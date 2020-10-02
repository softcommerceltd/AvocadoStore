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
use SoftCommerce\Avocado\Api\OrderCollectManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;
use SoftCommerce\Avocado\Model\Order\FileProcessorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CollectOrder
 * @package SoftCommerce\Avocado\Cron\Backend
 */
class CollectOrder extends AbstractCommand
{
    const COMMAND_NAME = 'softcommerce_avocado:collect_order';
    const SOURCE_FILTER = 'source';

    /**
     * @var OrderCollectManagementInterface
     */
    private OrderCollectManagementInterface $_orderCollectManagement;

    /**
     * @var FileProcessorInterface
     */
    private FileProcessorInterface $_fileProcessor;

    /**
     * CollectOrder constructor.
     * @param OrderCollectManagementInterface $orderCollectManagement
     * @param FileProcessorInterface $fileProcessor
     * @param State $appState
     * @param Logger $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string|null $name
     */
    public function __construct(
        OrderCollectManagementInterface $orderCollectManagement,
        FileProcessorInterface $fileProcessor,
        State $appState,
        Logger $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        string $name = null
    ) {
        $this->_orderCollectManagement = $orderCollectManagement;
        $this->_fileProcessor = $fileProcessor;
        parent::__construct($appState, $logger, $searchCriteriaBuilder, $name);
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Collect Avocado Order.')
            ->setDefinition([new InputOption(self::SOURCE_FILTER, '-s', InputOption::VALUE_REQUIRED, 'Source Filter')]);

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

        if (!$sourceFilter = $input->getOption(self::SOURCE_FILTER)) {
            throw new \InvalidArgumentException(
                'Required argument "source" is missing. Available arguments: ' . $this->getSynopsis()
            );
        }

        $output->writeln(sprintf('<info>Collecting Avocado orders by source %s.</info>', $sourceFilter));

        try {
            $this->_fileProcessor
                // ->setDelimiter(',')
                ->downloadSource($sourceFilter);
            if (!$sourceData = $this->_fileProcessor->getSourceData()) {
                return;
            }

            $this->_orderCollectManagement
                ->setSource($sourceData)
                ->execute();
        } catch (\Exception $e) {
            $this->_logger->log(100, $e->getMessage());
            return;
        }

        $output->writeln('<info>Done.</info>');

        return;
    }
}
