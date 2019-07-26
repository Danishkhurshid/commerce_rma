<?php

namespace Drupal\commerce_rma\Entity;

use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
//use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\commerce_price\Price;

/**
 * Defines the RMA item entity.
 *
 * @ingroup commerce_rma
 *
 * @ContentEntityType(
 *   id = "commerce_rma_item",
 *   label = @Translation("RMA item"),
 *   bundle_label = @Translation("RMA item type"),
 *   handlers = {
 *     "storage" = "Drupal\commerce_rma\RMAItemStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_rma\RMAItemListBuilder",
 *     "views_data" = "Drupal\commerce_rma\Entity\RMAItemViewsData",
 *     "translation" = "Drupal\commerce_rma\RMAItemTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\commerce_rma\Form\RMAItemForm",
 *       "add" = "Drupal\commerce_rma\Form\RMAItemForm",
 *       "edit" = "Drupal\commerce_rma\Form\RMAItemForm",
 *       "delete" = "Drupal\commerce_rma\Form\RMAItemDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_rma\RMAItemHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\commerce_rma\RMAItemAccessControlHandler",
 *   },
 *   base_table = "commerce_rma_item",
 *   data_table = "commerce_rma_item_field_data",
 *   revision_table = "commerce_rma_item_revision",
 *   revision_data_table = "commerce_rma_item_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer commerce_rma_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/rma_item/{commerce_rma_item}",
 *     "add-page" = "/admin/commerce/rma_item/add",
 *     "add-form" = "/admin/commerce/rma_item/add/{commerce_rma_item_type}",
 *     "edit-form" = "/admin/commerce/rma_item/{commerce_rma_item}/edit",
 *     "delete-form" = "/admin/commerce/rma_item/{commerce_rma_item}/delete",
 *     "collection" = "/admin/commerce/rma_item",
 *   },
 *   bundle_entity_type = "commerce_rma_item_type",
 *   field_ui_base_route = "entity.commerce_rma_item_type.edit_form"
 * )
 */
class RMAItem extends CommerceContentEntityBase implements RMAItemInterface {

  use EntityChangedTrait;

  /**
   * The purchasable entity type ID.
   *
   * @var \Drupal\commerce_order\Entity\OrderItem
   */
  protected $order_item;

  /**
   * {@inheritdoc}
   */
  public function getOrderItem() {
    return $this->order_item;
  }

  /**
   * {@inheritdoc}
   */
  public function setOrderItem($order_item) {
    $this->set('order_item', $order_item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    if (!$this->get('amount')->isEmpty()) {
      return $this->get('amount')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setAmount(Price $amount) {
    $this->set('amount', $amount);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->get('state')->first();
  }

  /**
   * {@inheritdoc}
   */
  public function getItem() {
    return $this->get('item')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function setItem($item) {
    $this->set('item', $item);
//    $this->recalculateTotalPrice();
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the RMA item entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the RMA item entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['amount'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Amount'))
      ->setDescription(t('The amount for return.'))
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['quantity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Quantity'))
      ->setDescription(t('The quantity for return.'))
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['state'] = BaseFieldDefinition::create('state')
      ->setLabel(t('State'))
      ->setDescription(t('The RMA state.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'state_transition_form',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setSetting('workflow_callback', ['\Drupal\commerce_rma\Entity\RMAItem', 'getWorkflowId']);


//    $fields['order_id'] = BaseFieldDefinition::create('entity_reference')
//      ->setLabel(t('Order'))
//      ->setDescription(t('The parent order.'))
//      ->setSetting('target_type', 'commerce_order')
//      ->setReadOnly(TRUE);

//    $fields['item'] = BaseFieldDefinition::create('commerce_order_item')
//      ->setLabel(t('Order item'))
//      ->setRequired(TRUE)
//      ->setDisplayConfigurable('form', TRUE)
//      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * Gets the workflow ID for the state field.
   *
   * @param \Drupal\commerce_rma\Entity\RMAItemInterface $rma_item
   *   The RMA Item
   *
   * @return string
   *   The workflow ID.
   */
  public static function getWorkflowId(RMAItemInterface $rma_item) {
    $workflow = RMAItemType::load($rma_item->bundle())->getWorkflowId();
    return $workflow;
  }

}
