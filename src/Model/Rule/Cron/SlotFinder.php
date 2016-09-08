<?php
namespace Bolt\Extension\IComeFromTheNet\BookMe\Model\Rule\Cron;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\DBALException;

use Bolt\Extension\IComeFromTheNet\BookMe\Model\Rule\Command\CreateRuleCommand;
use Bolt\Extension\IComeFromTheNet\BookMe\Model\Rule\Cron\SlotFinderException;
use Bolt\Extension\IComeFromTheNet\BookMe\Model\Rule\Cron\ParsedRange;

/**
 * Convert parsed ranges into slots.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class SlotFinder
{
    
    /**
     * @var Psr\Log\LoggerInterface
     */ 
    protected $oAppLogger;
    
    /**
     * @var Doctrine\DBAL\Connection
     */ 
    protected $oDatabase;
    
    /**
     * @var map of internal database table names to actual names
     */ 
    protected $aTables;
    
    
    /**
     * Create the tmp table that will hold the slot series
     * 
     * @return void 
     */ 
    protected function createTempTable()
    {
        $sSeriesTmpTable = $this->aTables['bm_tmp_rule_series'];
        $oDatabase       = $this->oDatabase;
        
        $oDatabase->query(
            "CREATE TEMPORARY TABLE IF NOT EXISTS $sSeriesTmpTable (
    		      `timeslot_id`       INT NOT NULL COMMENT 'FK to slot table',
                  `y`                 SMALLINT NULL COMMENT 'year where date occurs',
                  `m`                 TINYINT NULL COMMENT 'month of the year',
                  `d`                 TINYINT NULL COMMENT 'numeric date part',
                  `dw`                TINYINT NULL COMMENT 'day number of the date in a week',
                  `w`                 TINYINT NULL COMMENT 'week number in the year',
                  `open_minute`       INT NOT NULL COMMENT 'Closing Minute component',    
                  `close_minute`      INT NOT NULL COMMENT 'Closing Minute component', 
                 
                  `closing_slot`      DATETIME NOT NULL COMMENT 'The closing slot time',
                  `opening_slot`      DATETIME NOT NULL COMMENT 'The opening slot time',
        
                  PRIMARY KEY (`closing_slot`)
    		    
    	    ) 
    	    ENGINE=MEMORY");
    	    
    	$this->oAppLogger->debug('building SlotFinder rule series tmp table'); 
        
        
    }
    
    
    /**
     * Clear the tmp series table for another run
     * 
     * @return void
     */ 
    protected function flushTempTable()
    {
        $sSeriesTmpTable = $this->aTables['bm_tmp_rule_series'];
        $oDatabase       = $this->oDatabase;
        
        $oDatabase->query("DROP TEMPORARY TABLE IF EXISTS $sSeriesTmpTable ");
        
        $this->oAppLogger->debug('flushed all SlotFinder Tmp tables');
        
    }
    
    /**
     * Helper function to split range by their type
     * 
     * @return array(ParsedRange)
     */ 
    protected function extractRanges($sRangeType,$aParsedRanges)
    {
        $aRanges = []; 
        
        foreach($aParsedRanges as $oRange) {
            if($oRange->getRangeType() === $sRangeType) {
                $aRanges[] = $oRange;
            }
        }
        
        return $aRanges;
        
    }
    
    /**
     * Class Constructor
     * 
     * @param LoggerInterface $oAppLogger the application log
     * @param Connection $oDatabase The DBAL Connection
     * @param array $aTables Map of internal table names to actual 
     */ 
    public function __construct(LoggerInterface $oAppLogger, Connection $oDatabase, array $aTables)
    {
        $this->oAppLogger = $oAppLogger;
        $this->oDatabase  = $oDatabase;
        $this->aTables    = $aTables;
        
    }
    
    
   
    
    /**
     * Find the slots that interset the parsed ranges from the repeat cron query
     *  
     * Will only process the DayofMonth,Month and DayofWeek Segments.
     * 
     * @return void
     * @throws SlotFinderException if error occurs or no slots are matched
     */ 
    public function findSlots(CreateRuleCommand $oCommand,  array $aParsedRanges)
    {
        $iTimeslotDatabaseId = $oCommand->getTimeSlotId();
        $oStartFrom          = $oCommand->getCalendarStart();
        $oEndAt              = $oCommand->getCalendarEnd();
        $iOpeningDaySlot     = $oCommand->getOpeningSlot();
        $iClosingDaySlot     = $oCommand->getClosingSlot(); 
        $oDatabase           = $this->oDatabase;
        $oAppLogger          = $this->oAppLogger;
        
        $oAppLogger->debug('Flushing and creating slotFinder result table');
        
        $this->flushTempTable();
        $this->createTempTable();
        
        
        $sSeriesTmpTable =  $this->aTables['bm_tmp_rule_series'];
        $sYearSlotTabale =  $this->aTables['bm_timeslot_year'];
        
        $oDatabase       = $this->oDatabase;
        $aSql            = [];
        $sSql            = '';
        $aBinds          = [
            ':iTimeSlotId'    => $iTimeslotDatabaseId,
            ':sOpeningSlot'   => $oStartFrom->format('dmY'),
            ':sClosingSlot'   => $oEndAt->format('dmY'),
            ':iCalYear'       => $oStartFrom->format('Y'),
            ':iOpenMinute'    => $iOpeningDaySlot,
            ':iCloseMinute'   => $iClosingDaySlot,
        ];
        
        
        $aTypes = [
            ':iTimeSlotId'    => TYPE::getType(TYPE::INTEGER),
            ':sOpeningSlot'   => TYPE::getType(TYPE::STRING),
            ':sClosingSlot'   => TYPE::getType(TYPE::STRING),
            ':iCalYear'       => TYPE::getType(TYPE::INTEGER),
            ':iOpenMinute'    => TYPE::getType(TYPE::INTEGER),
            ':iCloseMinute'   => TYPE::getType(TYPE::INTEGER),
            
        ];

       
     
        // Find all slots between applicability date and in the calender year
        // This will find slots that finish after the current calendar day. (Tail end)
       
        $a1Sql[] =" INSERT INTO $sSeriesTmpTable (`timeslot_id`,`y`,`m`,`d`,`dw`,`w`,`open_minute`,`close_minute`,`closing_slot`,`opening_slot`) ";
        $a1Sql[] =" SELECT `d`.`timeslot_id`, `d`.`y`, `d`.`m`, `d`.`d`, `d`.`dw`, `d`.`w` , `d`.`open_minute`, `d`.`close_minute`,`d`.`closing_slot`, `d`.`opening_slot`";
        $a1Sql[] =" FROM $sYearSlotTabale d ";
        $a1Sql[] =" WHERE  `d`.`timeslot_id` = :iTimeSlotId ";
        $a1Sql[] =" AND date(`d`.`opening_slot`) < DATE_ADD(STR_TO_DATE(:sClosingSlot,'%d%m%Y'), INTERVAL 1 DAY) ";
        $a1Sql[] =" AND date(`d`.`closing_slot`) > STR_TO_DATE(:sOpeningSlot,'%d%m%Y') ";
        $a1Sql[] =" AND `d`.`y` = :iCalYear ";
        $a1Sql[] =" AND `d`.`open_minute` < :iCloseMinute ";
        $a1Sql[] =" AND `d`.`close_minute` >  :iOpenMinute";

        
     
        // This find all the slots between start and finish 
        $a2Sql[] =" REPLACE INTO $sSeriesTmpTable (`timeslot_id`,`y`,`m`,`d`,`dw`,`w`,`open_minute`,`close_minute`,`closing_slot`,`opening_slot`) ";
        $a2Sql[] =" SELECT `d`.`timeslot_id`, `d`.`y`, `d`.`m`, `d`.`d`, `d`.`dw`, `d`.`w` , `d`.`open_minute`, `d`.`close_minute`,`d`.`closing_slot`, `d`.`opening_slot`";
        $a2Sql[] =" FROM $sYearSlotTabale d ";
        $a2Sql[] =" WHERE  `d`.`timeslot_id` = :iTimeSlotId ";
        $a2Sql[] =" AND date(`d`.`opening_slot`) >= STR_TO_DATE(:sOpeningSlot,'%d%m%Y') ";
        $a2Sql[] =" AND date(`d`.`closing_slot`) <= STR_TO_DATE(:sClosingSlot,'%d%m%Y') ";
        $a2Sql[] =" AND `d`.`y` = :iCalYear ";
        $a2Sql[] =" AND `d`.`open_minute` >= :iOpenMinute ";
        $a2Sql[] =" AND `d`.`close_minute` <= :iCloseMinute ";


        // Limit of Months
        if(false === $oCommand->getIsSingleDay()) {
        
            $aMonthRanges = $this->extractRanges(ParsedRange::TYPE_MONTH,$aParsedRanges);
            $aSql[] = " AND ( ";
            foreach($aMonthRanges as $iIndex => $oRange) {
                $sSql = '';
                if($iIndex > 0) {
                    $sSql .= 'OR ( ';    
                } else {
                    $sSql .=  '( ';
                }
                
                $iFill = ($oRange->getRangeClose() - $oRange->getRangeOpen())+1;
          
                
                $aMRanges = array_keys(array_fill($oRange->getRangeOpen(),$iFill,''));
                
                $sSql .= " `d`.`m` IN (".implode(',',$aMRanges).") AND `d`.`m` % ".$oRange->getModValue().' = 0';
                
                $sSql .=  ') ';
                
                $aSql[] = $sSql;    
            }
            $aSql[] = " ) ";
            
            
            // Limit Day of Month Values
            $aDayMonthRanges = $this->extractRanges(ParsedRange::TYPE_DAYOFMONTH,$aParsedRanges);
            $aSql[] = " AND ( ";
            foreach($aDayMonthRanges as $iIndex => $oRange) {
                $sSql = '';
                if($iIndex > 0) {
                    $sSql .= 'OR ( ';    
                } else {
                    $sSql .=  '( ';
                }
                
                $iFill = ($oRange->getRangeClose() - $oRange->getRangeOpen()) +1;
                
                
                $aMRanges = array_keys(array_fill($oRange->getRangeOpen(),$iFill,''));
                
                $sSql .= " `d`.`d` IN (".implode(',',$aMRanges).") AND `d`.`d` % ".$oRange->getModValue().' = 0';
                
                $sSql .=  ') ';
                
                $aSql[] = $sSql;    
            }
            
            
            $aSql[] = " ) ";
     
           
            
            // Limit Day of Week Values which zero based in cron but 1 based in sql
            $aDayWeekRanges = $this->extractRanges(ParsedRange::TYPE_DAYOFWEEK,$aParsedRanges);
            $aSql[] = " AND ( ";
            foreach($aDayWeekRanges as $iIndex => $oRange) {
                $sSql = '';
                if($iIndex > 0) {
                    $sSql .= 'OR ( ';    
                } else {
                    $sSql .=  '( ';
                }
                
                $iFill = ($oRange->getRangeClose() - $oRange->getRangeOpen())+1;
                
                $aMRanges = array_keys(array_fill($oRange->getRangeOpen()+1,$iFill,''));
                
                // dw are 1 based while cron their 0 based
                $sSql .= " (`d`.`dw`) IN (".implode(',',$aMRanges).") AND (`d`.`dw`) % ".$oRange->getModValue().' = 0';
                
                $sSql .=  ') ';
                
                $aSql[] = $sSql;    
            }
           
            $aSql[] = " ) ";
            
            $aWeekYearRanges = $this->extractRanges(ParsedRange::TYPE_WEEKOFYEAR,$aParsedRanges);
            $aSql[] = " AND ( ";
            foreach($aWeekYearRanges as $iIndex => $oRange) {
                $sSql = '';
                if($iIndex > 0) {
                    $sSql .= 'OR ( ';    
                } else {
                    $sSql .=  '( ';
                }
                
                $iFill = ($oRange->getRangeClose() - $oRange->getRangeOpen())+1;
            
                
                $aMRanges = array_keys(array_fill($oRange->getRangeOpen(),$iFill,''));
                
                $sSql .= " `d`.`w` IN (".implode(',',$aMRanges).") AND `d`.`w` % ".$oRange->getModValue().' = 0';
                
                $sSql .=  ') ';
                
                $aSql[] = $sSql;    
            }
            $aSql[] = " ) ";
          
          
               
        }
        
        
        try {
            $oAppLogger->debug('Running slotFinder query table');
           
            $sSql1  =  implode(PHP_EOL,$a1Sql);
            $sSql1 .=  implode(PHP_EOL,$aSql);
    
    
            $iRows1Affected = $oDatabase->executeUpdate($sSql1,$aBinds,$aTypes);
            
            $sSql2  =   implode(PHP_EOL,$a2Sql);
            $sSql2 .=  implode(PHP_EOL,$aSql);
    
            $iRows2Affected = $oDatabase->executeUpdate($sSql2,$aBinds,$aTypes);
            
            
            $iTotalRowsAffected = $iRows1Affected + $iRows2Affected;
            
            if($iTotalRowsAffected == 0) {
                throw SlotFinderException::hasFailedToFindSlots($oCommand);
            }
           
            
            $this->oAppLogger->debug("Slot finder has matched $iTotalRowsAffected slots");
            
        }  catch(DBALException $e) {
            throw SlotFinderException::hasFailedToFindSlotsQuery($oCommand,$e);
        }
        
        return $iRowsAffected;
    }
    
    
}
/* End of class */