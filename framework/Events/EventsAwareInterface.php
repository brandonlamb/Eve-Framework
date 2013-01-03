<?php
namespace Eve\Events;

interface EventsAwareInterface
{
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