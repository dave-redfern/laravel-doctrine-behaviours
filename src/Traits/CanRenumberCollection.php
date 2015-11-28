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

namespace Somnambulist\Doctrine\Traits;

use Doctrine\Common\Collections\Collection;
use Somnambulist\Doctrine\Contracts\NumericallySortable as NumericallySortableContract;

/**
 * Trait CanRenumberCollection
 *
 * @package    Somnambulist\Doctrine\Traits
 * @subpackage Somnambulist\Doctrine\Traits\CanRenumberCollection
 * @author     Dave Redfern
 */
trait CanRenumberCollection
{

    /**
     * @param Collection $collection
     *
     * @return $this
     */
    public function renumberCollection(Collection $collection)
    {
        $i = 0;

        foreach ( $collection as $item ) {
            if ( $item instanceof NumericallySortableContract ) {
                $item->setOrdinal(++$i);
            }
        }

        return $this;
    }
}