<?php


/**
 * The Swift_Events_MappedTransportEventDispatcher which handles the event dispatching layer.
 * Based on Swift_Events_SimpleEventDispatcher - shame it didn't have a method to add to the event map as most code here is duplicated
 *
 */
class Swift_Events_MappedTransportEventDispatcher implements Swift_Events_MappedTransportEventDispatcherInterface
{
    /** A map of event types to their associated listener types */
    private $_eventMap = array();

    /** Event listeners bound to this dispatcher */
    private $_listeners = array();

    /** Listeners queued to have an Event bubbled up the stack to them */
    private $_bubbleQueue = array();
     
    /**
     * Create a new EventDispatcher.
     */
    public function __construct()
    {
        $this->_eventMap = array(
            'Swift_Events_MappedTransportEvent' => 'Swift_Events_MappedTransportListener'
            );
    }

    /**
     * Create a new TransportChangeEvent for $source.
     *
     * @param Swift_Transport $source
     *
     * @return Swift_Events_TransportChangeEvent
     */
    public function createMappedTransportSelectedEvent(Swift_Transport $source, Swift_Mime_Message $message)
    {
        return new Swift_Events_MappedTransportEvent($source, $message);
    }

    /**
     * Bind an event listener to this dispatcher.
     *
     * @param Swift_Events_EventListener $listener
     */
    public function bindEventListener(Swift_Events_EventListener $listener)
    {
        foreach ($this->_listeners as $l) {
            //Already loaded
            if ($l === $listener) {
                return;
            }
        }
        $this->_listeners[] = $listener;
    }
    
    /**
     * Dispatch the given Event to all suitable listeners.
     *
     * @param Swift_Events_EventObject $evt
     * @param string                   $target method
     */
    public function dispatchEvent(Swift_Events_EventObject $evt, $target)
    {
        $this->_prepareBubbleQueue($evt);
        $this->_bubble($evt, $target);
    }
    
    // -- Private methods

    /** Queue listeners on a stack ready for $evt to be bubbled up it */
    private function _prepareBubbleQueue(Swift_Events_EventObject $evt)
    {
        $this->_bubbleQueue = array();
        $evtClass = get_class($evt);
        foreach ($this->_listeners as $listener) {
            if (array_key_exists($evtClass, $this->_eventMap)
                && ($listener instanceof $this->_eventMap[$evtClass]))
            {
                $this->_bubbleQueue[] = $listener;
            }
        }
    }

    /** Bubble $evt up the stack calling $target() on each listener */
    private function _bubble(Swift_Events_EventObject $evt, $target)
    {
        if (!$evt->bubbleCancelled() && $listener = array_shift($this->_bubbleQueue)) {
            $listener->$target($evt);
            $this->_bubble($evt, $target);
        }
    }

}
