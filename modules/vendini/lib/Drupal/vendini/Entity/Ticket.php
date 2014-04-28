<?php

namespace Drupal\vendini\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\vendini\TicketInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * The Vendini Ticket entity.
 *
 * @ContentEntityType(
 * 	id = "vendini_ticket",
 * 	label = @Translation("Ticket"),
 * 	controllers = {
 * 	   "storage" = "Drupal\Core\Entity\FieldableDatabaseStorageController",
 *     "form" = {
 *       "delete" = "Drupal\vendini\Form\DeleteTicketForm",
 *       "admit" = "Drupal\vendini\Form\AdmitTicketForm"
 *     },
 *   },
 *   base_table = "ticket",
 *   fieldable = FALSE,
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "tid",
 *     "uuid" = "uuid"
 *   },
 * )
 */

class Ticket extends ContentEntityBase implements TicketInterface {

	/**
	 * {@inheritdoc}
	 */
	public function id() {
		
		/* Per contentEntityBase this defaults to $this->id->value.
		 * If you don't redefine it, pretty much nothing is going to work.
		 */
		return $this->get('tid')->value;
	}

	/**
	 * Definition of the base fields of a ticket.
	 * @param Drupal\Core\Entity\EntityTypeInterface $entity_type
	 * @return array An array of entity field definitions
	 */
	public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

		$fields['tid'] = FieldDefinition::create('integer')
				->setLabel(t('Ticket ID'))
				->setDescription(t('The ticket\'s unique ID'))
				->setReadOnly(true);

		$fields['uuid'] = FieldDefinition::create('uuid')
				->setLabel(t('Ticket UUID'))
				->setDescription(t('The ticket\'s unique UUID'))
				->setReadOnly(true);

		$fields['event'] = FieldDefinition::create('entity_reference')
				->setLabel(t('Event'))
				->setDescription(t('The Event a ticket grants access to.'))
				->setRequired(true)
				->setSettings(array(
			'target_type' => 'node',
			'bundle' => 'event')
		);

		$fields['user'] = FieldDefinition::create('entity_reference')
				->setLabel(t('User'))
				->setDescription(t('The owner of this ticket.'))
				->setRequired(true)
				->setSettings(array(
			'target_type' => 'user',
			'bundle' => 'user')
		);

		$fields['is_admitted'] = FieldDefinition::create('boolean')
				->setLabel(t('Admittance state'))
				->setDescription(t('The admittance state of the ticket.'))
				->setSetting('default', 0);

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEvent() {

		return $this->get('event')->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUser() {

		return $this->get('user')->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAdmitted() {

		return $this->get('is_admitted')->value == 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setAdmitted($admittance) {

		return $this->get('is_admitted')->value = (int)$admittance;
	}
}
