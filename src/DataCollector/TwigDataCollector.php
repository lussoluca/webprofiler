<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TwigDataCollector
 */
class TwigDataCollector extends DataCollector implements DrupalDataCollectorInterface, LateDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  private $profile;
  private $computed;

  /**
   * @param \Twig_Profiler_Profile $profile
   */
  public function __construct(\Twig_Profiler_Profile $profile) {
    $this->profile = $profile;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect() {
    $this->data['profile'] = serialize($this->profile);
  }

  /**
   * @return int
   */
  public function getTime() {
    return $this->getProfile()->getDuration() * 1000;
  }

  /**
   * @return mixed
   */
  public function getTemplateCount() {
    return $this->getComputedData('template_count');
  }

  /**
   * @return mixed
   */
  public function getTemplates() {
    return $this->getComputedData('templates');
  }

  /**
   * @return mixed
   */
  public function getBlockCount() {
    return $this->getComputedData('block_count');
  }

  /**
   * @return mixed
   */
  public function getMacroCount() {
    return $this->getComputedData('macro_count');
  }

  /**
   * @return \Twig_Markup
   */
  public function getHtmlCallGraph() {
    $dumper = new \Twig_Profiler_Dumper_Html();

    return new \Twig_Markup($dumper->dump($this->getProfile()), 'UTF-8');
  }

  /**
   * @return mixed|\Twig_Profiler_Profile
   */
  public function getProfile() {
    if (NULL === $this->profile) {
      $this->profile = unserialize($this->data['profile']);
    }

    return $this->profile;
  }

  /**
   * @param $index
   *
   * @return mixed
   */
  private function getComputedData($index) {
    if (NULL === $this->computed) {
      $this->computed = $this->computeData($this->getProfile());
    }

    return $this->computed[$index];
  }

  /**
   * @param \Twig_Profiler_Profile $profile
   *
   * @return array
   */
  private function computeData(\Twig_Profiler_Profile $profile) {
    $data = [
      'template_count' => 0,
      'block_count' => 0,
      'macro_count' => 0,
    ];

    $templates = [];
    foreach ($profile as $p) {
      $d = $this->computeData($p);

      $data['template_count'] += ($p->isTemplate() ? 1 : 0) + $d['template_count'];
      $data['block_count'] += ($p->isBlock() ? 1 : 0) + $d['block_count'];
      $data['macro_count'] += ($p->isMacro() ? 1 : 0) + $d['macro_count'];

      if ($p->isTemplate()) {
        if (!isset($templates[$p->getTemplate()])) {
          $templates[$p->getTemplate()] = 1;
        }
        else {
          $templates[$p->getTemplate()]++;
        }
      }

      foreach ($d['templates'] as $template => $count) {
        if (!isset($templates[$template])) {
          $templates[$template] = $count;
        }
        else {
          $templates[$template] += $count;
        }
      }
    }
    $data['templates'] = $templates;

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Twig');
  }


  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'twig';
  }

//  /**
//   * {@inheritdoc}
//   */
//  public function getPanel() {
//    $build = array();
//
//    $rows = array(
//      array(
//        $this->t('Total Render Time'),
//        SafeMarkup::format('!time ms',
//          array('!time' => sprintf('%.0f', $this->getTime()))),
//      ),
//      array(
//        $this->t('Template Calls'),
//        $this->getTemplateCount(),
//      ),
//      array(
//        $this->t('Block Calls'),
//        $this->getBlockCount(),
//      ),
//      array(
//        $this->t('Macro Calls'),
//        $this->getMacroCount(),
//      ),
//    );
//
//    $build['stats_title'] = array(
//      '#type' => 'inline_template',
//      '#template' => '<h3>Twig Stats</h3>',
//    );
//
//    $build['stats'] = array(
//      '#type' => 'table',
//      '#rows' => $rows,
//      '#header' => array(
//        $this->t('Config'),
//        $this->t('Value'),
//      ),
//      '#sticky' => TRUE,
//    );
//
//    $build['rendered_title'] = array(
//      '#type' => 'inline_template',
//      '#template' => '<h3>Rendered Templates</h3>',
//    );
//
//    $rows = array();
//    foreach($this->getTemplates() as $template => $count) {
//      $row = array(
//        $template,
//        $count,
//      );
//
//      $rows[] = $row;
//    }
//
//    $build['rendered'] = array(
//      '#type' => 'table',
//      '#rows' => $rows,
//      '#header' => array(
//        $this->t('Template Name'),
//        $this->t('Render Count'),
//      ),
//      '#sticky' => TRUE,
//    );
//
//    $build['rendering_call_title'] = array(
//      '#type' => 'inline_template',
//      '#template' => '<h3>Rendering Call Graph</h3>',
//    );
//
//    $build['rendering_call'] = array(
//      '#type' => 'inline_template',
//      '#template' => '{{ data }}',
//      '#context' => array(
//        'data' => $this->getHtmlCallGraph(),
//      ),
//    );
//
//    return $build;
//  }
}
