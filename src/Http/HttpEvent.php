<?php

namespace Drupal\webprofiler\Http;

/**
 * Class HttpEvent
 */
class HttpEvent {

  /**
   * @var string
   */
  private $url;

  /**
   * @var string
   */
  private $method;

  /**
   * @var string
   */
  private $statusCode;

  /**
   * @var array
   */
  private $requestHeaders;

  /**
   * @var array
   */
  private $responseHeaders;

  /**
   * @var array
   */
  private $transferInfo;

  /**
   * @param string $url
   * @param string $method
   * @param string $statusCode
   * @param array $requestHeaders
   * @param array $responseHeaders
   * @param array $transferInfo
   */
  public function __construct($url, $method, $statusCode, $requestHeaders, $responseHeaders, $transferInfo) {
    $this->url = $url;
    $this->method = $method;
    $this->statusCode = $statusCode;
    $this->requestHeaders = $requestHeaders;
    $this->responseHeaders = $responseHeaders;
    $this->transferInfo = $transferInfo;
  }

  /**
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @return string
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * @return string
   */
  public function getStatusCode() {
    return $this->statusCode;
  }

  /**
   * @return array
   */
  public function getRequestHeaders() {
    return $this->requestHeaders;
  }

  /**
   * @return array
   */
  public function getResponseHeaders() {
    return $this->responseHeaders;
  }

  /**
   * @return array
   */
  public function getTransferInfo() {
    return $this->transferInfo;
  }
}
