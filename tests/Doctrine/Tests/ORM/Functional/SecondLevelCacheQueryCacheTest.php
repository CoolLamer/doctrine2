<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\Tests\Models\Cache\Country;

/**
 * @group DDC-2183
 */
class SecondLevelCacheQueryCacheTest extends SecondLevelCacheAbstractTest
{
    public function testSelectAll()
    {
        $this->loadFixturesCountries();
        $this->_em->clear();

        $this->cache->containsEntity(Country::CLASSNAME, $this->countries[0]->getId());
        $this->cache->containsEntity(Country::CLASSNAME, $this->countries[1]->getId());

        $dql    = 'SELECT c FROM Doctrine\Tests\Models\Cache\Country c';
        $query1 = $this->_em->createQuery($dql)->setCacheable(true);

        $result1    = $query1->getResult();
        $queryCount = $this->getCurrentQueryCount();

        $this->_em->clear();

        $query2  = $this->_em->createQuery($dql)->setCacheable(true);
        $result2 = $query2->getResult();

        $this->assertEquals($queryCount, $this->getCurrentQueryCount());
        $this->assertCount(count($this->countries), $result2);

        $this->assertInstanceOf('Doctrine\Common\Proxy\Proxy', $result2[0]);
        $this->assertInstanceOf('Doctrine\Common\Proxy\Proxy', $result2[1]);
        $this->assertInstanceOf('Doctrine\Tests\Models\Cache\Country', $result2[0]);
        $this->assertInstanceOf('Doctrine\Tests\Models\Cache\Country', $result2[1]);

        $this->assertEquals($result1[0]->getId(), $result2[0]->getId());
        $this->assertEquals($result1[1]->getId(), $result2[1]->getId());

        $this->assertEquals($result1[0]->getName(), $result2[0]->getName());
        $this->assertEquals($result1[1]->getName(), $result2[1]->getName());
    }
}