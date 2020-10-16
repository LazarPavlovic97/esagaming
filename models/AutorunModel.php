<?php
namespace App\Models;

use App\Core\Field;
use App\Core\Model;
use App\Validators\NumberValidator;
use App\Validators\StringValidator;

class AutorunModel extends Model {
    protected function getFields() {
        return [
            'autorun_id'            => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11),false),
            'game_id'               => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11)),
            'status'                => new Field((new StringValidator())->setMinLength(0)->setMaxLength(255))
        ];
    }
}