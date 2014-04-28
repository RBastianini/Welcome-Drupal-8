<?php

namespace Drupal\vendini;
use Drupal\Core\Entity\ContentEntityInterface;

interface TicketInterface extends ContentEntityInterface {

	/**
	 * Returns the ID of the Event this ticket grants admittance to.
	 * @return \Drupal\node\Entity\Node
	 */
	public function getEvent();

	/**
	 * Returns the ID of the User this ticket belongs to.
	 * @return \Drupal\user\Entity\User
	 */
	public function getUser();

	/**
	 * Returns if this ticket has already been admitted or not.
	 * @return bool
	 */
	public function isAdmitted();

	/**
	 * Changes the admitted state of the ticket.
	 * @param bool $admittance
	 */
	public function setAdmitted($admittance);

}

?>
