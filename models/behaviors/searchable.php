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

    private function getText($modelAlias, $dataArray)
    {
        $text = '';
        foreach($dataArray[$modelAlias] as $field => $value)
        {
            if(in_array($field, $this->settings[$modelAlias]['fields']))
            {
                $text .= $value.' ';
            }
        }
        return self::text2Tags($text);
    }

    /**
     * Run after a save() operation. Get data and send to save in search table.
     * @param object $Model Model on which we are saving
     * @param bool $created true when a record is created, and false when a record is updated
     * @access public
     */
    public function afterSave(&$Model,$created)
    {
        App::import('model','Search');
        $Search = new Search;
        $modelId = $Model->id;
        $modelName = $Model->alias;
        if(!$created)
        {
            $Search->deleteAll(array('model'=>$modelName,'content_id'=>$modelId));
        }
        if(!empty($this->settings[$Model->alias]['category']))
        {
            $category = $Model->data[$Model->alias][$this->settings[$Model->alias]['category']];
        }
        else
        {
            $category = '';
        }
        $content = $this->getText($Model->alias, $Model->data);
        $Search->set(array(
            'model' => $modelName,
            'content_id' => $modelId,
            'content' => $content,
            'category' => $category
        ));
        return $Search->save();
    }
}
