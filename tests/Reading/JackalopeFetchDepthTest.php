<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Reading;

/**
 * functional tests for Jackalope fetch depth.
 */
class JackalopeFetchDepthTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '05_Reading/jackalopeFetchDepth')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();
        $this->renewSession();
    }

    public function testGetNodeWithFetchDepth()
    {
        if (!$this->session instanceof \Jackalope\Session) {
            return;
        }

        $node = $this->rootNode->getNode('tests_read_jackalope_fetch_depth');

        $this->session->setSessionOption(\Jackalope\Session::OPTION_FETCH_DEPTH, 5);
        $deepExample = $node->getNode('deepExample');
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExample = $deepExample->getNode('deepExample');
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExample = $deepExample->getNode('deepExample');
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExample = $deepExample->getNode('deepExample');
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());
    }

    public function testGetNodesWithFetchDepth()
    {
        if (!$this->session instanceof \Jackalope\Session) {
            return;
        }

        $node = $this->rootNode->getNode('tests_read_jackalope_fetch_depth');

        $this->session->setSessionOption(\Jackalope\Session::OPTION_FETCH_DEPTH, 5);
        $deepExamples = $node->getNodes('deepExample');
        $deepExample = $deepExamples->current();
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExamples = $node->getNodes('deepExample');
        $deepExample = $deepExamples->current();
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExamples = $node->getNodes('deepExample');
        $deepExample = $deepExamples->current();
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExamples = $node->getNodes('deepExample');
        $deepExample = $deepExamples->current();
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());
    }
}
