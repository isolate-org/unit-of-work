<?php

namespace Coduo\UnitOfWork\Tests\Double;

use Coduo\UnitOfWork\Command\BatchNewCommandHandler;

class BatchNewCommandHandlerMock extends BatchNewCommandHandler
{
    protected $handledBatches = 0;

    protected $handledCommands = 0;

    /**
     * @param array $commands
     * @return mixed
     */
    protected function handleBatch($commands = [])
    {
        $this->handledBatches++;
        $this->handledCommands += count($commands);
    }

    /**
     * @return int
     */
    public function getHandledBatchesCount()
    {
        return $this->handledBatches;
    }

    /**
     * @return int
     */
    public function getTotalHandledCommandsCount()
    {
        return $this->handledCommands;
    }
}
