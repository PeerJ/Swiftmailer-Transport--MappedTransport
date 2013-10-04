<?php

/**
 * Uses several Transports with mapping rules.
 *
 */
class Swift_Transport_MappedTransport implements Swift_Transport
{
    /** The event dispatching layer */
    protected $_eventDispatcher;
     
    /**
     * The Transports which are used in mapping.
     *
     * @var array[] (TransportName -> Swift_Transport)
     */
    protected $_transports = array();

    /**
     * The default transport name
     *
     * @var string
     */
    protected $_defaultTransportName;

    /**
     * The Mapping which are used in mapping.
     *
     * @var string[] (key -> value)
     */
    protected $_mappings = array();
    
    /**
     * Creates a new MappedTransport.
     */
    public function __construct(Swift_Events_MappedTransportEventDispatcherInterface $dispatcher)
    {
       $this->_eventDispatcher = $dispatcher;
    }

    /**
     * Set $transports to delegate to.
     *
     * @param Swift_Transport[] $transports
     */
    public function setTransports(array $transports)
    {
        foreach($transports as $key => $value) {
           if (is_string($key) == false) {
               throw new Exception("Invalid transport array - must be key(string) => value");
           } else {
               $this->_mappings[$key] = array();
           }
        }
        
        $this->_transports = $transports;
    }
    
    /**
     * Get $transports to delegate to.
     *
     * @return Swift_Transport[]
     */
    public function getTransports()
    {
        return $this->_transports;
    }

    /**
     * Get $transports to delegate to.
     *
     * @return Swift_Transport[]
     */
    public function getTransportByName($transportName)
    {
        if (array_key_exists($transportName, $this->_transports)) {
          return $this->_transports[$transportName];
        } else {
          return null;
        }
    }

    /**
     * Set the default transport name
     *
     * @param string $transportName
     */
    public function setDefaultTransportName($transportName)
    {
        $this->_defaultTransportName = $transportName;
    }

    /**
     * Get the default transport name
     *
     * @return string
     */
    public function getDefaultTransportName()
    {
        return $this->_defaultTransportName;
    }

    /**
     * Get the default transport
     *
     * @return Swift_Transport 
     */
    public function getDefaultTransport()
    {
        return $this->getTransportByName($this->getDefaultTransportName());
    }

    /**
     * Set $mappings to delegate to.
     *
     * @param string $transport
     * @param string[] $mappings
     */
    public function setMappings($transportName, array $mappings)
    {
        $this->_mappings[$transportName] = $mappings;
    }
    
    /**
     * Get $transports to delegate to.
     *
     * @param string $transportName
     * @return string[]
     */
    public function getMappings($transportName)
    {
        return $this->_mappings[$transportName];
    }

    /**
     * Test if this Transport mechanism has started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        foreach($this->_transports as $transport)
        {
           if ($transport->isStarted()) {
               return true;
           }
        }
        
        return false;
    }

    /**
     * Start this Transport mechanism.
     */
    public function start()
    {
      // nothing to do - individual transports will be started as required in the send
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
        foreach ($this->_transports as $transport) {
            $transport->stop();
        }
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $transport = $this->_getMappingTransport($message);
        if ($transport == null) {
            throw new Swift_TransportException(
                'Unable to find transport'
                );
        }
              if (!$transport->isStarted()) {
                  $transport->start();
              }
              $sent = $transport->send($message, $failedRecipients);

        return $sent;
    }

    /**
     * Register a plugin.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
     
        // mappedtransport for this instance, other listeners for the mapped transport objects
        //if ($plugin instanceof Swift_Events_MappedTransportListener) {
             $this->_eventDispatcher->bindEventListener($plugin);
        //} else {
          foreach ($this->_transports as $transport) {
              $transport->registerPlugin($plugin);
          }
        //}
    }

    // -- Protected methods

    /**
     * Find the transport according to mappings
     *
     * @return Swift_Transport
     */
    protected function _getMappingTransport(Swift_Mime_Message $message)
    {
       $evt = $this->_eventDispatcher->createMappedTransportSelectedEvent($this, $message);

       foreach ($this->getTransports() as $transportName => $transport) {
         $evt->setTransportDetails($transportName, $transport);
         $mapping = $this->getMappings($transportName);
         if ($mapping != null) {
           foreach ($mapping as $mappingItem)  {
              foreach ($mappingItem as $key => $mappingValue)  {
                  // call key function on the message (e.g. getFrom)
                  $messageValue = call_user_func(array($message, $key));

                  // we're currently expecting messagevalue to be:
                  // Swift_Mime_SimpleHeaderSet if getHeaders() is called
                  // array($email => name) if getFrom/getTo is called
                  // string if getSubject is called
                  if ($messageValue instanceof Swift_Mime_SimpleHeaderSet && is_array($mappingValue)) {
                     $mappingValueKey = key($mappingValue);
                     $headers = $messageValue->getAll($mappingValueKey);
                     foreach ($headers as $header) {
                       if (preg_match($this->ensureRegEx($mappingValue[$mappingValueKey]), $header->getValue())) {
                          $evt->setMatch(array(
                                               'Match' => 'Header/' .$mappingValueKey,
                                               'RegEx' => $this->ensureRegEx($mappingValue[$mappingValueKey]),
                                               'Value' => $header->getValue()
                          ));
                          $this->_eventDispatcher->dispatchEvent($evt, 'mappedTransportSelected');
                          return $transport;
                       }
                     }
                  } else if (is_array($messageValue)) {  //  email address (email=>name), in which case match the email
                       foreach ($messageValue as $messageItemKey => $messageItemValue) {
                         if (preg_match($this->ensureRegEx($mappingValue), $messageItemKey)) {
                            $evt->setMatch(array(
                              'Match' => 'Email',
                              'RegEx' => $this->ensureRegEx($mappingValue),
                              'Value' => $messageItemKey
                            ));
                            $this->_eventDispatcher->dispatchEvent($evt, 'mappedTransportSelected');
                            return $transport;
                         }
                       }
                  } else if (preg_match($this->ensureRegEx($mappingValue), $messageValue)) {
                       $evt->setMatch(array(
                              'Match' => 'Subject',
                              'RegEx' => $this->ensureRegEx($mappingValue),
                              'Value' => $messageValue
                       ));
                       $this->_eventDispatcher->dispatchEvent($evt, 'mappedTransportSelected');
                       return $transport;
                  }   
              }
           }
         }
       }

       $evt->setMatch(array(
            'Match' => 'Default',
            'RegEx' => '.*',
            'Value' => 'N/A'
       ));       
       $evt->setTransportDetails('Default', $this->getDefaultTransport());
       $this->_eventDispatcher->dispatchEvent($evt, 'mappedTransportSelected');
       return $this->getDefaultTransport(); 
    }
    
    /**
     * Find the transport according to mappings
     *
     * @return Swift_Transport
     */
    protected function ensureRegEx($pattern)
    {
      if (!preg_match('/^\/.*\/$/', $pattern)) {
          $pattern = '/^' . str_replace('.', '\.', $pattern) . '$/i';
      }
      
      return $pattern;
    }
}
