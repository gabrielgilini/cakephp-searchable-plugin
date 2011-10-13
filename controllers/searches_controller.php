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
        $limit = 25;
        $query = '';
        if(!empty($_GET['content']))
        {
            $query = explode(' ',$_GET['content']);
            foreach($query as &$word)
            {
                $word = "+{$word}*";
            }
            $query = implode(' ',$query);
            $conditions[] = "MATCH(Search.content) AGAINST('{$query}' IN BOOLEAN MODE)";
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
            $limit = $_GET['limit'];
        }
        $this->paginate['Search']['fields'] = array(
            'Search.id',
            'Search.model',
            'Search.content_id',
            'Search.category',
            'Search.content',
            'Search.display_field',
            'Search.created',
            "MATCH(Search.content) AGAINST ('{$query}' IN BOOLEAN MODE) AS rel"
        );
        $this->paginate['Search']['order'] = array(
            'Search.created' => 'DESC',
            'rel' => 'DESC'
        );
        $queryString = '?'.implode('&',$queryString);
        $this->set(compact('queryString','page','limit'));
        $this->set('results',$this->paginate('Search',$conditions));
    }
}
