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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Cache;

/**
 * Defines entity classes roles to be stored in the cache region.
 *
 * @since   2.5
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class EntityCacheKey implements CacheKey
{
    /**
     * @var array
     */
    public $identifier;

    /**
     * @var string
     */
    public $entityClass;

    /**
     * @var string
     */
    private $hash;

    /**
     * @param string $entityClass
     * @param array  $identifier
     */
    public function __construct($entityClass, array $identifier)
    {
        $this->identifier  = $identifier;
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function hash()
    {
        if ($this->hash === null) {
            ksort($this->identifier);

            return sprintf('%s[%s]', str_replace('\\', '.', strtolower($this->entityClass)) , implode(' ', $this->identifier));
        }

        return $this->hash;
    }
}
