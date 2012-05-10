<?php
/**
** Behavior for full site search management
*/
class SearchableBehavior extends ModelBehavior
{
    /**
     * Portugueses StopWords to remove from table
     */
    private static $stopwords = array(
        'a', 'à', 'agora', 'ainda', 'alguém', 'algum', 'alguma', 'algumas',
        'alguns', 'ampla', 'amplas', 'amplo', 'amplos', 'ante', 'antes', 'ao',
        'aos', 'após', 'aquela', 'aquelas', 'aquele', 'aqueles', 'aquilo',
        'as', 'até', 'através', 'cada', 'coisa', 'coisas', 'com', 'como',
        'contra', 'contudo', 'da', 'daquele', 'daqueles', 'das', 'de', 'dela',
        'delas', 'dele', 'deles', 'depois', 'dessa', 'dessas', 'desse',
        'desses', 'desta', 'destas', 'deste', 'deste', 'destes', 'deve',
        'devem', 'devendo', 'dever', 'deverá', 'deverão', 'deveria',
        'deveriam', 'devia', 'deviam', 'disse', 'disso', 'disto', 'dito',
        'diz', 'dizem', 'do', 'dos', 'e', 'é', 'ela', 'elas', 'ele', 'eles',
        'em', 'enquanto', 'entre', 'era', 'essa', 'essas', 'esse', 'esses',
        'esta', 'está', 'estamos', 'estão', 'estas', 'estava', 'estavam',
        'estávamos', 'este', 'estes', 'estou', 'eu', 'fazendo', 'fazer',
        'feita', 'feitas', 'feito', 'feitos', 'foi', 'for', 'foram', 'fosse',
        'fossem', 'grande', 'grandes', 'há', 'isso', 'isto', 'já', 'la', 'lá',
        'lhe', 'lhes', 'lo', 'mas', 'me', 'mesma', 'mesmas', 'mesmo', 'mesmos',
        'meu', 'meus', 'minha', 'minhas', 'muita', 'muitas', 'muito', 'muitos',
        'na', 'não', 'nas', 'nem', 'nenhum', 'nessa', 'nessas', 'nesta',
        'nestas', 'ninguém', 'no', 'nos', 'nós', 'nossa', 'nossas', 'nosso',
        'nossos', 'num', 'numa', 'nunca', 'o', 'os', 'ou', 'outra', 'outras',
        'outro', 'outros', 'para', 'pela', 'pelas', 'pelo', 'pelos', 'pequena',
        'pequenas', 'pequeno', 'pequenos', 'per', 'perante', 'pode', 'pôde',
        'podendo', 'poder', 'poderia', 'poderiam', 'podia', 'podiam', 'pois',
        'por', 'porém', 'porque', 'posso', 'pouca', 'poucas', 'pouco',
        'poucos', 'primeiro', 'primeiros', 'própria', 'próprias', 'próprio',
        'próprios', 'quais', 'qual', 'quando', 'quanto', 'quantos', 'que',
        'quem', 'são', 'se', 'seja', 'sejam', 'sem', 'sempre', 'sendo', 'será',
        'serão', 'seu', 'seus', 'si', 'sido', 'só', 'sob', 'sobre', 'sua',
        'suas', 'talvez', 'também', 'tampouco', 'te', 'tem', 'tendo', 'tenha',
        'ter', 'teu', 'teus', 'ti', 'tido', 'tinha', 'tinham', 'toda',
        'todas', 'todavia', 'todo', 'todos', 'tu', 'tua', 'tuas', 'tudo',
        'última', 'últimas', 'último', 'últimos', 'um', 'uma', 'umas', 'uns',
        'vendo', 'ver', 'vez', 'vindo', 'vir', 'vos', 'vós'
    );

    /**
     * Sets up the configuation for the model
     *
     * @param object $Model Model on which we are saving
     * @param mixed $settings
     * @return void
     * @access public
     */
    public function setup(&$Model, $settings) {
        if (!isset($this->settings[$Model->alias])) {
            $this->settings[$Model->alias] = array(
                'fields' => array_keys($Model->_schema),
                'categorySeparator' => ' '
            );
        }
        $this->settings[$Model->alias] = array_merge(
            $this->settings[$Model->alias],
            (array)$settings
        );
    }

    /**
     * Transform text to tags removing repeated and stopwords
     * @param string $text texto to be transformed
     * @return string transformed
     * @access static public
     */
    public static function text2Tags($text) {
        if(empty($text)) return "";
        mb_regex_encoding('UTF-8');
        $wordlist = mb_split('\s*\W+\s*', mb_strtolower($text, 'UTF-8'));
        $tokens = array_count_values($wordlist);
        foreach(array_keys($tokens) as $word) {
            if(mb_strlen($word) < 2) {
                unset($tokens[$word]);
            }
        }
        foreach(self::$stopwords as $word) {
            unset($tokens[$word]);
        }
        arsort($tokens, SORT_NUMERIC);
        return implode(' ', array_keys($tokens));
    }

    /**
     * Get text content from data array. Fields can be filter by setting's array
     * @param string $modelAlias Model's name on which we are saving
     * @param array $dataArray save data array
     * @return string content on tags format
     * @access private
     */
    private function getTextFromDataArray($modelAlias, $dataArray)
    {
        $text = '';
        if(is_array($this->settings[$modelAlias]['fields']))
        {
            foreach($this->settings[$modelAlias]['fields'] as $modelField => $fields)
            {
                foreach($fields as $field)
                {
                    if(!empty($dataArray[$modelField][$field]))
                    {
                        $text .= strip_tags(html_entity_decode($dataArray[$modelField][$field], null, 'UTF-8')).' ';
                    }
                }
            }
        }
        else
        {
            foreach($dataArray[$modelAlias] as $field => $value)
            {
                if(in_array($field, $this->settings[$modelAlias]['fields']))
                {
                    $text .= strip_tags(html_entity_decode($value, null, 'UTF-8')).' ';
                }
            }
        }
        return $text;
    }

