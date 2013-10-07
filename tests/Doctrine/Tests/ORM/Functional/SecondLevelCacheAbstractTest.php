<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\Tests\OrmFunctionalTestCase;

use Doctrine\Tests\Models\Cache\Country;
use Doctrine\Tests\Models\Cache\State;
use Doctrine\Tests\Models\Cache\City;

use Doctrine\Tests\Models\Cache\Traveler;
use Doctrine\Tests\Models\Cache\Travel;

use Doctrine\Tests\Models\Cache\Restaurant;
use Doctrine\Tests\Models\Cache\Beach;
use Doctrine\Tests\Models\Cache\Bar;

use Doctrine\Tests\Models\Cache\AttractionContactInfo;
use Doctrine\Tests\Models\Cache\AttractionLocationInfo;

/**
 * @group DDC-2183
 */
abstract class SecondLevelCacheAbstractTest extends OrmFunctionalTestCase
{
    protected $countries        = array();
    protected $states           = array();
    protected $cities           = array();
    protected $travels          = array();
    protected $travelers        = array();
    protected $attractions      = array();
    protected $attractionsInfo  = array();

    /**
     * @var \Doctrine\ORM\Cache
     */
    protected $cache;

    protected function setUp()
    {
        $this->enableSecondLevelCache();

        $this->useModelSet('cache');

        parent::setUp();

        $this->cache = $this->_em->getCache();
    }

    protected function loadFixturesCountries()
    {
        $brazil  = new Country("Brazil");
        $germany = new Country("Germany");

        $this->countries[] = $brazil;
        $this->countries[] = $germany;

        $this->_em->persist($brazil);
        $this->_em->persist($germany);
        $this->_em->flush();
    }

    protected function loadFixturesStates()
    {
        $saopaulo   = new State("São Paulo", $this->countries[0]);
        $rio        = new State("Rio de janeiro", $this->countries[0]);
        $berlin     = new State("Berlin", $this->countries[1]);
        $bavaria    = new State("Bavaria", $this->countries[1]);

        $this->states[] = $saopaulo;
        $this->states[] = $rio;
        $this->states[] = $bavaria;
        $this->states[] = $berlin;

        $this->_em->persist($saopaulo);
        $this->_em->persist($rio);
        $this->_em->persist($bavaria);
        $this->_em->persist($berlin);

        $this->_em->flush();
    }

    protected function loadFixturesCities()
    {
        $saopaulo   = new City("São Paulo", $this->states[0]);
        $rio        = new City("Rio de janeiro", $this->states[0]);
        $berlin     = new City("Berlin", $this->states[1]);
        $munich     = new City("Munich", $this->states[1]);

        $this->states[0]->addCity($saopaulo);
        $this->states[0]->addCity($rio);
        $this->states[1]->addCity($berlin);
        $this->states[1]->addCity($berlin);

        $this->cities[] = $saopaulo;
        $this->cities[] = $rio;
        $this->cities[] = $munich;
        $this->cities[] = $berlin;

        $this->_em->persist($saopaulo);
        $this->_em->persist($rio);
        $this->_em->persist($munich);
        $this->_em->persist($berlin);

        $this->_em->flush();
    }

    protected function loadFixturesTraveler()
    {
        $t1   = new Traveler("Fabio Silva");
        $t2   = new Traveler("Doctrine Bot");

        $this->_em->persist($t1);
        $this->_em->persist($t2);

        $this->travelers[] = $t1;
        $this->travelers[] = $t2;

        $this->_em->flush();
    }

    protected function loadFixturesTravels()
    {
        $t1   = new Travel($this->travelers[0]);
        $t2   = new Travel($this->travelers[1]);

        $t1->addVisitedCity($this->cities[0]);
        $t1->addVisitedCity($this->cities[1]);
        $t1->addVisitedCity($this->cities[2]);

        $t2->addVisitedCity($this->cities[1]);
        $t2->addVisitedCity($this->cities[3]);

        $this->_em->persist($t1);
        $this->_em->persist($t2);

        $this->travels[] = $t1;
        $this->travels[] = $t2;

        $this->_em->flush();
    }

    protected function loadFixturesAttractions()
    {
        $this->attractions[] = new Bar('Boteco São Bento', $this->cities[0]);
        $this->attractions[] = new Bar('Prainha Paulista', $this->cities[0]);
        $this->attractions[] = new Beach('Copacabana', $this->cities[1]);
        $this->attractions[] = new Beach('Ipanema', $this->cities[1]);
        $this->attractions[] = new Bar('Schneider Weisse', $this->cities[2]);
        $this->attractions[] = new Restaurant('Reinstoff', $this->cities[3]);
        $this->attractions[] = new Restaurant('Fischers Fritz', $this->cities[3]);

        $this->cities[0]->addAttraction($this->attractions[0]);
        $this->cities[0]->addAttraction($this->attractions[1]);
        $this->cities[1]->addAttraction($this->attractions[2]);
        $this->cities[1]->addAttraction($this->attractions[3]);
        $this->cities[2]->addAttraction($this->attractions[4]);
        $this->cities[3]->addAttraction($this->attractions[5]);
        $this->cities[3]->addAttraction($this->attractions[6]);

        foreach ($this->attractions as $attraction) {
            $this->_em->persist($attraction);
        }

        $this->_em->flush();
    }

    protected function loadFixturesAttractionsInfo()
    {
        $this->attractionsInfo[] = new AttractionContactInfo('0000-0000', $this->attractions[0]);
        $this->attractionsInfo[] = new AttractionContactInfo('1111-1111', $this->attractions[1]);
        $this->attractionsInfo[] = new AttractionLocationInfo('Some St 1', $this->attractions[2]);
        $this->attractionsInfo[] = new AttractionLocationInfo('Some St 2', $this->attractions[3]);

        foreach ($this->attractionsInfo as $info) {
            $this->_em->persist($info);
        }

        $this->_em->flush();
    }

    protected function getEntityRegion($className)
    {
        return $this->cache->getEntityCacheRegion($className)->getName();
    }

    protected function getCollectionRegion($className, $association)
    {
        return $this->cache->getCollectionCacheRegion($className, $association)->getName();
    }

    protected function getDefaultQueryRegionName()
    {
        return $this->cache->getQueryCache()->getRegion()->getName();
    }

    protected function evictRegions()
    {
        $this->cache->evictQueryRegions();
        $this->cache->evictEntityRegions();
        $this->cache->evictCollectionRegions();
    }
}