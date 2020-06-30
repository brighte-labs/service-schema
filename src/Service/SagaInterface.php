<?php

namespace ServiceSchema\Service;

use ServiceSchema\Event\MessageInterface;

interface SagaInterface extends ServiceInterface
{

    /**
     * @param \ServiceSchema\Event\MessageInterface $message
     * @return \ServiceSchema\Event\MessageInterface|bool
     */
    public function rollback(MessageInterface $message = null);
}
