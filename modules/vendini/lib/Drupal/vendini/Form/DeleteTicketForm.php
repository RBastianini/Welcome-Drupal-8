<?php

namespace Drupal\vendini\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Allows a privileged user to permanently delete a ticket.
 * @author RB
 */
class DeleteTicketForm extends ContentEntityConfirmFormBase {


	protected $userStorageController;
	protected $nodeStorageController;


	public function __construct(EntityManagerInterface $entity_manager) {

		$this->userStorageController = $entity_manager->getStorageController('user');
		$this->nodeStorageController = $entity_manager->getStorageController('node');
		parent::__construct($entity_manager);
	}

	public function getFormId() {

		return 'vendini_delete_ticket_form';
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

		// Get the user and the event to show a meaningful question
		$user = $this->userStorageController->load($this->entity->getUser());
		$event = $this->nodeStorageController->load($this->entity->getEvent());
		return $this->t('Are you sure you want to delete @user\'s ticket for the "@event" event?',
				array(
					'@user' => $user->getUsername(),
					'@event' => $event->getTitle(),
				)
		);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDescription() {
		return $this->t('This action cannot be undone.');
	}

	/**
	 * Actually deletes the ticket
	 * @param array $form
	 * @param array $form_state
	 */
	public function submit(array $form, array &$form_state) {

		$event = $this->nodeStorageController->load($this->entity->getEvent());
		$this->entity->delete();
		drupal_set_message($this->t('The ticket has been deleted.'));
		watchdog('vendini', 'Deleted ticket %id.', array('%id' => $this->entity->id()), WATCHDOG_NOTICE);
		$form_state['redirect_route']['route_name'] = 'node.view';
		$form_state['redirect_route']['route_parameters']['node'] = $event->id();
	}

	

}

