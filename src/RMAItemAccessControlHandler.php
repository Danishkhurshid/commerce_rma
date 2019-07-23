<?php

namespace Drupal\commerce_rma;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the RMA item entity.
 *
 * @see \Drupal\commerce_rma\Entity\RMAItem.
 */
class RMAItemAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_rma\Entity\RMAItemInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished rma item entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published rma item entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit rma item entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete rma item entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add rma item entities');
  }

}
