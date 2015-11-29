<?php

/*
 * This file is part of the Madagasgar.com website.
 * No distribution, modification permitted.
 *
 * (c) 2009-2015 Dave Redfern <dave@scorpioframework.com>
 */

namespace Somnambulist\Doctrine\EventSubscribers;

use Somnambulist\Doctrine\Contracts\Nameable as NameableContract;
use Somnambulist\Doctrine\Contracts\Sluggable as SluggableContract;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Class SluggableEventSubscriber
 *
 * @package    Somnambulist\Doctrine\EventSubscribers
 * @subpackage Somnambulist\Doctrine\EventSubscribers\SluggableEventSubscriber
 * @author     Dave Redfern
 */
class SluggableEventSubscriber
{

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $this->updateSlug($entity);
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($this->updateSlug($entity)) {
            $em = $eventArgs->getEntityManager();
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
                $em->getClassMetadata(ClassUtils::getClass($entity)),
                $entity
            );
        }
    }

    /**
     * @param SluggableContract $entity
     *
     * @return boolean
     */
    protected function updateSlug($entity)
    {
        if ($entity instanceof SluggableContract && !$entity->getSlug()) {
            if ($entity instanceof NameableContract) {
                $entity->setSlug(str_slug($entity->getName()));

                return true;
            }
        }

        return false;
    }
}