<?php
namespace Bolt\Extension\IComeFromTheNet\BookMe\Bus\Exception;

use Bolt\Extension\IComeFromTheNet\BookMe\BookMeException;
use Bolt\Extension\IComeFromTheNet\BookMe\Bus\Command\ToggleScheduleCarryCommand;
use Bolt\Extension\IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use Bolt\Extension\IComeFromTheNet\BookMe\Bus\Command\StopScheduleCommand;
use Bolt\Extension\IComeFromTheNet\BookMe\Bus\Command\RolloverSchedulesCommand;
use Bolt\Extension\IComeFromTheNet\BookMe\Bus\Command\RefreshScheduleCommand;

use League\Tactician\Exception\Exception as BusException;
use Doctrine\DBAL\DBALException;


/**
 * Custom Exception for Schedule Errors.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class ScheduleException extends BookMeException implements BusException
{
    /**
     * @var mixed
     */
    public $oCommand;
    
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedToggleScheduleCarry(ToggleScheduleCarryCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to toggle carry status of a schedule at id '.$oCommand->getScheduleId(), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedStopSchedule(StopScheduleCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to stop schedule and blackout availability for schedule at id  '.$oCommand->getScheduleId(), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedStartSchedule(StartScheduleCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to start schedule and create slots for member at id  '.$oCommand->getMemberId()
            .' For calender year '.$oCommand->getCalendarYear().' using timeslot at id '.$oCommand->getTimeSlotId()
            , 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedResumeSchedule(StopScheduleCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to resume schedule and remove blackout availability for schedule at id  '.$oCommand->getScheduleId(), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedRolloverSchedule(RolloverSchedulesCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to rollover schedules for next calendar year '.$oCommand->getNewCalendarYear()
            , 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedRefreshSchedule(RefreshScheduleCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to refresh schedules at '.$oCommand->getScheduleId()
            , 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * Return the command that has failed validation
     * 
     * @return mixed
     */
    public function getCommand()
    {
        return $this->oCommand;
    }
    
    
}
/* End of File */