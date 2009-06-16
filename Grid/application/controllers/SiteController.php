<?php


class SiteController extends Zend_Controller_Action
{

    /**
     * [EN]If a action don't exist, just redirect to the basic
     *
     * @param string $name
     * @param array $var
     */
    function __call($name, $var)
    {
        $this->_redirect ( 'default/site/basic', array ('exit' => 1 ) );
        return false;
    }


    /**
     * [EN] I think this is needed for something. can't remember
     *
     */
    function init()
    { 

        $this->view->url = Zend_Registry::get ( 'config' )->site->url;
    }


    /**
     * Same as __call
     *
     */
    function indexAction()
    {

        $this->_forward ( 'basic' );
    }


    /**
     * Show the source code for this controller
     *
     */
    function codeAction()
    {

        $this->render ( 'code' );
    }


    /**
     * [EN] Simplify the datagrid creation process
     * [EN] Instead of having to write "long" lines of code we can simplify this.
     * [EN] In fact if you have a Class that extends the Zend_Controller_Action
     * [EN] It's not a bad idea put this piece o code there. May be very useful
     * 
     *
     * @return unknown
     */
    function grid()
    {

        $export = $this->getRequest ()->getParam ( 'export' );
        
        $db = Zend_Registry::get ( 'db' );
        
        switch ($export)
        {
            case 'odt' :
                $grid = "Bvb_Grid_Deploy_Odt";
                break;
            case 'ods' :
                $grid = "Bvb_Grid_Deploy_Ods";
                break;
            case 'xml' :
                $grid = "Bvb_Grid_Deploy_Xml";
                break;
            case 'csv' :
                $grid = "Bvb_Grid_Deploy_Csv";
                break;
            case 'excel' :
                $grid = "Bvb_Grid_Deploy_Excel";
                break;
            case 'word' :
                $grid = "Bvb_Grid_Deploy_Word";
                break;
            case 'wordx' :
                $grid = "Bvb_Grid_Deploy_Wordx";
                break;
            case 'pdf' :
                $grid = "Bvb_Grid_Deploy_Pdf";
                break;
            case 'print' :
                $grid = "Bvb_Grid_Deploy_Print";
                break;
            default :
                $grid = "Bvb_Grid_Deploy_Table";
                break;
        }
        
        $grid = new $grid ( $db, 'Grid Example', 'media/temp', array ('download' ) );
        $grid->escapeOutput ( false );
        $grid->addTemplateDir ( 'My/Template/Table', 'My_Template_Table', 'table' );
        $grid->addElementDir ( 'My/Validate', 'My_Validate', 'validator' );
        $grid->addElementDir ( 'My/Filter', 'My_Filter', 'filter' );
        $grid->addFormatterDir ( 'My/Formatter', 'My_Formatter' );
        $grid->imagesUrl = $this->getRequest ()->getBaseUrl () . '/public/images/';
        $grid->cache = array ('use' => 0, 'instance' => Zend_Registry::get ( 'cache' ), 'tag' => 'grid' );
        
        return $grid;
    }

    
    function unionAction()
    {
    	$db = Zend_Registry::get('db');
		   		
		$select1 = "SELECT bug_id as id FROM bugs" ;
		$select2 = "SELECT price as id from products";
		
		$select = $db->select()
					->order('id')
			       ->union(array($select1, $select2));
		    		
		$grid = $this->grid ( 'table' );
		$grid->queryFromZendDbSelect ( $select, $db );
		    	 $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }

