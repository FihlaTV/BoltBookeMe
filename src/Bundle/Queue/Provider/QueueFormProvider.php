<?php
namespace Bolt\Extension\IComeFromTheNet\BookMe\Bundle\Queue\Provider;

use DateTime;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Bolt\Extension\IComeFromTheNet\BookMe\Form\GroupTypeExtension;

use Bolt\Extension\IComeFromTheNet\BookMe\Form\Build\FormContainer;
use Bolt\Extension\IComeFromTheNet\BookMe\Form\Build\FormFieldFactory;
use Bolt\Extension\IComeFromTheNet\BookMe\Form\Build\SchemaFieldFactory;
use Bolt\Extension\IComeFromTheNet\BookMe\Form\JSONArrayBuilder;
use Bolt\Extension\IComeFromTheNet\BookMe\Form\JSONObjectBuilder;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Output\FunctionReferenceType;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Output\StringOutput;
use Bolt\Extension\IComeFromTheNet\BookMe\DataTable\Output\DenseFormat;


/**
 * Loads this Queue Bunles Search Forms.
 *
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 */
class QueueFormProvider implements ServiceProviderInterface
{
    /**
     * @var config
     */ 
    private $config;


    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $aConfig   = $this->config;
        
        $app['bm.form.output'] = function($c) {
            return new StringOutput(new DenseFormat());
        };
 
        //----------------------------------------------------------------------
        # Rules Form
        
        $app['bm.form.queue.jobsearch'] = $app->share(function ($c) use ($aConfig) {
            $oString = $c['bm.form.output'];
            
            $oForm = new FormContainer($oString); 
            
            $oForm->getSchema()->addField('name',SchemaFieldFactory::createStringField($oString)
                                                 ->setTitle('name')
                                                 ->setRequired(true)                 
            
            );
            
            $oForm->getForm()->addField(FormFieldFactory::createTextType($oString)
                                        ->setKey('name')
                                        ->setTitle('Super Key')
                            )
                            ->addField(FormFieldFactory::createSubmitType($oString)
                                        ->setTitle('Click to Submit')
                            );
            
            return $oForm;
            
        });
      
        
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        
          
        
    }
}
/* End of Service Provider */