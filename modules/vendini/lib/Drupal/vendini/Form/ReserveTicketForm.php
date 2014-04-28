<?php

namespace Drupal\vendini\Form;

use Drupal\Core\Form\FormBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use \Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allows a user to reserve a ticket. Must be created by also passing in an event or an exception
 * will be thrown.
 * @author RB
 */
class ReserveTicketForm extends FormBase {

	protected $nodeStorageController;
	protected $ticketStorageController;


	/**
	 * This custom constructor needs the EntityStorageController of the ticket and node entities.
	 * @param \Drupal\Core\Entity\EntityStorageControllerInterface $nodeController
	 * @param \Drupal\Core\Entity\EntityStorageControllerInterface $ticketController
	 */
	public function __construct(EntityStorageControllerInterface $nodeController, EntityStorageControllerInterface $ticketController) {

		$this->ticketStorageController = $ticketController;
		$this->nodeStorageController = $nodeController;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container) {

		return new static(
			$container->get('entity.manager')->getStorageController('node'),
			$container->get('entity.manager')->getStorageController('vendini_ticket')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {

		return 'vendini_reserve_ticket_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, array &$form_state, NodeInterface $event = null) {

		if ($event == null || $event->bundle() !== 'event') {
			throw new Exception('An event node must be passed to construct a ReserveTicketForm');
		}
		$form['event'] = array(
			'#type' => 'hidden',
			'#value' => $event->id()
		);
		$form['button'] = array(
			'#type' => 'submit',
			'#name' => 'reserve',
			'#value' => $this->t('Reserve ticket for this event'),
			'#weight' => 2
		);
		// Calling these methods is not very classy, since we're basically breaking the dependency injection paradigm
		// please don't send me to hell for this.

		// An user can only get one ticket, if the user can't reserve more tickets, we should warn him
		if (user_count_reserved_tickets($event) >= VENDINI_MAX_TICKET_PER_USER) {
			$form['before_button_text'] = array(
				'#type' => 'markup',
				'#markup' => $this->t('You can\'t reserve more tickets for this event.'),
				'#weight' => 1
			);
			unset($form['button']);
		}

		// We should also make sure not to overbook an event
		if (!event_has_available_tickets($event)) {
			$form['before_button_text'] = array(
				'#type' => 'markup',
				'#markup' => $this->t('Tickets for this event are sold out.'),
				'#weight' => 1
			);
			unset($form['button']);
		}
		return $form;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, array &$form_state) {

		// I should probably not call check plain either :(
		$config = $this->config('vendini.sales');
		if (!$config->get('enabled')) {
			$this->setFormError('button', $form_state, check_plain($config->get('message')));
		}
		$event = $this->nodeStorageController->load($form_state['values']['event']);

		// Ensure that we can reserve this ticket
		if (!event_has_available_tickets($event)) {
			$this->setFormError('button', $form_state, $this->t('Tickets for this event are sold out.'));
		}
	}

	/**
	 * Beware: this form can actually allow users to reserve more thickets than the available venue seats.
	 * @param array $form
	 * @param array $form_state
	 */
	public function submitForm(array &$form, array &$form_state) {

		$ticket = $this->ticketStorageController->create(array(
			'user' => $this->currentUser()->id(),
			'event' => $form_state['values']['event']
		));
		if ($ticket->save() == SAVED_NEW) {
			drupal_set_message(t('Your ticket has been reserved. Your ticket ID is @code', array(
				'@code' => $ticket->uuid())));
			$form_state['redirect_route']['route_name'] = 'vendini.viewTicket';
			$form_state['redirect_route']['route_parameters']['vendini_ticket'] = $ticket->id();
			watchdog('vendini', 'Reserved ticket %ticket_id for event &event_id.', array(
				'%ticket_id' => $ticket->id(),
				'%event_id' => $form_state['values']['event']
					), WATCHDOG_NOTICE);
		} else {
			drupal_set_message($this->t('An error has occurred. Please try again.'), 'error');
		}
	}

}

