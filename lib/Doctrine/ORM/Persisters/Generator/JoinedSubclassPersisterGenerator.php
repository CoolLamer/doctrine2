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

namespace Doctrine\ORM\Persisters\Generator;

use Doctrine\ORM\Persisters\JoinedSubclassPersister;

/**
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @since   2.4
 */
class JoinedSubclassPersisterGenerator extends PersisterGenerator
{
    /**
     * @var \Doctrine\ORM\Persisters\JoinedSubclassPersister
     */
    private $persister;

    /**
    * {@inheritdoc}
    */
    public function initialize()
    {
        $this->persister = new JoinedSubclassPersister($this->em, $this->class);

        // initialize rsm
        $this->getInvokeMethod($this->persister, 'getSelectColumnsSQL');
    }

    /**
    * {@inheritdoc}
    */
    protected function generateConstructor()
    {
        $rsm    = $this->getPropertyValue($this->persister, 'rsm');
        $code[] = '$this->rsm = new \Doctrine\ORM\Query\ResultSetMapping();';

        foreach ($rsm as $property => $value) {

            if (is_array($value) && empty($value)) {
                continue;
            }

            $string = var_export($value, true);
            $inline = str_replace(PHP_EOL, '', $string);
            $code[] = sprintf('$this->rsm->%s = %s;', $property, $inline);
        }

        return implode(PHP_EOL . str_repeat(' ', 8), $code);
    }

    /**
    * {@inheritdoc}
    */
    protected function generateProperties()
    {
        $sqlTableAliases  = $this->getPropertyValue($this->persister, 'sqlTableAliases');
        $selectJoinSql    = $this->getPropertyValue($this->persister, 'selectJoinSql');
        $selectColumnList = $this->getPropertyValue($this->persister, 'selectColumnListSql');

        return array(
            'selectJoinSql'         => $selectJoinSql,
            'sqlTableAliases'       => $sqlTableAliases,
            'selectColumnListSql'   => $selectColumnList,
        );
    }

    /**
    * {@inheritdoc}
    */
    protected function generateMethods()
    {
        $getInsertSQL  = $this->getInvokeMethod($this->persister, 'getInsertSQL');
        $getInsertCode = sprintf('return %s;', var_export($getInsertSQL, true));

        return array(
            'getInsertSQL' => $getInsertCode,
        );
    }

    /**
    * {@inheritdoc}
    */
    protected function getParentClass()
    {
        return '\Doctrine\ORM\Persisters\JoinedSubclassPersister';
    }
}