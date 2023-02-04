<?php

namespace MicroweberPackages\Product\Models;

/**
 * @deprecated
 */


use MicroweberPackages\CustomField\Models\CustomField;
use MicroweberPackages\Product\Scopes\RecurrentScope;

class ProductRecurrent extends CustomField
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new RecurrentScope());
    }

    public function save(array $options = [])
    {
        $this->rel_type = 'content';
        $this->type = 'hidden';

        return parent::save($options);
    }
}
