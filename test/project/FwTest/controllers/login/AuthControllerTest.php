<?php

include realpath(dirname(__FILE__) . '/../../') . '/CommonTest.php';
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-07-16 at 13:55:49.
 */
class AuthControllerTest extends CommonTest {
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    public function testAuth()
    {
        $this->authorization();
    }

}
