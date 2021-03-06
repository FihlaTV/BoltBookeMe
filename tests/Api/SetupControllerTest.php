<?php
namespace Bolt\Extension\IComeFromTheNet\BookMe\Tests\Api;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpFoundation\Request;

use Bolt\Extension\IComeFromTheNet\BookMe\Tests\Base\ExtensionTest;
use Bolt\Extension\IComeFromTheNet\BookMe\Controller\SetupController;


class SetupControllerTest extends ExtensionTest
{
    
    
    protected $aDatabaseId = [];
    
    
    
   protected function handleEventPostFixtureRun()
   {
      // Create the Calendar 
      $oService = $this->getTestAPI();
    
    
    
      return;
      
      
   }  
   
   
    /**
    * @group Management
    */ 
    public function testSetupController()
    {
       $iCalYear = $this->getNow()->format('Y');
      
       $this->AddTimeslotTest(30);
       $this->AddCalendarYearTest($iCalYear+1);
       $this->ToggleTimeslotTest($this->aDatabaseId['five_minute']);
       
       $meta = $this->getApp()->offsetGet('storage.metadata')->getClassMetadata('bolt_bm_schedule_membership');
       
    }
    
    protected function AddTimeslotTest($iSlotLength)
    {
        $oApp        = $this->getApp();
        $oContainer  = $this->getContainer();
        $aConfig     = $this->getAppConfig();
       
        $oController = new SetupController($aConfig,$oContainer);
        
        $oRequest    = new Request(array(),array('iSlotLength'=>$iSlotLength));
     
        $oController->onAddTimeslotPost($oApp,$oRequest);
     
        $iYearCount = (int) $this->getDatabaseAdapter()->fetchColumn("
                            select COUNT( DISTINCT y.y )  
                            from bolt_bm_timeslot_year y 
                            join bolt_bm_timeslot b on b.timeslot_id = y.timeslot_id 
                            where b.timeslot_length = :iSlotLength"
                            ,[':iSlotLength' => $iSlotLength]);
       
       $this->assertEquals(1,$iYearCount);    
        
    }
    
    protected function AddCalendarYearTest($iCalYear)
    {
        $oApp        = $this->getApp();
        $oContainer  = $this->getContainer();
        $aConfig     = $this->getAppConfig();
       
        $oController = new SetupController($aConfig,$oContainer);
        
        $oRequest = new Request(array(),array('iCalendarYear'=>$iCalYear));
        
        $oController->onAddCalendarPost($oApp,$oRequest);
        
        
        # assert we have new calendar year
        $aDates = $this->getDatabaseAdapter()->fetchArray("select date_format(max(calendar_date),'%Y-%m-%d') as max from bolt_bm_calendar");
        $oMaxDateTime = \DateTime::createFromFormat('Y-m-d',$aDates[0]);
       
        $this->assertEquals($iCalYear.'-12-31', $oMaxDateTime->format('Y-m-d'));
        
        
        # assert timeslots setup in new cal year
        # have one extra timeslot from above test which run first
        $iSlotCount = (int) $this->getDatabaseAdapter()->fetchColumn("select COUNT( DISTINCT timeslot_id )  from bolt_bm_timeslot_year
                                                                      where y = :iNewCalYear" ,[':iNewCalYear' => $iCalYear],0,[]);
        $this->assertEquals(4,$iSlotCount);
    }
    
    
     protected function ToggleTimeslotTest($iSlotId)
    {
        $oApp        = $this->getApp();
        $oContainer  = $this->getContainer();
        $aConfig     = $this->getAppConfig();
       
        $oController = new SetupController($aConfig,$oContainer);
        
        $oRequest = new Request(array(),array('iTimeslotId'=>$iSlotId));
        
        
        // assert disabled slot
        $oController->onTimeslotToggle($oApp,$oRequest);
        
        $bSlotActive = (bool) $this->getDatabaseAdapter()->fetchColumn("select is_active_slot from bolt_bm_timeslot where timeslot_id = :islotId"
                                                             ,[':islotId' => $iSlotId]);
        
        $this->assertFalse($bSlotActive);
        
        
        // assert enabled the slot
        $oController->onTimeslotToggle($oApp,$oRequest);
        
        # assert we have new calendar year
        $bSlotActive = (bool) $this->getDatabaseAdapter()->fetchColumn("select is_active_slot from bolt_bm_timeslot where timeslot_id = :islotId"
                                                             ,[':islotId' => $iSlotId]);
        
        $this->assertTrue($bSlotActive);
        
    }
    
   
    
    
}
/* end of file */
