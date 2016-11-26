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

namespace Somnambulist\Doctrine;

/**
 * Class EntityAccessor
 *
 * @package    Somnambulist\Doctrine
 * @subpackage Somnambulist\Doctrine\EntityAccessor
 * @author     Dave Redfern
 */
class EntityAccessor
{

    /**
     * Helper to allow calling protected methods with variable arguments
     *
     * @param object $object
     * @param string $method
     * @param mixed  ...$args
     *
     * @return mixed
     */
    public static function callProtectedMethod($object, $method, ...$args)
    {
        $refObject = new \ReflectionObject($object);
        $refProp   = $refObject->getMethod($method);
        $refProp->setAccessible(true);

        return $refProp->invokeArgs($object, $args);
    }

    /**
     * Helper to allow accessing protected properties without an accessor
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    public static function getProtectedProperty($object, $property)
    {
        $refObject = new \ReflectionObject($object);
        $refProp   = $refObject->getProperty($property);
        $refProp->setAccessible(true);

        return $refProp->getValue($object);
    }

    /**
     * Helper to allow setting protected properties, returns the passed object
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     *
     * @return object
     */
    public static function setProtectedProperty($object, $property, $value)
    {
        $refObject = new \ReflectionObject($object);
        $refProp   = $refObject->getProperty($property);
        $refProp->setAccessible(true);
        $refProp->setValue($object, $value);

        return $object;
    }
}
