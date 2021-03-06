<?php
namespace Bolt\Extension\IComeFromTheNet\BookMe\Schema;

use Bolt\Extension\IComeFromTheNet\BookMe\Model\VirtualColumnTable;


class ScheduleMembershipTable extends VirtualColumnTable
{
    
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
       
        $this->table->addOption('comment','Used to group schedules by externel membership entity');
        
        $this->table->addColumn('membership_id',     'integer',  ['notnull' => true,'comment' =>'Table Primary key', 'autoincrement' => true, 'unsigned' => true ]);
        $this->table->addColumn('registered_date',   'datetime',   ['notnull' => true, 'comment' =>'Date membership was created' ]);
        
        $this->table->addColumn('member_name',   'string',   ['notnull' => true, 'comment' =>'Member Name', 'length' => 100 ]);        
        $this->table->addColumn('bolt_user_id',   'integer',   ['notnull' => false, 'comment' =>'Reference to a bolt user', 'unsigned' => true ]);
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
        $this->table->setPrimaryKey(['membership_id']);
    }
    
    /**
     * Set the table's foreign key constraints.
     */
    protected function addForeignKeyConstraints()
    {
         
    }
}
/* End of Table */