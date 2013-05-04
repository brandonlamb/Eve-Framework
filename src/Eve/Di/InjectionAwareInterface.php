<?php
namespace Eve\Di;

use Eve\Di\DiInterface as DiInterface;

interface InjectionAwareInterface
{
    /**
     * Sets the DI container
     *
     * @param DiInterface $di
     */
    public function setDI(DiInterface $di);

    /**
     * Returns the DI container
     *
     * @return DiInterface
     */
    public function getDI();
}
