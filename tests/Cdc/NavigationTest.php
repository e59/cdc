<?php

class Cdc_NavigationTest extends PHPUnit_Framework_TestCase
{

    protected $def;

    protected function setUp()
    {
        $this->def = array(
            'google' => array(
                'title'   => 'Google',
                'url'     => '//google.com',
                'classes' => array('outer', 'outertest'),
                'alt' => 'Back to Home', // this is named alt because there is already title, so it's an ALTernative title
                'submenu' => array(
                    'gmail' => array(
                        'title'   => 'Gmail',
                        'url'     => '//gmail.com',
                        'submenu' => array(
                            'inbox' => array(
                                'title'   => 'Inbox',
                                'url'     => '#',
                                'submenu' => array(
                                    'message' => array(
                                        'title' => 'Message 1',
                                        'url'   => '//gmail.com/message-1',
                                    ),
                                )
                            ),
                        ),
                    ),
                ),
            ),
            'disallowed_item' => array(
                'title' => 'disallowed item',
                'url' => '#',
                'allow' => false,
            ),
        );
    }

    public function testEmptyMenuRendersNothing()
    {
        $this->assertEquals('', Cdc_Navigation::menu(array(), 'google'));
    }

    public function testEmptyBreadCrumbRendersNothing()
    {
        $this->assertEquals('', Cdc_Navigation::breadcrumb(array(), 'google'));
    }

    public function testBreadCrumb()
    {
        $this->assertRegExp('#Message 1#', Cdc_Navigation::breadcrumb($this->def, 'message'));
        $this->assertNotRegExp('#Message 1#', Cdc_Navigation::breadcrumb($this->def, 'gmail')); // stop before
        $this->assertRegExp('#Gmail#', Cdc_Navigation::breadcrumb($this->def, 'gmail'));
    }

    public function testMenu()
    {
        $this->assertRegExp('#Message 1#', Cdc_Navigation::menu($this->def, 'message'));
    }

    public function testAltAttribute()
    {
        $this->assertRegExp('#title=\"Back to Home\"#', Cdc_Navigation::menu($this->def, 'message'));
    }

    public function testClassesAttribute()
    {
        $this->assertRegExp('#outer outertest#', Cdc_Navigation::menu($this->def, 'message'));
    }

    public function testBreadcrumbStart()
    {
        $title = 'Breadcrumb Start';
        $breadcrumb_start = array('start' => array('title' => $title, 'url' => '#'));
        $this->assertRegExp("#$title#", Cdc_Navigation::breadcrumb($this->def, 'message', null, $breadcrumb_start));
    }

    public function testBreadcrumbEnd()
    {
        $title = 'Breadcrumb End';
        $breadcrumb_end = array('irrelevant index' => array('title' => $title, 'url' => '#'));
        $this->assertRegExp("#$title#", Cdc_Navigation::breadcrumb($this->def, 'message', null, null, $breadcrumb_end));
    }

    public function testBreadcrumbStartAndEnd()
    {

        $title_start = 'Breadcrumb Start';
        $breadcrumb_start = array('start' => array('title' => $title_start, 'url' => '#'));

        $title_end = 'Breadcrumb End';
        $breadcrumb_end = array('irrelevant index' => array('title' => $title_end, 'url' => '#'));

        $assembled_breadcrumb = Cdc_Navigation::breadcrumb($this->def, 'message', null, $breadcrumb_start, $breadcrumb_end);

        $this->assertRegExp("#$title_start#", $assembled_breadcrumb);
        $this->assertRegExp("#$title_end#", $assembled_breadcrumb);

    }

    public function testAllowAttributeToFalseMakesItemDisappear()
    {
        $this->assertNotRegExp('#disallowed item#', Cdc_Navigation::menu($this->def));
    }

    public function testAllowAttributeToTrueMakesItemAppear()
    {
        $this->def['disallowed_item']['allow'] = true;
        $this->assertRegExp('#disallowed item#', Cdc_Navigation::menu($this->def));
    }

    public function testUlOpenMenu()
    {
        $this->assertRegExp('#^<ul class="testing">#', Cdc_Navigation::menu($this->def, 'google', '<ul class="testing">'));
    }

    public function testUlOpenBreadcrumb()
    {
        $this->assertRegExp('#^<ul class="testing">#', Cdc_Navigation::breadcrumb($this->def, 'google', '<ul class="testing">'));
    }
}
