<?php

namespace Drupal\vendini\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



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
}
