<?php
namespace Bolt\Extension\IComeFromTheNet\BookMe\Bus\Exception;

use Bolt\Extension\IComeFromTheNet\BookMe\BookMeException;
use Bolt\Extension\IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;
use Bolt\Extension\IComeFromTheNet\Bookme\Bus\Command\SlotToggleStatusCommand;
use Bolt\Extension\IComeFromTheNet\Bookme\Bus\Command\RolloverTimeslotCommand;

use League\Tactician\Exception\Exception as BusException;
use Doctrine\DBAL\DBALException;


/**
 * Custom Exception for Validation Middleware.
 * 
 * This is raised when exception fails
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class SlotFailedException extends BookMeException implements BusException
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
    public static function hasFailedToCreateNewTimeslot(SlotAddCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to create new timeslot for '. $oCommand->getSlotLength() .' ', 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedToCreateDays(SlotAddCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to create new timeslot days for '. $oCommand->getSlotLength() .' ', 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedToCreateYear(SlotAddCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to create new timeslot year for '. $oCommand->getSlotLength() .' ', 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedToToggleStatus(SlotToggleStatusCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to toggle timeslot status for '. $oCommand->getTimeSlotId() .' ', 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedToRollover(RolloverTimeslotCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to rollover timeslots for new calyear '. $oCommand->getCalendarYearRollover() .' ', 0 , $oDatabaseException
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