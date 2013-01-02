<?php
namespace Eve\Mvc;

class Event
{
    /**
     * Array of events
     *
     * @var array
     */
    protected static $_events;

    /**
     * Attach multiple callbacks to an event
     *
     * @param  string $event    name
     * @param  mixed  $callback the method or function to call
     * @return Event
     */
    public function attach($event, $callback)
    {
        // Add callack to event
        static::$_events[$event][] = $callback;

        return $this;
    }

    /**
     * Remove event
     *
     * @param  string $event name
     * @return Event
     */
    public function remove($event)
    {
        unset(static::$_events[$event]);

        return $this;
    }

    /**
     * Trigger callbacks for event
     *
     * @param  string $event name
     * @param  mixed  $value the optional value to pass to each callback
     * @return mixed
     */
    public function trigger($event, $value = null)
    {
        if (!isset(static::$_events[$event])) { return false; }

        // Fire a callback
        foreach (static::$_events[$event] as $function) {
            $value = call_user_func($function, $value);
        }

        return $value;
    }
}
