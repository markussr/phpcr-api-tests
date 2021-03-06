<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Writing;

use PHPCR\PropertyInterface;
use PHPCR\PropertyType;

/**
 * Testing whether the property correctly handles all types.
 *
 * For every test we do the assertions twice:
 *   - Once after the property has been set in memory
 *   - Once after renewing the session and reading the property from the backend
 *
 * Covering jcr-2.8.3 spec $10.4.2
 */
class SetPropertyTypesTest extends \PHPCR\Test\BaseCase
{
    /** @var PropertyInterface */
    private $property;

    public function setUp()
    {
        parent::setUp();

        $this->renewSession();
        $this->node = $this->session->getNode('/tests_general_base/numberPropertyNode/jcr:content');
        $this->property = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/longNumber');
    }

    //TODO: have this for all types in PropertyType and each with and without the explicit type parameter. also test node->getPropertyValue for correct type

    public function testCreateString()
    {
        $value = $this->node->setProperty('propString', '10.6 test');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertSame('10.6 test', $value->getString());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propString');
        $this->assertSame('10.6 test', $value->getString());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());
    }

    public function testCreateValueBinary()
    {
        $bin = $this->node->setProperty('newBinary', 'foobar', \PHPCR\PropertyType::BINARY);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $bin);
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());
        $this->assertEquals('foobar', stream_get_contents($bin->getBinary()));

        $this->saveAndRenewSession();
        $bin = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/newBinary');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());
        $this->assertEquals('foobar', stream_get_contents($bin->getBinary()));
    }

    public function testCreateValueBinaryFromStream()
    {
        $stream = fopen('php://memory', 'w+');
        fwrite($stream, 'foo bar');
        rewind($stream);
        $bin = $this->node->setProperty('newBinaryStream', $stream, \PHPCR\PropertyType::BINARY);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $bin);
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());

        $oldSession = $this->session;
        $this->saveAndRenewSession(); // either this
        $oldSession->logout(); // or this should close the stream
        $this->assertFalse(is_resource($stream), 'The responsibility for the stream goes into phpcr who must close it');

        $bin = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/newBinaryStream');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());
        $this->assertEquals('foo bar', stream_get_contents($bin->getBinary()));
    }

    public function testCreateValueBinaryFromStreamAndRead()
    {
        $stream = fopen('php://memory', 'w+');
        fwrite($stream, 'foo bar');
        rewind($stream);
        $bin = $this->node->setProperty('newBinaryStream', $stream, \PHPCR\PropertyType::BINARY);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $bin);
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());
        $this->assertEquals('foo bar', stream_get_contents($bin->getBinary()));

        $oldSession = $this->session;
        $this->saveAndRenewSession(); // either this
        $oldSession->logout(); // or this should close the stream
        $this->assertFalse(is_resource($stream), 'The responsibility for the stream goes into phpcr who must close it');

        $bin = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/newBinaryStream');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());
        $this->assertEquals('foo bar', stream_get_contents($bin->getBinary()));
    }

    public function testCreateValueInt()
    {
        $value = $this->node->setProperty('propInt', 100);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertSame('100', $value->getString());
        $this->assertSame(100, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propInt');
        $this->assertSame('100', $value->getString());
        $this->assertSame(100, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $value->getType());
    }

    /**
     * Test that explicitly setting the type overrides autodetection.
     */
    public function testCreateValueIntWithDouble()
    {
        $value = $this->node->setProperty('propIntNum', 100.3, PropertyType::LONG);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertSame('100', $value->getString());
        $this->assertSame(100, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propIntNum');
        $this->assertSame('100', $value->getString());
        $this->assertSame(100, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $value->getType());
    }

    public function testCreateValueDouble()
    {
        $value = $this->node->setProperty('propDouble', 10.6);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertSame('10.6', $value->getString());
        $this->assertSame(10.6, $value->getDouble());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::DOUBLE, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propDouble');
        $this->assertSame('10.6', $value->getString());
        $this->assertSame(10.6, $value->getDouble());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::DOUBLE, $value->getType());
    }

    /**
     * Test that explicitly setting the type overrides autodetection.
     */
    public function testCreateValueDoubleWithInt()
    {
        $value = $this->node->setProperty('propDoubleNum', 10, PropertyType::DOUBLE);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertSame('10', $value->getString());
        $this->assertSame(10.0, $value->getDouble());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::DOUBLE, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propDoubleNum');
        $this->assertSame('10', $value->getString());
        $this->assertSame(10.0, $value->getDouble());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::DOUBLE, $value->getType());
    }

    public function testCreateValueBoolean()
    {
        $value = $this->node->setProperty('propBoolean', true);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::BOOLEAN, $value->getType(), 'wrong type');
        $this->assertTrue($value->getBoolean(), 'boolean not true');
        $this->assertTrue($value->getString() == true, 'wrong string value'); //boolean converted to string must be true

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propBoolean');
        $this->assertEquals(\PHPCR\PropertyType::BOOLEAN, $value->getType(), 'wrong type');
        $this->assertTrue($value->getBoolean(), 'boolean not true');
        $this->assertTrue($value->getString() == true, 'wrong string value'); //boolean converted to string must be true
    }

    public function testCreateValueNode()
    {
        $node = $this->session->getNode('/tests_general_base/multiValueProperty');
        $identifier = $node->getIdentifier();
        $value = $this->node->setProperty('propNode', $node);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $value->getType(), 'wrong type');
        $this->assertEquals($node->getIdentifier(), $value->getString(), 'different uuid');
        $this->assertSame($node, $value->getValue());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propNode');
        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $value->getType(), 'wrong type');
        $this->assertEquals($identifier, $value->getString(), 'different uuid');
    }

    public function testCreateValueNodeWeak()
    {
        $node = $this->session->getRootNode()->getNode('tests_general_base/multiValueProperty');

        $identifier = $node->getIdentifier();
        $value = $this->node->setProperty('propNodeWeak', $node, \PHPCR\PropertyType::WEAKREFERENCE);

        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $value->getType());
        $this->assertEquals($node->getIdentifier(), $value->getString());
        $this->assertSame($node, $value->getValue());

        $this->session->save();
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $value->getType());
        $this->assertEquals($identifier, $value->getString());
        $node = $value->getValue();
        $this->assertInstanceOf('PHPCR\\NodeInterface', $node);

        $this->renewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propNodeWeak');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $value->getType());
        $this->assertEquals($identifier, $value->getString());
        $node = $value->getValue();
        $this->assertInstanceOf('PHPCR\\NodeInterface', $node);
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueNodeNonReferenceable()
    {
        $node = $this->session->getRootNode()->getNode('tests_general_base/numberPropertyNode/jcr:content');
        $value = $this->node->setProperty('x', $node);
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueNodeNonReferenceableWeak()
    {
        $node = $this->session->getRootNode()->getNode('tests_general_base/numberPropertyNode/jcr:content');
        $value = $this->node->setProperty('x', $node, \PHPCR\PropertyType::WEAKREFERENCE);
    }

    public function testCreateValueStringType()
    {
        $value = $this->node->setProperty('propString', 33, \PHPCR\PropertyType::STRING);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propString');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());
    }

    public function testCreateValueDateType()
    {
        $time = time();
        $value = $this->node->setProperty('propDate', $time, \PHPCR\PropertyType::DATE);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::DATE, $value->getType());
        $this->assertEquals(date('Y-m-d\TH:i:s.000P', $time), $value->getString());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propDate');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::DATE, $value->getType());
        $this->assertEquals(date('Y-m-d\TH:i:s.000P', $time), $value->getString());
    }

    public function testCreateValueUndefined()
    {
        $value = $this->node->setProperty('propUndefined', 'some value', \PHPCR\PropertyType::UNDEFINED);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertNotEquals(\PHPCR\PropertyType::UNDEFINED, $value->getType(), 'getType should never return UNDEFINED');

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propUndefined');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertNotEquals(\PHPCR\PropertyType::UNDEFINED, $value->getType(), 'getType should never return UNDEFINED');
    }

    public function testCreateValueName()
    {
        $value = $this->node->setProperty('propName', 'jcr:name', \PHPCR\PropertyType::NAME);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::NAME, $value->getType());
        $this->assertEquals('jcr:name', $value->getString());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propName');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::NAME, $value->getType());
        $this->assertEquals('jcr:name', $value->getString());
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueNameInvalidName()
    {
        // "namespace" is not a registered namespace
        $value = $this->node->setProperty('propName', 'namespace:name', \PHPCR\PropertyType::NAME);
        $this->saveAndRenewSession();
    }

    public function testCreateValuePath()
    {
        $value = $this->node->setProperty('propPath', '/some/path', \PHPCR\PropertyType::PATH);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::PATH, $value->getType());
        $this->assertEquals('/some/path', $value->getString());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propPath');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::PATH, $value->getType());
        $this->assertEquals('/some/path', $value->getString());
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValuePathInvalidPath()
    {
        // "Space"/ /" is not a valid path (space)
        $value = $this->node->setProperty('propPath', '/ /', \PHPCR\PropertyType::PATH);
        $this->saveAndRenewSession();
    }

    public function testCreateValueUri()
    {
        $value = $this->node->setProperty('propUri', 'http://some/uri', \PHPCR\PropertyType::URI);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::URI, $value->getType());
        $this->assertEquals('http://some/uri', $value->getString());

        $this->saveAndRenewSession();
        $value = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/propUri');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $value);
        $this->assertEquals(\PHPCR\PropertyType::URI, $value->getType());
        $this->assertEquals('http://some/uri', $value->getString());
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueUriInvalidUri()
    {
        $this->node->setProperty('propUri', '\\This/is\invalid', \PHPCR\PropertyType::URI);
        $this->saveAndRenewSession();
    }

    public function testCopyPropertyString()
    {
        $path = $this->node->getPath();
        $this->node->setProperty('copyPropString', $this->property, PropertyType::STRING);
        $this->saveAndRenewSession();
        $this->assertTrue($this->session->getNode($path)->hasProperty('copyPropString'));
        $prop = $this->session->getNode($path)->getProperty('copyPropString');
        $this->assertEquals(PropertyType::STRING, $prop->getType());
        $this->assertSame('999', $prop->getValue());
    }

    public function testCopyPropertyBinary()
    {
        $path = $this->node->getPath();
        $prop = $this->session->getProperty('/tests_general_base/index.txt/jcr:content/jcr:data');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $prop->getType(), 'Expected binary type');
        $data = $prop->getString();
        $length = $prop->getLength();

        $this->node->setProperty('copyPropBinary', $prop);
        $this->saveAndRenewSession();
        $this->assertTrue($this->session->getNode($path)->hasProperty('copyPropBinary'));
        $newProp = $this->session->getNode($path)->getProperty('copyPropBinary');
        $this->assertEquals(PropertyType::BINARY, $newProp->getType());
        $this->assertEquals($length, $newProp->getLength());
        $this->assertEquals($data, $newProp->getString());
    }
}
