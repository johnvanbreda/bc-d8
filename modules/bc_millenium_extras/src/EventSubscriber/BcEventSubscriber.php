<?php

/**
* @file
* Contains \Drupal\my_event_subscriber\EventSubscriber\MyEventSubscriber.
*/

namespace Drupal\bc_millenium_extras\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event Subscriber BcEventSubscriber.
*/
class BcEventSubscriber implements EventSubscriberInterface {

  /**
   * Set header 'Content-Security-Policy' to response to allow embedding in iFrame.
   */
  public function setHeaderContentSecurityPolicy(FilterResponseEvent $event) {
    \Drupal::logger('bc_millenium_extras')->notice('In setHeaderContentSecurityPolicy');
    $response = $event->getResponse();
    $response->headers->remove('X-Frame-Options');
    $response->headers->set('Content-Security-Policy',
        "frame-ancestors 'self' butterfly-conservation.org *.butterfly-conservation.org localhost", FALSE);
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    \Drupal::logger('bc_millenium_extras')->notice('In getSubscribedEvents');
    // Response: set header content security policy
    $events[KernelEvents::RESPONSE][] = ['setHeaderContentSecurityPolicy', -10];
    return $events;
  }

}