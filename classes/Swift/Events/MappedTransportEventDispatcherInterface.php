<?php

/**
 * Interface for the Swift_Events_MappedTransportEventDispatcher which handles the event dispatching layer.
 *
 * @package    Swift
 * @subpackage Events
 * @author     Chris Corbyn
 */
interface Swift_Events_MappedTransportEventDispatcherInterface
{

    /**
     * Create a new createMappedTransportSelectedEvent for $source.
     *
     * @param Swift_Transport $source
     *
     * @return createMappedTransportSelectedEvent
     */
    public function createMappedTransportSelectedEvent(Swift_Transport $source, Swift_Mime_Message $message);

    /**
     * Dispatch the given Event to all suitable listeners.
     *
     * @param Swift_Events_EventObject $evt
     * @param string                   $target method
     */
    public function dispatchEvent(Swift_Events_EventObject $evt, $target);
}
