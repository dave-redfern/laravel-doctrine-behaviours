<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
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
class SluggableEventSubscriber implements EventSubscriber
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