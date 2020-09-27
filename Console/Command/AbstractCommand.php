<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Console\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\Phrase;
use SoftCommerce\Avocado\Logger\Logger;
use SoftCommerce\Avocado\Model\Source\Status;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 * @package SoftCommerce\Avocado\Console\Command
 */
class AbstractCommand extends Command
{
    /**
     * @var State
     */
    protected State $_appState;

    /**
     * @var Logger
     */
    protected Logger $_logger;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * AbstractCommand constructor.
     * @param State $appState
     * @param Logger $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string|null $name
     */
    public function __construct(
        State $appState,
        Logger $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        string $name = null
    ) {
        $this->_appState = $appState;
        $this->_logger = $logger;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($name);
    }

    /**
     * @param OutputInterface $output
     * @param array $response
     * @return $this
     */
    protected function executeAfter(OutputInterface $output, array $response)
    {
        if (!is_array($response)) {
            $output->writeln(sprintf("<error>{$response}</error>"));
            return $this;
        }

        foreach ($response as $status => $message) {
            if (is_array($message)) {
                $this->executeAfter($output, $message);
                continue;
            }

            if ($message instanceof Phrase) {
                $message = $message->render();
            }

            if ($status === Status::ERROR) {
                $output->writeln(sprintf("<error>{$message}</error>"));
            } elseif ($status === Status::WARNING) {
                $output->writeln(sprintf("<warning>{$message}</warning>"));
            } elseif ($status === Status::NOTICE) {
                $output->writeln(sprintf("<notice>{$message}</notice>"));
            } else {
                $output->writeln(sprintf("<info>{$message}</info>"));
            }
        }

        return $this;
    }
}
