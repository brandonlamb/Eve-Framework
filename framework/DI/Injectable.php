<?php
namespace Eve\DI;

abstract class Injectable implements InjectableInterface
{
    /**
     * @var DI
     */
    private $di;

    /**
     * @var Events\Manager
     */
    private $eventsManager;

    public function __construct(\Eve\DI $di = null)
    {
        if (null === $di) {
            $di = \Eve\DI::getDefault();
        }
        $this->di = $di;
    }

    /**
     * Magic getter method that checks DI container for propery
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $di = $this->getDI();
        if ($di->hasShared($key)) {
            return $this->getShared($key);
        } else if ($this->has($key)) {
            return $this->get($key);
        }
        throw new \InvalidArgument($key . ' is not a valid property');
    }

    /**
     * Set the DI container
     *
     * @param  DI         $di
     * @return Injectable
     */
    public final function setDI(\Eve\DI $di)
    {
        $this->di = $di;

        return $this;
    }

    /**
     * Return the DI container
     *
     * @return DI
     */
    public final function getDI()
    {
        return $this->di;
    }

    /**
     * Set the events manager
     *
     * @param Events\Manager $eventsManager
     * @return Injectable
     */
    public final function setEventsManager(\Eve\Events\Manager $eventsManager)
    {
        $this->eventsManager = $eventsManager;
        return $this;
    }

    /**
     * Return the events manager
     *
     * @return Events\Manager
     */
    public final function getEventsManager()
    {
        return $this->eventsManager;
    }
}
