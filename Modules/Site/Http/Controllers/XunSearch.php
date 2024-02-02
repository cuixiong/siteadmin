<?php

namespace Modules\Site\Http\Controllers;
use Modules\Site\Http\Controllers\CrudController;
use XS;
use XSDocument;

class XunSearch extends CrudController
{
    public function add()
    {
        $xs = new XS('ProductRoutine');
        $data = [
            'id' => 1,
            'name' => 'test',
            'english_name' => 'english_test',
            'keywords' => 'keywords_test',
        ];
        $doc = new XSDocument();
        $doc->setFields($data);
        $xs->index->add($doc);
    }
}
