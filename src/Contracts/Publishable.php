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


namespace Somnambulist\Doctrine\Contracts;

use Carbon\Carbon;

/**
 * Interface Publishable
 *
 * @package    Somnambulist\Doctrine\Contracts
 * @subpackage Somnambulist\Doctrine\Contracts\Publishable
 * @author     Dave Redfern
 */
interface Publishable
{

    /**
     * @return boolean
     */
    public function isPublished();

    /**
     * Publish at the date/time supplied, or "now"
     *
     * @param null|Carbon $date
     *
     * @return $this
     */
    public function publishAt(Carbon $date = null);

    /**
     * Remove the publish date
     *
     * @return $this
     */
    public function unPublish();

    /**
     * @return Carbon
     */
    public function getPublishedAt();

    /**
     * @param Carbon $publishedAt
     *
     * @return $this
     */
    public function setPublishedAt(Carbon $publishedAt = null);
}