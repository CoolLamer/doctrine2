<?php

namespace Doctrine\Tests\ORM\Cache;

use Doctrine\ORM\UnitOfWork;
use Doctrine\Tests\OrmTestCase;
use Doctrine\ORM\Cache\EntityCacheKey;
use Doctrine\ORM\Cache\EntityCacheEntry;
use Doctrine\Tests\Models\Cache\State;
use Doctrine\Tests\Models\Cache\Country;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\DefaultEntityEntryStructure;

/**
 * @group DDC-2183
 */
class EntityEntryStructureTest extends OrmTestCase
{
    /**
     * @var \Doctrine\ORM\Cache\EntityEntryStructure
     */
    private $structure;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    protected function setUp()
    {
        parent::setUp();

        $this->em        = $this->_getTestEntityManager();
        $this->structure = new DefaultEntityEntryStructure($this->em);
    }

    public function testImplementsEntityEntryStructure()
    {
        $this->assertInstanceOf('Doctrine\ORM\Cache\EntityEntryStructure', $this->structure);
    }

    public function testCreateEntity()
    {
        $metadata = $this->em->getClassMetadata(Country::CLASSNAME);
        $key      = new EntityCacheKey($metadata->name, array('id'=>1));
        $entry    = new EntityCacheEntry($metadata->name, array('id'=>1, 'name'=>'Foo'));
        $entity   = $this->structure->loadCacheEntry($metadata, $key, $entry);

        $this->assertInstanceOf($metadata->name, $entity);

        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('Foo', $entity->getName());
        $this->assertEquals(UnitOfWork::STATE_MANAGED, $this->em->getUnitOfWork()->getEntityState($entity));
    }

    public function testLoadProxy()
    {
        $metadata = $this->em->getClassMetadata(Country::CLASSNAME);
        $key      = new EntityCacheKey($metadata->name, array('id'=>1));
        $entry    = new EntityCacheEntry($metadata->name, array('id'=>1, 'name'=>'Foo'));
        $proxy    = $this->em->getReference($metadata->name, $key->identifier);
        $entity   = $this->structure->loadCacheEntry($metadata, $key, $entry, $proxy);

        $this->assertInstanceOf($metadata->name, $entity);
        $this->assertSame($proxy, $entity);

        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('Foo', $entity->getName());
        $this->assertEquals(UnitOfWork::STATE_MANAGED, $this->em->getUnitOfWork()->getEntityState($proxy));
    }

    public function testBuildCacheEntry()
    {
        $entity   = new Country('Foo');
        $uow      = $this->em->getUnitOfWork();
        $data     = array('id'=>1, 'name'=>'Foo');
        $metadata = $this->em->getClassMetadata(Country::CLASSNAME);
        $key      = new EntityCacheKey($metadata->name, array('id'=>1));

        $entity->setId(1);
        $uow->registerManaged($entity, $key->identifier, $data);

        $cache  = $this->structure->buildCacheEntry($metadata, $key, $entity);

        $this->assertInstanceOf('Doctrine\ORM\Cache\CacheEntry', $cache);
        $this->assertInstanceOf('Doctrine\ORM\Cache\EntityCacheEntry', $cache);

        $this->assertArrayHasKey('id', $cache->data);
        $this->assertArrayHasKey('name', $cache->data);
        $this->assertEquals(array(
            'id'   => 1,
            'name' => 'Foo',
        ), $cache->data);
    }

    public function testBuildCacheEntryOwningSide()
    {
        $country        = new Country('Foo');
        $state          = new State('Bat', $country);
        $uow            = $this->em->getUnitOfWork();
        $countryData    = array('id'=>11, 'name'=>'Foo');
        $stateData      = array('id'=>12, 'name'=>'Bar', 'country' => $country);
        $metadata       = $this->em->getClassMetadata(State::CLASSNAME);
        $key            = new EntityCacheKey($metadata->name, array('id'=>11));

        $country->setId(11);
        $state->setId(12);

        $uow->registerManaged($country, array('id'=>11), $countryData);
        $uow->registerManaged($state, array('id'=>12), $stateData);

        $cache = $this->structure->buildCacheEntry($metadata, $key, $state);

        $this->assertInstanceOf('Doctrine\ORM\Cache\CacheEntry', $cache);
        $this->assertInstanceOf('Doctrine\ORM\Cache\EntityCacheEntry', $cache);

        $this->assertArrayHasKey('id', $cache->data);
        $this->assertArrayHasKey('name', $cache->data);
        $this->assertArrayHasKey('country', $cache->data);
        $this->assertEquals(array(
            'id'        => 11,
            'name'      => 'Bar',
            'country'   => array ('id' => 11),
        ), $cache->data);
    }
}