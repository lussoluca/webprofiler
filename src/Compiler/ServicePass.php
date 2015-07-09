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

    $definition->addMethodCall('setServicesGraph', array($this->extractData($graph)));
  }

  /**
   * @param \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph $graph
   *
   * @return array
   */
  private function extractData(ServiceReferenceGraph $graph) {
    $data = array();
    foreach($graph->getNodes() as $node) {
      $nodeValue = $node->getValue();
      if($nodeValue instanceof Definition) {
        $class = $nodeValue->getClass();
        $id = NULL;
        $tags = $nodeValue->getTags();
        $public = $nodeValue->isPublic();
        $synthetic = $nodeValue->isSynthetic();
      } else {
        $id = $nodeValue->__toString();
        $class = NULL;
        $tags = array();
        $public = NULL;
        $synthetic = NULL;
      }

      $inEdges = array();
      /** @var \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphEdge $edge */
      foreach($node->getInEdges() as $edge) {
        /** @var \Symfony\Component\DependencyInjection\Reference $edgeValue */
        $edgeValue = $edge->getValue();

        $inEdges[] = array(
          'id' => $edge->getSourceNode()->getId(),
          'invalidBehavior' => $edgeValue ? $edgeValue->getInvalidBehavior() : NULL,
          'strict' => $edgeValue ? $edgeValue->isStrict() : NULL,
        );
      }

      $outEdges = array();
      /** @var \Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphEdge $edge */
      foreach($node->getOutEdges() as $edge) {
        /** @var \Symfony\Component\DependencyInjection\Reference $edgeValue */
        $edgeValue = $edge->getValue();

        $outEdges[] = array(
          'id' => $edge->getDestNode()->getId(),
          'invalidBehavior' => $edgeValue ? $edgeValue->getInvalidBehavior() : NULL,
          'strict' => $edgeValue ? $edgeValue->isStrict() : NULL,
        );
      }

      $data[$node->getId()] = array(
        'inEdges' => $inEdges,
        'outEdges' => $outEdges,
        'value' => array(
          'class' => $class,
          'id' => $id,
          'tags' => $tags,
          'public' => $public,
          'synthetic' => $synthetic,
        ),
      );
    }

    return $data;
  }
}
