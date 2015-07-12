<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Compiler\ServicePass.
 */

namespace Drupal\webprofiler\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class ServicePass
 */
class ServicePass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    if (FALSE === $container->hasDefinition('webprofiler.services')) {
      return;
    }

    $definition = $container->getDefinition('webprofiler.services');
    $graph = $container->getCompiler()->getServiceReferenceGraph();

    $definition->addMethodCall('setServicesGraph', [$this->extractData($graph)]);
  }

  /**
   * @param \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph $graph
   *
   * @return array
   */
  private function extractData(ServiceReferenceGraph $graph) {
    $data = [];
    foreach ($graph->getNodes() as $node) {
      $nodeValue = $node->getValue();
      if ($nodeValue instanceof Definition) {
        $class = $nodeValue->getClass();

        try {
          $reflectedClass = new \ReflectionClass($class);
          $file = $reflectedClass->getFileName();
        } catch (\ReflectionException $e) {
          $file = NULL;
        }

        $id = NULL;
        $tags = $nodeValue->getTags();
        $public = $nodeValue->isPublic();
        $synthetic = $nodeValue->isSynthetic();
      }
      else {
        $id = $nodeValue->__toString();
        $class = NULL;
        $file = NULL;
        $tags = [];
        $public = NULL;
        $synthetic = NULL;
      }

      $inEdges = [];
      /** @var \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphEdge $edge */
      foreach ($node->getInEdges() as $edge) {
        /** @var \Symfony\Component\DependencyInjection\Reference $edgeValue */
        $edgeValue = $edge->getValue();

        $inEdges[] = [
          'id' => $edge->getSourceNode()->getId(),
          'invalidBehavior' => $edgeValue ? $edgeValue->getInvalidBehavior() : NULL,
          'strict' => $edgeValue ? $edgeValue->isStrict() : NULL,
        ];
      }

      $outEdges = [];
      /** @var \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphEdge $edge */
      foreach ($node->getOutEdges() as $edge) {
        /** @var \Symfony\Component\DependencyInjection\Reference $edgeValue */
        $edgeValue = $edge->getValue();

        $outEdges[] = [
          'id' => $edge->getDestNode()->getId(),
          'invalidBehavior' => $edgeValue ? $edgeValue->getInvalidBehavior() : NULL,
          'strict' => $edgeValue ? $edgeValue->isStrict() : NULL,
        ];
      }

      $data[$node->getId()] = [
        'inEdges' => $inEdges,
        'outEdges' => $outEdges,
        'value' => [
          'class' => $class,
          'file' => $file,
          'id' => $id,
          'tags' => $tags,
          'public' => $public,
          'synthetic' => $synthetic,
        ],
      ];
    }

    return $data;
  }
}
