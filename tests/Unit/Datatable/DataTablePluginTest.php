<?php
namespace  Bolt\Extension\IComeFromTheNet\BookMe\Tests\Unit\Datatable;

use Doctrine\DBAL\Types\Type;
use Bolt\Extension\IComeFromTheNet\BookMe\Tests\Base\ExtensionTest;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\DataTableException;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\DataTableEventRegistry;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Plugin\FixedHeaderPlugin;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Plugin\FixedColumnPlugin;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Plugin\ScrollerPlugin;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Plugin\SelectPlugin;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Plugin\ButtonPlugin;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Plugin\Button;

use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\General\AjaxOptions;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Schema\ColumnRenderFunc;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Schema\ColumnRenderOption;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\General\KeyboardKeyOption;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\General\DomFormatOption;




class DataTablePluginTest extends ExtensionTest
{
    
    
    protected function handleEventPostFixtureRun()
    {
        return;
    }  


    public function testFixedHeaderPlugin()
    {
        $oPlugin = new FixedHeaderPlugin();
        
        $oPlugin->setHeaderMode(false);
        $oPlugin->setFooterMode(true);
        $oPlugin->setFooterOffset(100);
        $oPlugin->setHeaderOffset(200);
        
        $aStruct = $oPlugin->getStruct();
        
        $this->assertNotEmpty($aStruct);
        
        $this->assertEquals(false,$aStruct['fixedHeader']['header']);
        $this->assertEquals(true,$aStruct['fixedHeader']['footer']);
        $this->assertEquals(200,$aStruct['fixedHeader']['headerOffset']);
        $this->assertEquals(100,$aStruct['fixedHeader']['footerOffset']);
    }
    

    
    public function testFixedColumnPlugin()
    {
        
        $oPlugin = new FixedColumnPlugin();
        
        $oPlugin->setHeightCalculationAuto();
        $oPlugin->setHeightCalculationCallback('window.func');
        $oPlugin->setNumberFixedRightColumn(2);
        $oPlugin->setNumberFixedLeftColumn(0);
        
        $aStruct = $oPlugin->getStruct();
        
        $this->assertEquals(0,$aStruct['fixedColumns']['iLeftColumns']);
        $this->assertEquals(2,$aStruct['fixedColumns']['iRightColumns']);
        $this->assertEquals('window.func',$aStruct['fixedColumns']['fnDrawCallback']->getValue());
        $this->assertEquals('auto',$aStruct['fixedColumns']['sHeightMatch']);
    }
    
    public function testFixedScrollerPlugin()
    {
        
        $oPlugin = new ScrollerPlugin();
        
        $oPlugin->setUseTrace(true);
        $oPlugin->setRowHeight(100);
        $oPlugin->setUseLoadingIndicator(true);
        $oPlugin->setDisplayBuffer(200);
        $oPlugin->setBoundryScale(1.0);
        
        $aStruct = $oPlugin->getStruct();
        
        $this->assertEquals(true,$aStruct['scroller']['trace']);
        $this->assertEquals(100,$aStruct['scroller']['rowHeight']);
        $this->assertEquals(true,$aStruct['scroller']['loadingIndicator']);
        $this->assertEquals(200,$aStruct['scroller']['displayBuffer']);
        $this->assertEquals(1.0,$aStruct['scroller']['boundaryScale']);
        
    }
    
