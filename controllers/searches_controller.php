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
            $content = strip_tags($_GET['content']);
            $query = explode(' ', $content);
            if(count($query) > 2)
            {
                $fullQuery = '"' . implode(' ', $query) . '"';
            }

            foreach($query as &$word)
            {
                $word = "+{$word}";
            }

            if(!empty($fullQuery))
            {
                $query[] = $fullQuery;
            }

            $query = implode(' ',$query);
            $conditions[] = "MATCH(Search.content) AGAINST('{$query}' IN BOOLEAN MODE)";
            $queryString[] = 'content='.urlencode($content);
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
            $model = strip_tags($_GET['model']);
            $conditions['Search.model'] = $model;
            $queryString[] = 'model='.urlencode($model);
        }
        if(!empty($_GET['page']))
        {
            $queryStringPage = strip_tags($_GET['page']);
            $this->paginate['Search']['page'] = $queryStringPage;
            $page = $queryStringPage;
        }
        if(!empty($_GET['limit']))
        {
            $queryStringLimit = strip_tags($_GET['limit']);
            $this->paginate['Search']['limit'] = $queryStringLimit;
            $queryString[] = 'limit='.urlencode($queryStringLimit);
            $limit = $queryStringLimit;
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
            'rel' => 'DESC',
        );
        $queryString = '?'.implode('&',$queryString);
        $this->set(compact('queryString','page','limit'));
        $this->set('results',$this->paginate('Search',$conditions));
    }
}
