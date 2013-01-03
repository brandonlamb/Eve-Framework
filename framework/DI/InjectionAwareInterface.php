<?php
namespace Eve\DI;
use Eve\DiInterface as DiInterface;

interface InjectionAwareInterface
{
    /**
     * Sets the DI container
     *
     * @param DI $di
     */
    public function setDI(DiInterface $di);

    /**
     * Returns the DI container
     *
     * @return DI
     */
    public function getDI();
}