    /**
     * Get text category from data array. Fields can be filter by setting's array
     * @param string $modelAlias Model's name on which we are saving
     * @param array $dataArray save data array
     * @return string categoy
     * @access private
     */
    private function getCategoryFromDataArray($modelAlias, $dataArray)
    {
        $category = array();
        if(!empty($this->settings[$modelAlias]['category']))
        {
            if(is_array($this->settings[$modelAlias]['category']))
            {
                foreach($this->settings[$modelAlias]['category'] as $modelField => $fields)
                {
                    foreach($fields as $k => $field)
                    {
                        if(!empty($dataArray[$modelField][$field]))
                        {
                            $category[] = $modelAlias.'-'.$dataArray[$modelField][$field];
                        }
                    }
                }
            }
            else
            {
                foreach($dataArray[$modelAlias] as $field => $value)
                {
                    if(in_array($field, $this->settings[$modelAlias]['category']))
                    {
                        $category[] = $modelAlias.'-'.$value;
                    }
                }
            }
        }
        return implode($this->settings[$modelAlias]['categorySeparator'],$category);
    }

    private function getCreatedFromDataArray($modelAlias, $dataArray)
    {
        $created = date('Y-m-d h:i:s');
        if(!empty($this->settings[$modelAlias]['createdModel']))
        {
            $model = !empty($this->settings[$modelAlias]['createdModel']) ? $this->settings[$modelAlias]['createdModel'] : $modelAlias;
            $created = $dataArray[$model]['created'];
        }
        return $created;
    }
    
    private function _handleDisplayFieldSettings($fieldSettings, $dataArray, $modelAlias)
    {
        $displayField = array();
        
        if(!array_key_exists('model', $fieldSettings))
        {
            foreach($fieldSettings as $model=>$modelsFields)
            {
                foreach($modelsFields as $fields)
                {
                    if(isset($dataArray[$model][$fields]))
                    {
                        $displayField[$fields] = $dataArray[$model][$fields];    
                    }
                    elseif(isset($dataArray[$model][0][$fields]))
                    {
                        $displayField[$fields] = $dataArray[$model][0][$fields];
                    }
                }
            }
        }
        else
        {
            $displayField['display_default'] = $dataArray[$this->settings[$modelAlias]['displayField']['model']][$this->settings[$modelAlias]['displayField']['field']];
        }
        
        return json_encode($displayField);
    }
    
    /**
     * Get display field from data array. Fields can be filter by setting's array
     * @param string $modelAlias Model's name on which we are saving
     * @param array $dataArray save data array
     * @return string display field
     * @access private
     */
    private function getDisplayFieldFromDataArray($modelAlias, $dataArray)
    {
        $displayField = array();
        if(!empty($this->settings[$modelAlias]['displayField']))
        {
            $displayField = $this->_handleDisplayFieldSettings($this->settings[$modelAlias]['displayField'], $dataArray, $modelAlias);
            //$displayField = $dataArray[$this->settings[$modelAlias]['displayField']['model']][$this->settings[$modelAlias]['displayField']['field']];
        }
        return $displayField;
    }

    /**
     * Run after a save() operation. Get data and send to save in search table.
     * @param object $Model Model on which we are saving
     * @param bool $created true when a record is created, and false when a record is updated
     * @access public
     */
    public function afterSave(&$Model,$created)
    {
        //if(!empty($Model->data[$this->settings[$Model->alias]['displayField']['model']][$this->settings[$Model->alias]['displayField']['field']]))
        //debug($Model->data[$this->settings[$Model->alias]]['displayField']);die;
        //if(!empty($Model->data[$this->settings[$Model->alias]['displayField']]))
        //{
            //debug($Model->data[ $this->settings[$Model->alias]['scope']['model'] ][ $this->settings[$Model->alias]['scope']['field'] ]);
            //debug($this->settings[$Model->alias]['scope']['value']);die;
            /*if(
                !empty($this->settings[$Model->alias]['scope']) &&
                $Model->data[ $this->settings[$Model->alias]['scope']['model'] ][ $this->settings[$Model->alias]['scope']['field'] ] != $this->settings[$Model->alias]['scope']['value']
            )
            {
                return true;
            }*/
            
            App::import('model','Searchable.Search');
            $Search = new Search;
            $modelId = $Model->id;
            $modelName = $Model->alias;
            if(!$created)
            {
                $Search->deleteAll(array('model'=>$modelName,'content_id'=>$modelId));
            }
            $category = $this->getCategoryFromDataArray($Model->alias, $Model->data);
            $content = $this->getTextFromDataArray($Model->alias, $Model->data);
            $displayField = $this->getDisplayFieldFromDataArray($Model->alias,$Model->data);
            $created = $this->getCreatedFromDataArray($Model->alias, $Model->data);
            $Search->set(array(
                'model' => $modelName,
                'content_id' => $modelId,
                'content' => $content,
                'category' => $category,
                'display_field' => $displayField,
                'created' => $created
            ));
            return $Search->save();
        //}
    }

    /**
     * Run after a delete() operation. Remove content from search table
     * @param object $Model Model on which we are deleting
     * @access public
     */
    public function beforeDelete(&$Model)
    {
        App::import('model','Searchable.Search');
        $Search = new Search;
        return $Search->deleteAll(array('model'=>$Model->alias,'content_id'=>$Model->id));
    }

}
