<?php

namespace ServiceSchema\Main;

use ServiceSchema\Event\MessageInterface;
use ServiceSchema\Service\ServiceInterface;

interface ProcessorInterface
{
    /**
     * @param string|\ServiceSchema\Event\Message $json
     * @param bool $return return first service result
     * @param array|null $filteredEvents
     * @return bool
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     * @throws \ServiceSchema\Main\Exception\ProcessorException
     */
    public function process($json = null, array $filteredEvents = null, bool $return = false);

    /**
     * @param string|\ServiceSchema\Event\Message $json
     * @return bool
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Main\Exception\ProcessorException
     */
    public function rollback($json = null);

    /**
     * @param \ServiceSchema\Event\MessageInterface|null $message
     * @param \ServiceSchema\Service\ServiceInterface|null $service
     * @param array|null $callbacks
     * @return mixed
     */
    public function runService(
        MessageInterface $message = null,
        ServiceInterface $service = null,
        array $callbacks = null
    );

    /**
     * @param \ServiceSchema\Event\MessageInterface $event
     * @param array|null $callbacks
     * @return mixed
     */
    public function runCallbacks(MessageInterface $event, array $callbacks = null);
}
