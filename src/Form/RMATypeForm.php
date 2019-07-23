<?php

namespace Drupal\commerce_rma\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RMATypeForm.
 */
class RMATypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $rma_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $rma_type->label(),
      '#description' => $this->t("Label for the RMA type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $rma_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_rma\Entity\RMAType::load',
      ],
      '#disabled' => !$rma_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $rma_type = $this->entity;
    $status = $rma_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label RMA type.', [
          '%label' => $rma_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label RMA type.', [
          '%label' => $rma_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($rma_type->toUrl('collection'));
  }

}