    public function testSelectPlugin()
    {
        
        $oPlugin = new SelectPlugin();
        
        
        $oPlugin->setSelectStyleSingleRow();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('single',$aStruct['select']['style']);
        
        $oPlugin->setSelectStyleDefault();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('api',$aStruct['select']['style']);
        
        $oPlugin->setSelectStyleHybrid();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('multi+shift',$aStruct['select']['style']);
        
        $oPlugin->setSelectStyleOS();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('os',$aStruct['select']['style']);
        
        $oPlugin->setSelectStyleMultiRow();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('multi',$aStruct['select']['style']);
        
        $oPlugin->setSelectStyleSingleRow();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('single',$aStruct['select']['style']);
        
        $oPlugin->setItemRows();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('row',$aStruct['select']['items']);
        
        
        $oPlugin->setItemColumns();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('column',$aStruct['select']['items']);
    
        $oPlugin->setItemCells();
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('cell',$aStruct['select']['items']);
        
        $oPlugin->setBlurable(true);
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals(true,$aStruct['select']['blurable']);
        
        $oPlugin->setSelectCssClassName('bob');
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('bob',$aStruct['select']['className']);
        
        $oPlugin->setSelectedInfoDisplayed(false);
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals(false,$aStruct['select']['info']);
        
        $oPlugin->setSelectorFilter('.test1');
        $aStruct = $oPlugin->getStruct();
        $this->assertEquals('.test1',$aStruct['select']['selector']);
        
    }   
    
