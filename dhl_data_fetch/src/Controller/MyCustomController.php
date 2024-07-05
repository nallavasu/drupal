<?php

namespace Drupal\dhl_data_fetch\Controller;

use Drupal\Core\Controller\ControllerBase;

class MyCustomController extends ControllerBase {
  public function getApiResponse() {
    $yaml = \Drupal::service('tempstore.private')->get('dhl_data_fetch')->get('api_response_yaml');

    if ($yaml) {
      return [
        '#markup' => '<pre>' . htmlspecialchars($yaml) . '</pre>',
      ];
    }
    else {
      return [
        '#markup' => $this->t('No API response found.'),
      ];
    }
  }
}


