<?php
namespace Eve\DI;

interface InjectableInterface
{
    /**
     * Sets the DI container
     *
     * @param DI $di
     */
    public function setDI(\Eve\DI $di);

    /**
     * Returns the DI container
     *
     * @return DI
     */
    public function getDI();

    /**
     * Sets the events manager
     *
     * @param Events\Manager $eventsManager
     * @return DI
     */
    public function setEventsManager(\Eve\Events\Manager $eventsManager);

    /**
     * Returns the events manager
     *
     * @return Events\Manager
     */
    public function getEventsManager();
}
