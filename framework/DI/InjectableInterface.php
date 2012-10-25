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
}
