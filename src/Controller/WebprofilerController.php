<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Controller\WebprofilerController.
 */

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\system\FileDownloadController;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Profiler\ProfilerStorageManager;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\webprofiler\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Routing\RouterInterface;
use Twig_Loader_Filesystem;

/**
 * Class WebprofilerController
 */
class WebprofilerController extends ControllerBase {

  /**
   * @var \Drupal\webprofiler\Profiler\Profiler
   */
  private $profiler;

  /**
   * @var \Symfony\Cmf\Component\Routing\ChainRouter
   */
  private $router;

  /**
   * @var \Drupal\webprofiler\Profiler\TemplateManager
   */
  private $templateManager;

  /**
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  private $date;

  /**
   * @var \Drupal\system\FileDownloadController
   */
  private $fileDownloadController;

  /**
   * @var \Drupal\webprofiler\Profiler\ProfilerStorageManager
   */
  private $profilerDownloadManager;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler'),
      $container->get('router'),
      $container->get('templateManager'),
      $container->get('date.formatter'),
      $container->get('profiler.storage_manager'),
      new FileDownloadController(),
      $container->get('renderer')
    );
  }

  /**
   * Constructs a new WebprofilerController.
   *
   * @param \Drupal\webprofiler\Profiler\Profiler $profiler
   * @param \Symfony\Component\Routing\RouterInterface $router
   * @param \Drupal\webprofiler\Profiler\TemplateManager $templateManager
   * @param \Drupal\Core\Datetime\DateFormatter $date
   * @param \Drupal\webprofiler\Profiler\ProfilerStorageManager $profilerDownloadManager
   * @param \Drupal\system\FileDownloadController $fileDownloadController
   * @param \Drupal\Core\Render\RendererInterface $renderer
   */
  public function __construct(Profiler $profiler, RouterInterface $router, TemplateManager $templateManager, DateFormatter $date, ProfilerStorageManager $profilerDownloadManager, FileDownloadController $fileDownloadController, RendererInterface $renderer) {
    $this->profiler = $profiler;
    $this->router = $router;
    $this->templateManager = $templateManager;
    $this->date = $date;
    $this->fileDownloadController = $fileDownloadController;
    $this->profilerDownloadManager = $profilerDownloadManager;
    $this->renderer = $renderer;
  }

  /**
   * Generates the profile page.
   *
   * @param Profile $profile
   *
   * @return array
   */
  public function profilerAction(Profile $profile) {
    $this->profiler->disable();

    $templateManager = $this->templateManager;
    $templates = $templateManager->getTemplates($profile);

    $childrens = array();
    foreach ($templates as $name => $template) {
      /** @var DrupalDataCollectorInterface $collector */
      $collector = $profile->getCollector($name);

      if ($collector->hasPanel()) {
        $childrens[] = array(
          '#theme' => 'details',
          '#attributes' => array('id' => $name),
          '#title' => $collector->getTitle(),
          '#summary' => 'test',
          '#value' => array(
            '#theme' => 'webprofiler_panel',
            '#name' => $name,
            '#template' => $template,
            '#profile' => $profile,
            '#summary' => $collector->getPanelSummary(),
            '#content' => $collector->getPanel(),
          ),
        );
      }
    }

    $build = array();
    $build['resume'] = array(
      '#theme' => 'webprofiler_resume',
      '#profile' => $profile,
    );

    $build['panels'] = array(
      '#theme' => 'vertical_tabs',
      '#children' => $childrens,
      '#attributes' => array('class' => array('webprofiler')),
      '#attached' => array(
        'drupalSettings' => array(
          'webprofiler' => array(
            'token' => $profile->getToken(),
          ),
        ),
        'library' => array(
          'core/drupal.vertical-tabs',
          'webprofiler/webprofiler',
        ),
      ),
    );

    return $build;
  }

  /**
   * Generates the toolbar.
   *
   * @param Profile $profile
   *
   * @return array
   */
  public function toolbarAction(Profile $profile) {
    $this->profiler->disable();

    $templates = $this->templateManager->getTemplates($profile);

    $toolbar = array(
      '#theme' => 'webprofiler_toolbar',
      '#token' => $profile->getToken(),
      '#templates' => $templates,
      '#profile' => $profile,
    );

    return new Response($this->renderer->render($toolbar));
  }

  /**
   * Generates the list page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function listAction(Request $request) {
    $limit = $request->get('limit', 10);
    $this->profiler->disable();

    $ip = $request->query->get('ip');
    $method = $request->query->get('method');
    $url = $request->query->get('url');

    $profiles = $this->profiler->find($ip, $url, $limit, $method, '', '');

    $rows = array();
    if (count($profiles)) {
      foreach ($profiles as $profile) {
        $row = array();
        $row[] = $this->l($profile['token'], new Url('webprofiler.profiler', array('profile' => $profile['token'])));
        $row[] = $profile['ip'];
        $row[] = $profile['method'];
        $row[] = $profile['url'];
        $row[] = $this->date->format($profile['time']);

        $rows[] = $row;
      }
    }
    else {
      $rows[] = array(
        array(
          'data' => $this->t('No profiles found'),
          'colspan' => 6,
        ),
      );
    }

    $build = array();

    $storage_id = $this->config('webprofiler.config')->get('storage');
    $storage = $this->profilerDownloadManager->getStorage($storage_id);

    $build['resume'] = array(
      '#type' => 'inline_template',
      '#template' => '<p>{{ message }}</p>',
      '#context' => array(
        'message' => $this->t('Profiles stored with %storage service.', array('%storage' => $storage['title'])),
      ),
    );

    $build['filters'] = $this->formBuilder()
      ->getForm('Drupal\\webprofiler\\Form\\ProfilesFilterForm');

    $build['table'] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => array(
        $this->t('Token'),
        array(
          'data' => $this->t('Ip'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        array(
          'data' => $this->t('Method'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        $this->t('Url'),
        array(
          'data' => $this->t('Time'),
          'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
        ),
      ),
      '#sticky' => TRUE,
      '#attached' => array(
        'library' => array(
          'webprofiler/webprofiler',
        ),
      ),
    );

    return $build;
  }
}
