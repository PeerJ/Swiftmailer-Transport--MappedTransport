<?php

Swift_DependencyContainer::getInstance()
    -> register('transport.mapped')
    -> asNewInstanceOf('Swift_Transport_MappedTransport')

;

