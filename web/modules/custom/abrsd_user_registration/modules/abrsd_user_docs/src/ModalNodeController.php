<?php

namespace Drupal\abrsd_user_docs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Response;

class ModalNodeController extends ControllerBase
{
    /**
     * Returns the rendered content of a node.
     *
     * @param \Drupal\node\Entity\Node $node
     *   The node entity to render.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *   The response object containing the rendered content.
     */
    public function content(Node $node)
    {
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
        $content = $view_builder->view($node, 'full');
        $renderer = \Drupal::service('renderer');
        $response = new Response($renderer->render($content));
        $response->headers->set('Content-Type', 'text/html; charset=utf-8');

        return $response;
    }
}
