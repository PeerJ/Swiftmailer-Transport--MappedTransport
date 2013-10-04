<?php

/**
 * Generated when the mapped transport is selected
 *
 */
class Swift_Events_MappedTransportEvent extends Swift_Events_EventObject
{
    private $message;
    private $transportName;
    private $transport;
    private $match;

    /**
     * Create a new Swift_Events_MappedTransportEvent
     *
     * @param Swift_Transport $source
     * @param Swift_Mime_message $message
     *
     */
    public function __construct(Swift_Transport $source, Swift_Mime_Message $message)
    {
        parent::__construct($source);
        $this->message = $message;
    }
    
    /**
     * Get the Mapped Transport.
     *
     * @return Swift_Transport
     */
    public function getMappedTransport()
    {
        return $this->getSource();
    }

    /**
     * Get the Transport.
     *
     * @return Swift_Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }
    
    /**
     * Set the Transport.
     *
     * @param Swift_Transport $transport
     */
    public function setTransport(Swift_Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Get the Transport Name.
     *
     * @return string
     */
    public function getTransportName()
    {
        return $this->transportName;
    }
    
    /**
     * Set the Transport Name.
     *
     * @param string $transport
     */
    public function setTransportName($transportName)
    {
        $this->transportName = $transportName;
    }

    /**
     * Set the Transport Details.
     *
     * @param string $transportName
     * @param Swift_Transport $transport
     */
    public function setTransportDetails($transportName, Swift_Transport $transport)
    {
        $this->setTransportName($transportName);
        $this->setTransport($transport);
    }
    
    /**
     * Get the Mail Message.
     *
     * @return Swift_Mime_Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set the Mail Message.
     *
     * @return Swift_Mime_Message
     */
    public function setMessage(Swift_Mime_Message $message)
    {
        return $this->message = $message;
    }

    /**
     * Get the match
     *
     * @return array 
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * Set the match
     *
     * @param array $match
     * 
     * @return array
     */
    public function setMatch(array $match)
    {
        return $this->match = $match;
    }
}
