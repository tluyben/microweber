<?php

namespace MicroweberPackages\Product\Traits;

use MicroweberPackages\Product\Models\ProductRecurrent;

trait CustomFieldRecurrentTrait
{

    private $_addRecurrentField = null;
    private $_removeRecurrentField = null;
    private $_addSpecialRecurrentField = null;

    public function initializeCustomFieldRecurrentTrait()
    {
        $this->fillable[] = 'recurrent'; // related with boot custom fields trait
    }

    public static function bootCustomFieldRecurrentTrait()
    {

        static::saving(function ($model) {
            if ($model->attributes and array_key_exists("recurrent", $model->attributes)) {
                if (isset($model->attributes['recurrent'])) {
                    $model->_addRecurrentField = $model->attributes['recurrent'];
                } else {
                    $model->_removeRecurrentField = true;
                }
                unset($model->attributes['recurrent']);
            }
        });

        static::saved(function ($model) {
            if (isset($model->_addRecurrentField)) {

                $recurrent = ProductRecurrent::where('rel_id', $model->id)->where('rel_type', $model->getMorphClass())->first();
                if (!$recurrent) {
                    $recurrent = new ProductRecurrent();
                    $recurrent->name = 'recurrent';
                    $recurrent->name_key = 'recurrent';
                }

                $recurrentInputVal = trim($model->_addRecurrentField);
                //if (is_numeric($recurrentInputVal)) {
                $recurrent->value = $recurrentInputVal;
                // } else {
                //    $recurrent->value = floatval($recurrentInputVal);
                //}

                $recurrent->rel_id = $model->id;
                $recurrent->save();
            }

            if (isset($model->_removeRecurrentField) and $model->_removeRecurrentField) {
                $recurrent = ProductRecurrent::where('rel_id', $model->id)->where('rel_type', $model->getMorphClass())->first();
                if ($recurrent) {
                    $recurrent->delete();
                }
            }
        });
    }
}
