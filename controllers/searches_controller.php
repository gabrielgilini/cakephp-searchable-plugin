<?php
class SearchesController extends AppController
{
    public $uses = array('Searchable.Search');
    
    function beforeFilter()
    {
        $this->Auth->allow('*');
    }
    
    public function index($query=' ')
    {
        $this->set('results',$this->paginate('Search',array("MATCH(Search.content) AGAINST('$query')")));
    }
}
