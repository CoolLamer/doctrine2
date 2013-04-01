<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\Tests\Models\Cache\City;
use Doctrine\Tests\Models\Cache\State;
use Doctrine\Tests\Models\Cache\Travel;
use Doctrine\Tests\Models\Cache\Traveler;

/**
 * @group DDC-2183
 */
class SecondLevelCacheManyToManyTest extends SecondLevelCacheAbstractTest
{
    public function testPutOnPersist()
    {
        $this->loadFixturesCountries();
        $this->loadFixturesStates();
        $this->loadFixturesCities();
        $this->loadFixturesTraveler();
        $this->loadFixturesTravels();
        $this->_em->clear();

        $this->assertTrue($this->cache->containsEntity(Travel::CLASSNAME, $this->travels[0]->getId()));
        $this->assertTrue($this->cache->containsEntity(Travel::CLASSNAME, $this->travels[1]->getId()));

        $this->assertTrue($this->cache->containsCollection(Travel::CLASSNAME, 'visitedCities', $this->travels[0]->getId()));
        $this->assertTrue($this->cache->containsCollection(Travel::CLASSNAME, 'visitedCities', $this->travels[1]->getId()));

        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[0]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[1]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[2]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[3]->getId()));
    }

    public function testPutAndLoadManyToManyRelation()
    {
        $this->loadFixturesCountries();
        $this->loadFixturesStates();
        $this->loadFixturesCities();
        $this->loadFixturesTraveler();
        $this->loadFixturesTravels();

        $this->_em->clear();

        $this->cache->evictEntityRegion(Travel::CLASSNAME);
        $this->cache->evictEntityRegion(City::CLASSNAME);
        $this->cache->evictCollectionRegion(Travel::CLASSNAME, 'visitedCities');

        $this->assertFalse($this->cache->containsEntity(Travel::CLASSNAME, $this->travels[0]->getId()));
        $this->assertFalse($this->cache->containsEntity(Travel::CLASSNAME, $this->travels[1]->getId()));

        $this->assertFalse($this->cache->containsCollection(Travel::CLASSNAME, 'visitedCities', $this->travels[0]->getId()));
        $this->assertFalse($this->cache->containsCollection(Travel::CLASSNAME, 'visitedCities', $this->travels[1]->getId()));

        $this->assertFalse($this->cache->containsEntity(City::CLASSNAME, $this->cities[0]->getId()));
        $this->assertFalse($this->cache->containsEntity(City::CLASSNAME, $this->cities[1]->getId()));
        $this->assertFalse($this->cache->containsEntity(City::CLASSNAME, $this->cities[2]->getId()));
        $this->assertFalse($this->cache->containsEntity(City::CLASSNAME, $this->cities[3]->getId()));

        $t1 = $this->_em->find(Travel::CLASSNAME, $this->travels[0]->getId());
        $t2 = $this->_em->find(Travel::CLASSNAME, $this->travels[1]->getId());

        //trigger lazy load
        $this->assertCount(3, $t1->getVisitedCities());
        $this->assertCount(2, $t2->getVisitedCities());

        $this->assertInstanceOf(City::CLASSNAME, $t1->getVisitedCities()->get(0));
        $this->assertInstanceOf(City::CLASSNAME, $t1->getVisitedCities()->get(1));
        $this->assertInstanceOf(City::CLASSNAME, $t1->getVisitedCities()->get(2));

        $this->assertInstanceOf(City::CLASSNAME, $t2->getVisitedCities()->get(0));
        $this->assertInstanceOf(City::CLASSNAME, $t2->getVisitedCities()->get(1));

        $this->assertTrue($this->cache->containsEntity(Travel::CLASSNAME, $this->travels[0]->getId()));
        $this->assertTrue($this->cache->containsEntity(Travel::CLASSNAME, $this->travels[1]->getId()));

        $this->assertTrue($this->cache->containsCollection(Travel::CLASSNAME, 'visitedCities', $this->travels[0]->getId()));
        $this->assertTrue($this->cache->containsCollection(Travel::CLASSNAME, 'visitedCities', $this->travels[1]->getId()));

        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[0]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[1]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[2]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[3]->getId()));

        $this->_em->clear();

        $queryCount = $this->getCurrentQueryCount();

        $t3 = $this->_em->find(Travel::CLASSNAME, $this->travels[0]->getId());
        $t4 = $this->_em->find(Travel::CLASSNAME, $this->travels[1]->getId());

        //trigger lazy load from cache
        $this->assertCount(3, $t3->getVisitedCities());
        $this->assertCount(2, $t4->getVisitedCities());

        $this->assertInstanceOf(City::CLASSNAME, $t3->getVisitedCities()->get(0));
        $this->assertInstanceOf(City::CLASSNAME, $t3->getVisitedCities()->get(1));
        $this->assertInstanceOf(City::CLASSNAME, $t3->getVisitedCities()->get(2));

        $this->assertInstanceOf(City::CLASSNAME, $t4->getVisitedCities()->get(0));
        $this->assertInstanceOf(City::CLASSNAME, $t4->getVisitedCities()->get(1));

        $this->assertNotSame($t1->getVisitedCities()->get(0), $t3->getVisitedCities()->get(0));
        $this->assertEquals($t1->getVisitedCities()->get(0)->getId(), $t3->getVisitedCities()->get(0)->getId());
        $this->assertEquals($t1->getVisitedCities()->get(0)->getName(), $t3->getVisitedCities()->get(0)->getName());

        $this->assertNotSame($t1->getVisitedCities()->get(1), $t3->getVisitedCities()->get(1));
        $this->assertEquals($t1->getVisitedCities()->get(1)->getId(), $t3->getVisitedCities()->get(1)->getId());
        $this->assertEquals($t1->getVisitedCities()->get(1)->getName(), $t3->getVisitedCities()->get(1)->getName());

        $this->assertNotSame($t1->getVisitedCities()->get(2), $t3->getVisitedCities()->get(2));
        $this->assertEquals($t1->getVisitedCities()->get(2)->getId(), $t3->getVisitedCities()->get(2)->getId());
        $this->assertEquals($t1->getVisitedCities()->get(2)->getName(), $t3->getVisitedCities()->get(2)->getName());

        $this->assertNotSame($t2->getVisitedCities()->get(0), $t4->getVisitedCities()->get(0));
        $this->assertEquals($t2->getVisitedCities()->get(0)->getId(), $t4->getVisitedCities()->get(0)->getId());
        $this->assertEquals($t2->getVisitedCities()->get(0)->getName(), $t4->getVisitedCities()->get(0)->getName());

        $this->assertNotSame($t2->getVisitedCities()->get(1), $t4->getVisitedCities()->get(1));
        $this->assertEquals($t2->getVisitedCities()->get(1)->getId(), $t4->getVisitedCities()->get(1)->getId());
        $this->assertEquals($t2->getVisitedCities()->get(1)->getName(), $t4->getVisitedCities()->get(1)->getName());

        $this->assertEquals($queryCount, $this->getCurrentQueryCount());
    }

    public function testStoreManyToManyAssociationWhitCascade()
    {
        $this->loadFixturesCountries();
        $this->loadFixturesStates();
        $this->loadFixturesCities();

        $this->cache->evictEntityRegion(City::CLASSNAME);
        $this->cache->evictEntityRegion(Traveler::CLASSNAME);
        $this->cache->evictEntityRegion(Travel::CLASSNAME);
        $this->cache->evictCollectionRegion(State::CLASSNAME, 'cities');
        $this->cache->evictCollectionRegion(Traveler::CLASSNAME, 'travels');

        $traveler   = new Traveler('Doctrine Bot');
        $travel     = new Travel($traveler);

        $travel->addVisitedCity($this->cities[0]);
        $travel->addVisitedCity($this->cities[1]);
        $travel->addVisitedCity($this->cities[3]);

        $this->_em->persist($traveler);
        $this->_em->persist($travel);
        $this->_em->flush();
        $this->_em->clear();

        $this->assertTrue($this->cache->containsEntity(Traveler::CLASSNAME, $travel->getId()));
        $this->assertTrue($this->cache->containsEntity(Traveler::CLASSNAME, $traveler->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[0]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[1]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[3]->getId()));
        $this->assertTrue($this->cache->containsCollection(Travel::CLASSNAME, 'visitedCities', $travel->getId()));

        $queryCount1 = $this->getCurrentQueryCount();
        $t1          = $this->_em->find(Travel::CLASSNAME, $travel->getId());

        $this->assertInstanceOf(Travel::CLASSNAME, $t1);
        $this->assertCount(3, $t1->getVisitedCities());
        $this->assertEquals($queryCount1, $this->getCurrentQueryCount());

        $t1->removeVisitedCity($t1->getVisitedCities()->get(1));

        $this->_em->persist($t1);
        $this->_em->flush();
        $this->_em->clear();

        $this->assertTrue($this->cache->containsEntity(Traveler::CLASSNAME, $travel->getId()));
        $this->assertTrue($this->cache->containsEntity(Traveler::CLASSNAME, $traveler->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[0]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[1]->getId()));
        $this->assertTrue($this->cache->containsEntity(City::CLASSNAME, $this->cities[3]->getId()));
        $this->assertTrue($this->cache->containsCollection(Travel::CLASSNAME, 'visitedCities', $travel->getId()));

        $queryCount2 = $this->getCurrentQueryCount();
        $t2          = $this->_em->find(Travel::CLASSNAME, $travel->getId());

        $this->assertInstanceOf(Travel::CLASSNAME, $t1);
        $this->assertCount(2, $t2->getVisitedCities());
        $this->assertEquals($queryCount2, $this->getCurrentQueryCount());
    }
}