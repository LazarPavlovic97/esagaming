<?php
namespace App\Models;

use App\Core\Field;
use App\Core\Model;
use App\Validators\NumberValidator;

class NextAttackModel extends Model {
    protected function getFields() {
        return [
            'next_attack_id'            => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11),false),
            'game_id'                   => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11)),
            'next_attack_army_id'       => new Field((new NumberValidator())->setInteger()->setUnsigned()->setMaxIntegerDigits(11))
        ];
    }
}