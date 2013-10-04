<?php

Swift_DependencyContainer::getInstance()
    -> register('transport.mapped')
    -> asNewInstanceOf('Swift_Transport_MappedTransport')
    ->withDependencies('transport.eventdispatcher')

    ->register('transport.mapped.eventdispatcher')
    ->asNewInstanceOf('Swift_Events_MappedTransportEventDispatcher')

;