    public function testGeneralAjaxOptions()
    {
        
        $oPlugin = new AjaxOptions();
       
        $sUrl = 'https://www.icomefromthenet.com/data';
        $sMethod = 'POST';
        $sDataType = 'HTML';
        
       
        $oPlugin->setDataUrl($sUrl);
        $oPlugin->setHttpRequestMethod($sMethod);
        $oPlugin->setResponseDataType($sDataType);
        $oPlugin->setRequestParam('index1',1);
        
        $aStruct = $oPlugin->getStruct();
       
        $this->assertEquals($sUrl,$aStruct['ajax']['url']);
        $this->assertEquals($sMethod,$aStruct['ajax']['method']);
        $this->assertEquals($sDataType,$aStruct['ajax']['dataType']);
        $this->assertEquals(1,$aStruct['ajax']['data']['index1']);
    }
       
  
    public function testSchemaColumnRenderWithFunc()
    {
        
        $oPlugin = new ColumnRenderFunc('window.func');
        
        $aStruct = $oPlugin->getStruct();
       
        $this->assertEquals('window.func',$aStruct['render']->getValue());
        
    }
    
    
    public function testGeneralColumnRenderOption()
    {
        
        $oPlugin = new ColumnRenderOption();
       
        $sDefault = 'column_c';
        $sFilter = 'column_a';
        $sDisplay = 'column_b';
        
       
        $oPlugin->setDisplayIndex($sDisplay);
        $oPlugin->setFilterIndex($sFilter);
        $oPlugin->setDefaultIndex($sDefault);
        
        $aStruct = $oPlugin->getStruct();
       
        $this->assertEquals($sDefault,$aStruct['render']['_']);
        $this->assertEquals($sDisplay,$aStruct['render']['display']);
        $this->assertEquals($sFilter,$aStruct['render']['filter']);
        
    }
    
    
    public function testEventRegistry()
    {
        $oRegistry = new DataTableEventRegistry();
        
        # test event not found
        $this->assertFalse($oRegistry->hasEventRegistered('aaa','window.func'));
        
        # test add event
        $oRegistry->addEvent('aaa','window.func','#aa');
        
        $this->assertTrue($oRegistry->hasEventRegistered('aaa','window.func'));
        
        # test the get method
        
        $aEvents = $oRegistry->getRegistry();
        
        $this->assertCount(1,$aEvents);
        
        # test remove event
        
        $oRegistry->removeEvent('aaa','window.func');
        
        $aEvents = $oRegistry->getRegistry();
        
        $this->assertCount(0,$aEvents);
        
        # test exception thrown when removing not exists event
        try {
            $oRegistry->removeEvent('aaa','window.func');
            $this->assertFalse(true,'exception expected to be thrown when event does not exist');
        } catch(DataTableException $e) {
            $this->assertTrue(true);
        }
        
        # Test add multiple
        
        $oRegistry->addEvent('aaa','window.funcA');
        $oRegistry->addEvent('aaa','window.funcB');
        
        $aEvents = $oRegistry->getRegistry();
        
        $this->assertCount(2,$aEvents);
      
    }
    
    
    public function testKeybordKeyOption()
    {
        $oOption = new KeyboardKeyOption();
        
        // Test The Basic Key Option
        
        $oOption->setKeyboardKey('a');
        
        $this->assertEquals([
           'shiftKey' => false,
           'altKey'  => false,
           'ctrlKey' => false,
           'metaKey' => false,
           'key'     => 'a',
            
        ],$oOption->getStruct());
        
        
        // Test Alt Key
        
        $oOption->setRequiresAltKey(true);
        
        $this->assertEquals([
           'shiftKey' => false,
           'altKey'  => true,
           'ctrlKey' => false,
           'metaKey' => false,
           'key'     => 'a',
            
        ],$oOption->getStruct());
        
        // Test Shift Key
        
        $oOption->setRequiresShiftKey(true);
        
        $this->assertEquals([
           'shiftKey' => true,
           'altKey'  => true,
           'ctrlKey' => false,
           'metaKey' => false,
           'key'     => 'a',
            
        ],$oOption->getStruct());
        
        // Test the ctrl key
        
        
        $oOption->setRequiresCtrlKey(true);
        
        $this->assertEquals([
           'shiftKey' => true,
           'altKey'  => true,
           'ctrlKey' => true,
           'metaKey' => false,
           'key'     => 'a',
            
        ],$oOption->getStruct());
        
        // Test Meta Key
        
        $oOption->setRequiresMetaKey(true);
        
        $this->assertEquals([
           'shiftKey' => true,
           'altKey'  => true,
           'ctrlKey' => true,
           'metaKey' => true,
           'key'     => 'a',
            
        ],$oOption->getStruct());
        
    }
    
    
    public function testButtonsPlugin()
    {
        // Test Button Callbacks
        
        $oActionCallback = new Button\ActionCallback('window.func');
        
        $aStruct = $oActionCallback->getStruct();
       
        $this->assertEquals('window.func',$aStruct['action']->getValue());
        
        
        // Test Init Callback
        
        $oInitCallback = new Button\InitCallback('windows.func');
        
        $aStruct = $oInitCallback->getStruct();
       
        $this->assertEquals('windows.func',$aStruct['init']->getValue());
     
     
        // Test Standard Button
        
        $oButton = new Button\StandardButton();
        
        $oButton->setButtonText('Delete');
        $oButton->setButtonSelector('buttonA');
        $oButton->setInitialEnableState(false);
        $oButton->setCSSClassName('newButton');
       
        $oKeyOption = new KeyboardKeyOption();
        $oKeyOption->setKeyboardKey('a');
        $oButton->setKeyboardKey($oKeyOption);
        
        $this->assertEquals([
            'key' => [
             'shiftKey' => false,
             'altKey'  => false,
             'ctrlKey' => false,
             'metaKey' => false,
             'key'     => 'a',    
            ],
            'text'     => 'Delete',
            'name'     => 'buttonA',
            'enabled'  => false,
            'className' => 'newButton',
            'action'   => null,
            'init'     => null,
            'crudLinks' => null,
            
        ],$oButton->getStruct());
        
        
        $oButton->setActionCallback($oActionCallback);
        $oButton->setInitCallback($oInitCallback);
    
        $aStruct = $oButton->getStruct();
        
    
        $this->assertEquals('windows.func',$aStruct['init']->getValue());
        $this->assertEquals('window.func',$aStruct['action']->getValue());
        
        
        // Test Button Collection Plugin
        
        $oPlugin = new ButtonPlugin();
        
        $oPlugin->addButton('a',$oButton);
        
        $this->assertEquals($oButton,$oPlugin->getButton('a'));
        $this->assertEquals(null,$oPlugin->getButton('aaa'));
        
        $aStruct = $oPlugin->getStruct();
        
        $this->assertTrue(isset($aStruct['buttons']['buttons'][0]));
        
    }
    
    
    public function testDomFormatOption()
    {
        $oOption = new DomFormatOption(DomFormatOption::TABLE_INFO_OPTION);
        
        $this->assertEquals(['dom' =>    DomFormatOption::TABLE_INFO_OPTION  ],$oOption->getStruct());
    }
    
    
}
/* end of file */