    /**
     * A simple action that shows pictures in a complete diferent template
     *
     */
    function imagesAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'images' )
        ->addColumn ( 'url', array ('decorator' => '<a href="{{url}}"><img src="{{url}}" border="0"></a>', 'title' => 'Katie Melua - Image Gallery' ) )
        ->noOrder ( 1 )
        ->setPagination ( 10000 )
        ->setTemplate ( 'images' );
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * An example of a group grid
     *
     */
    function groupAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'crud' )
        ->addColumn ( 'id' )
        ->addColumn ( 'firstname' )
        ->addColumn ( 'lastname', array ('title' => 'Last name (Grouped)' ) )
        ->addColumn ( 'age', array ('sqlexp' => 'ROUND(AVG(age))', 'title' => 'Age Average', 'class' => 'center' ) )
        ->groupby ( 'lastname' )
        ->noFilters ( 1 )
        ->setTemplate ( 'select' );
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * A simple usage of advanced filters. Every time you change a filter, the system automatically  
     *runs a query to the others filters, making sure they don't allow you to filter for a record that is not in the database
     *
     * 
     * We also use SQL expressions and they will appear on the last line (before pagination)
     * The average of LifeExpectancy and to SUM of Population
     */
    function filtersAction()
    {

        $grid = $this->grid ( 'table' );
        
        $grid->from ( 'Country ' )
        ->order ( 'name ASC' )
        ->addColumn ( 'Name', array ('title' => 'Country', 'class' => 'width_200' ) )
        ->addColumn ( 'continent', array ('title' => 'Continent' ) )
        ->addColumn ( 'Population', array ('title' => 'Population', 'class' => 'width_80', 'eval' => "number_format('{{Population}}');" ) )
        ->addColumn ( 'LifeExpectancy', array ('title' => 'Life E.', 'class' => 'width_50' ) )
        ->addColumn ( 'GovernmentForm', array ('title' => 'Government Form', 'searchType' => '=' ) )
        ->addColumn ( 'HeadOfState', array ('title' => 'Head Of State', 'searchType' => '=' ) )
        ->sqlexp ( array ('LifeExpectancy' => 'AVG', 'Population' => 'SUM' ) );
        

        $filters = new Bvb_Grid_Filters ( );
        $filters->addFilter ( 'Name', array ('distinct' => array ('field' => 'Name', 'name' => 'Name' ) ) )
        ->addFilter ( 'continent', array ('distinct' => array ('field' => 'continent', 'name' => 'continent' ) ) )
        ->addFilter ( 'LifeExpectancy', array ('distinct' => array ('field' => 'LifeExpectancy', 'name' => 'LifeExpectancy' ) ) )
        ->addFilter ( 'GovernmentForm', array ('distinct' => array ('field' => 'GovernmentForm', 'name' => 'GovernmentForm' ) ) )
        ->addFilter ( 'HeadOfState' )
        ->addFilter ( 'Population' );
        
        $grid->addFilters ( $filters );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * A join query example
     * 
     * Just don't forget if there is a field with the sdame name in more than one table
     * you must rename the output name of that fielf ba appending AS othername
     *
     */
    function joinsAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ' )
        ->table ( array ('c' => 'Country', 'ct' => 'City' ) )
        ->order ( 'c.Continent' );
        
        $grid->addColumn ( 'c.Name AS Country', array ('title' => 'Country (Capital)', 'class' => 'hideInput', 'decorator' => '{{c.Name}} <em>({{ct.Name}})</em>' ) )
        ->addColumn ( 'ct.Name', array ('title' => 'District', 'hide' => 1 ) )
        ->addColumn('ct.ID',array('hide'=>1))
        ->addColumn ( 'c.Continent', array ('title' => 'Continent', 'class' => 'width_120' ) )
        ->addColumn ( 'c.Population', array ('title' => 'Population', 'class' => 'width_80', 'format' => 'number' ) )
        ->addColumn ( 'c.LifeExpectancy', array ('title' => 'Life E.', 'class' => 'width_50' ) )
        ->addColumn ( 'c.GovernmentForm', array ('title' => 'Government Form' ) )
        ->addColumn ( 'c.HeadOfState', array ('title' => 'Head Of State' ) );
        
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }

    
    function joincrudAction()
    {
        
         
        $grid = $this->grid ( 'table' );
        $grid->from ( 'crud c LEFT JOIN bugs b ON c.id_bug=b.bug_id ' )
        ->table ( array ('c' => 'crud', 'b' => 'bugs' ) )
        ->order ( 'c.id' );
        
        $grid->addColumn ( 'c.id', array ('title' => 'ID',  ) )
        ->addColumn ( 'c.firstname', array ('title' => 'First Name', 'class' => 'width_120' ) )
        ->addColumn ( 'c.lastname', array ('title' => 'Last Name') )
        ->addColumn ( 'c.age', array ('title' => 'Age', 'class' => 'width_50' ) )
        ->addColumn ( 'c.email', array ('title' => 'Email' ) )
        ->addColumn ( 'b.bug_description', array ('title' => 'Bug Description' , 'class' => 'width_100', ) );
        
        
        $form = new Bvb_Grid_Form ( );
        $form->add ( 1 )
        ->edit ( 1 )
        ->primaryKey('c.id')
        ->button ( 1 )
        ->delete ( 1 );
        
        $lang = new Bvb_Grid_Form_Column ( 'c.firstname' );
        $lang->title ( 'First Name' )
        ->validators ( array ('StringLength' => array (3, 10 ) ) ); 
        
        $nome = new Bvb_Grid_Form_Column('c.lastname');
        $nome->title('Last Name');
        
        $continent = new Bvb_Grid_Form_Column('c.age');
        $continent->title('Age');
        
        $region = new Bvb_Grid_Form_Column('b.bug_description');
        $region->title('Bug Description');
        
        $form->addColumns ( $lang,$nome,$continent, $region);
        

        $grid->addForm ( $form );
           
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }

    /**
     * Adding extra columns to a datagrid. They can be at left or right.
     * Also notice that you can use fields values to populate the fields by surrounding the field name with {{}}
     *
     */
    function extraAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ' )
        ->table ( array ('c' => 'Country', 'ct' => 'City' ) )
        ->noFilters ( 1 );
        
        $grid->addColumn ( 'c.name', array ('title' => 'Country (Capital)', 'class' => 'hideInput', 'decorator' => '{{c.name}} <em>({{ct.Name}})</em>' ) );
        $grid->addColumn ( 'ct.Name', array ('title' => 'Capital', 'hide' => 1 ) );
        $grid->addColumn ( 'c.continent', array ('title' => 'Continent' ) );
        $grid->addColumn ( 'c.Population', array ('title' => 'Population', 'class' => 'width_80', 'eval' => "number_format('{{c.Population}}');" ) );
        $grid->addColumn ( 'c.LifeExpectancy', array ('title' => 'Life E.', 'class' => 'width_50' ) );
        $grid->addColumn ( 'c.GovernmentForm', array ('title' => 'Government Form' ) );
        $grid->addColumn ( 'c.HeadOfState', array ('title' => 'Head Of State', 'hide' => 1 ) );
        

        $extra = new Bvb_Grid_ExtraColumns ( );
        $extra->position ( 'right' )
        ->name ( 'Right' )
        ->decorator ( "<input class='input_p'type='text' value=\"{{c.LifeExpectancy}}\" size=\"3\" name='number[]'>" );
        
        $esquerda = new Bvb_Grid_ExtraColumns ( );
        $esquerda->position ( 'left' )
        ->name ( 'Left' )
        ->decorator ( "<input  type='checkbox' name='number[]'>" );
        
        $grid->addExtraColumns ( $extra, $esquerda );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * Performing CRUD operations.
     * 
     * Check how easy it is to set a form.
     *
     */
    function crudAction()
    {

        $db = Zend_Registry::get ( 'db' );
        

        $grid = $this->grid ( 'table' );
        $grid->from ( 'crud' )
        ->order ( 'id DESC ' );
        
        $paises = $db->fetchCol ( "SELECT DISTINCT(Name) FROM Country ORDER BY Name ASC " );
        $language = $db->fetchCol ( "SELECT DISTINCT(Language) FROM CountryLanguage ORDER BY Language ASC" );
        
        $grid->addColumn ( 'id', array ('title' => 'ID', 'hide' => 1 ) );
        $grid->addColumn ( 'firstname AS apelido', array ('title' => 'First Name' ) );
        $grid->addColumn ( 'lastname', array ('title' => 'Last Name' ) );
        $grid->addColumn ( 'email', array ('title' => 'Email' ) );
        $grid->addColumn ( 'age', array ('title' => 'Age' ) );
        $grid->addColumn ( 'language', array ('title' => 'Language' ) );
        $grid->addColumn ( 'date_added', array ('title' => 'Updated', 'format' => array ('date', 'en_US' ), 'class' => 'width_150' ) );
        $grid->addColumn ( 'country', array ('title' => 'Country' ) );
        

        $form = new Bvb_Grid_Form ( );
        $form->add ( 1 )
        ->edit ( 1 )
        ->button ( 1 )
        ->delete ( 1 )
        ->onAddForce ( array ('date_added' => date ( 'Y-m-d H:i:s' ) ) )
        ->onEditForce ( array ('date_added' => date ( 'Y-m-d H:i:s' ) ) );
        

        #->onDeleteCascade(array('table'=>'teste','parentField'=>'age','childField'=>'op','operand'=>'='))
        


        $fAdd = new Bvb_Grid_Form_Column ( 'firstname' );
        $fAdd->title ( 'First name' )
        ->validators ( array ('StringLength' => array (3, 10 ) ) )
        ->filters ( array ('StripTags', 'StringTrim', 'StringToLower' ) )
        ->description ( 'Insert your first name' );
        
        $lastName = new Bvb_Grid_Form_Column ( 'lastname' );
        $lastName->title ( 'Last name' )
        ->description ( 'Your last name' )
        ->validators ( array ('StringLength' => array (3, 10 ) ) );
        
        $country = new Bvb_Grid_Form_Column ( 'country' );
        $country->title ( 'Country' )
        ->description ( 'Choose your Country' )
        ->values ( array_combine ( $paises, $paises ) );
        
        $email = new Bvb_Grid_Form_Column ( 'email' );
        $email->title ( 'Email Address' )
        ->validators ( array ('EmailAddress' ) )
        ->filters ( array ('StripTags', 'StringTrim', 'StringToLower' ) )
        ->description ( 'Insert you email address' );
        

        $lang = new Bvb_Grid_Form_Column ( 'language' );
        $lang->title ( 'Language' )
        ->description ( 'Your language' )
        ->values ( array_combine ( $language, $language ) );
        

        $age = new Bvb_Grid_Form_Column ( 'age' );
        $age->title ( 'Age' )
        ->description ( 'Choose your age' )
        ->values ( array_combine ( range ( 10, 100 ), range ( 10, 100 ) ) );
        
        $form->addColumns ( $fAdd, $lastName, $email, $lang, $country, $age );
        

        $grid->addForm ( $form );
        

        //Add  filters
        $filters = new Bvb_Grid_Filters ( );
        $filters->addFilter ( 'firstname' )
        ->addFilter ( 'lastname' )
        ->addFilter ( 'email' )
        ->addFilter ( 'age', array ('distinct' => array ('name' => 'age', 'field' => 'age' ) ) )
        ->addFilter ( 'country', array ('distinct' => array ('name' => 'country', 'field' => 'country' ) ) )
        ->addFilter ( 'language', array ('distinct' => array ('name' => 'language', 'field' => 'language' ) ) );
        
        $grid->addFilters ( $filters );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * This example shows you how to use a Zend_Db_Select instance to build the grid.
     * 
     *
     */
    function selectAction()
    {

        $grid = $this->grid ( 'table' );
        
        $db = Zend_Registry::get ( 'db' );
        $select = $db->select ()
        ->from ( 'products', array ('product_id', 'product_name', 'price' ) )
        ->where ( 'price > 100.00' );
        
        $grid->queryFromZendDbSelect ( $select, $db );
        $grid->noFilters ( 1 );
        
        $this->view->pages = $grid->deploy ();
        $this->view->action = 'basic';
        $this->render ( 'index' );
    }


    function testAction()
    {

        $grid = $this->grid ( 'table' );
        
        #grid->setDataFromCsv ( 'media/files/grid.csv' );
        #$grid->setDataFromXml ( $url, 'channel,item' );
        #$grid->removeColumns ( array ('guid', 'pubDate', 'link', 'author', 'category', 'title' ) );
        $grid->setDataFromJson('http://services.sapo.pt/JobOffers/JSON',true,'rss,channel,item');
       $grid->setPagination(10);
        
        $this->view->pages = $grid->deploy ();
        
        $this->render ( 'index' );
    }


    /**
     * The 'most' basic example.
     * 
     * Please check the $pdf array to see how we can configure the templates header and footer. 
     * If you are exporting to PDF you can even choose between  a letter format or A4 format, and set the page orientation
     * landascape or '' (empty) for vertical
     *
     */
    function basicAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'City' );
        

        $pdf = array ('logo' => 'public/images/logo.png', 'baseUrl' => '/grid/', 'title' => 'DataGrid Zend Framework', 'subtitle' => 'Easy and powerfull - (Demo document)', 'footer' => 'Downloaded from: http://www.petala-azul.com ', 'size' => 'a4', #letter || a4
'orientation' => 'landscape', # || ''
'page' => 'Page N.' );
        

        $grid->setTemplate ( 'print', 'print', $pdf );
        $grid->setTemplate ( 'pdf', 'pdf', $pdf );
        $grid->setTemplate ( 'word', 'word', $pdf );
        $grid->setTemplate ( 'wordx', 'wordx', $pdf );
        $grid->setTemplate ( 'ods', 'ods', $pdf );
        
        $grid->setPrimaryGrid(false);
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function pdfAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'pdf' );
        

        $pdf = array ('logo' => 'public/images/logo.png', 'baseUrl' => '/grid/', 'title' => 'DataGrid Zend Framework', 'subtitle' => 'Easy and powerfull - (Demo document)', 'footer' => 'Downloaded from: http://www.petala-azul.com ', 'size' => 'a4', #letter || a4
'orientation' => 'landscape', # || ''
'page' => 'Page N.' );
        

        $grid->setTemplate ( 'pdf', 'pdf', $pdf );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * This demonstrates how easy it is for us to use our own templates (Check the grid function at the page top)
     *
     */
    function templateAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->noFilters ( 1 )
        ->from ( 'City' )
        ->setTemplate ( 'outside', 'table' );
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * This example allow you to create an horizontal row, for every distinct value from a field
     *
     */
    function hrowAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ' )
        ->table ( array ('c' => 'Country', 'ct' => 'City' ) )
        ->order ( ' c.Continent, c.Name' );
        #->noFilters(1);
        #->noOrder(1);
        


        $grid->setPagination ( 1200 );
        
        $grid->addColumn ( 'c.Name AS cap', array ('title' => 'Country (Capital)', 'decorator' => '{{c.Name}} <em>({{ct.Name}})</em>' ) );
        $grid->addColumn ( 'ct.Name', array ('title' => 'Capital', 'hide' => 1 ) );
        $grid->addColumn ( 'c.Continent', array ('title' => 'Continent', 'hRow' => 1 ) );
        $grid->addColumn ( 'c.Population', array ('title' => 'Population', 'class' => 'width_80' ) );
        $grid->addColumn ( 'c.LifeExpectancy', array ('title' => 'Life E.', 'class' => 'width_50' ) );
        $grid->addColumn ( 'c.GovernmentForm', array ('title' => 'Government Form' ) );
        $grid->addColumn ( 'c.HeadOfState', array ('title' => 'Head Of State' ) );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * If you don't like to work with array when adding columns, you can work by dereferencing objects
     *
     */
    function columnAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ' )
        ->table ( array ('c' => 'Country', 'ct' => 'City' ) )
        ->order ( ' c.Continent, c.Name' )
        ->setPagination ( 20 );
        #->noFilters(1);
        #->noOrder(1);
        


        $cap = new Bvb_Grid_Column ( 'c.Name AS cap' );
        $cap->title ( 'Country (Capital)' )
        ->decorator ( '{{c.Name}} <em>({{ct.Name}})</em>' );
        
        $name = new Bvb_Grid_Column ( 'ct.Name' );
        $name->title ( 'Capital' )
        ->hide ( 1 );
        
        $continent = new Bvb_Grid_Column ( 'c.Continent' );
        $continent->title ( 'Continent' );
        
        $population = new Bvb_Grid_Column ( 'c.Population' );
        $population->title ( 'Population' )
        ->class ( 'width_80' );
        
        $lifeExpectation = new Bvb_Grid_Column ( 'c.LifeExpectancy' );
        $lifeExpectation->title ( 'Life E.' )
        ->class ( 'width_50' );
        
        $governmentForm = new Bvb_Grid_Column ( 'c.GovernmentForm' );
        $governmentForm->title ( 'Government Form' );
        
        $headState = new Bvb_Grid_Column ( 'c.HeadOfState' );
        $headState->title ( 'Head Of State' );
        
        $grid->addColumns ( $cap, $name, $continent, $population, $lifeExpectation, $governmentForm, $headState );
        

        $filters = new Bvb_Grid_Filters ( );
        $filters->addFilter ( 'c.Name', array ('distinct' => array ('field' => 'c.Name AS cap', 'name' => 'c.Name AS cap' ) ) )
        ->addFilter ( 'ct.Name', array ('distinct' => array ('field' => 'ct.Name', 'name' => 'ct.Name' ) ) )
        ->addFilter ( 'c.Continent', array ('distinct' => array ('field' => 'c.Continent', 'name' => 'c.Continent' ) ) )
        ->addFilter ( 'c.LifeExpectancy', array ('distinct' => array ('field' => 'c.LifeExpectancy', 'name' => 'c.LifeExpectancy' ) ) )
        ->addFilter ( 'c.GovernmentForm', array ('distinct' => array ('field' => 'c.GovernmentForm', 'name' => 'c.GovernmentForm' ) ) )
        ->addFilter ( 'c.HeadOfState' )
        ->addFilter ( 'c.Population' );
        
        $grid->addFilters ( $filters );
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function xmlAction()
    {

        $grid = $this->grid ( 'table' );
        
        $grid->setGridFromXMl ( 'application/grids/Basic' );
        
        $this->view->pages = $grid->deploy ();
        $this->view->action = 'basic';
        $this->render ( 'index' );
    }


    /**
     * [PT]Converter um object para um array.
     * [PT] è necessário quando quisermos faszer o load 
     * [PT]através de um ficheiro XML
     *
     * @param object $object
     * @return array
     */
    function object2array($object)
    {

        $return = NULL;
        if (is_array ( $object ))
        {
            foreach ( $object as $key => $value )
                $return [$key] = self::object2array ( $value );
        } else
        {
            $var = get_object_vars ( $object );
            if ($var)
            {
                foreach ( $var as $key => $value )
                    $return [$key] = self::object2array ( $value );
            } else
            {
                return strval ( $object );
            }
        }
        return $return;
    }
}
