<?php

namespace Drupal\commerce_rma\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for RMA item entities.
 */
class RMAItemViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
