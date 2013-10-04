<?php

/**
 * Listens for changes within the MappedTransport system.
 *
 */
interface Swift_Events_MappedTransportListener extends Swift_Events_EventListener
{
    /**
     * Invoked immediately after the Mapped Transport is selected
     *
     * @param Swift_Events_MappedTransportEvent $evt
     */
    public function mappedTransportSelected(Swift_Events_MappedTransportEvent $evt);
}
