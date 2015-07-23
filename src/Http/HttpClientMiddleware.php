<?php

namespace Drupal\webprofiler\Http;

use Psr\Http\Message\RequestInterface;

/**
 * Class HttpClientMiddleware
 */
class HttpClientMiddleware {

  /**
   * @var array
   */
  private $completedRequests;

  /**
   * @var array
   */
  private $failedRequests;

  /**
   *
   */
  public function __construct() {
    $this->completedRequests = [];
    $this->failedRequests = [];
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke() {
    return function ($handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        return $handler($request, $options)->then(
          function ($response) use ($request) {

            $this->completedRequests[] = [
              'request' => $request,
              'response' => $response,
            ];

            return $response;
          },
          function ($reason) use ($request) {

            $this->failedRequests[] = [
              'request' => $request,
            ];

            return \GuzzleHttp\Promise\rejection_for($reason);
          }
        );
      };
    };
  }

  /**
   * @return array
   */
  public function getCompletedRequests() {
    return $this->completedRequests;
  }

  /**
   * @return array
   */
  public function getFailedRequests() {
    return $this->failedRequests;
  }
}
