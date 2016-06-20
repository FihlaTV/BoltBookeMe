<?php
namespace Bolt\Extension\IComeFromTheNet\BookMe\Schema;

use Bolt\Storage\Database\Schema\Table\BaseTable;


class CalendarWeekTable extends BaseTable
{
    
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addOption('comment','Calender table that store the next x years in week aggerates');
     
        
        $this->table->addColumn('y',             'smallint',    ['notnull' => true,'comment' =>'year where date occurs' ]);
        $this->table->addColumn('m',             'smallint',    ['notnull' => true,'comment' => 'month of the year']);
        $this->table->addColumn('w',             'smallint',    ['notnull' => true,'comment' => 'week number in the year']);
 
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['y','w']);
    }
}
/* End of Table */