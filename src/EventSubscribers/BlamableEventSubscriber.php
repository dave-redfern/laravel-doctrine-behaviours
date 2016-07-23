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

use Illuminate\Contracts\Auth\Guard;
use Somnambulist\Doctrine\Contracts;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Somnambulist\Doctrine\Contracts\UniversallyIdentifiable;

/**
 * Class BlamableEventSubscriber
 *
 * @package    Somnambulist\Doctrine\EventSubscribers
 * @subpackage Somnambulist\Doctrine\EventSubscribers\BlamableEventSubscriber
 * @author     Dave Redfern
 */
class BlamableEventSubscriber implements EventSubscriber
{

    /**
     * @var Guard
     */
    protected $guard;



    /**
     * Constructor.
     *
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

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
        if ($entity instanceof Contracts\Blamable) {
            $user = $this->getUpdateByNameFromUser($eventArgs);

            $entity->setCreatedBy($user);
            $entity->setUpdatedBy($user);
        }
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Contracts\Blamable) {
            $entity->setUpdatedBy($this->getUpdateByNameFromUser($eventArgs));

            $em = $eventArgs->getEntityManager();
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
                $em->getClassMetadata(ClassUtils::getClass($entity)), $entity
            );
        }
    }



    /**
     * @return boolean
     */
    protected function hasCurrentUser()
    {
        return (null !== $this->guard->user());
    }

    /**
     * @return string
     */
    protected function getFallbackUser()
    {
        return env('DOCTRINE_BLAMABLE_DEFAULT_USER', 'system');
    }

    /**
     * @return null|AuthenticatableContract
     */
    protected function currentUser()
    {
        return $this->guard->user();
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @return string
     */
    protected function getUpdateByNameFromUser(LifecycleEventArgs $eventArgs)
    {
        if (!$this->hasCurrentUser()) {
            return $this->getFallbackUser();
        }

        if (null !== $name = $this->getNameFromUser($this->currentUser())) {
            return $name;
        }

        $identifier = 'id';
        $user       = $this->currentUser();
        $repo       = $eventArgs->getEntityManager()->getRepository($this->getUserClass());

        // did not implement standard methods, try and look them up using identifier
        if ($user instanceof AuthenticatableContract) {
            $identifier = $user->getAuthIdentifierName();
        }

        if (null !== $user = $repo->findOneBy([$identifier => $user->getAuthIdentifier()])) {
            if (null !== $name = $this->getNameFromUser($user)) {
                return $name;
            }
        }

        // fall back completely
        return $this->getFallbackUser();
    }

    /**
     * @return string
     */
    protected function getUserClass()
    {
        return ClassUtils::getClass($this->currentUser());
    }

    /**
     * Attempts to get a "name" from the user in the following order:
     *
     *  * getUuid
     *  * getUsername
     *  * getEmail
     *  * getId
     *
     * @param object $user
     *
     * @return null|string
     */
    protected function getNameFromUser($user)
    {
        switch (true) {
            // favour (potentially) unchanging user credentials
            case $user instanceof Contracts\UniversallyIdentifiable:
                return $user->getUuid();

            case $user instanceof AuthenticatableContract:
                return $user->getAuthIdentifier();

            case $user instanceof Contracts\Identifiable:
                return $user->getId();

            case $user instanceof Contracts\Nameable:
                return $user->getName();

            default:
                return null;
        }
    }
}
