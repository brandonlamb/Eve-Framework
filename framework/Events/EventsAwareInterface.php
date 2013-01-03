<?php
namespace Eve\Events;

interface EventsAwareInterface
{
	/**
	 * Sets the events manager
	 *
	 * @param Events\Manager $eventsManager
	 * @return Manager
	 */
	public function setEventsManager(\Eve\Events\Manager $eventsManager);

	/**
	 * Returns the events manager
	 *
	 * @return Manager
	 */
	public function getEventsManager();
}