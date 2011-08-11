<?php
class SearchesController extends AppController
{
    public $uses = array('Searchable.Search');

    public $paginate = array(
        'Search' => array(
            'limit' => 25,
            'order' => array(
                'Search.created' => 'desc'
            )
        )
    );
    
    function beforeFilter()
    {
        $this->Auth->allow('*');
    }
    
    public function index()
    {
        $conditions = array();
        $queryString = array();
        $page = 1;
        if(!empty($_GET['content']))
        {
            $conditions[] = "MATCH(Search.content) AGAINST('{$_GET['content']}')";
            $queryString[] = 'content='.urlencode($_GET['content']);
        }
        if(!empty($_GET['category']))
        {
            $conditions['Search.category'] = $_GET['category'];
            if(is_array($_GET['category']))
            {
                foreach($_GET['category'] as $category)
                {
                    $queryString[] = 'category[]='.urlencode($category);
                }
            }
            else
            {
                $queryString[] = 'category='.urlencode($_GET['category']);
            }
        }
        if(!empty($_GET['model']))
        {
            $conditions['Search.model'] = $_GET['model'];
            $queryString[] = 'model='.urlencode($_GET['model']);
        }
        if(!empty($_GET['page']))
        {
            $this->paginate['Search']['page'] = $_GET['page'];
            $page = $_GET['page'];
        }
        if(!empty($_GET['limit']))
        {
            $this->paginate['Search']['limit'] = $_GET['limit'];
            $queryString[] = 'limit='.urlencode($_GET['limit']);
        }
        $this->set('page',$page);
        $queryString = '?'.implode('&',$queryString);
        $this->set('queryString',$queryString);
        $this->set('results',$this->paginate('Search',$conditions));
    }
}
