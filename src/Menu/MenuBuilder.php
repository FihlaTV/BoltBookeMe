<?php
namespace Bolt\Extension\IComeFromTheNet\BookMe\Menu;



class MenuBuilder implements \IteratorAggregate
{
    
    use SortMenuTrait;
    
    
    protected $aMenuItems;
    
    
    /**
     * This function can be used by children to do their menu configs
     * 
     * This is called by the constructor of this class.
     * 
     * @return void
     */ 
    protected function setDefaults()
    {
        
        
    }
    
    public function __construct()
    {
        $this->aMenuItems = [];
        
        $this->setDefaults();
        
    }
    
    public function addMenuGroup(MenuGroup $oMenuGroup)
    {
        $this->aMenuItems[] = $oMenuGroup;
        
    }
    
    public function getMenuGroups()
    {
        $this->sortMenu();
        
        return $this->getIterator();
    }
    
    
    public function validate()
    {
        # pass one validate the groups
        foreach($this->getIterator() as $oMenuGroup) {
            $oMenuGroup->validate();
        }
        
        # pass two validate the item
        foreach($this->getIterator() as $oMenuGroup) {
           foreach($oMenuGroup as $oMenuItem) {
                $oMenuItem->validate();   
           }
            
        }
        
        return true;
    }
    
    /**
     * Used to set a query param on all menu items
     *  
     * @param string    $sParamName
     * @param mixed     $sParamValue
     */ 
    public function addQueryParam($sParamName, $sParamValue) 
    {
        foreach($this->getIterator() as $oMenuGroup) {
           foreach($oMenuGroup as $oMenuItem) {
                $oMenuItem->getQueryParams()->set($sParamName,$sParamValue);  
           }
            
        }
        
    }
    
    
   //-------------------------------------------------------------------------
    #IteratorAggregate
    
    public function getIterator()
    {
        return new \ArrayIterator($this->aMenuItems);
    }
    
    
    
    //-----------------------------------------------------------------------
    # Visitor
    
    public function visit(MenuVisitorInterface $oVisitor)
    {
        foreach($this->aMenuItems as $oItem){
            $oItem->visit($oVisitor);
        }
        
    }
    
}
/* End of class */ 