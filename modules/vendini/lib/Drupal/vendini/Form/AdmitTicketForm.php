<?php

namespace Drupal\vendini\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityManagerInterface;


/**
 * Allows a privileged user to admit a ticket. Once admitted, a ticket cannot be reused.
 * Privileged users can also unadmit tickets.
 * @author RB
 */
class AdmitTicketForm extends ContentEntityConfirmFormBase {

	protected $userStorageController;
	protected $nodeStorageController;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(EntityManagerInterface $entity_manager) {

		$this->userStorageController = $entity_manager->getStorageController('user');
		$this->nodeStorageController = $entity_manager->getStorageController('node');
		parent::__construct($entity_manager);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {

		return 'vendini_admit_ticket_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelRoute() {
		return array(
			'route_name' => 'vendini.viewTicket',
			'route_parameters' => array(
				'vendini_ticket' => $this->entity->id()
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuestion() {

		// Get the right question depending on the ticket state
		if ($this->entity->isAdmitted()) {
			$question = 'Are you sure you want to un-admit the following ticket?';
		}
		else {
			$question = 'You are about to admit the following ticket';
		}
		return $this->t($question);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDescription() {

		$user = $this->userStorageController->load($this->entity->getUser());
		$event = $this->nodeStorageController->load($this->entity->getEvent());
		return $this->t('@user\'s ticket for the "@event" event',
				array(
					'@user' => $user->getUsername(),
					'@event' => $event->getTitle(),
				)
		);
	}

	/**
	 * Actually deletes the ticket
	 * @param array $form
	 * @param array $form_state
	 */
	public function submit(array $form, array &$form_state) {

		// Yes, this may cause issues if two users are trying to admit/unadmit a ticket at the same time,
		// but come on, it's a demo!
		$this->entity->setAdmitted(!$this->entity->isAdmitted());
		if ($this->entity->save() == SAVED_UPDATED) {
			if ($this->entity->isAdmitted()) {
			drupal_set_message($this->t('The ticket has been admitted.'));
			watchdog('vendini', 'Ticket %id has been admitted.', array('%id' => $this->entity->id()), WATCHDOG_NOTICE);
		}
		else {
			drupal_set_message($this->t('The ticket has been un-admitted.'));
			watchdog('vendini', 'Ticket %id has been un-admitted.', array('%id' => $this->entity->id()), WATCHDOG_NOTICE);
		}
		$form_state['redirect_route']['route_name'] = 'vendini.viewTicket';
		$form_state['redirect_route']['route_parameters']['vendini_ticket'] = $this->entity->id();
		}
		else {
			drupal_set_message(t('An error has occurred, please try again.'), 'error');
		}
		
	}
}