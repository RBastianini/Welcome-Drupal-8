<?php

namespace Drupal\vendini\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Drupal\Core\Access\AccessInterface;
use Drupal\vendini\TicketInterface;


class TicketController extends ControllerBase {

	/**
	 * Returns a renderable array for the reserve ticket page
	 * @param \Drupal\node\NodeInterface $node
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function reserveTicket(NodeInterface $node) {

		// This page only makes sense for events
		if ($node->bundle() !== 'event') {
			// Drupal_not_found()
			throw new NotFoundHttpException();
		}

		$page = array();
		// Display the content only if sales are actuve
		$config = $this->config('vendini.sales');
		if ($config->get('enabled')) {
			// Display the event in "teaser" mode (node_view)
			$this->entityManager()->getViewBuilder('node')->view($node, 'teaser');
			$page['reserve_form'] = $this->formBuilder()->getForm('\Drupal\vendini\Form\ReserveTicketForm', $node);

		}
		else{
			// Just display the message specified in the settings
			$page[] = array(
				'#type' => 'markup',
				'#markup' => check_plain($config->get('message')),
				'#prefix' => '<h2>',
				'#suffix' => '</h2>',
			);

		}

		
		return $page;
	}

	/**
	 * Allows any user to see their own tickets.
	 * @param \Drupal\vendini\Controller\Request $request
	 * @param \Drupal\vendini\TicketInterface $ticket
	 * @return string ALLOW or DENY.
	 */
	public function accessView(TicketInterface $vendini_ticket) {

		$user = $this->currentUser();
		return $vendini_ticket->getUser() == $user->id() ? AccessInterface::ALLOW : AccessInterface::DENY;
	}


	/**
	 * Returns renderable data to display the ticket info.
	 * @param \Drupal\vendini\Controller\Request $request
	 * @param type $custom_arg
	 */
	public function viewTicket(TicketInterface $vendini_ticket) {

		$page =	array();
		// Node_load()
		$event = $this->entityManager()->getStorageController('node')->load($vendini_ticket->getEvent());
		// user_load()
		$user =	$this->entityManager()->getStorageController('user')->load($vendini_ticket->getUser());
		$page['user'] = array(
			'name' => array(
				'#type' => 'markup',
				'#markup' => $this->t('User: @user', array('@user' => $user->getUsername())),
				'#prefix' => '<h2>',
				'#suffix' => '</h2>',
				'#weight' => -1
			),
			// user_view
			// Can we have $user->view($view_mode) please?
			'entity' => $this->entityManager()->getViewBuilder('user')->view($user)
		);
		// node_view
		$page['event'] = $this->entityManager()->getViewBuilder('node')->view($event, 'teaser');
		$page['ticket_code'] = array(
			'#type' => 'markup',
			'#markup' => $this->t('Ticket code: @code.', array('@code' => $vendini_ticket->uuid())),
			'#weight' => 3
		);
		$page['ticket_admittance'] = array(
			'#type' => 'markup',
			'#markup' => $vendini_ticket->isAdmitted() ? $this->t('The ticket has already been admitted.') : '',
			'#prefix' => '<b>',
			'#suffix' => '</b>',
			'#weight' => 4
		);
		$page['actions'] = array(
			'#type' => 'container',
			'#weight' => 5
		);
		return $page;
	}
}

