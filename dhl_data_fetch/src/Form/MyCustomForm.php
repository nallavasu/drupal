<?php

namespace Drupal\dhl_data_fetch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Class ApiForm.
 */
class MyCustomForm extends FormBase {

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new ApiForm object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_custom_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country'),
      '#required' => TRUE,
    ];
    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
    ];
    $form['pincode'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pincode'),
      '#required' => TRUE,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $country = $form_state->getValue('country');
    $city = $form_state->getValue('city');
    $pincode = $form_state->getValue('pincode');

    // API URL
    $url = 'https://api.dhl.com/location-finder/v1/find-by-address';

    try {
      // Make the API request
      $response = $this->httpClient->request('GET', $url, [
        'query' => [
          'countryCode' => $country,
          'addressLocality' => $city,
          'postalCode' => $pincode,
        ],
        'headers' => [
          'Authorization' => 'Bearer',
          'DHL-API-Key' => 'demo-key',
        ],
      ]);

      $data = json_decode($response->getBody(), TRUE);

      $yaml = \Symfony\Component\Yaml\Yaml::dump($data);
  
      // Store the YAML in a temporary storage and redirect to the response page.
      \Drupal::service('tempstore.private')->get('dhl_data_fetch')->set('api_response_yaml', $yaml);
  
      $form_state->setRedirect('dhl_data_fetch.api_response');


    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('API request failed. Message: @message', ['@message' => $e->getMessage()]));
    }
  }

}

